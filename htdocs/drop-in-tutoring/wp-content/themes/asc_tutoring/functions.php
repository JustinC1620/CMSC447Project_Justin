<?php
const CHECK_ICON_SVG     = '<svg aria-hidden="true" focusable="false" style="width:1em;height:1em;vertical-align:middle" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13.5 4.5L6.5 11.5L2.5 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
const X_ICON_SVG         = '<svg aria-hidden="true" focusable="false" style="width:1em;height:1em;vertical-align:middle" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
const C_WARNING_ICON_SVG = '<svg aria-hidden="true" focusable="false" style="width:1em;height:1em;vertical-align:middle" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="2"/><path d="M11.5 4.5L4.5 11.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
const T_WARNING_ICON_SVG = '<svg aria-hidden="true" focusable="false" style="width:1em;height:1em;vertical-align:middle" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8 2L14.5 13.5H1.5L8 2Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><line x1="8" y1="6" x2="8" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="8" cy="12" r="0.75" fill="currentColor"/></svg>';
const PAUSE_ICON_SVG     = '<svg aria-hidden="true" focusable="false" style="width:1em;height:1em;vertical-align:middle" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="2.5" width="3.5" height="11" rx="1" fill="currentColor"/><rect x="9.5" y="2.5" width="3.5" height="11" rx="1" fill="currentColor"/></svg>';

const U_SCHEDULE_CACHE_KEY   = "user_schedule";
const U_EVENTS_CACHE_KEY     = "user_events";
const USER_CACHE_GROUP       = "user_group";

const M_SUBJECTS_CACHE_KEY   = "management_subjects";
const M_COURSES_CACHE_KEY    = "management_courses";
const M_USERS_CACHE_KEY      = "management_users";
const M_SCHEDULE_CACHE_KEY   = "management_schedule";
const M_EVENTS_CACHE_KEY     = "management_events";
const MANAGEMENT_CACHE_GROUP = "management_group";

const TUTOR_ROLE = "tutor";
const STAFF_ROLE = "asc_staff";
const ADMIN_ROLE = "asc_admin";

const DAYS_OF_WEEK = [
    "MON" => "Monday",
    "TUE" => "Tuesday",
    "WED" => "Wednesday",
    "THU" => "Thursday",
    "FRI" => "Friday",
];

const EVENT_TYPES = [
    "called_out"    => "1",
    "late"          => "2",
    "leaving_early" => "3",
    "at_capacity"   => "4",
];

const TUTORING_SNAPSHOT_PATH      = '/tutoring-snapshot.html';
const TUTORING_PAGE_SLUG          = 'drop-in-tutoring';

if (!function_exists("wp_delete_user")) {
    require_once ABSPATH . "wp-admin/includes/user.php";
}

require_once get_template_directory() . "/rest.php";
require_once get_template_directory() . "/rest-import.php";

add_action('wp_enqueue_scripts', function() {
    $template = get_page_template_slug();
    $is_tutoring_admin = $template === 'page-tutoring-admin.php';
    $is_tutoring       = $template === 'page-tutoring.php';

    if (!$is_tutoring_admin && !$is_tutoring) return;

    // --- Shared ---

    wp_enqueue_script(
        'shared',
        get_template_directory_uri() . '/js/shared.js',
        [],
        '1.0',
        true
    );

    wp_enqueue_style( 
        'umbc-style', 
        get_template_directory_uri() . '/css/umbc-style.css', 
        false, 
        '1.0', 
        'all'
    );

    // --- Drop-In Tutoring page ---

    if ($is_tutoring) {
        wp_enqueue_script(
            'drop-in-tutoring',
            get_template_directory_uri() . '/js/drop-in-tutoring.js',
            ['shared'],
            '1.0',
            true
        );
        wp_enqueue_style( 
            'drop-in-tutoring-style', 
            get_template_directory_uri() . '/css/drop-in-tutoring-style.css', 
            false, 
            '1.0', 
            'all'
        );
        return;
    }

    // --- Tutoring Admin page ---

    wp_enqueue_style(
        'select2',
        'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
        [],
        '4.1.0'
    );
    wp_enqueue_script(
        'select2',
        'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
        ['jquery'],
        '4.1.0',
        true
    );
    wp_enqueue_style(
        'flatpickr',
        'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css',
        [],
        '4.6.13'
    );
    wp_enqueue_script(
        'flatpickr',
        'https://cdn.jsdelivr.net/npm/flatpickr',
        [],
        '4.6.13',
        true
    );

    wp_enqueue_script(
        'asc-staff',
        get_template_directory_uri() . '/js/asc-staff.js',
        ['shared', 'select2', 'flatpickr'],
        '1.0',
        true
    );

    if (current_user_can('admin_control')) {
        wp_enqueue_script(
            'asc-admin',
            get_template_directory_uri() . '/js/asc-admin.js',
            ['asc-staff'],
            '1.0',
            true
        );
    }

    wp_enqueue_style( 
        'tutoring-admin-style', 
        get_template_directory_uri() . '/css/tutoring-admin-style.css', 
        false, 
        '1.0', 
        'all'
    );

    wp_localize_script('asc-staff', 'wpApiSettings', [
        'nonce' => wp_create_nonce('wp_rest'),
        'root'  => esc_url_raw(rest_url()),
    ]);
});

add_action("after_setup_theme", function() {
    if (current_user_can(TUTOR_ROLE) ||
        current_user_can(ADMIN_ROLE) ||
        current_user_can(STAFF_ROLE)) {
        show_admin_bar(false);
    }
});

add_filter("login_redirect", function($redirect_to, $request, $user) {
    return home_url();
}, 10, 3);

add_action("login_init", function() {
    if (isset($_GET["SAMLResponse"])) {
        wp_redirect(home_url());
        exit;
    }
});


// --- Snapshot Generator ------------------------------------------------------

function tutoring_generate_static_snapshot() {
    $filepath = 
    $page = get_page_by_path(TUTORING_PAGE_SLUG);
    if (!$page) {
        return;
    }

    global $wp, $wp_query;

    ob_start();
    
    query_posts(['page_id' => $page->ID]);
    
    if (have_posts()) {
        the_post();
        define('TUTORING_IS_STATIC_RENDER', true);
        include get_template_directory() . '/page-tutoring.php';
    }
    
    wp_reset_query();
    
    $html = ob_get_clean();
    error_log($html);
    if (empty($html)) {
        return;
    }

    file_put_contents(get_template_directory() . TUTORING_SNAPSHOT_PATH, $html);
}

// REBUILDS PAGE EVERY VISIT FOR TESTING
// CHANGE TO MIDNIGHT ONCE EVERYTHING IS WORKING
add_action('wp', function() {
    wp_clear_scheduled_hook('tutoring_rebuild_snapshot');
    if (!wp_next_scheduled('tutoring_rebuild_snapshot')) {
        wp_clear_scheduled_hook('tutoring_rebuild_snapshot');
        $midnight_et = new DateTime('now', new DateTimeZone('America/New_York'));
        wp_schedule_event($midnight_et->getTimestamp(), 'daily', 'tutoring_rebuild_snapshot');
    }
});

add_action('tutoring_rebuild_snapshot', 'tutoring_generate_static_snapshot');

// Utility Functions
//---------------------------------------------------------------------------------------------------------------------
{
    function tutoring_day_label($abbr_day) {
        return DAYS_OF_WEEK[$abbr_day] ?? $abbr_day;
    }

    function get_day_abbr($full_day) {
        return array_search(ucwords(strtolower($full_day)), DAYS_OF_WEEK) ?: null;
    }

    function tutoring_format_time($time) {
        $formatted = str_replace(
            ["am", "pm"],
            ["a.m.", "p.m."],
            DateTime::createFromFormat("H:i:s", $time)->format("g:i a")
        );
        return str_replace(["12:00 p.m.", "12:00 a.m."], ["Noon", "Midnight"], strtolower($formatted));
    }

    function tutoring_admin_time_label($time) {
        if (!$time) {
            return "";
        }
        return tutoring_format_time($time);
    }

    function tutoring_anchor_from_subject($subject) {
        return strtolower($subject["subject_code"]);
    }

    function tutoring_subject_heading($subject) {
        return esc_html($subject["subject_name"]) . " Courses";
    }

    function tutoring_admin_user_label($user) {
        $name = trim(($user["first_name"] ?? "") . " " . ($user["last_name"] ?? ""));
        if ($name === "") {
            $name = $user["user_login"];
        }
        return $name . " (" . $user["user_login"] . ")";
    }

    function display_snake_case($snake_case_str) {
        return ucwords(str_replace("_", " ", $snake_case_str));
    }

    function display_roles($roles) {
        return display_snake_case(str_replace("asc", "ASC", implode(", ", $roles)) ?? "—");
    }

    function tutoring_time_options($base, $max, $step, $offset, $pad) {
        $base += $offset ? $step : 0;
        for ($m = $base; $m < $max; $m += $step) {
            $val = $pad ? sprintf("%02d", $m) : $m;
            echo '<option value="' . esc_attr(sprintf("%02d", $m)) . '">' . esc_html($val) . "</option>";
        }
    }

    function tutoring_hour_options() {
        tutoring_time_options(0, 13, 1, true, false);
    }

    function tutoring_minute_options($step = 15, $offset = false, $pad = false) {
        tutoring_time_options(0, 60, $step, $offset, $pad);
    }

    function tutoring_get_tutor_status($day_of_week, $start_time, $end_time, $event_types, $u_events) {
        $now       = new DateTime("now", new DateTimeZone("America/New_York"));
        $curr_date = $now->format("Y-m-d");
        $curr_time = $now->format("H:i:s");
        $curr_day  = strtoupper($now->format("D"));

        $co_event   = null;
        $late_event = null;
        $le_event   = null;
        $ac_event   = null;

        foreach ($u_events as $ev) {
            $type_name = $event_types[$ev["event_type_id"]] ?? "";
            $start_day = $ev["start_day"];
            $final_day = $ev["final_day"] ?? $ev["start_day"];

            switch ($type_name) {
                case "called_out":
                    if ($curr_date >= $start_day && $curr_date <= $final_day) {
                        $co_event = $ev;
                    } elseif ($co_event === null && $start_day > $curr_date) {
                        $seven_days_out = (new DateTime($curr_date))->modify("+7 days")->format("Y-m-d");
                        if ($start_day <= $seven_days_out) {
                            $co_event = $ev;
                        }
                    }
                    break;

                case "late":
                    if ($start_day === $curr_date) {
                        $late_event = $ev;
                    }
                    break;

                case "leaving_early":
                    if ($start_day === $curr_date) {
                        $le_event = $ev;
                    }
                    break;

                case "at_capacity":
                    if ($start_day === $curr_date) {
                        $ac_event = $ev;
                    }
                    break;
            }
        }

        $co_note      = "";
        $co_in_effect = false;

        if ($co_event !== null) {
            $abs_start    = $co_event["start_day"];
            $abs_final    = $co_event["final_day"] ?? $co_event["start_day"];
            $fmt_start    = (new DateTime($abs_start))->format("M j");
            $fmt_end      = (new DateTime($abs_final))->format("M j");
            $co_note      = "Called out on: " . ($abs_start === $abs_final ? $fmt_start : "$fmt_start to $fmt_end");

            $six_days_out = (new DateTime($curr_date))->modify("+6 days")->format("Y-m-d");
            $check        = new DateTime(max($abs_start, $curr_date));
            $loop_end     = new DateTime(min($abs_final, $six_days_out));

            while ($check <= $loop_end) {
                if (strtoupper($check->format("D")) === $day_of_week) {
                    $co_in_effect = true;
                    break;
                }
                $check->modify("+1 day");
            }
        }

        $le_note  = "";
        $has_left = false;
        $is_today = ($curr_day === $day_of_week);

        if ($le_event !== null && $is_today) {
            $leaving_time = $le_event["leaving_time"] ?? null;

            if ($leaving_time !== null) {
                if ($curr_time >= $leaving_time) {
                    $has_left = true;
                } else {
                    $le_note = "Leaving early at " . tutoring_admin_time_label($leaving_time);
                }
            } else {
                $le_note = "Leaving early";
            }
        }

        $make = function($label, $color, $icon) use ($le_note, $co_note) {
            return [
                "label"              => $label,
                "color"              => $color,
                "icon"               => $icon,
                "leaving_early_note" => $le_note,
                "leaving_early_icon" => $le_note !== "" ? T_WARNING_ICON_SVG : "",
                "call_out_note"      => $co_note,
            ];
        };

        $is_active_window = ($curr_time >= $start_time && $curr_time <= $end_time);

        if ($co_in_effect)                    return $make("Called Out",               "#da2128", X_ICON_SVG);
        if (!$is_active_window || !$is_today) return $make("Unavailable",              "#212121", "");
        if ($has_left)                        return $make("Left Early",               "#da2128", X_ICON_SVG);
        if ($late_event !== null)             return $make("Running Late",             "#e65100", C_WARNING_ICON_SVG);
        if ($ac_event !== null)               return $make("At Capacity for Students", "#a67a05", PAUSE_ICON_SVG);

        return $make("Available", "#2e7d32", CHECK_ICON_SVG);
    }
}
//---------------------------------------------------------------------------------------------------------------------

// User Data Functions
//---------------------------------------------------------------------------------------------------------------------
{
    function user_query() {
        $u_schedule_data = wp_cache_get(U_SCHEDULE_CACHE_KEY, USER_CACHE_GROUP);

        if ($u_schedule_data === false) {
            $u_schedule_data = u_get_schedule_data(u_schedule_db_query());
            wp_cache_set(U_SCHEDULE_CACHE_KEY, $u_schedule_data, USER_CACHE_GROUP, HOUR_IN_SECONDS);
        }
        [$u_subjects, $u_courses, $u_schedule] = $u_schedule_data;

        $events_data = wp_cache_get(U_EVENTS_CACHE_KEY, USER_CACHE_GROUP);

        if ($events_data === false) {
            $events_data = u_get_events_data(events_db_query());
            wp_cache_set(U_EVENTS_CACHE_KEY, $events_data, USER_CACHE_GROUP, HOUR_IN_SECONDS);
        }
        [$event_types, $u_events] = $events_data;

        return [$u_subjects, $u_courses, $u_schedule, $event_types, $u_events];
    }

    function u_schedule_db_query() {
        global $wpdb;

        return $wpdb->get_results("
            SELECT
                s.user_id,
                s.day_of_week,
                s.start_time,
                s.end_time,
                c.course_id,
                c.course_subject,
                c.course_code,
                c.course_name,
                sub.subject_code,
                sub.subject_name,
                um.meta_value AS first_name
            FROM schedule s
            JOIN courses c         ON s.course_id      = c.course_id
            JOIN subjects sub      ON c.course_subject = sub.subject_code
            JOIN wp_users u        ON s.user_id        = u.ID
            JOIN wp_usermeta um    ON u.ID             = um.user_id
                                 AND um.meta_key       = 'first_name'
            ORDER BY
                sub.subject_code,
                c.course_code,
                s.day_of_week,
                s.start_time
        ");
    }

    function events_db_query() {
        global $wpdb;

        return $wpdb->get_results("
            SELECT
                e.event_id,
                e.user_id,
                e.event_type,
                e.start_day,
                e.final_day,
                e.leaving_time,
                et.event_type_id,
                et.event_name
            FROM events e
            JOIN event_types et ON e.event_type = et.event_type_id
            ORDER BY
                e.user_id,
                et.event_type_id DESC,
                e.start_day
        ");
    }

    function u_get_schedule_data($u_schedule_obj) {
        $u_subjects   = [];
        $u_courses    = [];
        $u_schedule   = [];
        $course_index = [];

        foreach ($u_schedule_obj as $row) {
            if (!isset($u_subjects[$row->subject_code])) {
                $u_subjects[$row->subject_code] = [
                    "subject_code" => $row->subject_code,
                    "subject_name" => $row->subject_name,
                ];
            }

            if (!isset($course_index[$row->course_id])) {
                $course_index[$row->course_id]           = true;
                $u_courses[$row->course_subject][] = [
                    "course_id"      => $row->course_id,
                    "course_code"    => $row->course_code,
                    "course_subject" => $row->course_subject,
                    "course_name"    => $row->course_name,
                ];
            }

            $u_schedule[$row->course_id][] = [
                "user_id"     => $row->user_id,
                "first_name"  => $row->first_name,
                "course_id"   => $row->course_id,
                "day_of_week" => $row->day_of_week,
                "start_time"  => $row->start_time,
                "end_time"    => $row->end_time,
            ];
        }

        return [array_values($u_subjects), $u_courses, $u_schedule];
    }

    function u_get_events_data($events_obj) {
        $event_types = [];
        $u_events    = [];

        foreach ($events_obj as $row) {
            if (!isset($event_types[$row->event_type_id])) {
                $event_types[$row->event_type_id] = $row->event_name;
            }

            $u_events[$row->user_id][] = [
                "event_type_id" => $row->event_type_id,
                "start_day"     => $row->start_day,
                "final_day"     => $row->final_day,
                "leaving_time"      => $row->leaving_time,
            ];
        }

        return [$event_types, $u_events];
    }
}
//---------------------------------------------------------------------------------------------------------------------

// Management Data Functions
//---------------------------------------------------------------------------------------------------------------------
{
    function management_query() {
        $m_subjects = wp_cache_get(M_SUBJECTS_CACHE_KEY, MANAGEMENT_CACHE_GROUP);
        $m_courses  = wp_cache_get(M_COURSES_CACHE_KEY, MANAGEMENT_CACHE_GROUP);
        $m_users    = wp_cache_get(M_USERS_CACHE_KEY, MANAGEMENT_CACHE_GROUP);
        $m_schedule = wp_cache_get(M_SCHEDULE_CACHE_KEY, MANAGEMENT_CACHE_GROUP);
        $event_data = wp_cache_get(M_EVENTS_CACHE_KEY, MANAGEMENT_CACHE_GROUP);

        if ($m_subjects === false) {
            $m_subjects = m_get_subjects_data(m_subjects_db_query());
            wp_cache_set(M_SUBJECTS_CACHE_KEY, $m_subjects, MANAGEMENT_CACHE_GROUP, HOUR_IN_SECONDS);
        }

        if ($m_courses === false) {
            $m_courses = m_get_courses_data(m_courses_db_query());
            wp_cache_set(M_COURSES_CACHE_KEY, $m_courses, MANAGEMENT_CACHE_GROUP, HOUR_IN_SECONDS);
        }

        if ($m_users === false) {
            $m_users = m_get_users_data(m_users_db_query());
            wp_cache_set(M_USERS_CACHE_KEY, $m_users, MANAGEMENT_CACHE_GROUP, HOUR_IN_SECONDS);
        }

        if ($m_schedule === false) {
            $m_schedule = m_get_schedule_data(m_schedule_db_query());
            wp_cache_set(M_SCHEDULE_CACHE_KEY, $m_schedule, MANAGEMENT_CACHE_GROUP, HOUR_IN_SECONDS);
        }

        if ($event_data === false) {
            [$m_event_types_obj, $m_events_obj] = m_events_db_query();
            [$m_event_types, $m_events]         = m_get_events_data($m_event_types_obj, $m_events_obj);
            wp_cache_set(M_EVENTS_CACHE_KEY, [$m_event_types, $m_events], MANAGEMENT_CACHE_GROUP, HOUR_IN_SECONDS);
        } else {
            [$m_event_types, $m_events] = $event_data;
        }

        return [$m_subjects, $m_courses, $m_users, $m_schedule, $m_event_types, $m_events];
    }

    function m_subjects_db_query() {
        global $wpdb;

        return $wpdb->get_results("
            SELECT
                subject_code,
                subject_name,
                subject_count
            FROM subjects
        ");
    }

    function m_courses_db_query() {
        global $wpdb;

        return $wpdb->get_results("
            SELECT
                course_id,
                course_subject,
                course_code,
                course_name,
                course_count
            FROM courses
        ");
    }

    function m_users_db_query() {
        global $wpdb;

        return $wpdb->get_results("
            SELECT
                u.ID AS user_id,
                u.user_login,
                u.user_email,
                MAX(CASE WHEN um.meta_key = 'first_name'      THEN um.meta_value END) AS first_name,
                MAX(CASE WHEN um.meta_key = 'last_name'       THEN um.meta_value END) AS last_name,
                MAX(CASE WHEN um.meta_key = 'wp_capabilities' THEN um.meta_value END) AS capabilities
            FROM {$wpdb->users} u
            LEFT JOIN {$wpdb->usermeta} um
                ON u.ID = um.user_id
                AND um.meta_key IN ('first_name', 'last_name', 'wp_capabilities')
            WHERE u.ID IN (
                SELECT user_id FROM {$wpdb->usermeta}
                WHERE meta_key = 'wp_capabilities'
                AND (
                    meta_value LIKE '%\"tutor\"%'
                    OR meta_value LIKE '%\"asc_staff\"%'
                    OR meta_value LIKE '%\"asc_admin\"%'
                )
            )
            GROUP BY u.ID, u.user_login, u.user_email
            ORDER BY u.ID DESC
        ");
    }

    function m_schedule_db_query() {
        global $wpdb;

        return $wpdb->get_results("
            SELECT
                schedule_id,
                user_id,
                course_id,
                day_of_week,
                start_time,
                end_time
            FROM schedule
            ORDER BY schedule_id DESC
        ");
    }

    function m_events_db_query() {
        global $wpdb;

        $m_event_types_obj = $wpdb->get_results("
            SELECT
                event_type_id,
                event_name
            FROM event_types
            ORDER BY event_type_id DESC
        ");

        $m_events_obj = $wpdb->get_results("
            SELECT
                event_id,
                user_id,
                event_type,
                start_day,
                final_day,
                leaving_time
            FROM events
            ORDER BY user_id DESC
        ");

        return [$m_event_types_obj, $m_events_obj];
    }

    function m_get_subjects_data($m_subjects_obj) {
        $m_subjects = [];

        foreach ($m_subjects_obj as $row) {
            $m_subjects[$row->subject_code] = [
                "subject_code"  => $row->subject_code,
                "subject_name"  => $row->subject_name,
                "subject_count" => $row->subject_count,
            ];
        }

        return array_values($m_subjects);
    }

    function m_get_courses_data($m_courses_obj) {
        $m_courses = [];

        foreach ($m_courses_obj as $row) {
            $m_courses[$row->course_id] = [
                "course_id"      => $row->course_id,
                "course_code"    => $row->course_code,
                "course_name"    => $row->course_name,
                "course_subject" => $row->course_subject,
                "course_count"   => $row->course_count,
            ];
        }

        return $m_courses;
    }

    function m_get_users_data($m_users_obj) {
        $m_users = [];

        foreach ($m_users_obj as $row) {
            $roles = null;
            if (!empty($row->capabilities)) {
                $caps = maybe_unserialize($row->capabilities);
                if (is_array($caps)) {
                    $roles = array_keys($caps);
                }
            }

            $m_users[$row->user_id] = [
                "user_id"    => $row->user_id,
                "user_login" => $row->user_login,
                "user_email" => $row->user_email,
                "first_name" => $row->first_name,
                "last_name"  => $row->last_name,
                "roles"      => $roles,
            ];
        }

        return array_values($m_users);
    }

    function m_get_schedule_data($m_schedule_obj) {
        $m_schedule = [];

        foreach ($m_schedule_obj as $row) {
            $m_schedule[] = [
                "schedule_id" => $row->schedule_id,
                "user_id"     => $row->user_id,
                "course_id"   => $row->course_id,
                "day_of_week" => $row->day_of_week,
                "start_time"  => $row->start_time,
                "end_time"    => $row->end_time,
            ];
        }

        return $m_schedule;
    }

    function m_get_events_data($m_event_types_obj, $m_events_obj) {
        $event_types = [];
        $m_events    = [];

        foreach ($m_event_types_obj as $row) {
            $event_types[$row->event_type_id] = [
                "event_type_id" => $row->event_type_id,
                "event_name"    => $row->event_name,
            ];
        }

        foreach ($m_events_obj as $row) {
            $m_events[] = [
                "event_id"   => $row->event_id,
                "user_id"    => $row->user_id,
                "event_type" => $row->event_type,
                "start_day"  => $row->start_day,
                "final_day"  => $row->final_day,
                "leaving_time"   => $row->leaving_time,
            ];
        }

        return [array_values($event_types), $m_events];
    }
}
//---------------------------------------------------------------------------------------------------------------------

