<?php

// REST Callback Helpers
//---------------------------------------------------------------------------------------------------------------------
{
    // NEED TO REPLACE
    // Should not use root for database connections
    function db_connect_root($dbName) {
        $host     = "localhost";
        $username = "root";
        $password = "";

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }

        return $pdo;
    }

    function rollback_error($code, $msg, $status = 500) {
        global $wpdb;
        $wpdb->query("ROLLBACK");
        return new WP_Error($code, $msg, ["status" => $status]);
    }

    function format_audit_data($fields) {
        $escaped = array_map(function($v) {
            if ($v === null) return "NULL";
            return '"' . str_replace('"', '""', $v) . '"';
        }, $fields);
        return implode(",", $escaped);
    }

    function format_audit_roles($roles) {
        return implode(",", array_intersect($roles, [TUTOR_ROLE, STAFF_ROLE, ADMIN_ROLE]));
    }

    function resolve_user_name($user_id) {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare(
            "SELECT CONCAT(
                COALESCE(MAX(CASE WHEN meta_key = 'first_name' THEN meta_value END), ''),
                ' ',
                COALESCE(MAX(CASE WHEN meta_key = 'last_name'  THEN meta_value END), '')
            )
            FROM {$wpdb->usermeta}
            WHERE user_id = %d
            AND meta_key IN ('first_name', 'last_name')",
            $user_id
        ));
    }

    function resolve_schedule_audit_fields($course_id, $user_id) {
        global $wpdb;
        $course_label = $wpdb->get_var($wpdb->prepare(
            "SELECT CONCAT(course_subject, ' ', course_code) FROM courses WHERE course_id = %d",
            $course_id
        ));
        return [$course_label, resolve_user_name($user_id)];
    }

    function resolve_event_audit_fields($event_type_id, $user_id) {
        global $wpdb;
        $event_type_name = $wpdb->get_var($wpdb->prepare(
            "SELECT event_name FROM event_types WHERE event_type_id = %d",
            $event_type_id
        ));
        return [$event_type_name, resolve_user_name($user_id)];
    }

    function insert_audit_log($action, $table_name, $table_key, $old_data, $new_data) {
        global $wpdb;
        $curr_user = wp_get_current_user();
        return $wpdb->insert(
            "audit_log",
            [
                "user_login" => $curr_user->user_login . ", " . $curr_user->user_firstname . " " . $curr_user->user_lastname,
                "action"     => $action,
                "table_name" => $table_name,
                "table_key"  => $table_key,
                "old_data"   => $old_data,
                "new_data"   => $new_data,
            ],
            ["%s", "%s", "%s", "%s", "%s", "%s"]
        ) !== false;
    }

    function validate_positive_int($param, $request, $key) {
        if (!is_numeric($param) || (int) $param <= 0) {
            return new WP_Error(
                "invalid_$key",
                "$key must be a positive integer.",
                ["status" => 400]
            );
        }
        return true;
    }

    function validate_day_field($param, $request, $key) {
        $normalized = ucwords(strtolower((string) $param));
        if (array_search($normalized, DAYS_OF_WEEK) === false) {
            return new WP_Error(
                "invalid_day_of_week",
                "Day of Week must be a full day name (e.g. Monday).",
                ["status" => 400]
            );
        }
        return true;
    }

    function sanitize_day_field($param) {
        return array_search(ucwords(strtolower((string) $param)), DAYS_OF_WEEK);
    }

    function validate_time_field($param, $request, $key) {
        if (DateTime::createFromFormat("H:i:s", trim((string) $param)) === false) {
            return new WP_Error(
                "invalid_$key",
                "$key must be in H:i:s format.",
                ["status" => 400]
            );
        }
        return true;
    }

    function sanitize_time_field($param) {
        return DateTime::createFromFormat("H:i:s", trim((string) $param))->format("H:i:s");
    }

    function validate_date_field($param, $request, $key) {
        if ($param === null || $param === "" || strtolower((string) $param) === "null") {
            return true;
        }
        if (DateTime::createFromFormat("Y-m-d", (string) $param) === false) {
            return new WP_Error(
                "invalid_$key",
                "$key must be in Y-m-d format.",
                ["status" => 400]
            );
        }
        return true;
    }

    function sanitize_date_field($param) {
        if ($param === null || $param === "" || strtolower((string) $param) === "null") {
            return null;
        }
        return DateTime::createFromFormat("Y-m-d", (string) $param)->format("Y-m-d");
    }

    function normalize_event_params($event_type, &$final_day, &$duration) {
        if ($event_type != EVENT_TYPES["leaving_early"]) {
            $duration = null;
        }
        if ($event_type != EVENT_TYPES["called_out"]) {
            $final_day = null;
        }
        if ($duration === "" || $duration === "null") {
            $duration = null;
        }
    }

    function validate_event_dates($start_day, $final_day) {
        if ($final_day !== null && $final_day < $start_day) {
            return new WP_Error("invalid_final_day", "Final Day must be the same or after Start Day.", ["status" => 400]);
        }
        return true;
    }

    function validate_umbc_id($param, $request, $key) {
        if (!preg_match("/^[A-Z]{2}\d{5}$/", (string) $param)) {
            return new WP_Error(
                "invalid_user_login",
                "UMBC ID must be two uppercase letters followed by five digits (e.g. AB12345).",
                ["status" => 400]
            );
        }
        return true;
    }

    function validate_umbc_email($param, $request, $key) {
        $email = (string) $param;
        if (!is_email($email) || !str_ends_with(strtolower($email), "@umbc.edu")) {
            return new WP_Error(
                "invalid_user_email",
                "UMBC Email must be a valid @umbc.edu address.",
                ["status" => 400]
            );
        }
        return true;
    }

    function validate_course_subject($param, $request, $key) {
        if (!preg_match("/^[A-Z]+$/", (string) $param)) {
            return new WP_Error(
                "invalid_course_subject",
                "Course Subject must contain only uppercase letters.",
                ["status" => 400]
            );
        }
        return true;
    }

    function validate_course_code($param, $request, $key) {
        if (!preg_match("/^\d{3}[A-Z]?$/", (string) $param)) {
            return new WP_Error(
                "invalid_course_code",
                "Course Code must be three digits optionally followed by one uppercase letter (e.g. 101 or 101H).",
                ["status" => 400]
            );
        }
        return true;
    }

    
    function validate_roles($param, $request, $key) {
        
        if (!is_array($param) || count($param) < 1) {
            return new WP_Error(
                "invalid_roles",
                "Account must have at least one role.",
                ["status" => 400]
            );
        }

        $valid_roles = [TUTOR_ROLE, STAFF_ROLE, ADMIN_ROLE];

        foreach ($param as $role) {
            if (!in_array($role, $valid_roles, true)) {
                return new WP_Error(
                    "invalid_roles",
                    "\"$role\" is not a recognized role.",
                    ["status" => 400]
                );
            }
        }

        if (in_array(ADMIN_ROLE, $param, true) && count($param) > 1) {
            return new WP_Error(
                "invalid_roles",
                "ASC Admin cannot be assigned with other roles.",
                ["status" => 400]
            );
        }

        return true;
    }

    function clean_up_user($user_id) {
        global $wpdb;

        $result = $wpdb->delete("events", ["user_id" => $user_id], ["%d"]);
        if ($result === false) {
            return new WP_Error("db_error", "Failed to delete user events", ["status" => 500]);
        }

        $result = $wpdb->delete("schedule", ["user_id" => $user_id], ["%d"]);
        if ($result === false) {
            return new WP_Error("db_error", "Failed to delete user schedule", ["status" => 500]);
        }

        return true;
    }

    function ensure_course_exists($course_id, $request) {
        global $wpdb;

        $course_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM courses WHERE course_id = %d",
            $course_id
        ));

        if ($course_exists) {
            return true;
        }

        $course_subject = $request->get_param("course_subject");
        $subject_name   = $request->get_param("subject_name");
        $course_code    = $request->get_param("course_code");
        $course_name    = $request->get_param("course_name");

        if (!$course_subject || !$course_code || !$course_name) {
            return new WP_Error(
                "missing_course_data",
                "course_subject, course_code, and course_name are required for new courses.",
                ["status" => 400]
            );
        }

        if (!$subject_name) {
            return new WP_Error(
                "missing_subject_data",
                "subject_name is required for new subjects.",
                ["status" => 400]
            );
        }

        $result = $wpdb->query($wpdb->prepare("
            INSERT INTO subjects (subject_code, subject_name, subject_count)
            VALUES (%s, %s, 1)
            ON DUPLICATE KEY UPDATE subject_count = subject_count + 1
        ", $course_subject, $subject_name));

        if ($result === false) {
            return new WP_Error("db_error", $wpdb->last_error, ["status" => 500]);
        }

        $result = $wpdb->insert("courses", [
            "course_id"      => $course_id,
            "course_subject" => $course_subject,
            "course_code"    => $course_code,
            "course_name"    => $course_name,
            "course_count"   => 0,
        ], ["%d", "%s", "%s", "%s", "%d"]);

        if ($result === false) {
            return new WP_Error("db_error", $wpdb->last_error, ["status" => 500]);
        }

        return true;
    }

    function invalidate_schedule_cache() {
        wp_cache_delete(U_SCHEDULE_CACHE_KEY, USER_CACHE_GROUP);
        wp_cache_delete(M_SCHEDULE_CACHE_KEY, MANAGEMENT_CACHE_GROUP);
    }

    function invalidate_events_cache() {
        wp_cache_delete(U_EVENTS_CACHE_KEY, USER_CACHE_GROUP);
        wp_cache_delete(M_EVENTS_CACHE_KEY, MANAGEMENT_CACHE_GROUP);
    }

    function invalidate_tutor_cache() {
        invalidate_schedule_cache();
        invalidate_events_cache();
    }

    function flush_cache() {
        invalidate_tutor_cache();
        wp_cache_delete(M_SUBJECTS_CACHE_KEY, MANAGEMENT_CACHE_GROUP);
        wp_cache_delete(M_COURSES_CACHE_KEY, MANAGEMENT_CACHE_GROUP);
        wp_cache_delete(M_USERS_CACHE_KEY, MANAGEMENT_CACHE_GROUP);
    }
}
//---------------------------------------------------------------------------------------------------------------------

// REST Routes
//---------------------------------------------------------------------------------------------------------------------
{
   // Schedule REST API
    add_action("rest_api_init", function() {

        $numeric_id = [
            "required"          => true,
            "validate_callback" => "validate_positive_int",
            "sanitize_callback" => "absint",
        ];

        $schedule_write_args = [
            "user_id"   => $numeric_id,
            "course_id" => $numeric_id,
            "day_of_week" => [
                "required"          => true,
                "validate_callback" => "validate_day_field",
                "sanitize_callback" => "sanitize_day_field",
            ],
            "start_time" => [
                "required"          => true,
                "validate_callback" => "validate_time_field",
                "sanitize_callback" => "sanitize_time_field",
            ],
            "end_time" => [
                "required"          => true,
                "validate_callback" => "validate_time_field",
                "sanitize_callback" => "sanitize_time_field",
            ],
            "course_subject" => [
                "required"          => false,
                "validate_callback" => function($param, $request, $key) {
                    return $param === null || $param === ""
                        ? true
                        : validate_course_subject($param, $request, $key);
                },
                "sanitize_callback" => "sanitize_text_field",
            ],
            "subject_name" => [
                "required"          => false,
                "sanitize_callback" => "sanitize_text_field",
            ],
            "course_code" => [
                "required"          => false,
                "validate_callback" => function($param, $request, $key) {
                    return $param === null || $param === ""
                        ? true
                        : validate_course_code($param, $request, $key);
                },
                "sanitize_callback" => "sanitize_text_field",
            ],
            "course_name" => [
                "required"          => false,
                "sanitize_callback" => "sanitize_text_field",
            ],
        ];

        register_rest_route("asc-tutoring/v1", "/schedule", [
            "methods"             => "POST",
            "callback"            => "create_schedule",
            "permission_callback" => function() { return current_user_can("admin_control"); },
            "args"                => $schedule_write_args,
        ]);

        register_rest_route("asc-tutoring/v1", "/schedule/(?P<schedule_id>\d+)", [
            "methods"             => "DELETE",
            "callback"            => "delete_schedule",
            "permission_callback" => function() { return current_user_can("admin_control"); },
            "args"                => ["schedule_id" => $numeric_id],
        ]);

        register_rest_route("asc-tutoring/v1", "/schedule/(?P<schedule_id>\d+)", [
            "methods"             => "PATCH",
            "callback"            => "update_schedule",
            "permission_callback" => function() { return current_user_can("admin_control"); },
            "args"                => array_merge(["schedule_id" => $numeric_id], $schedule_write_args),
        ]);
    });

    // Events REST API
    add_action("rest_api_init", function() {

        $numeric_id = [
            "required"          => true,
            "validate_callback" => "validate_positive_int",
            "sanitize_callback" => "absint",
        ];

        $event_write_args = [
            "event_type" => $numeric_id,
            "user_id"    => $numeric_id,
            "start_day"  => [
                "required"          => true,
                "validate_callback" => "validate_date_field",
                "sanitize_callback" => "sanitize_date_field",
            ],
            "final_day" => [
                "required"          => false,
                "default"           => null,
                "validate_callback" => "validate_date_field",
                "sanitize_callback" => "sanitize_date_field",
            ],
            "duration" => [
                "required"          => false,
                "default"           => null,
                "validate_callback" => function($param, $request, $key) {
                    if ($param === null || $param === "") return true;
                    return validate_positive_int($param, $request, $key);
                },
                "sanitize_callback" => function($param) {
                    return ($param === null || $param === "") ? null : absint($param);
                },
            ],
        ];

        register_rest_route("asc-tutoring/v1", "/events", [
            "methods"             => "POST",
            "callback"            => "create_event",
            "permission_callback" => function() { return current_user_can("staff_control"); },
            "args"                => $event_write_args,
        ]);

        register_rest_route("asc-tutoring/v1", "/events/(?P<event_id>\d+)", [
            "methods"             => "DELETE",
            "callback"            => "delete_event",
            "permission_callback" => function() { return current_user_can("staff_control"); },
            "args"                => ["event_id" => $numeric_id],
        ]);

        register_rest_route("asc-tutoring/v1", "/events/(?P<event_id>\d+)", [
            "methods"             => "PATCH",
            "callback"            => "update_event",
            "permission_callback" => function() { return current_user_can("staff_control"); },
            "args"                => array_merge(["event_id" => $numeric_id], $event_write_args),
        ]);
    });

    // Accounts REST API
    add_action("rest_api_init", function() {

        $numeric_id = [
            "required"          => true,
            "validate_callback" => "validate_positive_int",
            "sanitize_callback" => "absint",
        ];

        $account_write_args = [
            "user_login" => [
                "required"          => true,
                "validate_callback" => "validate_umbc_id",
                "sanitize_callback" => "sanitize_text_field",
            ],
            "user_email" => [
                "required"          => true,
                "validate_callback" => "validate_umbc_email",
                "sanitize_callback" => "sanitize_email",
            ],
            "first_name" => [
                "required"          => true,
                "sanitize_callback" => "sanitize_text_field",
            ],
            "last_name" => [
                "required"          => true,
                "sanitize_callback" => "sanitize_text_field",
            ],
            "roles" => [
                "required"          => true,
                "validate_callback" => "validate_roles",
                "sanitize_callback" => function($param) {
                    return array_map("sanitize_text_field", (array) $param);
                },
            ],
        ];

        register_rest_route("asc-tutoring/v1", "/accounts", [
            "methods"             => "POST",
            "callback"            => "create_account",
            "permission_callback" => function() { return current_user_can("admin_control"); },
            "args"                => $account_write_args,
        ]);

        register_rest_route("asc-tutoring/v1", "/accounts/(?P<user_id>\d+)", [
            "methods"             => "DELETE",
            "callback"            => "delete_account",
            "permission_callback" => function() { return current_user_can("admin_control"); },
            "args"                => ["user_id" => $numeric_id],
        ]);

        register_rest_route("asc-tutoring/v1", "/accounts/(?P<user_id>\d+)", [
            "methods"             => "PATCH",
            "callback"            => "update_account",
            "permission_callback" => function() { return current_user_can("admin_control"); },
            "args"                => array_merge(["user_id" => $numeric_id], $account_write_args),
        ]);
    });

    // Audit Log REST API
    add_action("rest_api_init", function() {
        register_rest_route("asc-tutoring/v1", "/logs", [
            "methods"             => "GET",
            "callback"            => "get_audit_logs",
            "permission_callback" => function() { return current_user_can("admin_control"); },
        ]);
    });
}
//---------------------------------------------------------------------------------------------------------------------

// Schedule Callbacks
//---------------------------------------------------------------------------------------------------------------------
{
    function create_schedule(WP_REST_Request $request) {
        global $wpdb;

        $day_of_week = $request->get_param("day_of_week");
        $user_id     = $request->get_param("user_id");
        $course_id   = $request->get_param("course_id");
        $start_time  = $request->get_param("start_time");
        $end_time    = $request->get_param("end_time");
        
        if ($end_time <= $start_time) {
            return new WP_Error("invalid_end_time", "end_time must be after start_time.", ["status" => 400]);
        }

        $wpdb->query("START TRANSACTION");

        $ensured = ensure_course_exists($course_id, $request);
        if (is_wp_error($ensured)) return rollback_error($ensured->get_error_code(), $ensured->get_error_message(), 400);

        $result = $wpdb->insert("schedule", [
            "user_id"     => $user_id,
            "course_id"   => $course_id,
            "day_of_week" => $day_of_week,
            "start_time"  => $start_time,
            "end_time"    => $end_time,
        ], ["%d", "%d", "%s", "%s", "%s"]);

        if ($result === false) return rollback_error("db_error", $wpdb->last_error);

        $result = $wpdb->query($wpdb->prepare(
            "UPDATE courses SET course_count = course_count + 1 WHERE course_id = %d",
            $course_id
        ));

        if ($result === false) return rollback_error("db_error", $wpdb->last_error);

        $schedule_id = $wpdb->insert_id;
        [$new_course_label, $new_user_name] = resolve_schedule_audit_fields($course_id, $user_id);
        $logged = insert_audit_log("CRE", "schedule", $schedule_id, null,
            format_audit_data([$new_user_name, $new_course_label, $day_of_week, $start_time, $end_time]));

        if (!$logged) return rollback_error("db_error", "Failed to write audit log");

        $wpdb->query("COMMIT");

        wp_cache_delete(M_SUBJECTS_CACHE_KEY, MANAGEMENT_CACHE_GROUP);
        wp_cache_delete(M_COURSES_CACHE_KEY, MANAGEMENT_CACHE_GROUP);
        invalidate_schedule_cache();

        return rest_ensure_response(["created" => true, "schedule_id" => $schedule_id]);
    }

    function delete_schedule(WP_REST_Request $request) {
        global $wpdb;

        $schedule_id = $request->get_param("schedule_id");

        $wpdb->query("START TRANSACTION");

        $old_row = $wpdb->get_row($wpdb->prepare(
            "SELECT u.ID,
                    CONCAT(c.course_subject, ' ', c.course_code) AS course_label,
                    s.day_of_week, s.start_time, s.end_time, s.course_id
             FROM schedule s
             JOIN {$wpdb->users} u ON s.user_id   = u.ID
             JOIN courses c        ON s.course_id = c.course_id
             WHERE s.schedule_id = %d",
            $schedule_id
        ), ARRAY_A);

        if (!$old_row) return rollback_error("not_found", "No schedule found with that ID", 404);

        $wpdb->query("SET @course_id = NULL");

        $result = $wpdb->query($wpdb->prepare(
            "DELETE FROM schedule WHERE schedule_id = %d AND (@course_id := course_id) IS NOT NULL",
            $schedule_id
        ));

        if ($result === false || $result === 0) return rollback_error("not_found", "No schedule found with that ID", 404);

        $result = $wpdb->query(
            "UPDATE courses SET course_count = course_count - 1 WHERE course_id = @course_id"
        );

        if ($result === false) return rollback_error("db_error", "Failed to decrement course count");

        $old_user_name = resolve_user_name($old_row["ID"]);
        $logged = insert_audit_log("DEL", "schedule", $schedule_id,
            format_audit_data([$old_user_name, $old_row["course_label"],
                $old_row["day_of_week"], $old_row["start_time"], $old_row["end_time"]]),
            null);

        if (!$logged) return rollback_error("db_error", "Failed to write audit log");

        $wpdb->query("COMMIT");

        invalidate_schedule_cache();

        return rest_ensure_response(["deleted" => true, "schedule_id" => $schedule_id]);
    }

    function update_schedule(WP_REST_Request $request) {
        global $wpdb;

        $schedule_id = $request->get_param("schedule_id");
        $day_of_week = $request->get_param("day_of_week");
        $user_id     = $request->get_param("user_id");
        $course_id   = $request->get_param("course_id");
        $start_time  = $request->get_param("start_time");
        $end_time    = $request->get_param("end_time");
        
        if ($end_time <= $start_time) {
            return new WP_Error("invalid_end_time", "end_time must be after start_time.", ["status" => 400]);
        }

        $old_row = $wpdb->get_row($wpdb->prepare(
            "SELECT u.ID,
                    CONCAT(c.course_subject, ' ', c.course_code) AS course_label,
                    s.day_of_week, s.start_time, s.end_time, s.course_id
             FROM schedule s
             JOIN {$wpdb->users} u ON s.user_id   = u.ID
             JOIN courses c        ON s.course_id = c.course_id
             WHERE s.schedule_id = %d",
            $schedule_id
        ), ARRAY_A);

        if (!$old_row) return new WP_Error("not_found", "No schedule found with that ID", ["status" => 404]);

        $old_course_id  = $old_row["course_id"];
        $course_changed = (int) $old_course_id !== (int) $course_id;

        $wpdb->query("START TRANSACTION");

        $ensured = ensure_course_exists($course_id, $request);
        if (is_wp_error($ensured)) return rollback_error($ensured->get_error_code(), $ensured->get_error_message(), 400);

        if ($course_changed) {
            $result = $wpdb->query($wpdb->prepare("
                UPDATE courses SET course_count = course_count + CASE course_id
                    WHEN %d THEN -1
                    WHEN %d THEN  1
                END
                WHERE course_id IN (%d, %d)
            ", $old_course_id, $course_id, $old_course_id, $course_id));

            if ($result === false) return rollback_error("db_error", $wpdb->last_error);
        }

        $result = $wpdb->update(
            "schedule",
            [
                "user_id"     => $user_id,
                "course_id"   => $course_id,
                "day_of_week" => $day_of_week,
                "start_time"  => $start_time,
                "end_time"    => $end_time,
            ],
            ["schedule_id" => $schedule_id],
            ["%d", "%d", "%s", "%s", "%s"],
            ["%d"]
        );

        if ($result === false) return rollback_error("db_error", $wpdb->last_error);

        [$new_course_label, $new_user_name] = resolve_schedule_audit_fields($course_id, $user_id);
        $old_user_name = resolve_user_name($old_row["ID"]);
        $logged = insert_audit_log("MOD", "schedule", $schedule_id,
            format_audit_data([$old_user_name, $old_row["course_label"],
                $old_row["day_of_week"], $old_row["start_time"], $old_row["end_time"]]),
            format_audit_data([$new_user_name, $new_course_label, $day_of_week, $start_time, $end_time]));

        if (!$logged) return rollback_error("db_error", "Failed to write audit log");

        $wpdb->query("COMMIT");

        invalidate_schedule_cache();

        return rest_ensure_response(["updated" => true, "schedule_id" => $schedule_id]);
    }
}
//---------------------------------------------------------------------------------------------------------------------

// Event Callbacks
//---------------------------------------------------------------------------------------------------------------------
{
    function create_event(WP_REST_Request $request) {
        global $wpdb;

        $event_type = $request->get_param("event_type");
        $user_id    = $request->get_param("user_id");
        $start_day  = $request->get_param("start_day");
        $final_day  = $request->get_param("final_day");
        $duration   = $request->get_param("duration");

        normalize_event_params($event_type, $final_day, $duration);

        $validated = validate_event_dates($start_day, $final_day);
        if (is_wp_error($validated)) return $validated;

        $wpdb->query("START TRANSACTION");

        $result = $wpdb->insert(
            "events",
            [
                "event_type" => $event_type,
                "user_id"    => $user_id,
                "start_day"  => $start_day,
                "final_day"  => $final_day,
                "duration"   => $duration,
            ],
            ["%d", "%d", "%s", "%s", "%d"]
        );

        if ($result === false) return rollback_error("db_error", $wpdb->last_error);

        $event_id = $wpdb->insert_id;
        [$new_event_type_name, $new_user_name] = resolve_event_audit_fields($event_type, $user_id);
        $logged = insert_audit_log("CRE", "events", $event_id, null,
            format_audit_data([$new_user_name, $new_event_type_name, $start_day, $final_day, $duration]));

        if (!$logged) return rollback_error("db_error", "Failed to write audit log");

        $wpdb->query("COMMIT");

        invalidate_events_cache();

        return rest_ensure_response(["created" => true, "event_id" => $event_id]);
    }

    function delete_event(WP_REST_Request $request) {
        global $wpdb;

        $event_id = $request->get_param("event_id");

        $wpdb->query("START TRANSACTION");

        $old_row = $wpdb->get_row($wpdb->prepare(
            "SELECT u.ID, et.event_name, e.start_day, e.final_day, e.duration
             FROM events e
             JOIN event_types et    ON e.event_type = et.event_type_id
             JOIN {$wpdb->users} u  ON e.user_id    = u.ID
             WHERE e.event_id = %d",
            $event_id
        ), ARRAY_A);

        if (!$old_row) return rollback_error("not_found", "No event found with ID: " . $event_id, 404);

        $result = $wpdb->delete("events", ["event_id" => $event_id], ["%d"]);

        if ($result === false) return rollback_error("db_error", "Failed to delete event");

        $old_user_name = resolve_user_name($old_row["ID"]);
        $logged = insert_audit_log("DEL", "events", $event_id,
            format_audit_data([$old_user_name, ...array_slice($old_row, 1)]), null);

        if (!$logged) return rollback_error("db_error", "Failed to write audit log");

        $wpdb->query("COMMIT");

        invalidate_events_cache();

        return rest_ensure_response(["deleted" => true, "event_id" => $event_id]);
    }

    function update_event(WP_REST_Request $request) {
        global $wpdb;

        $event_id   = $request->get_param("event_id");
        $event_type = $request->get_param("event_type");
        $user_id    = $request->get_param("user_id");
        $start_day  = $request->get_param("start_day");
        $final_day  = $request->get_param("final_day");
        $duration   = $request->get_param("duration");

        normalize_event_params($event_type, $final_day, $duration);

        $validated = validate_event_dates($start_day, $final_day);
        if (is_wp_error($validated)) return $validated;

        $old_row = $wpdb->get_row($wpdb->prepare(
            "SELECT u.ID, et.event_name, e.start_day, e.final_day, e.duration
             FROM events e
             JOIN event_types et    ON e.event_type = et.event_type_id
             JOIN {$wpdb->users} u  ON e.user_id    = u.ID
             WHERE e.event_id = %d",
            $event_id
        ), ARRAY_A);

        if (!$old_row) return new WP_Error("not_found", "No event found with that ID", ["status" => 404]);

        $wpdb->query("START TRANSACTION");

        $result = $wpdb->update(
            "events",
            [
                "event_type" => $event_type,
                "user_id"    => $user_id,
                "start_day"  => $start_day,
                "final_day"  => $final_day,
                "duration"   => $duration,
            ],
            ["event_id" => $event_id],
            ["%d", "%d", "%s", "%s", "%d"],
            ["%d"]
        );

        if ($result === false) return rollback_error("db_error", $wpdb->last_error);

        [$new_event_type_name, $new_user_name] = resolve_event_audit_fields($event_type, $user_id);
        $old_user_name = resolve_user_name($old_row["ID"]);
        $logged = insert_audit_log("MOD", "events", $event_id,
            format_audit_data([$old_user_name, ...array_slice($old_row, 1)]),
            format_audit_data([$new_user_name, $new_event_type_name, $start_day, $final_day, $duration]));

        if (!$logged) return rollback_error("db_error", "Failed to write audit log");

        $wpdb->query("COMMIT");

        invalidate_events_cache();

        return rest_ensure_response(["updated" => true, "event_id" => $event_id]);
    }
}
//---------------------------------------------------------------------------------------------------------------------

// Account Callbacks
//---------------------------------------------------------------------------------------------------------------------
{
    function create_account(WP_REST_Request $request) {
        global $wpdb;

        $user_login = $request->get_param("user_login");
        $user_email = $request->get_param("user_email");
        $first_name = $request->get_param("first_name");
        $last_name  = $request->get_param("last_name");
        $roles      = array_map("sanitize_text_field", (array) $request->get_param("roles"));

        $wpdb->query("START TRANSACTION");

        $user_id = wp_insert_user([
            "user_login" => $user_login,
            "user_email" => $user_email,
            "first_name" => $first_name,
            "last_name"  => $last_name,
            "user_pass"  => wp_generate_password(64),
            "role"       => $roles[0],
        ]);

        if (is_wp_error($user_id)) return rollback_error("db_error", $user_id->get_error_message());

        if (count($roles) === 2) {
            (new WP_User($user_id))->add_role($roles[1]);
        }

        $logged = insert_audit_log("CRE", "wp_users", $user_id, null,
            format_audit_data([$user_login, $user_email, $first_name, $last_name, format_audit_roles($roles)]));

        if (!$logged) return rollback_error("db_error", "Failed to write audit log");

        $wpdb->query("COMMIT");

        wp_cache_delete(M_USERS_CACHE_KEY, MANAGEMENT_CACHE_GROUP);

        return rest_ensure_response(["created" => true, "user_id" => $user_id]);
    }

    function delete_account(WP_REST_Request $request) {
        global $wpdb;

        $user_id      = $request->get_param("user_id");
        $curr_user_id = get_current_user_id();

        if ($user_id == $curr_user_id) {
            return new WP_Error("invalid_user_id", "Cannot delete the current user", ["status" => 400]);
        }

        $user = new WP_User($user_id);

        if (!$user->exists()) {
            return new WP_Error("not_found", "No user found with that ID", ["status" => 404]);
        }

        $is_tutor = in_array(TUTOR_ROLE, (array) $user->roles, true);
        $old_data = format_audit_data([
            $user->user_login, $user->user_email,
            $user->first_name, $user->last_name,
            format_audit_roles((array) $user->roles),
        ]);

        $wpdb->query("START TRANSACTION");

        $cleaned = clean_up_user($user_id);
        if (is_wp_error($cleaned)) return rollback_error($cleaned->get_error_code(), $cleaned->get_error_message());

        $result = wp_delete_user($user_id);
        if ($result === false) return rollback_error("not_found", "No user found with that ID", 404);

        $logged = insert_audit_log("DEL", "wp_users", $user_id, $old_data, null);
        if (!$logged) return rollback_error("db_error", "Failed to write audit log");

        $wpdb->query("COMMIT");

        if ($is_tutor) {
            invalidate_tutor_cache();
        }

        wp_cache_delete(M_USERS_CACHE_KEY, MANAGEMENT_CACHE_GROUP);

        return rest_ensure_response(["deleted" => true, "user_id" => $user_id]);
    }

    function update_account(WP_REST_Request $request) {
        global $wpdb;

        $user_id      = $request->get_param("user_id");
        $curr_user_id = get_current_user_id();
        $user_login   = $request->get_param("user_login");
        $user_email   = $request->get_param("user_email");
        $first_name   = $request->get_param("first_name");
        $last_name    = $request->get_param("last_name");
        $roles        = $request->get_param("roles");

        if ($user_id == $curr_user_id) {
            return new WP_Error("invalid_user_id", "Cannot modify the current user", ["status" => 400]);
        }

        $user = new WP_User($user_id);

        if (!$user->exists()) {
            return new WP_Error("account_not_found", "Account does not exist.", ["status" => 404]);
        }

        $was_tutor = in_array(TUTOR_ROLE, (array) $user->roles, true);
        $old_data  = format_audit_data([
            $user->user_login, $user->user_email,
            $user->first_name, $user->last_name,
            format_audit_roles((array) $user->roles),
        ]);

        $wpdb->query("START TRANSACTION");

        $updated = wp_update_user([
            "ID"         => $user_id,
            "user_login" => $user_login,
            "user_email" => $user_email,
            "first_name" => $first_name,
            "last_name"  => $last_name,
        ]);

        if (is_wp_error($updated)) return rollback_error("db_error", $updated->get_error_message());

        $user = new WP_User($user_id);
        $user->set_role($roles[0]);
        if (count($roles) === 2) {
            $user->add_role($roles[1]);
        }

        if (!in_array(TUTOR_ROLE, $roles, true) && $was_tutor) {
            $cleaned = clean_up_user($user_id);
            if (is_wp_error($cleaned)) return rollback_error($cleaned->get_error_code(), $cleaned->get_error_message());
            invalidate_tutor_cache();
        }

        $logged = insert_audit_log("MOD", "wp_users", $user_id,
            $old_data,
            format_audit_data([$user_login, $user_email, $first_name, $last_name, format_audit_roles($roles)]));

        if (!$logged) return rollback_error("db_error", "Failed to write audit log");

        $wpdb->query("COMMIT");

        wp_cache_delete(M_USERS_CACHE_KEY, MANAGEMENT_CACHE_GROUP);

        return rest_ensure_response(["updated" => true, "user_id" => $user_id]);
    }
}
//---------------------------------------------------------------------------------------------------------------------

// umbc_db Callbacks
//---------------------------------------------------------------------------------------------------------------------
{
    function get_umbc_courses(WP_REST_Request $request) {
        $search_str = trim((string) $request->get_param("search_str"));
        $search     = $search_str !== "" ? "%" . $search_str . "%" : "%";
        $umbcPdo    = db_connect_root("umbc_db");

        try {
            $stmt = $umbcPdo->prepare("
                SELECT
                    c.course_id,
                    c.course_subject,
                    s.subject_name,
                    c.course_code,
                    c.course_name
                FROM umbc_courses c
                JOIN umbc_subjects s
                    ON c.course_subject = s.subject_code
                WHERE
                    c.course_subject LIKE :search
                    OR s.subject_name LIKE :search
                    OR c.course_code  LIKE :search
                    OR c.course_name  LIKE :search
                ORDER BY c.course_subject, c.course_code
            ");
            $stmt->bindValue(":search", $search, PDO::PARAM_STR);
            $stmt->execute();
            $umbc_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return new WP_Error("db_error", "Failed to retrieve courses.", ["status" => 500]);
        }

        return rest_ensure_response(["success" => true, "umbc_courses" => $umbc_courses]);
    }

    function get_umbc_accounts(WP_REST_Request $request) {
        $search_str = trim((string) $request->get_param("search_str"));
        $search     = $search_str !== "" ? "%" . $search_str . "%" : "%";
        $umbcPdo    = db_connect_root("umbc_db");

        try {
            $stmt = $umbcPdo->prepare("
                SELECT
                    umbc_id,
                    first_name,
                    last_name,
                    umbc_email
                FROM umbc_accounts
                WHERE
                    umbc_id    LIKE :search
                    OR first_name LIKE :search
                    OR last_name  LIKE :search
                    OR umbc_email LIKE :search
                ORDER BY last_name, first_name, umbc_id
            ");
            $stmt->bindValue(":search", $search, PDO::PARAM_STR);
            $stmt->execute();
            $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("UMBC account search query failed: " . $e->getMessage());
            return new WP_Error("db_error", "Failed to retrieve accounts.", ["status" => 500]);
        }

        return rest_ensure_response(["success" => true, "umbc_accounts" => $accounts]);
    }
}
//---------------------------------------------------------------------------------------------------------------------

// Audit Log Callbacks
//---------------------------------------------------------------------------------------------------------------------
{
    function parse_audit_csv($csv) {
        if ($csv === null) return [];
        $fields = [];
        $len    = strlen($csv);
        $i      = 0;
        while ($i < $len) {
            if ($csv[$i] === '"') {
                $i++;
                $val = "";
                while ($i < $len) {
                    if ($csv[$i] === '"' && isset($csv[$i + 1]) && $csv[$i + 1] === '"') {
                        $val .= '"';
                        $i  += 2;
                    } elseif ($csv[$i] === '"') {
                        $i++;
                        break;
                    } else {
                        $val .= $csv[$i++];
                    }
                }
                $fields[] = $val;
                if ($i < $len && $csv[$i] === ',') $i++;
            } else {
                $start = $i;
                while ($i < $len && $csv[$i] !== ',') $i++;
                $raw      = substr($csv, $start, $i - $start);
                $fields[] = $raw === "NULL" ? null : $raw;
                if ($i < $len && $csv[$i] === ',') $i++;
            }
        }
        return $fields;
    }

    function diff_audit_fields($old_fields, $new_fields) {
        $changes = [];
        $count   = max(count($old_fields), count($new_fields));
        for ($i = 0; $i < $count; $i++) {
            $old = $old_fields[$i] ?? null;
            $new = $new_fields[$i] ?? null;
            if ($old === $new) continue;
            if ($old === null || $new === null) continue;
            $changes[] = "$old -> $new";
        }
        return $changes;
    }

    function format_log_time($timestamp) {
        return (new DateTime($timestamp))->format("H:i:s");
    }

    function format_log_actor($user_login, $requester_roles) {
        $known_roles = [ADMIN_ROLE => "admin", STAFF_ROLE => "staff"];
        foreach ($known_roles as $role => $label) {
            if (in_array($role, $requester_roles, true)) {
                return "$label ($user_login)";
            }
        }
        return "UNKNOWN ($user_login)";
    }

    function get_log_actor_roles($user_login_field) {
        $login = explode(", ", $user_login_field, 2)[0];
        $user  = get_user_by("login", $login);
        if (!$user) return [];
        return array_intersect((array) $user->roles, [TUTOR_ROLE, STAFF_ROLE, ADMIN_ROLE]);
    }

    function snake_to_capital_words($value) {
        return implode(" ", array_map("ucfirst", explode("_", $value)));
    }

    function format_log_entry($row) {
        $time      = format_log_time($row["time_stamp"]);
        $roles     = get_log_actor_roles($row["user_login"]);
        $actor     = format_log_actor($row["user_login"], $roles);
        $table     = $row["table_name"];
        $action    = $row["action"];
        $old_data  = $row["old_data"];
        $new_data  = $row["new_data"];
        $date_key  = (new DateTime($row["time_stamp"]))->format("Y-m-d");

        $table_label = match($table) {
            "schedule" => "schedule entry",
            "events"   => "event",
            "wp_users" => "account",
            default    => $table,
        };
        $action_label = match($action) {
            "CRE" => "CREATED",
            "DEL" => "DELETED",
            "MOD" => "EDITED",
            default => $action,
        };

        $format_fields = function(array $fields) use ($table): array {
            return array_filter(
                array_map(fn($v) => $v === null ? null : ($table === "events" ? snake_to_capital_words($v) : $v), $fields),
                fn($v) => $v !== null
            );
        };

        $data_line = $action === "DEL"
            ? implode(", ", $format_fields(parse_audit_csv($old_data)))
            : implode(", ", $format_fields(parse_audit_csv($new_data)));

        $lines = ["[$date_key $time] $actor $action_label $table_label:\n" . str_repeat(" ", 22) . $data_line];

        if ($action === "MOD" && $old_data !== null && $new_data !== null) {
            $old_fields = parse_audit_csv($old_data);
            $new_fields = parse_audit_csv($new_data);
            $changes    = diff_audit_fields($old_fields, $new_fields);
            if (!empty($changes) && $table === "events") {
                $changes = array_map(function($change) {
                    [$from, $to] = explode(" -> ", $change, 2);
                    return snake_to_capital_words($from) . " -> " . snake_to_capital_words($to);
                }, $changes);
            }
            if (!empty($changes)) {
                $lines[] = str_repeat(" ", 22) . "Changed: " . implode(", ", $changes);
            }
        }

        return [
            "date"   => $date_key,
            "action" => $action,
            "table"  => $table,
            "lines"  => $lines,
        ];
    }

    function get_audit_logs(WP_REST_Request $request) {
        global $wpdb;

        $rows = $wpdb->get_results(
            "SELECT user_login, action, table_name, table_key, old_data, new_data, time_stamp
             FROM audit_log
             ORDER BY time_stamp DESC",
            ARRAY_A
        );

        if ($rows === null) {
            return new WP_Error("db_error", "Failed to retrieve audit log", ["status" => 500]);
        }

        $entries = array_map("format_log_entry", $rows);

        return rest_ensure_response(["success" => true, "logs" => $entries]);
    }
}
//---------------------------------------------------------------------------------------------------------------------