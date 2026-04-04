<?php
const U_SCHEDULE_CACHE_KEY   = "user_schedule";
const U_EVENTS_CACHE_KEY     = "user_events";
const USER_CACHE_GROUP       = "user_group";

const M_SUBJECTS_CACHE_KEY   = "management_subjects";
const M_COURSES_CACHE_KEY    = "management_courses";
const M_USERS_CACHE_KEY      = "management_users";
const M_SCHEDULE_CACHE_KEY   = "management_schedule";
const M_EVENTS_CACHE_KEY     = "management_events";
const MANAGEMENT_CACHE_GROUP = "management_group";

if (!function_exists('wp_delete_user')) {
    require_once ABSPATH . 'wp-admin/includes/user.php';
}

// Utility Functions
//---------------------------------------------------------------------------------------------------------------------
{
    add_action('wp_enqueue_scripts', function() {
        wp_enqueue_script(
            'scripts',
            get_template_directory_uri() . '/js/scripts.js',
            [],
            '1.0',
            true
        );

        wp_localize_script('scripts', 'wpApiSettings', [
            'nonce' => wp_create_nonce('wp_rest'),
            'root'  => esc_url_raw(rest_url()),
        ]);
    });


    // Supposed to prevent seeing "User Does not have Account Message"
    // Currently makes it that you can't log in ):
    /*
    add_action('init', function () {

        // Only run on wp-login.php
        if (strpos($_SERVER['REQUEST_URI'], 'wp-login.php') === false) {
            return;
        }

        // If this is NOT a normal login form submission
        // and user is not logged in → likely SAML failure
        if (!is_user_logged_in() && $_SERVER['REQUEST_METHOD'] !== 'POST') {

            // Optional: avoid infinite loop
            if (isset($_GET['redirected_from_saml'])) {
                return;
            }

            wp_redirect(home_url());
            exit;
        }
    });
    */

    add_filter('login_redirect', function($redirect_to, $request, $user) {
        return home_url();
    }, 10, 3);

    add_action('wp_logout', function() {
        wp_redirect( home_url() );
        exit;
    }, 10);

    function db_connect_root($dbName) {
        $host = "localhost";
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

    function tutoring_day_label($day) {
        $map = [
            'MON' => 'Monday',
            'TUE' => 'Tuesday',
            'WED' => 'Wednesday',
            'THU' => 'Thursday',
            'FRI' => 'Friday',
        ];
        return $map[$day] ?? $day;
    }

    function tutoring_format_time($time) {
    $formatted = str_replace(['am', 'pm'], ['a.m.', 'p.m.'], 
                             DateTime::createFromFormat('H:i:s', $time)->format('g:i a'));
    $formatted = str_replace(['12:00 p.m.', '12:00 a.m.'], ['Noon', 'Midnight'], strtolower($formatted));
    return $formatted;
    }

    function tutoring_admin_time_label($time) {
        if (!$time) {
            return '';
        }
        return tutoring_format_time($time);
    }

    function tutoring_anchor_from_subject($subject) {
        return strtolower($subject['subject_code']);
    }

    function tutoring_subject_heading($subject) {
        return esc_html($subject['subject_name']) . ' Courses';
    }

    function tutoring_admin_user_label($user) {
        $name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        if ($name === '') {
            $name = $user['user_login'];
        }
        return $name . ' (' . $user['user_login'] . ')';
    }   

    function display_snake_case($snake_case_str) {
        return ucwords(str_replace('_', ' ', $snake_case_str));
    }

    function display_roles($roles) {
        return display_snake_case(str_replace("asc", "ASC", implode(", ", $roles)) ?? '—');
    }

}
//---------------------------------------------------------------------------------------------------------------------

// User Data Functions
//---------------------------------------------------------------------------------------------------------------------
{
    function user_query() {
        $u_schedule_data = wp_cache_get(U_SCHEDULE_CACHE_KEY, USER_CACHE_GROUP);

        if ($u_schedule_data === false) {
            $u_schedule_obj = u_schedule_db_query();
            $u_schedule_data = u_get_schedule_data($u_schedule_obj);
            wp_cache_set(U_SCHEDULE_CACHE_KEY, $u_schedule_data, USER_CACHE_GROUP, HOUR_IN_SECONDS);
        }
        [$u_subjects, $u_courses, $u_schedule] = $u_schedule_data;

        $events_data = wp_cache_get(U_EVENTS_CACHE_KEY, USER_CACHE_GROUP);
        if ($events_data === false) {
            $events_obj = events_db_query();
            $events_data = u_get_events_data($events_obj);
            wp_cache_set(U_EVENTS_CACHE_KEY, $events_data, USER_CACHE_GROUP, HOUR_IN_SECONDS);
        }
        [$event_types, $u_events] = $events_data;

        return [$u_subjects, $u_courses, $u_schedule, $event_types, $u_events];
    }


    function u_schedule_db_query() {
        global $wpdb;

        $u_schedule_obj = $wpdb->get_results("
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
                                AND um.meta_key      = 'first_name'
            ORDER BY
                sub.subject_code,
                c.course_code,
                s.day_of_week,
                s.start_time
        ");

        return $u_schedule_obj;
    }


    function events_db_query() {
        global $wpdb;

        $events_obj = $wpdb->get_results("
            SELECT
                e.event_id,
                e.user_id,
                e.event_type,
                e.start_day,
                e.final_day,
                e.duration,
                et.event_type_id,
                et.event_name
            FROM events e
            JOIN event_types et ON e.event_type = et.event_type_id
            ORDER BY
                e.user_id,
                et.event_type_id DESC,
                e.start_day
        ");

        return $events_obj;
    }


    function u_get_schedule_data($u_schedule_obj) {
        $u_subjects = []; $u_courses = []; $u_schedule = [];
        $course_index = [];
        foreach ($u_schedule_obj as $row) {
            if (!isset($u_subjects[$row->subject_code])) {
                $u_subjects[$row->subject_code] = [
                    "subject_code"  => $row->subject_code,
                    "subject_name"  => $row->subject_name
                ];
            }

            if (!isset($course_index[$row->course_id])) {
                $course_index[$row->course_id] = true;
                $u_courses[$row->course_subject][] = [
                    "course_id"      => $row->course_id,
                    "course_code"    => $row->course_code,
                    "course_subject" => $row->course_subject,
                    "course_name"    => $row->course_name
                ];
            }

            $u_schedule[$row->course_id][] = [
                "user_id"        => $row->user_id,
                "first_name"     => $row->first_name,
                "course_id"      => $row->course_id,  
                "day_of_week"    => $row->day_of_week,
                "start_time"     => $row->start_time,
                "end_time"       => $row->end_time
            ];
        }
        return [array_values($u_subjects), $u_courses, $u_schedule];
    }


    function u_get_events_data($events_obj) {
        $event_types = []; $u_events = [];
        foreach ($events_obj as $row) {
            if (!isset($event_types[$row->event_type_id])) {
                $event_types[$row->event_type_id] = [
                    "event_type_id" => $row->event_type_id,
                    "event_name"    => $row->event_name
                ];
            }

            $u_events[] = [
                "user_id"        => $row->user_id,
                "event_type_id"  => $row->event_type_id,
                "start_day"      => $row->start_day,  
                "final_day"      => $row->final_day,
                "duration"       => $row->duration
            ];
        }
        return [array_values($event_types), $u_events];
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
            $m_subjects_obj = m_subjects_db_query();
            $m_subjects     = m_get_subjects_data($m_subjects_obj);
            wp_cache_set(M_SUBJECTS_CACHE_KEY, $m_subjects, MANAGEMENT_CACHE_GROUP, HOUR_IN_SECONDS);
        }

        if ($m_courses === false) {
            $m_courses_obj = m_courses_db_query();
            $m_courses     = m_get_courses_data($m_courses_obj);
            wp_cache_set(M_COURSES_CACHE_KEY, $m_courses, MANAGEMENT_CACHE_GROUP, HOUR_IN_SECONDS);
        }

        if ($m_users === false) {
            $m_users_obj = m_users_db_query();
            $m_users     = m_get_users_data($m_users_obj);
            wp_cache_set(M_USERS_CACHE_KEY, $m_users, MANAGEMENT_CACHE_GROUP, HOUR_IN_SECONDS);
        }

        if ($m_schedule === false) {
            $m_schedule_obj = m_schedule_db_query();
            $m_schedule     = m_get_schedule_data($m_schedule_obj);
            wp_cache_set(M_SCHEDULE_CACHE_KEY, $m_schedule, MANAGEMENT_CACHE_GROUP, HOUR_IN_SECONDS);
        }

        if ($event_data === false) {
            [$m_event_types_obj, $m_events_obj] = m_events_db_query();
            [$m_event_types, $m_events]         = m_get_events_data($m_event_types_obj, $m_events_obj);
            wp_cache_set(M_EVENTS_CACHE_KEY, [$m_event_types, $m_events], MANAGEMENT_CACHE_GROUP, HOUR_IN_SECONDS);
        }
        else {
            [$m_event_types, $m_events] = $event_data;
        }

        return [$m_subjects, $m_courses, $m_users, $m_schedule, $m_event_types, $m_events];
    }


    function m_subjects_db_query() {
        global $wpdb;

        $m_subjects_obj = $wpdb->get_results("
            SELECT
                subject_code,
                subject_name,
                subject_count
            FROM subjects
        ");
        return $m_subjects_obj;
    }


    function m_courses_db_query() {
        global $wpdb;

        $m_courses_obj = $wpdb->get_results("
            SELECT
                course_id,
                course_subject,
                course_code,
                course_name,
                course_count
            FROM courses
        ");
        return $m_courses_obj;
    }


    function m_users_db_query() {
        global $wpdb;

        $m_users_obj = $wpdb->get_results("
            SELECT
                u.ID as user_id,
                u.user_login,
                u.user_email,
                MAX(CASE WHEN um.meta_key = 'first_name' THEN um.meta_value END) AS first_name,
                MAX(CASE WHEN um.meta_key = 'last_name' THEN um.meta_value END) AS last_name,
                MAX(CASE WHEN um.meta_key = 'wp_capabilities' THEN um.meta_value END) AS capabilities
            FROM {$wpdb->users} u
            LEFT JOIN {$wpdb->usermeta} um
                ON u.ID = um.user_id
                AND um.meta_key IN ('first_name','last_name','wp_capabilities')
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
        return $m_users_obj;
    }


    function m_schedule_db_query() {
        global $wpdb;

        $m_schedule_obj = $wpdb->get_results("
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

        return $m_schedule_obj;
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
            Select
                event_id,
                user_id,
                event_type,
                start_day,
                final_day,
                duration
            FROM events
            ORDER BY user_id DESC
        ");

        return [$m_event_types_obj, $m_events_obj];
    }


    function m_get_subjects_data($m_subjects_obj) {
        $m_subjects = [];    
        foreach ($m_subjects_obj as $row) {
                $m_subjects[$row->subject_code] = [
                    'subject_code'  => $row->subject_code,
                    'subject_name'  => $row->subject_name,
                    'subject_count' => $row->subject_count
                ];
            }
        return array_values($m_subjects);
    }

        
    function m_get_courses_data($m_courses_obj) {
        $m_courses = [];
        foreach ($m_courses_obj as $row) {
            $m_courses[$row->course_id] = [
                'course_id'      => $row->course_id,
                'course_code'    => $row->course_code,
                'course_name'    => $row->course_name,
                'course_subject' => $row->course_subject,
                'course_count'   => $row->course_count
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
                    'user_id'    => $row->user_id,
                    'user_login' => $row->user_login,
                    'user_email' => $row->user_email,
                    'first_name' => $row->first_name,
                    'last_name'  => $row->last_name,
                    'roles'      => $roles
            ];
        }
        
        return array_values($m_users);
    }


    function m_get_schedule_data($m_scheduleObj) {
        $m_schedule = [];
        foreach ($m_scheduleObj as $row) {
            $m_schedule[] = [
                "schedule_id" => $row->schedule_id,
                "user_id"     => $row->user_id,
                "course_id"   => $row->course_id,
                "day_of_week" => $row->day_of_week,
                "start_time"  => $row->start_time,
                "end_time"    => $row->end_time
            ];
        }

        return $m_schedule;
    }


    function m_get_events_data($m_event_types_obj, $m_events_obj) {
        $event_types = []; $m_events = [];
        foreach ($m_event_types_obj as $row) {
            $event_types[$row->event_type_id] = [
                "event_type_id" => $row->event_type_id,
                "event_name"    => $row->event_name
            ];
        }

        foreach ($m_events_obj as $row) {
            $m_events[] = [
                "event_id"       => $row->event_id,
                "user_id"        => $row->user_id,
                "event_type"     => $row->event_type,
                "start_day"      => $row->start_day,  
                "final_day"      => $row->final_day,
                "duration"       => $row->duration
            ];
        }
        return [array_values($event_types), $m_events];
    }
}
//---------------------------------------------------------------------------------------------------------------------


// REST Routes
//---------------------------------------------------------------------------------------------------------------------
{
    // Schedule REST API
    add_action('rest_api_init', function() {
        register_rest_route('asc-tutoring/v1', '/schedule', [
        'methods'             => 'POST',
        'callback'            => 'create_schedule',
        'permission_callback' => function() {
            return current_user_can('admin_control');
        },
        'args' => [
            'user_id' => [
                'required'          => true,
                'validate_callback' => 'validate_numeric_param',
                'sanitize_callback' => 'absint'
            ],
            'course_id' => [
                'required'          => true,
                'validate_callback' => 'validate_numeric_param',
                'sanitize_callback' => 'absint'
            ],
            'day_of_week' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_day_field',
            ],
            'start_time' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_time_field',
            ],
            'end_time' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_time_field',
            ],
            'course_subject' => [
                'required'          => false,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'subject_name' => [
                'required'          => false,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'course_code' => [
                'required'          => false,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'course_name' => [
                'required'          => false,
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ],
    ]);

        register_rest_route('asc-tutoring/v1', '/schedule/(?P<schedule_id>\d+)', [
            'methods'             => 'DELETE',
            'callback'            => 'delete_schedule',
            'permission_callback' => function() {
                return current_user_can('admin_control');
            },
            'args' => [
                'schedule_id' => [
                    'required'          => true,
                    'validate_callback' => 'validate_numeric_param',
                    'sanitize_callback' => 'absint'
                ],
            ],
        ]);

        register_rest_route('asc-tutoring/v1', '/schedule/(?P<schedule_id>\d+)', [
            'methods'             => 'PATCH',
            'callback'            => 'update_schedule',
            'permission_callback' => function() {
                return current_user_can('admin_control');
            },
            'args' => [
                'schedule_id' => [
                    'required'          => true,
                    'validate_callback' => 'validate_numeric_param',
                    'sanitize_callback' => 'absint',
                ],
                'user_id' => [
                    'required'          => true,
                    'validate_callback' => 'validate_numeric_param',
                    'sanitize_callback' => 'absint',
                ],
                'course_id' => [
                    'required'          => true,
                    'validate_callback' => 'validate_numeric_param',
                    'sanitize_callback' => 'absint',
                ],
                'day_of_week' => [
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_day_field',
                ],
                'start_time' => [
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_time_field',
                ],
                'end_time' => [
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_time_field',
                ],
                'course_subject' => [
                    'required'          => false,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'subject_name' => [
                    'required'          => false,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'course_code' => [
                    'required'          => false,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'course_name' => [
                    'required'          => false,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);
    });


    // Events REST API

    /*
    * Event API notes:
    * - final_day and duration are nullable in the DB schema, so the REST layer must allow blank values too
    * - duration must accept either a number or an empty value
    * - create/update handlers must use the real field names: event_type and duration
    * - delete_event() should return event_id, not schedule_id
    */

    add_action('rest_api_init', function() {
        register_rest_route('asc-tutoring/v1', '/events', [
            'methods'             => 'POST',
            'callback'            => 'create_event',
            'permission_callback' => function() {
                return current_user_can('staff_control');
            },
            'args' => [
                'event_type' => [
                    'required'          => true,
                    'validate_callback' => 'validate_numeric_param',
                    'sanitize_callback' => 'absint'
                ],
                'user_id' => [
                    'required'          => true,
                    'validate_callback' => 'validate_numeric_param',
                    'sanitize_callback' => 'absint'
                ],
                'start_day' => [
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_date_field',
                ],
                'final_day' => [
                    'required'          => false,
                    'sanitize_callback' => 'sanitize_date_field',
                    'default'           => null
                ],
                'duration' => [
                    'required'          => false,
                    'validate_callback' => function($param, $request, $key) {
                        return $param === null || $param === '' || is_numeric($param);
                    },
                    'sanitize_callback' => function($value) {
                        return ($value === null || $value === '') ? null : absint($value);
                    },
                    'default'           => null
                ],
            ],
        ]);

        register_rest_route('asc-tutoring/v1', '/events/(?P<event_id>\d+)', [
            'methods'             => 'DELETE',
            'callback'            => 'delete_event',
            'permission_callback' => function() {
                return current_user_can('staff_control');
            },
            'args' => [
                'event_id' => [
                    'required'          => true,
                    'validate_callback' => 'validate_numeric_param',
                    'sanitize_callback' => 'absint'
                ],
            ],
        ]);

        register_rest_route('asc-tutoring/v1', '/events/(?P<event_id>\d+)', [
            'methods'             => 'PATCH',
            'callback'            => 'update_event',
            'permission_callback' => function() {
                return current_user_can('staff_control');
        },
            'args' => [
                'event_id' => [
                    'required'          => true,
                    'validate_callback' => 'validate_numeric_param',
                    'sanitize_callback' => 'absint',
                ],
                'event_type' => [
                    'required'          => true,
                    'validate_callback' => 'validate_numeric_param',
                    'sanitize_callback' => 'absint'
                ],
                'user_id' => [
                    'required'          => true,
                    'validate_callback' => 'validate_numeric_param',
                    'sanitize_callback' => 'absint'
                ],
                'start_day' => [
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_date_field',
                ],
                'final_day' => [
                    'required'          => false,
                    'sanitize_callback' => 'sanitize_date_field',
                    'default'           => null
                ],
                'duration' => [
                    'required'          => false,
                    'validate_callback' => function($param, $request, $key) {
                        return $param === null || $param === '' || is_numeric($param);
                    },
                    'sanitize_callback' => function($value) {
                        return ($value === null || $value === '') ? null : absint($value);
                    },
                    'default'           => null
                ],
            ],
        ]);
    });


    // wp_delete_user() is defined in wp-admin/includes/user.php and may not be loaded
    // during frontend/theme REST callbacks, so load it explicitly before account deletion.



    // Accounts REST API
    /*
    * Account API notes:
    * - user_id validators must use validate_numeric_param(), not raw is_numeric(),
    *   because WordPress passes 3 args to validate_callback
    * - roles is submitted as an array from the admin UI, so it should be validated
    *   as an array and sanitized inside the handler, not with sanitize_text_field
    * - delete_account() needs global $wpdb for its transaction calls
    * - delete_account() should return user_id, not an undefined account_id variable
    */

    add_action('rest_api_init', function() {
        register_rest_route('asc-tutoring/v1', '/accounts', [
            'methods'             => 'POST',
            'callback'            => 'create_account',
            'permission_callback' => function() {
                return current_user_can('admin_control');
            },
            'args' => [
                'user_login' => [
                    'required'          => true,
                    'validate_callback' => 'is_umbc_id',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'user_email' => [
                    'required'          => true,
                    'validate_callback' => 'is_email',
                    'sanitize_callback' => 'sanitize_email',
                ],
                'first_name' => [
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'last_name' => [
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'roles' => [
                    'required'          => true,
                    'validate_callback' => 'validate_roles',
                ],
            ],
        ]);

        register_rest_route('asc-tutoring/v1', '/accounts/(?P<user_id>\d+)', [
            'methods'             => 'DELETE',
            'callback'            => 'delete_account',
            'permission_callback' => function() {
                return current_user_can('admin_control');
            },
            'args' => [
                'user_id' => [
                    'required'          => true,
                    'validate_callback' => 'validate_numeric_param',
                    'sanitize_callback' => 'absint'
                ],
            ],
        ]);

        register_rest_route('asc-tutoring/v1', '/accounts/(?P<user_id>\d+)', [
            'methods'             => 'PATCH',
            'callback'            => 'update_account',
            'permission_callback' => function() {
                return current_user_can('admin_control');
            },
            'args' => [
                'user_id' => [
                    'required'          => true,
                    'validate_callback' => 'validate_numeric_param',
                    'sanitize_callback' => 'absint'
                ],
                'user_login' => [
                    'required'          => true,
                    'validate_callback' => 'is_umbc_id',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'user_email' => [
                    'required'          => true,
                    'validate_callback' => 'is_email',
                    'sanitize_callback' => 'sanitize_email',
                ],
                'first_name' => [
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'last_name' => [
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'roles' => [
                    'required'          => true,
                    'validate_callback' => 'validate_roles'
                ]
            ],
        ]);
    });
}
//---------------------------------------------------------------------------------------------------------------------


// REST Callback Helpers
//---------------------------------------------------------------------------------------------------------------------
{
    function validate_numeric_param($param, $request, $key) {
        return is_numeric($param);
    }

    function sanitize_time_field($timeStr) {
        if ($timeStr === null || $timeStr === '') {
            return false;
        }

        $timeStr = trim($timeStr);

        if (strtolower($timeStr) === 'noon') {
            return '12:00:00';
        }

        $time = DateTime::createFromFormat('H:i:s', $timeStr);
        if ($time !== false) {
            return $time->format('H:i:s');
        }

        $normalized = str_replace('.', '', strtolower($timeStr));
        $time = DateTime::createFromFormat('g:i a', $normalized);

        if ($time === false) {
            return false;
        }

        return $time->format('H:i:s');
    }


    function sanitize_day_field($day) {
        $days = [
            'Monday'    => 'MON',
            'Tuesday'   => 'TUE',
            'Wednesday' => 'WED',
            'Thursday'  => 'THU',
            'Friday'    => 'FRI',
        ];

        $day = ucfirst(strtolower($day));

        return $days[$day] ?? false;
    }


    function sanitize_date_field($date) {
        if ($date === null || $date === '' || strtolower((string)$date) === 'null') {
            return null;
        }

        $date = DateTime::createFromFormat('Y-m-d', $date);

        if ($date === false) {
            return false;
        }

        return $date->format('Y-m-d');
    }


    function is_umbc_id($id) {
        if (!preg_match('/^[A-Z]{2}\d{5}$/', $id)) {
            return new WP_Error(
                'invalid_id',
                "$id must be two uppercase letters followed by five digits (e.g. AB12345)",
                ['status' => 400]
            );
        }
        return true;
    }

    function validate_roles($roles) {
        if (!is_array($roles)) {
            return false;
        }

        $valid_roles = ['tutor', 'asc_staff', 'asc_admin'];

        if (count($roles) < 1 || count($roles) > 2) {
            return false;
        }

        foreach ($roles as $role) {
            if (!in_array($role, $valid_roles, true)) {
                return false;
            }
        }

        if (in_array('asc_staff', $roles, true) && in_array('asc_admin', $roles, true)) {
            return false;
        }

        return true;
    }

    function clean_up_user($user_id) {
        global $wpdb;

        $result = $wpdb->delete(
            'events',
            ['user_id' => $user_id],
            ['%s']
        );
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to delete event', ['status' => 500]);
        }

        $result = $wpdb->delete(
            'schedule',
            ['user_id' => $user_id],
            ['%s']
        );

        if ($result === false) {
            return new WP_Error('db_error', 'Failed to delete schedule', ['status' => 500]);
        }
    }
}
//---------------------------------------------------------------------------------------------------------------------


// Schedule Callbacks
//---------------------------------------------------------------------------------------------------------------------
{
    function create_schedule(WP_REST_Request $request) {
        global $wpdb;
        $user_id     = $request->get_param('user_id');
        $course_id   = $request->get_param('course_id');
        $day_of_week = $request->get_param('day_of_week');
        $start_time  = $request->get_param('start_time');
        $end_time    = $request->get_param('end_time');

        if ($day_of_week === false) {
            return new WP_Error('invalid_day', 'Invalid day of week', ['status' => 400]);
        }
        if ($start_time === false) {
            return new WP_Error('invalid_start_time', 'Invalid start time format', ['status' => 400]);
        }
        if ($end_time === false) {
            return new WP_Error('invalid_end_time', 'Invalid end time format', ['status' => 400]);
        }

        $wpdb->query('START TRANSACTION');

        $course_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM courses WHERE course_id = %d",
            $course_id
        ));

        if (!$course_exists) {
            $course_subject = $request->get_param('course_subject');
            $subject_name   = $request->get_param('subject_name');
            $course_code    = $request->get_param('course_code');
            $course_name    = $request->get_param('course_name');

            if (!$course_subject || !$course_code || !$course_name) {
                $wpdb->query('ROLLBACK');
                return new WP_Error('missing_course_data', 
                                    'course_subject, course_code, and course_name are required for new courses.',
                                    ['status' => 400]);
            }

            $subject_exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM subjects WHERE subject_code = %s",
                $course_subject
            ));

            if (!$subject_exists) {
                if (!$subject_name) {
                    $wpdb->query('ROLLBACK');
                    return new WP_Error('missing_subject_data', 
                                        'subject_name is required for new subjects.', ['status' => 400]);
                }

                $result = $wpdb->insert(
                    'subjects',
                    [
                        'subject_code'  => $course_subject,
                        'subject_name'  => $subject_name,
                        'subject_count' => 0
                    ],
                    ['%s', '%s', '%d']
                );

                if ($result === false) {
                    $wpdb->query('ROLLBACK');
                    return new WP_Error('db_error', $wpdb->last_error, ['status' => 500]);
                }
            }

            $result = $wpdb->insert(
                'courses',
                [
                    'course_id'      => $course_id,
                    'course_subject' => $course_subject,
                    'course_code'    => $course_code,
                    'course_name'    => $course_name,
                    'course_count'   => 0
                ],
                ['%d', '%s', '%s', '%s', '%d']
            );

            if ($result === false) {
                $wpdb->query('ROLLBACK');
                return new WP_Error('db_error', $wpdb->last_error, ['status' => 500]);
            }

            $result = $wpdb->query($wpdb->prepare(
                "UPDATE subjects SET subject_count = subject_count + 1 WHERE subject_code = %s",
                $course_subject
            ));

            if ($result === false) {
                $wpdb->query('ROLLBACK');
                return new WP_Error('db_error', $wpdb->last_error, ['status' => 500]);
            }
        }

        $result = $wpdb->insert(
            'schedule',
            [
                'user_id'     => $user_id,
                'course_id'   => $course_id,
                'day_of_week' => $day_of_week,
                'start_time'  => $start_time,
                'end_time'    => $end_time
            ],
            ['%d', '%d', '%s', '%s', '%s']
        );

        if ($result === false) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('db_error', $wpdb->last_error, ['status' => 500]);
        }

        $result = $wpdb->query($wpdb->prepare(
            "UPDATE courses SET course_count = course_count + 1 WHERE course_id = %d",
            $course_id
        ));

        if ($result === false) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('db_error', $wpdb->last_error, ['status' => 500]);
        }

        $wpdb->query('COMMIT');

        wp_cache_delete(U_SCHEDULE_CACHE_KEY, USER_CACHE_GROUP);
        wp_cache_delete(M_SUBJECTS_CACHE_KEY, MANAGEMENT_CACHE_GROUP);
        wp_cache_delete(M_COURSES_CACHE_KEY, MANAGEMENT_CACHE_GROUP);
        wp_cache_delete(M_SCHEDULE_CACHE_KEY, MANAGEMENT_CACHE_GROUP);
        

        return rest_ensure_response(['created' => true, 'schedule_id' => $wpdb->insert_id]);
    }


    function delete_schedule(WP_REST_Request $request) {
        global $wpdb;
        $schedule_id = $request->get_param('schedule_id');

        // Get the course_id before deleting
        $course_id = $wpdb->get_var($wpdb->prepare(
            "SELECT course_id FROM schedule WHERE schedule_id = %d",
            $schedule_id
        ));

        if ($course_id === null) {
            return new WP_Error('not_found', 'No schedule found with that ID', ['status' => 404]);
        }

        $wpdb->query('START TRANSACTION');

        $result = $wpdb->delete(
            'schedule',
            ['schedule_id' => $schedule_id],
            ['%d']
        );

        if ($result === false) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('db_error', 'Failed to delete schedule', ['status' => 500]);
        }

        $result = $wpdb->query($wpdb->prepare(
            "UPDATE courses SET course_count = course_count - 1 WHERE course_id = %d",
            $course_id
        ));

        if ($result === false) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('db_error', 'Failed to decrement course count', ['status' => 500]);
        }

        $wpdb->query('COMMIT');

        wp_cache_delete(U_SCHEDULE_CACHE_KEY, USER_CACHE_GROUP);
        wp_cache_delete(M_SCHEDULE_CACHE_KEY, MANAGEMENT_CACHE_GROUP);

        return rest_ensure_response(['deleted' => true, 'schedule_id' => $schedule_id]);
    }


    function update_schedule(WP_REST_Request $request) {
        global $wpdb;
        $schedule_id = $request->get_param('schedule_id');
        $user_id     = $request->get_param('user_id');
        $course_id   = $request->get_param('course_id');
        $day_of_week = $request->get_param('day_of_week');
        $start_time  = $request->get_param('start_time');
        $end_time    = $request->get_param('end_time');

        if ($day_of_week === false) {
            return new WP_Error('invalid_day', 'Invalid day of week', ['status' => 400]);
        }
        if ($start_time === false) {
            return new WP_Error('invalid_start_time', 'Invalid start time format', ['status' => 400]);
        }
        if ($end_time === false) {
            return new WP_Error('invalid_end_time', 'Invalid end time format', ['status' => 400]);
        }

        // Get the current course_id before making any changes
        $old_course_id = $wpdb->get_var($wpdb->prepare(
            "SELECT course_id FROM schedule WHERE schedule_id = %d",
            $schedule_id
        ));

        if ($old_course_id === null) {
            return new WP_Error('not_found', 'No schedule found with that ID', ['status' => 404]);
        }

        $wpdb->query('START TRANSACTION');

        $course_changed = (int)$old_course_id !== (int)$course_id;

        // Check if the new course exists
        $course_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM courses WHERE course_id = %d",
            $course_id
        ));

        if (!$course_exists) {
            $course_subject = $request->get_param('course_subject');
            $subject_name   = $request->get_param('subject_name');
            $course_code    = $request->get_param('course_code');
            $course_name    = $request->get_param('course_name');

            if (!$course_subject || !$course_code || !$course_name) {
                $wpdb->query('ROLLBACK');
                return new WP_Error('missing_course_data', 
                                    'course_subject, course_code, and course_name are required for new courses.', 
                                    ['status' => 400]);
            }

            $subject_exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM subjects WHERE subject_code = %s",
                $course_subject
            ));

            if (!$subject_exists) {
                if (!$subject_name) {
                    $wpdb->query('ROLLBACK');
                    return new WP_Error('missing_subject_data', 
                                        'subject_name is required for new subjects.', ['status' => 400]);
                }

                $result = $wpdb->insert(
                    'subjects',
                    [
                        'subject_code'  => $course_subject,
                        'subject_name'  => $subject_name,
                        'subject_count' => 0
                    ],
                    ['%s', '%s', '%d']
                );

                if ($result === false) {
                    $wpdb->query('ROLLBACK');
                    return new WP_Error('db_error', $wpdb->last_error, ['status' => 500]);
                }
            }

            $result = $wpdb->insert(
                'courses',
                [
                    'course_id'      => $course_id,
                    'course_subject' => $course_subject,
                    'course_code'    => $course_code,
                    'course_name'    => $course_name,
                    'course_count'   => 0
                ],
                ['%d', '%s', '%s', '%s', '%d']
            );

            if ($result === false) {
                $wpdb->query('ROLLBACK');
                return new WP_Error('db_error', $wpdb->last_error, ['status' => 500]);
            }

            $result = $wpdb->query($wpdb->prepare(
                "UPDATE subjects SET subject_count = subject_count + 1 WHERE subject_code = %s",
                $course_subject
            ));

            if ($result === false) {
                $wpdb->query('ROLLBACK');
                return new WP_Error('db_error', $wpdb->last_error, ['status' => 500]);
            }
        }

        if ($course_changed) {
            $result = $wpdb->query($wpdb->prepare(
                "UPDATE courses SET course_count = course_count - 1 WHERE course_id = %d",
                $old_course_id
            ));

            if ($result === false) {
                $wpdb->query('ROLLBACK');
                return new WP_Error('db_error', $wpdb->last_error, ['status' => 500]);
            }

            $result = $wpdb->query($wpdb->prepare(
                "UPDATE courses SET course_count = course_count + 1 WHERE course_id = %d",
                $course_id
            ));

            if ($result === false) {
                $wpdb->query('ROLLBACK');
                return new WP_Error('db_error', $wpdb->last_error, ['status' => 500]);
            }
        }

        $result = $wpdb->update(
            'schedule',
            [
                'user_id'     => $user_id,
                'course_id'   => $course_id,
                'day_of_week' => $day_of_week,
                'start_time'  => $start_time,
                'end_time'    => $end_time,
            ],
            ['schedule_id' => $schedule_id],
            ['%d', '%d', '%s', '%s', '%s'],
            ['%d']
        );

        if ($result === false) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('db_error', $wpdb->last_error, ['status' => 500]);
        }

        $wpdb->query('COMMIT');

        wp_cache_delete(U_SCHEDULE_CACHE_KEY, USER_CACHE_GROUP);
        wp_cache_delete(M_SCHEDULE_CACHE_KEY, MANAGEMENT_CACHE_GROUP);

        return rest_ensure_response(['updated' => true, 'schedule_id' => $schedule_id]);
    }
}
//---------------------------------------------------------------------------------------------------------------------


// Event Callbacks
//---------------------------------------------------------------------------------------------------------------------
{
    function create_event(WP_REST_Request $request) {
        global $wpdb;

        $event_type = $request->get_param('event_type');
        $user_id    = $request->get_param('user_id');
        $start_day  = $request->get_param('start_day');
        $final_day  = $request->get_param('final_day');
        $duration   = $request->get_param('duration');

        if ($start_day === false || $start_day === null) {
            return new WP_Error('invalid_start_day', 'Invalid start day', ['status' => 400]);
        }

        if ($final_day === false) {
            return new WP_Error('invalid_final_day', 'Invalid final day', ['status' => 400]);
        }

        if ($duration === '' || $duration === 'null') {
            $duration = null;
        }

        $result = $wpdb->insert(
            'events',
            [
                'event_type' => $event_type,
                'user_id'    => $user_id,
                'start_day'  => $start_day,
                'final_day'  => $final_day,
                'duration'   => $duration
            ],
            ['%d', '%d', '%s', '%s', '%d']
        );

        if ($result === false) {
            return new WP_Error('db_error', $wpdb->last_error, ['status' => 500]);
        }

        wp_cache_delete(U_EVENTS_CACHE_KEY, USER_CACHE_GROUP);
        wp_cache_delete(M_EVENTS_CACHE_KEY, MANAGEMENT_CACHE_GROUP);

        return rest_ensure_response(['created' => true, 'event_id' => $wpdb->insert_id]);
    }


    function delete_event(WP_REST_Request $request) {
        global $wpdb;
        $event_id = $request->get_param('event_id');
        
        $result = $wpdb->delete(
            'events',
            ['event_id' => $event_id],
            ['%d']
        );

        if ($result === false) {
            return new WP_Error('db_error', 'Failed to delete event' . print_r($event_id) , ['status' => 500]);
        }

        if ($result === 0) {
            return new WP_Error('not_found', 'No event found with ID: ' . $event_id, ['status' => 404]);
        }
        
        wp_cache_delete(U_EVENTS_CACHE_KEY, USER_CACHE_GROUP);
        wp_cache_delete(M_EVENTS_CACHE_KEY, MANAGEMENT_CACHE_GROUP);

        return rest_ensure_response(['deleted' => true, 'event_id' => $event_id]);
    }


    function update_event(WP_REST_Request $request) {
        global $wpdb;

        $event_id    = $request->get_param('event_id');
        $event_type  = $request->get_param('event_type');
        $user_id     = $request->get_param('user_id');
        $start_day   = $request->get_param('start_day');
        $final_day   = $request->get_param('final_day');
        $duration    = $request->get_param('duration');

        if ($start_day === false || $start_day === null) {
            return new WP_Error('invalid_start_day', 'Invalid start day', ['status' => 400]);
        }

        if ($final_day === false) {
            return new WP_Error('invalid_final_day', 'Invalid final day', ['status' => 400]);
        }

        if ($duration === '' || $duration === 'null') {
            $duration = null;
        }

        $result = $wpdb->update(
            'events',
            [
                'event_type' => $event_type,
                'user_id'    => $user_id,
                'start_day'  => $start_day,
                'final_day'  => $final_day,
                'duration'   => $duration
            ],
            ['event_id' => $event_id],
            ['%d', '%d', '%s', '%s', '%d'],
            ['%d']
        );

        if ($result === false) {
            return new WP_Error('db_error', $wpdb->last_error, ['status' => 500]);
        }

        wp_cache_delete(U_EVENTS_CACHE_KEY, USER_CACHE_GROUP);
        wp_cache_delete(M_EVENTS_CACHE_KEY, MANAGEMENT_CACHE_GROUP);

        return rest_ensure_response(['updated' => true, 'event_id' => $event_id]);
    }
}


// Account Callbacks
//---------------------------------------------------------------------------------------------------------------------
{
    function create_account(WP_REST_Request $request) {
        $user_login = $request->get_param('user_login');
        $user_email = $request->get_param('user_email');
        $first_name = $request->get_param('first_name');
        $last_name  = $request->get_param('last_name');
        $roles      = $request->get_param('roles');

        if (!is_array($roles) || count($roles) < 1) {
            return new WP_Error('invalid_roles', 'At least one valid role is required.', ['status' => 400]);
        }

        $roles = array_map('sanitize_text_field', $roles);

        $user_id = wp_insert_user([
            'user_login' => $user_login,
            'user_email' => $user_email,
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'user_pass'  => wp_generate_password(64),
            'role'       => $roles[0]
        ]);

        if (is_wp_error($user_id)) {
            return new WP_Error('db_error', $user_id->get_error_message(), ['status' => 500]);
        }

        if (count($roles) === 2) {
            $user = new WP_User($user_id);
            $user->add_role($roles[1]);
        }

        wp_cache_delete(M_USERS_CACHE_KEY, MANAGEMENT_CACHE_GROUP);

        return rest_ensure_response(['created' => true, 'user_id' => $user_id]);
    }


    function delete_account(WP_REST_Request $request) {
        global $wpdb;

        $user_id = $request->get_param('user_id');
        $curr_user_id = get_current_user_id();

        if ($user_id == $curr_user_id) {
            return new WP_Error('invalid_user_id', 'Cannot delete the current user', ['status' => 400]);
        }

        $is_tutor = false;
        $user = new WP_User($user_id);

        if (!$user->exists()) {
            return new WP_Error('not_found', 'No user found with that ID', ['status' => 404]);
        }

        if (in_array('tutor', (array) $user->roles, true)) {
            $is_tutor = true;
        }

        $wpdb->query('START TRANSACTION');

        $cleaned = clean_up_user($user_id);
        if (is_wp_error($cleaned)) {
            $wpdb->query('ROLLBACK');
            return $cleaned;
        }

        $result = wp_delete_user($user_id);

        if ($result === false) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('not_found', 'No user found with that ID', ['status' => 404]);
        }

        $wpdb->query('COMMIT');

        if ($is_tutor) {
            wp_cache_delete(U_SCHEDULE_CACHE_KEY, USER_CACHE_GROUP);
            wp_cache_delete(U_EVENTS_CACHE_KEY, USER_CACHE_GROUP);
            wp_cache_delete(M_SCHEDULE_CACHE_KEY, MANAGEMENT_CACHE_GROUP);
            wp_cache_delete(M_EVENTS_CACHE_KEY, USER_CACHE_GROUP);
        }

        wp_cache_delete(M_USERS_CACHE_KEY, MANAGEMENT_CACHE_GROUP);

        return rest_ensure_response(['deleted' => true, 'user_id' => $user_id]);
    }


    function update_account(WP_REST_Request $request) {
        global $wpdb;

        $user_id     = $request->get_param('user_id');
        $curr_user_id = get_current_user_id();

        $user_login  = $request->get_param('user_login');
        $user_email  = $request->get_param('user_email');
        $first_name  = $request->get_param('first_name');
        $last_name   = $request->get_param('last_name');
        $roles       = $request->get_param('roles');

        if ($user_id == $curr_user_id) {
            return new WP_Error('invalid_user_id', 'Cannot modify the current user', ['status' => 400]);
        }

        $user = new WP_User($user_id);
        if (!$user->exists()) {
            return new WP_Error('user_not_found', 'User does not exist.', ['status' => 404]);
        }

        $was_tutor = in_array('tutor', (array) $user->roles, true);

        $wpdb->query('START TRANSACTION');

        $updated = wp_update_user([
            'ID'         => $user_id,
            'user_login' => $user_login,
            'user_email' => $user_email,
            'first_name' => $first_name,
            'last_name'  => $last_name,
        ]);

        if (is_wp_error($updated)) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('db_error', $updated->get_error_message(), ['status' => 500]);
        }

        $user = new WP_User($user_id);
        $user->set_role($roles[0]);
        if (count($roles) === 2) {
            $user->add_role($roles[1]);
        }

        if (!in_array('tutor', $roles, true) && $was_tutor) {
            $cleaned = clean_up_user($user_id);
            if (is_wp_error($cleaned)) {
                $wpdb->query('ROLLBACK');
                return $cleaned;
            }

            wp_cache_delete(U_SCHEDULE_CACHE_KEY, USER_CACHE_GROUP);
            wp_cache_delete(U_EVENTS_CACHE_KEY, USER_CACHE_GROUP);
            wp_cache_delete(M_SCHEDULE_CACHE_KEY, MANAGEMENT_CACHE_GROUP);
            wp_cache_delete(M_EVENTS_CACHE_KEY, USER_CACHE_GROUP);
        }

        $wpdb->query('COMMIT');

        wp_cache_delete(M_USERS_CACHE_KEY, MANAGEMENT_CACHE_GROUP);

        return rest_ensure_response(['updated' => true, 'user_id' => $user_id]);
    }
}
//---------------------------------------------------------------------------------------------------------------------


// umbc_db REST API
//---------------------------------------------------------------------------------------------------------------------
{
    add_action('rest_api_init', function() {
        register_rest_route('asc-tutoring/v1', '/umbc_db/accounts', [
        'methods'             => 'GET',
        'callback'            => 'get_umbc_accounts',
        'permission_callback' => function() {
            return current_user_can('admin_control');
        },
        'args' => [
            'search_str' => [
                'required'          => false,
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ]
        ],
    ]);

        register_rest_route('asc-tutoring/v1', '/umbc_db/courses', [
            'methods'             => 'GET',
            'callback'            => 'get_umbc_courses',
            'permission_callback' => function() {
                return current_user_can('admin_control');
            },
            'args' => [
                'search_str' => [
                    'required'          => false,
                    'sanitize_callback' => 'sanitize_text_field',
                    'default'           => '',
                ]
            ],
        ]);
    });


    function get_umbc_courses(WP_REST_Request $request) {
        $search_str = trim((string) $request->get_param('search_str'));

        $umbcPdo = db_connect_root('umbc_db');

        try {
            if ($search_str === '') {
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
                    ORDER BY c.course_subject, c.course_code
                ");
                $stmt->execute();
            } else {
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
                        OR c.course_code LIKE :search
                        OR c.course_name LIKE :search
                    ORDER BY c.course_subject, c.course_code
                ");

                $stmt->bindValue(':search', '%' . $search_str . '%', PDO::PARAM_STR);
                $stmt->execute();
            }

            $umbc_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            return new WP_Error('db_error', 'Failed to retrieve courses.', ['status' => 500]);
        }

        return rest_ensure_response([
            'success' => true,
            'umbc_courses' => $umbc_courses
        ]);
    }


    function get_umbc_accounts(WP_REST_Request $request) {
        $search_str = trim((string) $request->get_param('search_str'));

        $umbcPdo = db_connect_root('umbc_db');

        try {
            if ($search_str === '') {
                $stmt = $umbcPdo->prepare("SELECT
                                            umbc_id,
                                            first_name,
                                            last_name,
                                            umbc_email
                                        FROM umbc_accounts
                                        ORDER BY last_name, first_name, umbc_id");
                $stmt->execute();
            } else {
                $stmt = $umbcPdo->prepare("SELECT
                                            umbc_id,
                                            first_name,
                                            last_name,
                                            umbc_email
                                        FROM umbc_accounts
                                        WHERE
                                            umbc_id LIKE :search
                                            OR first_name LIKE :search
                                            OR last_name LIKE :search
                                            OR umbc_email LIKE :search
                                        ORDER BY last_name, first_name, umbc_id");

                $stmt->bindValue(':search', '%' . $search_str . '%', PDO::PARAM_STR);
                $stmt->execute();
            }

            $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log('UMBC account search query failed: ' . $e->getMessage());
            return new WP_Error('db_error', 'Failed to retrieve accounts.', ['status' => 500]);
        }

        return rest_ensure_response(['success' => true, 'umbc_accounts' => $accounts]);
    }
}
//---------------------------------------------------------------------------------------------------------------------