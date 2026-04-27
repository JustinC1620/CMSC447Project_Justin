<?php
const IMPORT_SECTIONS        = ["subjects", "courses", "wp_users", "schedule"];
const IMPORT_TRANSIENT_TTL   = 600; // 10 minutes
const VALID_DAYS_FULL        = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"];
const VALID_ROLES_IMPORT     = [TUTOR_ROLE, STAFF_ROLE];

// Import / Export REST Routes
//---------------------------------------------------------------------------------------------------------------------
{
    add_action("rest_api_init", function() {

        register_rest_route("asc-tutoring/v1", "/import/validate", [
            "methods"             => "POST",
            "callback"            => "import_validate",
            "permission_callback" => function() { return current_user_can("admin_control"); },
        ]);

        register_rest_route("asc-tutoring/v1", "/import/confirm", [
            "methods"             => "POST",
            "callback"            => "import_confirm",
            "permission_callback" => function() { return current_user_can("admin_control"); },
            "args"                => [
                "token" => [
                    "required"          => true,
                    "sanitize_callback" => "sanitize_text_field",
                ],
            ],
        ]);

        register_rest_route("asc-tutoring/v1", "/import/export", [
            "methods"             => "GET",
            "callback"            => "import_export",
            "permission_callback" => function() { return current_user_can("admin_control"); },
        ]);

        register_rest_route("asc-tutoring/v1", "/import/template", [
            "methods"             => "GET",
            "callback"            => "import_template",
            "permission_callback" => function() { return current_user_can("admin_control"); },
        ]);
    });
}
//---------------------------------------------------------------------------------------------------------------------

// Import Helpers
//---------------------------------------------------------------------------------------------------------------------
{
    /**
     * Parse the raw CSV file handle into named sections.
     * Returns ['sections' => [...], 'errors' => [...]]
     *
     * Section format:
     *   'subjects' => [ ['subject_code'=>..., 'subject_name'=>...], ... ]
     *   'courses'  => [ ['course_subject'=>..., 'course_code'=>..., 'course_name'=>...], ... ]
     *   'wp_users' => [ ['first_name'=>..., 'last_name'=>..., 'umbc_id'=>..., 'umbc_email'=>..., 'roles'=>...], ... ]
     *   'schedule' => [ ['umbc_id'=>..., 'course_subject'=>..., 'course_code'=>..., 'day_of_week'=>..., 'start_time'=>..., 'end_time'=>...], ... ]
     */
    function import_parse_csv($file_path) {
        $errors   = [];
        $sections = [];

        $handle = fopen($file_path, "r");
        if ($handle === false) {
            return ["sections" => [], "errors" => [["section" => "file", "row" => null, "message" => "Could not open uploaded file."]]];
        }

        $current_section = null;
        $headers         = null;
        $row_num         = 0;   // line number in file (for error messages)
        $section_row     = 0;   // data row within the current section

        while (($raw = fgetcsv($handle)) !== false) {
            $row_num++;

            // Skip completely blank lines
            if ($raw === [null] || $raw === [""]) {
                continue;
            }

            $first = trim($raw[0]);

            // Section header line
            if (in_array($first, IMPORT_SECTIONS, true)) {
                $current_section = $first;
                $section_row     = 0;
                $headers         = null;
                $sections[$current_section] = [];
                continue;
            }

            // Before any section header is seen, skip
            if ($current_section === null) {
                continue;
            }

            // First row after section header = column headers
            if ($headers === null) {
                $headers = array_map("trim", $raw);
                continue;
            }

            // Data row
            $section_row++;

            // Trim all values and combine with headers
            $raw_trimmed = array_map("trim", $raw);

            if (count($headers) !== count($raw_trimmed)) {
                $errors[] = [
                    "section" => $current_section,
                    "row"     => $section_row,
                    "message" => "Column count mismatch: expected " . count($headers) . " columns, got " . count($raw_trimmed) . ".",
                ];
                continue;
            }

            $data = array_combine($headers, $raw_trimmed);
            $sections[$current_section][] = $data;
        }

        fclose($handle);

        return ["sections" => $sections, "errors" => $errors];
    }

    /**
     * Convert a time string from the CSV format ("11:00 a.m.", "Noon", etc.)
     * to H:i:s. Returns null on failure.
     */
    function import_convert_time($time_str) {
        $time_str = trim($time_str);
        if ($time_str === "" || strtolower($time_str) === "null") {
            return null;
        }
        if (strtolower($time_str) === "noon") {
            return "12:00:00";
        }
        if (strtolower($time_str) === "midnight") {
            return "00:00:00";
        }
        // Normalise "a.m." / "p.m." -> "am" / "pm"
        $normalised = strtolower(str_replace(["a.m.", "p.m.", "."], ["am", "pm", ""], $time_str));
        $normalised = preg_replace('/\s+/', ' ', trim($normalised));
        $dt = DateTime::createFromFormat("g:i a", $normalised)
           ?: DateTime::createFromFormat("g a", $normalised);
        if ($dt === false) {
            return null;
        }
        return $dt->format("H:i:s");
    }

    /**
     * Parse a roles cell from the CSV.
     * The cell may contain a single role or two roles separated by " | "
     * to avoid clashing with the comma delimiter.
     * Returns an array of role strings, or WP_Error on invalid input.
     */
    function import_parse_roles($raw_roles) {
        // Roles are separated by " | " in the CSV to avoid CSV comma conflicts
        $parts = array_map("trim", explode("|", $raw_roles));
        $parts = array_filter($parts, fn($r) => $r !== "");
        return array_values($parts);
    }

    /**
     * Validate all sections. Returns array of error objects.
     * Also returns a normalised $parsed array (times converted, day abbr converted, etc.)
     *
     * $umbc_data is fetched once: ['subjects'=>[code=>true], 'courses'=>[subject_code=>[code=>id]], 'accounts'=>[umbc_id=>[...]]]
     */
    function import_validate_sections(&$sections, $umbc_data) {
        $errors = [];

        // ---- subjects ----
        $subject_codes_in_csv = [];
        if (isset($sections["subjects"])) {
            foreach ($sections["subjects"] as $i => &$row) {
                $r = $i + 1;

                if (empty($row["subject_code"])) {
                    $errors[] = import_err("subjects", $r, "subject_code", "subject_code is required.");
                    continue;
                }
                if (empty($row["subject_name"])) {
                    $errors[] = import_err("subjects", $r, "subject_name", "subject_name is required.");
                }

                $code = $row["subject_code"];

                // Must exist in umbc_db
                if (!isset($umbc_data["subjects"][$code])) {
                    $errors[] = import_err("subjects", $r, "subject_code", "\"$code\" does not exist in the UMBC database.");
                    continue;
                }

                // Must match the name in umbc_db exactly
                if ($umbc_data["subjects"][$code] !== $row["subject_name"]) {
                    $errors[] = import_err("subjects", $r, "subject_name",
                        "subject_name \"" . $row["subject_name"] . "\" does not match the UMBC database (expected \"" . $umbc_data["subjects"][$code] . "\").");
                }

                if (isset($subject_codes_in_csv[$code])) {
                    $errors[] = import_err("subjects", $r, "subject_code", "Duplicate subject_code \"$code\".");
                } else {
                    $subject_codes_in_csv[$code] = true;
                }
            }
            unset($row);
        }

        // ---- courses ----
        // key: "SUBJECT_CODE" => true  (courses present in this CSV)
        $courses_in_csv = [];
        if (isset($sections["courses"])) {
            foreach ($sections["courses"] as $i => &$row) {
                $r = $i + 1;

                foreach (["course_subject", "course_code", "course_name"] as $field) {
                    if (empty($row[$field])) {
                        $errors[] = import_err("courses", $r, $field, "$field is required.");
                    }
                }

                $subj = $row["course_subject"] ?? "";
                $code = $row["course_code"]    ?? "";
                $name = $row["course_name"]    ?? "";
                $key  = $subj . "_" . $code;

                // subject must be in this CSV's subjects section
                if (!isset($subject_codes_in_csv[$subj])) {
                    $errors[] = import_err("courses", $r, "course_subject",
                        "course_subject \"$subj\" is not present in the subjects section of this CSV.");
                }

                // Must exist in umbc_db
                if (!isset($umbc_data["courses"][$subj][$code])) {
                    $errors[] = import_err("courses", $r, "course_subject/course_code",
                        "Course \"$subj $code\" does not exist in the UMBC database.");
                } else {
                    $umbc_course = $umbc_data["courses"][$subj][$code];
                    if ($umbc_course["course_name"] !== $name) {
                        $errors[] = import_err("courses", $r, "course_name",
                            "course_name \"$name\" does not match UMBC database (expected \"" . $umbc_course["course_name"] . "\").");
                    }
                    // Stamp the real course_id from umbc_db onto the row
                    $row["course_id"] = $umbc_course["course_id"];
                }

                if (isset($courses_in_csv[$key])) {
                    $errors[] = import_err("courses", $r, "course_subject/course_code", "Duplicate course \"$subj $code\".");
                } else {
                    $courses_in_csv[$key] = true;
                }
            }
            unset($row);
        }

        // ---- wp_users ----
        // key: umbc_id => wp user data (for schedule FK lookup)
        $users_in_csv = [];
        if (isset($sections["wp_users"])) {
            foreach ($sections["wp_users"] as $i => &$row) {
                $r = $i + 1;

                foreach (["first_name", "last_name", "umbc_id", "umbc_email", "roles"] as $field) {
                    if (!isset($row[$field]) || $row[$field] === "") {
                        $errors[] = import_err("wp_users", $r, $field, "$field is required.");
                    }
                }

                $umbc_id    = $row["umbc_id"]    ?? "";
                $umbc_email = $row["umbc_email"]  ?? "";
                $first_name = $row["first_name"]  ?? "";
                $last_name  = $row["last_name"]   ?? "";

                // Validate format
                if ($umbc_id !== "" && !preg_match("/^[A-Z]{2}\d{5}$/", $umbc_id)) {
                    $errors[] = import_err("wp_users", $r, "umbc_id",
                        "umbc_id \"$umbc_id\" must be two uppercase letters followed by five digits (e.g. AB12345).");
                }
                if ($umbc_email !== "" && (!is_email($umbc_email) || !str_ends_with(strtolower($umbc_email), "@umbc.edu"))) {
                    $errors[] = import_err("wp_users", $r, "umbc_email",
                        "umbc_email \"$umbc_email\" must be a valid @umbc.edu address.");
                }

                // Validate and parse roles
                $roles_raw    = $row["roles"] ?? "";
                $roles_parsed = import_parse_roles($roles_raw);
                $roles_valid  = true;

                if (count($roles_parsed) === 0) {
                    $errors[] = import_err("wp_users", $r, "roles", "At least one role is required.");
                    $roles_valid = false;
                }

                foreach ($roles_parsed as $role) {
                    if (!in_array($role, VALID_ROLES_IMPORT, true)) {
                        $errors[] = import_err("wp_users", $r, "roles",
                            "\"$role\" is not a valid import role. Only \"" . TUTOR_ROLE . "\" and \"" . STAFF_ROLE . "\" are allowed.");
                        $roles_valid = false;
                    }
                }

                if ($roles_valid) {
                    $row["roles_parsed"] = $roles_parsed;
                }

                // Must exist in umbc_db with all matching fields
                if ($umbc_id !== "" && isset($umbc_data["accounts"][$umbc_id])) {
                    $acc = $umbc_data["accounts"][$umbc_id];
                    if ($acc["first_name"] !== $first_name) {
                        $errors[] = import_err("wp_users", $r, "first_name",
                            "first_name \"$first_name\" does not match UMBC database (expected \"" . $acc["first_name"] . "\").");
                    }
                    if ($acc["last_name"] !== $last_name) {
                        $errors[] = import_err("wp_users", $r, "last_name",
                            "last_name \"$last_name\" does not match UMBC database (expected \"" . $acc["last_name"] . "\").");
                    }
                    if (strtolower($acc["umbc_email"]) !== strtolower($umbc_email)) {
                        $errors[] = import_err("wp_users", $r, "umbc_email",
                            "umbc_email \"$umbc_email\" does not match UMBC database (expected \"" . $acc["umbc_email"] . "\").");
                    }
                } elseif ($umbc_id !== "") {
                    $errors[] = import_err("wp_users", $r, "umbc_id",
                        "umbc_id \"$umbc_id\" does not exist in the UMBC database.");
                }

                if (isset($users_in_csv[$umbc_id])) {
                    $errors[] = import_err("wp_users", $r, "umbc_id", "Duplicate umbc_id \"$umbc_id\".");
                } else {
                    $users_in_csv[$umbc_id] = $row;
                }
            }
            unset($row);
        }

        // ---- schedule ----
        if (isset($sections["schedule"])) {
            foreach ($sections["schedule"] as $i => &$row) {
                $r = $i + 1;

                foreach (["umbc_id", "course_subject", "course_code", "day_of_week", "start_time", "end_time"] as $field) {
                    if (!isset($row[$field]) || $row[$field] === "") {
                        $errors[] = import_err("schedule", $r, $field, "$field is required.");
                    }
                }

                $umbc_id     = $row["umbc_id"]        ?? "";
                $subj        = $row["course_subject"]  ?? "";
                $code        = $row["course_code"]     ?? "";
                $day         = $row["day_of_week"]     ?? "";
                $start_raw   = $row["start_time"]      ?? "";
                $end_raw     = $row["end_time"]        ?? "";

                // FK: user must be in wp_users section
                if ($umbc_id !== "" && !isset($users_in_csv[$umbc_id])) {
                    $errors[] = import_err("schedule", $r, "umbc_id",
                        "umbc_id \"$umbc_id\" is not present in the wp_users section of this CSV.");
                }

                // FK: course must be in courses section
                $course_key = $subj . "_" . $code;
                if ($subj !== "" && $code !== "" && !isset($courses_in_csv[$course_key])) {
                    $errors[] = import_err("schedule", $r, "course_subject/course_code",
                        "Course \"$subj $code\" is not present in the courses section of this CSV.");
                }

                // Validate day
                if ($day !== "" && !in_array($day, VALID_DAYS_FULL, true)) {
                    $errors[] = import_err("schedule", $r, "day_of_week",
                        "day_of_week \"$day\" must be a full day name (Monday-Friday).");
                } else {
                    $row["day_abbr"] = get_day_abbr($day);
                }

                // Convert and validate times
                $start_converted = import_convert_time($start_raw);
                $end_converted   = import_convert_time($end_raw);

                if ($start_raw !== "" && $start_converted === null) {
                    $errors[] = import_err("schedule", $r, "start_time",
                        "start_time \"$start_raw\" could not be parsed (e.g. use \"11:00 a.m.\" or \"Noon\").");
                } else {
                    $row["start_time_converted"] = $start_converted;
                }

                if ($end_raw !== "" && $end_converted === null) {
                    $errors[] = import_err("schedule", $r, "end_time",
                        "end_time \"$end_raw\" could not be parsed (e.g. use \"1:00 p.m.\" or \"Noon\").");
                } else {
                    $row["end_time_converted"] = $end_converted;
                }

                if ($start_converted !== null && $end_converted !== null && $end_converted <= $start_converted) {
                    $errors[] = import_err("schedule", $r, "end_time",
                        "end_time \"$end_raw\" must be after start_time \"$start_raw\".");
                }
            }
            unset($row);
        }

        return $errors;
    }

    function import_err($section, $row, $field, $message) {
        return [
            "section" => $section,
            "row"     => $row,
            "field"   => $field,
            "message" => $message,
        ];
    }

    /**
     * Fetch all data needed from umbc_db for validation in one pass.
     */
    function import_fetch_umbc_data($umbcPdo) {
        $data = ["subjects" => [], "courses" => [], "accounts" => []];

        $rows = $umbcPdo->query("SELECT subject_code, subject_name FROM umbc_subjects")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $data["subjects"][$row["subject_code"]] = $row["subject_name"];
        }

        $rows = $umbcPdo->query("SELECT course_id, course_subject, course_code, course_name FROM umbc_courses")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $data["courses"][$row["course_subject"]][$row["course_code"]] = [
                "course_id"   => $row["course_id"],
                "course_name" => $row["course_name"],
            ];
        }

        $rows = $umbcPdo->query("SELECT umbc_id, first_name, last_name, umbc_email FROM umbc_accounts")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $data["accounts"][$row["umbc_id"]] = $row;
        }

        return $data;
    }

    /**
     * Build a preview summary from validated sections.
     */
    function import_build_preview($sections) {
        return [
            "subjects" => count($sections["subjects"] ?? []),
            "courses"  => count($sections["courses"]  ?? []),
            "users"    => count($sections["wp_users"] ?? []),
            "schedule" => count($sections["schedule"] ?? []),
        ];
    }

    /**
     * Write CSV rows to output with proper quoting.
     * Handles values containing commas by wrapping in double quotes.
     */
    function import_fputcsv_row($handle, array $row) {
        $escaped = array_map(function($val) {
            if ($val === null) return "";
            $val = (string) $val;
            if (strpos($val, ",") !== false || strpos($val, '"') !== false || strpos($val, "\n") !== false) {
                return '"' . str_replace('"', '""', $val) . '"';
            }
            return $val;
        }, $row);
        fwrite($handle, implode(",", $escaped) . "\n");
    }
}
//---------------------------------------------------------------------------------------------------------------------

// Import / Export Callbacks
//---------------------------------------------------------------------------------------------------------------------
{
    function import_validate(WP_REST_Request $request) {
        // File must be uploaded as multipart/form-data
        $files = $request->get_file_params();

        if (empty($files["csv_file"]) || $files["csv_file"]["error"] !== UPLOAD_ERR_OK) {
            return new WP_Error("no_file", "No valid CSV file was uploaded.", ["status" => 400]);
        }

        $file      = $files["csv_file"];
        $mime_type = mime_content_type($file["tmp_name"]);
        $ext       = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

        if ($ext !== "csv" || !in_array($mime_type, ["text/csv", "text/plain", "application/csv", "application/octet-stream"], true)) {
            return new WP_Error("invalid_file", "Uploaded file must be a .csv file.", ["status" => 400]);
        }

        // --- Parse ---
        ["sections" => $sections, "errors" => $parse_errors] = import_parse_csv($file["tmp_name"]);

        // Check that all required sections are present
        foreach (IMPORT_SECTIONS as $section) {
            if (!isset($sections[$section])) {
                $parse_errors[] = import_err($section, null, null, "Section \"$section\" is missing from the CSV.");
            }
        }

        if (!empty($parse_errors)) {
            return rest_ensure_response([
                "status"  => "error",
                "errors"  => $parse_errors,
                "preview" => null,
                "token"   => null,
            ]);
        }

        // --- Validate against umbc_db ---
        try {
            $umbcPdo   = db_connect_root("umbc_db");
            $umbc_data = import_fetch_umbc_data($umbcPdo);
        } catch (PDOException $e) {
            return new WP_Error("db_error", "Could not connect to UMBC database.", ["status" => 500]);
        }

        $validation_errors = import_validate_sections($sections, $umbc_data);

        if (!empty($validation_errors)) {
            return rest_ensure_response([
                "status"  => "error",
                "errors"  => $validation_errors,
                "preview" => null,
                "token"   => null,
            ]);
        }

        // --- Store validated data in transient ---
        $token = wp_generate_password(32, false);
        set_transient("import_csv_" . $token, $sections, IMPORT_TRANSIENT_TTL);

        return rest_ensure_response([
            "status"  => "success",
            "errors"  => [],
            "preview" => import_build_preview($sections),
            "token"   => $token,
        ]);
    }


    function import_confirm(WP_REST_Request $request) {
        global $wpdb;

        $token = $request->get_param("token");

        // Retrieve validated data from transient
        $sections = get_transient("import_csv_" . $token);
        if ($sections === false) {
            return new WP_Error("invalid_token",
                "Import session has expired or is invalid. Please re-upload the CSV.",
                ["status" => 400]);
        }
        delete_transient("import_csv_" . $token);

        // --- Begin transaction ---
        $wpdb->query("START TRANSACTION");

        // TRUNCATE is DDL and cannot be rolled back in MySQL — all clears use
        // DELETE FROM instead, which is DML and fully respects the transaction.
        // FK checks are disabled for the delete sequence so that the order of
        // deletion does not need to perfectly satisfy every FK constraint
        // (e.g. events referencing users that are being deleted in the same pass).
        // They are re-enabled immediately before any inserts so that new data
        // is still validated on the way in.

        if ($wpdb->query("SET FOREIGN_KEY_CHECKS = 0") === false) {
            return rollback_error("db_error", "Failed to disable FK checks.");
        }

        // --- Always clear events ---
        if ($wpdb->query("DELETE FROM events") === false) {
            return rollback_error("db_error", "Failed to clear events table.");
        }

        // --- Clear schedule ---
        if ($wpdb->query("DELETE FROM schedule") === false) {
            return rollback_error("db_error", "Failed to clear schedule table.");
        }

        // --- Clear courses ---
        if ($wpdb->query("DELETE FROM courses") === false) {
            return rollback_error("db_error", "Failed to clear courses table.");
        }

        // --- Clear subjects ---
        if ($wpdb->query("DELETE FROM subjects") === false) {
            return rollback_error("db_error", "Failed to clear subjects table.");
        }

        // Re-enable FK checks before any inserts so new data is still validated.
        if ($wpdb->query("SET FOREIGN_KEY_CHECKS = 1") === false) {
            return rollback_error("db_error", "Failed to re-enable FK checks.");
        }

        // --- Delete tutor/staff WP users (not admins) ---
        $curr_user_id = get_current_user_id();
        $users_to_delete = get_users([
            "role__in" => [TUTOR_ROLE, STAFF_ROLE],
            "exclude"  => [$curr_user_id],
            "fields"   => ["ID", "roles"],
        ]);

        foreach ($users_to_delete as $wp_user) {
            // Only delete users whose ONLY roles are tutor and/or asc_staff
            $user_obj   = new WP_User($wp_user->ID);
            $user_roles = (array) $user_obj->roles;
            $non_import_roles = array_diff($user_roles, [TUTOR_ROLE, STAFF_ROLE]);

            if (!empty($non_import_roles)) {
                // Has other roles (e.g. asc_admin) — skip
                continue;
            }

            // Clean up dependent data first
            $cleaned = clean_up_user($wp_user->ID);
            if (is_wp_error($cleaned)) {
                return rollback_error($cleaned->get_error_code(), $cleaned->get_error_message());
            }

            $result = wp_delete_user($wp_user->ID);
            if ($result === false) {
                return rollback_error("db_error", "Failed to delete user ID " . $wp_user->ID . ".");
            }
        }

        // --- Insert subjects ---
        foreach ($sections["subjects"] as $row) {
            $result = $wpdb->insert("subjects", [
                "subject_code"  => $row["subject_code"],
                "subject_name"  => $row["subject_name"],
                "subject_count" => 0,
            ], ["%s", "%s", "%d"]);

            if ($result === false) {
                return rollback_error("db_error", "Failed to insert subject \"" . $row["subject_code"] . "\": " . $wpdb->last_error);
            }
        }

        // --- Insert courses ---
        // subject_count is incremented per course below
        foreach ($sections["courses"] as $row) {
            $result = $wpdb->insert("courses", [
                "course_id"      => $row["course_id"],
                "course_subject" => $row["course_subject"],
                "course_code"    => $row["course_code"],
                "course_name"    => $row["course_name"],
                "course_count"   => 0,
            ], ["%d", "%s", "%s", "%s", "%d"]);

            if ($result === false) {
                return rollback_error("db_error", "Failed to insert course \"" . $row["course_subject"] . " " . $row["course_code"] . "\": " . $wpdb->last_error);
            }

            $wpdb->query($wpdb->prepare(
                "UPDATE subjects SET subject_count = subject_count + 1 WHERE subject_code = %s",
                $row["course_subject"]
            ));
        }

        // --- Insert wp_users ---
        // Build a map of umbc_id => new WP user_id for schedule inserts
        $user_id_map = []; // umbc_id => wp user_id

        foreach ($sections["wp_users"] as $row) {
            $roles = $row["roles_parsed"];

            $user_id = wp_insert_user([
                "user_login" => $row["umbc_id"],
                "user_email" => $row["umbc_email"],
                "first_name" => $row["first_name"],
                "last_name"  => $row["last_name"],
                "user_pass"  => wp_generate_password(64),
                "role"       => $roles[0],
            ]);

            if (is_wp_error($user_id)) {
                return rollback_error("db_error", "Failed to create user \"" . $row["umbc_id"] . "\": " . $user_id->get_error_message());
            }

            if (count($roles) === 2) {
                (new WP_User($user_id))->add_role($roles[1]);
            }

            $user_id_map[$row["umbc_id"]] = $user_id;
        }

        // --- Insert schedule ---
        foreach ($sections["schedule"] as $row) {
            $umbc_id   = $row["umbc_id"];
            $subj      = $row["course_subject"];
            $code      = $row["course_code"];

            // Resolve user_id from the map built above
            if (!isset($user_id_map[$umbc_id])) {
                return rollback_error("db_error", "Could not resolve user_id for umbc_id \"$umbc_id\" during schedule insert.");
            }
            $user_id = $user_id_map[$umbc_id];

            // Resolve course_id from the courses section (stamped during validation)
            $course_id = null;
            foreach ($sections["courses"] as $c) {
                if ($c["course_subject"] === $subj && $c["course_code"] === $code) {
                    $course_id = $c["course_id"];
                    break;
                }
            }

            if ($course_id === null) {
                return rollback_error("db_error", "Could not resolve course_id for \"$subj $code\" during schedule insert.");
            }

            $day_abbr   = $row["day_abbr"];
            $start_time = $row["start_time_converted"];
            $end_time   = $row["end_time_converted"];

            $result = $wpdb->insert("schedule", [
                "user_id"     => $user_id,
                "course_id"   => $course_id,
                "day_of_week" => $day_abbr,
                "start_time"  => $start_time,
                "end_time"    => $end_time,
            ], ["%d", "%d", "%s", "%s", "%s"]);

            if ($result === false) {
                return rollback_error("db_error", "Failed to insert schedule row for \"$umbc_id\" / \"$subj $code\": " . $wpdb->last_error);
            }

            $wpdb->query($wpdb->prepare(
                "UPDATE courses SET course_count = course_count + 1 WHERE course_id = %d",
                $course_id
            ));
        }

        $wpdb->query("COMMIT");

        flush_cache();

        return rest_ensure_response([
            "success"  => true,
            "imported" => import_build_preview($sections),
        ]);
    }


    function import_export(WP_REST_Request $request) {
        global $wpdb;

        // --- Subjects ---
        $subjects = $wpdb->get_results("SELECT subject_code, subject_name FROM subjects ORDER BY subject_code", ARRAY_A);

        // --- Courses ---
        $courses = $wpdb->get_results("
            SELECT course_subject, course_code, course_name
            FROM courses
            ORDER BY course_subject, course_code
        ", ARRAY_A);

        // --- Users (tutor/staff only, not admin) ---
        $users_raw = $wpdb->get_results("
            SELECT
                u.user_login AS umbc_id,
                u.user_email AS umbc_email,
                MAX(CASE WHEN um.meta_key = 'first_name' THEN um.meta_value END) AS first_name,
                MAX(CASE WHEN um.meta_key = 'last_name'  THEN um.meta_value END) AS last_name,
                MAX(CASE WHEN um.meta_key = 'wp_capabilities' THEN um.meta_value END) AS capabilities
            FROM {$wpdb->users} u
            LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
                AND um.meta_key IN ('first_name', 'last_name', 'wp_capabilities')
            WHERE u.ID IN (
                SELECT user_id FROM {$wpdb->usermeta}
                WHERE meta_key = 'wp_capabilities'
                AND (
                    meta_value LIKE '%\"tutor\"%'
                    OR meta_value LIKE '%\"asc_staff\"%'
                )
                AND meta_value NOT LIKE '%\"asc_admin\"%'
            )
            GROUP BY u.ID, u.user_login, u.user_email
            ORDER BY u.user_login
        ", ARRAY_A);

        $users = [];
        foreach ($users_raw as $row) {
            $roles = [];
            if (!empty($row["capabilities"])) {
                $caps = maybe_unserialize($row["capabilities"]);
                if (is_array($caps)) {
                    $roles = array_intersect(array_keys($caps), [TUTOR_ROLE, STAFF_ROLE]);
                }
            }
            $users[] = [
                "umbc_id"    => $row["umbc_id"],
                "umbc_email" => $row["umbc_email"],
                "first_name" => $row["first_name"],
                "last_name"  => $row["last_name"],
                // Roles use " | " separator to avoid CSV comma conflict
                "roles"      => implode(" | ", array_values($roles)),
            ];
        }

        // --- Schedule (join to get umbc_id) ---
        $schedule_raw = $wpdb->get_results("
            SELECT
                u.user_login AS umbc_id,
                c.course_subject,
                c.course_code,
                s.day_of_week,
                s.start_time,
                s.end_time
            FROM schedule s
            JOIN {$wpdb->users} u ON s.user_id   = u.ID
            JOIN courses c        ON s.course_id  = c.course_id
            ORDER BY c.course_subject, c.course_code, s.day_of_week, s.start_time
        ", ARRAY_A);

        $schedule = [];
        foreach ($schedule_raw as $row) {
            $schedule[] = [
                "umbc_id"        => $row["umbc_id"],
                "course_subject" => $row["course_subject"],
                "course_code"    => $row["course_code"],
                "day_of_week"    => tutoring_day_label($row["day_of_week"]),
                "start_time"     => tutoring_format_time($row["start_time"]),
                "end_time"       => tutoring_format_time($row["end_time"]),
            ];
        }

        // --- Stream CSV response ---
        $filename = "tutoring-export-" . date("Y-m-d") . ".csv";

        // Buffer output into a temp stream so WordPress can return it
        $stream = fopen("php://temp", "r+");

        // subjects section
        fwrite($stream, "subjects\n");
        import_fputcsv_row($stream, ["subject_code", "subject_name"]);
        foreach ($subjects as $row) {
            import_fputcsv_row($stream, [$row["subject_code"], $row["subject_name"]]);
        }

        // courses section
        fwrite($stream, "courses\n");
        import_fputcsv_row($stream, ["course_subject", "course_code", "course_name"]);
        foreach ($courses as $row) {
            import_fputcsv_row($stream, [$row["course_subject"], $row["course_code"], $row["course_name"]]);
        }

        // wp_users section
        fwrite($stream, "wp_users\n");
        import_fputcsv_row($stream, ["first_name", "last_name", "umbc_id", "umbc_email", "roles"]);
        foreach ($users as $row) {
            import_fputcsv_row($stream, [$row["first_name"], $row["last_name"], $row["umbc_id"], $row["umbc_email"], $row["roles"]]);
        }

        // schedule section
        fwrite($stream, "schedule\n");
        import_fputcsv_row($stream, ["umbc_id", "course_subject", "course_code", "day_of_week", "start_time", "end_time"]);
        foreach ($schedule as $row) {
            import_fputcsv_row($stream, [$row["umbc_id"], $row["course_subject"], $row["course_code"], $row["day_of_week"], $row["start_time"], $row["end_time"]]);
        }

        rewind($stream);
        $csv_content = stream_get_contents($stream);
        fclose($stream);

        // Send as a download
        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Length: " . strlen($csv_content));
        echo $csv_content;
        exit;
    }


    function import_template(WP_REST_Request $request) {
        $filename = "tutoring-import-template.csv";
        $stream   = fopen("php://temp", "r+");

        fwrite($stream, "subjects\n");
        import_fputcsv_row($stream, ["subject_code", "subject_name"]);
        import_fputcsv_row($stream, ["CMSC", "Computer Science"]);

        fwrite($stream, "courses\n");
        import_fputcsv_row($stream, ["course_subject", "course_code", "course_name"]);
        import_fputcsv_row($stream, ["CMSC", "201", "Computer Science I"]);

        fwrite($stream, "wp_users\n");
        import_fputcsv_row($stream, ["first_name", "last_name", "umbc_id", "umbc_email", "roles"]);
        import_fputcsv_row($stream, ["Jane", "Smith", "AB12345", "jsmith@umbc.edu", "tutor"]);
        import_fputcsv_row($stream, ["John", "Doe",   "CD67890", "jdoe@umbc.edu",   "tutor | asc_staff"]);

        fwrite($stream, "schedule\n");
        import_fputcsv_row($stream, ["umbc_id", "course_subject", "course_code", "day_of_week", "start_time", "end_time"]);
        import_fputcsv_row($stream, ["AB12345", "CMSC", "201", "Monday", "11:00 a.m.", "1:00 p.m."]);
        import_fputcsv_row($stream, ["CD67890", "CMSC", "201", "Wednesday", "Noon", "2:00 p.m."]);

        rewind($stream);
        $csv_content = stream_get_contents($stream);
        fclose($stream);

        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Length: " . strlen($csv_content));
        echo $csv_content;
        exit;
    }
}
//---------------------------------------------------------------------------------------------------------------------