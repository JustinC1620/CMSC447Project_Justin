<?php
const U_SCHEDULE_CACHE_KEY   = "user_schedule";
const EVENTS_CACHE_KEY       = "events";
const USER_CACHE_GROUP       = "user_group";

const M_SCHEDULE_CACHE_KEY   = "management_schedule";
const MANAGEMENT_CACHE_GROUP = "management_group";

//---------------------------------------------------------------------------------------------------------------------
function user_query() {
    $uScheduleData = wp_cache_get(U_SCHEDULE_CACHE_KEY, USER_CACHE_GROUP);

    if ($uScheduleData === false) {
        $uScheduleObj = u_schedule_db_query();
        $uScheduleData = u_get_schedule_data($uScheduleObj);
        wp_cache_set(U_SCHEDULE_CACHE_KEY, $uScheduleData, USER_CACHE_GROUP, HOUR_IN_SECONDS);
    }
    [$uSubjects, $uCourses, $uSchedule] = $uScheduleData;

    $eventsData = wp_cache_get(EVENTS_CACHE_KEY, USER_CACHE_GROUP);
    if ($eventsData === false) {
        $eventsObj = events_db_query();
        $eventsData = u_get_events_data($eventsObj);
        wp_cache_set(EVENTS_CACHE_KEY, $eventsData, USER_CACHE_GROUP, HOUR_IN_SECONDS);
    }
    [$eventTypes, $uEvents] = $eventsData;

    return [$uSubjects, $uCourses, $uSchedule, $eventTypes, $uEvents];
}


function u_schedule_db_query() {
    global $wpdb;

    $uScheduleObj = $wpdb->get_results("
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

    return $uScheduleObj;
}


function events_db_query() {
    global $wpdb;

    $eventsObj = $wpdb->get_results("
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

    return $eventsObj;
}


function u_get_schedule_data($uScheduleObj) {
    $uSubjects = []; $uCourses = []; $uSchedule = [];
    $course_index = [];
    foreach ($uScheduleObj as $row) {
        if (!isset($uSubjects[$row->subject_code])) {
            $uSubjects[$row->subject_code] = [
                "subject_code"  => $row->subject_code,
                "subject_name"  => $row->subject_name
            ];
        }

        if (!isset($course_index[$row->course_id])) {
            $course_index[$row->course_id] = true;
            $uCourses[$row->course_subject][] = [
                "course_id"      => $row->course_id,
                "course_code"    => $row->course_code,
                "course_subject" => $row->course_subject,
                "course_name"    => $row->course_name
            ];
        }

        $uSchedule[$row->course_id][] = [
            "user_id"        => $row->user_id,
            "first_name"     => $row->first_name,
            "course_id"      => $row->course_id,  
            "day_of_week"    => $row->day_of_week,
            "start_time"     => $row->start_time,
            "end_time"       => $row->end_time
        ];
    }
    return [array_values($uSubjects), $uCourses, $uSchedule];
}


function u_get_events_data($eventsObj) {
    $eventTypes = []; $uEvents = [];
    foreach ($eventsObj as $row) {
        if (!isset($eventTypes[$row->event_type_id])) {
            $eventTypes[$row->event_type_id] = [
                "event_type_id" => $row->event_type_id,
                "event_name"    => $row->event_name
            ];
        }

        $uEvents[] = [
            "user_id"        => $row->user_id,
            "event_type_id"  => $row->event_type_id,
            "start_day"      => $row->start_day,  
            "final_day"      => $row->final_day,
            "duration"       => $row->duration
        ];
    }
    return [array_values($eventTypes), $uEvents];
}
//---------------------------------------------------------------------------------------------------------------------


//---------------------------------------------------------------------------------------------------------------------
function management_query() {
    $mScheduleData = wp_cache_get(M_SCHEDULE_CACHE_KEY, MANAGEMENT_CACHE_GROUP);

    if ($mScheduleData === false) {
        $mScheduleObj = m_schedule_db_query();
        $mScheduleData = m_get_schedule_data($mScheduleObj);
        wp_cache_set(M_SCHEDULE_CACHE_KEY, $mScheduleData, MANAGEMENT_CACHE_GROUP, HOUR_IN_SECONDS);
    }
    [$mSubjects, $mCourses, $users, $mSchedule] = $mScheduleData;

    $eventsData = wp_cache_get(EVENTS_CACHE_KEY, USER_CACHE_GROUP);
    if ($eventsData === false) {
        $eventsObj = events_db_query();
        $eventsData = m_get_events_data($eventsObj);
        wp_cache_set(EVENTS_CACHE_KEY, $eventsData, USER_CACHE_GROUP, HOUR_IN_SECONDS);
    }
    [$eventTypes, $mEvents] = $eventsData;

    return [$mSubjects, $mCourses, $users, $mSchedule, $eventTypes, $mEvents];
}


function m_schedule_db_query() {
    global $wpdb;

    // Subjects
    $subjects = $wpdb->get_results("
        SELECT
            subject_code,
            subject_name,
            subject_count
        FROM subjects
    ");

    $courses = $wpdb->get_results("
        SELECT
            course_id,
            course_subject,
            course_code,
            course_name,
            course_count
        FROM courses
    ");

    $users = $wpdb->get_results("
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
        GROUP BY u.ID, u.user_login, u.user_email
    ");

    $schedule = $wpdb->get_results("
        SELECT
            schedule_id,
            user_id,
            course_id,
            day_of_week,
            start_time,
            end_time
        FROM schedule
    ");

    return [
        'subjects' => $subjects,
        'courses'  => $courses,
        'users'    => $users,
        'schedule' => $schedule
    ];
}


function m_get_schedule_data($mScheduleObj) {
    $mSubjects = []; $mCourses = []; $users = []; $mSchedule = [];

    foreach ($mScheduleObj['subjects'] as $row) {
        if (!isset($mSubjects[$row->subject_code])) {
            $mSubjects[$row->subject_code] = [
                'subject_code'  => $row->subject_code,
                'subject_name'  => $row->subject_name,
                'subject_count' => $row->subject_count
            ];
        }
    }

    foreach ($mScheduleObj['courses'] as $row) {
        if (!isset($mCourses[$row->course_id])) {
            $mCourses[$row->course_id] = [
                'course_id'      => $row->course_id,
                'course_code'    => $row->course_code,
                'course_name'    => $row->course_name,
                'course_subject' => $row->course_subject,
                'course_count'   => $row->course_count
            ];
        }
    }

    foreach ($mScheduleObj['users'] as $row) {
        if (!isset($users[$row->user_id])) {

            $roles = null;
            if (!empty($row->capabilities)) {
                $caps = maybe_unserialize($row->capabilities);
                if (is_array($caps)) {
                    $roles = array_key_first($caps);
                }
            }

            $users[$row->user_id] = [
                'user_id'    => $row->user_id,
                'user_login' => $row->user_login,
                'user_email' => $row->user_email,
                'first_name' => $row->first_name,
                'last_name'  => $row->last_name,
                'roles'      => $roles
            ];
        }
    }

    foreach ($mScheduleObj['schedule'] as $row) {
        $mSchedule[] = [
            "schedule_id" => $row->schedule_id,
            "user_id"     => $row->user_id,
            "course_id"   => $row->course_id,
            "day_of_week" => $row->day_of_week,
            "start_time"  => $row->start_time,
            "end_time"    => $row->end_time
        ];
    }

    return [
        array_values($mSubjects),
        array_values($mCourses),
        array_values($users),
        $mSchedule
    ];
}


function m_get_events_data($eventsObj) {
    $eventTypes = []; $mEvents = [];
    foreach ($eventsObj as $row) {
        if (!isset($eventTypes[$row->event_type_id])) {
            $eventTypes[$row->event_type_id] = [
                "event_type_id" => $row->event_type_id,
                "event_name"    => $row->event_name
            ];
        }

        $mEvents[] = [
            "event_id"       => $row->event_id,
            "user_id"        => $row->user_id,
            "event_type_id"  => $row->event_type_id,
            "start_day"      => $row->start_day,  
            "final_day"      => $row->final_day,
            "duration"       => $row->duration
        ];
    }
    return [array_values($eventTypes), $mEvents];
}
//---------------------------------------------------------------------------------------------------------------------


//---------------------------------------------------------------------------------------------------------------------
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script(
        'scripts',
        get_template_directory_uri() . '/js/scripts.js',
        [], // no dependencies
        '1.0',
        true // load in footer
    );

    wp_localize_script('sights-script', 'wpApiSettings', [
        'nonce' => wp_create_nonce('wp_rest'),
        'root'  => esc_url_raw(rest_url()),
    ]);
});


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
            'validate_callback' => 'is_numeric',
            'sanitize_callback' => 'absint'
        ],
        'course_id' => [
            'required'          => true,
            'validate_callback' => 'is_numeric',
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
                'validate_callback' => 'is_numeric',
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
                'validate_callback' => 'is_numeric',
                'sanitize_callback' => 'absint',
            ],
            'user_id' => [
                'required'          => true,
                'validate_callback' => 'is_numeric',
                'sanitize_callback' => 'absint',
            ],
            'course_id' => [
                'required'          => true,
                'validate_callback' => 'is_numeric',
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
                'validate_callback' => 'is_numeric',
                'sanitize_callback' => 'absint'
            ],
            'user_id' => [
                'required'          => true,
                'validate_callback' => 'is_numeric',
                'sanitize_callback' => 'absint'
            ],
            'start_day' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_date_field',
            ],
            'final_day' => [
                'required'          => false,
                'sanitize_callback' => 'sanitize_date_field',
                'defualt'           => 'null'
            ],
            'duration' => [
                'required'          => false,
                'validate_callback' => 'is_numeric',
                'sanitize_callback' => 'absint',
                'defualt'           => 'null'
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
                'validate_callback' => 'is_numeric',
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
                'validate_callback' => 'is_numeric',
                'sanitize_callback' => 'absint',
            ],
            'event_type' => [
                'required'          => true,
                'validate_callback' => 'is_numeric',
                'sanitize_callback' => 'absint'
            ],
            'user_id' => [
                'required'          => true,
                'validate_callback' => 'is_numeric',
                'sanitize_callback' => 'absint'
            ],
            'start_day' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_date_field',
            ],
            'final_day' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_date_field',
            ],
            'duration' => [
                'required'          => true,
                'validate_callback' => 'is_numeric',
                'sanitize_callback' => 'absint'
            ],
        ],
    ]);
});


// Accounts REST API
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
            ],
            'user_email' => [
                'required'          => true,
                'validate_callback' => 'is_email',
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
                'sanitize_callback' => 'sanitize_text_field'
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
                'validate_callback' => 'is_numeric',
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
                'validate_callback' => 'is_numeric',
                'sanitize_callback' => 'absint'
            ],
            'roles' => [
                'required'          => true,
                'validate_callback' => 'validate_roles'
            ]
        ],
    ]);
});


function sanitize_time_field($timeStr) {
    if (strtolower($timeStr) === 'noon') {
        return '12:00:00';
    }

    $time = DateTime::createFromFormat('g:i a', str_replace('.', '', strtolower($timeStr)));

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
    if ($date == "null") {
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
    $valid_roles = ['tutor', 'asc_staff', 'asc_admin'];

    if (count($roles) < 1 || count($roles) > 2) {
        return false;
    }

    foreach ($roles as $role) {
        if (!in_array($role, $valid_roles)) {
            return false;
        }
    }

    if (in_array('asc_staff', $roles) && in_array('asc_admin', $roles)) {
        return false;
    }

    return true;
}

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


function create_event(WP_REST_Request $request) {
    global $wpdb;
    $event_type = $request->get_param('event_type');
    $user_id = $request->get_param('user_id');
    $start_day = $request->get_param('start_day');
    $final_day = $request->get_param('final_day');
    $duration = $request->get_param('$duration');

    if ($start_day === false || $start_day === null) {
        return new WP_Error('invalid_start_day', 'Invalid start day', ['status' => 400]);
    }

    if ($final_day === false) {
        return new WP_Error('invalid_final_day', 'Invalid final day', ['status' => 400]);
    }

    if ($duration == "null") {
        $duration = null;
    }

    $result = $wpdb->insert(
        'events',
        [
            '$event_type' => $event_type,
            'user_id'     => $user_id,
            'start_day'   => $start_day,
            'final_day' => $final_day,
            'duration'  => $duration
        ],
        ['%d', '%d', '%s', '%s', '%d']
    );

    if ($result === false) {
        return new WP_Error('db_error', $wpdb->last_error, ['status' => 500]);
    }

    wp_cache_delete(EVENTS_CACHE_KEY, USER_CACHE_GROUP);

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
        return new WP_Error('db_error', 'Failed to delete event', ['status' => 500]);
    }

    if ($result === 0) {
        return new WP_Error('not_found', 'No event found with that ID', ['status' => 404]);
    }
    
    wp_cache_delete(EVENTS_CACHE_KEY, USER_CACHE_GROUP);

    return rest_ensure_response(['deleted' => true, 'event_id' => $schedule_id]);
}


function update_event(WP_REST_Request $request) {
    global $wpdb;
    $event_id = $request->get_param('event_id');
    $event_type = $request->get_param('event_type');
    $user_id = $request->get_param('user_id');
    $start_day = $request->get_param('start_day');
    $final_day = $request->get_param('final_day');
    $duration = $request->get_param('$duration');

    if ($start_day === false || $start_day === null) {
        return new WP_Error('invalid_start_day', 'Invalid start day', ['status' => 400]);
    }

    if ($final_day === false) {
        return new WP_Error('invalid_final_day', 'Invalid final day', ['status' => 400]);
    }

    if ($duration == "null") {
        $duration = null;
    }

    $result = $wpdb->update(
        'events',
        [
            '$event_type' => $event_type,
            'user_id'     => $user_id,
            'start_day'   => $start_day,
            'final_day' => $final_day,
            'duration'  => $duration
        ],
        ['event_id' => $event_id],
        ['%d', '%d', '%s', '%s', '%s'],
        ['%d']
    );

    if ($result === false) {
        return new WP_Error('db_error', $wpdb->last_error, ['status' => 500]);
    }

    if ($result === 0) {
        return new WP_Error('not_found', 'No schedule found with that ID', ['status' => 404]);
    }

    wp_cache_delete(EVENTS_CACHE_KEY, USER_CACHE_GROUP);

    return rest_ensure_response(['updated' => true, 'event_id' => $event_id]);
}


function create_account(WP_REST_Request $request) {
    global $wpdb;
    $user_login = $request->get_param('user_login');
    $user_email = $request->get_param('user_email');
    $first_name = $request->get_param('first_name');
    $last_name  = $request->get_param('last_name');
    $roles      = $request->get_param('roles');

    $user_id = wp_insert_user([
        "user_login" => $user_login,
        "user_email" => $user_email,
        "first_name" => $first_name,
        "last_name"  => $last_name,
        "user_pass"  => wp_generate_password(64),
        "role"       => $roles[0]
    ]);

    if (is_wp_error($user_id)) {
        return new WP_Error('db_error', $user_id->get_error_message(), ['status' => 500]);
    }
    if (count($roles) == 2) {
        $user = new WP_User($user_id);
        $user->add_role($roles[1]);
    }

    wp_cache_delete(M_SCHEDULE_CACHE_KEY, MANAGEMENT_CACHE_GROUP);

    return rest_ensure_response(['created' => true, 'user_id' => $user_id]);
}


function delete_account(WP_REST_Request $request) {
    $user_id = $request->get_param('user_id');
    $curr_user_id = get_current_user_id();

    if ($user_id == $curr_user_id) {
        return new WP_Error('invalid_user_id', 'Cannot delete the current user', ['status' => 400]);
    }

    $is_tutor = false;
    $user = new WP_User($user_id);
    if (in_array('tutor', $user->roles)) {
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
        wp_cache_delete(EVENTS_CACHE_KEY, USER_CACHE_GROUP);
        wp_cache_delete(U_SCHEDULE_CACHE_KEY, USER_CACHE_GROUP);
    }
    wp_cache_delete(M_SCHEDULE_CACHE_KEY, MANAGEMENT_CACHE_GROUP);

    return rest_ensure_response(['deleted' => true, 'account_id' => $account_id]);
}


function update_account(WP_REST_Request $request) {
    global $wpdb;
    $user_id = $request->get_param('user_id');
    $curr_user_id = get_current_user_id();
    $roles = $request->get_param('roles');

    if ($user_id == $curr_user_id) {
        return new WP_Error('invalid_user_id', 'Cannot modify the current user', ['status' => 400]);
    }

    $user = new WP_User($user_id);
    if (!$user->exists()) {
        return new WP_Error('user_not_found', 'User does not exist.', ['status' => 404]);
    }

    $was_tutor = false;
    if (in_array('tutor', $user->roles)) {
        $was_tutor = true;
    }

    $wpdb->query('START TRANSACTION');

    $user->set_role($roles[0]);
    if (count($roles) == 2) {
        $user->add_role($roles[1]);
    }

    if (!in_array('tutor', $roles) && $was_tutor) {
        $cleaned = clean_up_user($user_id);
        if (is_wp_error($cleaned)) {
            $wpdb->query('ROLLBACK');
            return $cleaned;
        }

        wp_cache_delete(EVENTS_CACHE_KEY, USER_CACHE_GROUP);
        wp_cache_delete(U_SCHEDULE_CACHE_KEY, USER_CACHE_GROUP);
    }

    $wpdb->query('COMMIT');

    wp_cache_delete(M_SCHEDULE_CACHE_KEY, MANAGEMENT_CACHE_GROUP);

    return rest_ensure_response(['updated' => true, 'user_id' => $user_id]);
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
//---------------------------------------------------------------------------------------------------------------------


//---------------------------------------------------------------------------------------------------------------------
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

// umbc_db REST API
add_action('rest_api_init', function() {
    register_rest_route('asc-tutoring/v1', '/umbc_db/accounts', [
        'methods'             => 'GET',
        'callback'            => 'get_umbc_accounts',
        'permission_callback' => function() {
            return current_user_can('admin_control');
        },
        'args' => [
            'search_str' => [
                'required'          => true,
                'validate_callback' => 'is_string',
                'sanitize_callback' => 'sanitize_text_field',
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
                'required'          => true,
                'validate_callback' => 'is_string',
                'sanitize_callback' => 'sanitize_text_field',
            ]
        ],
    ]);
});


function get_courses_accounts(WP_REST_Request $request) {
    $search_str = $request->get_param('search_str');

    if (strlen($search_str) < 1) {
        return new WP_Error('invalid_param', 'search_str cannot be empty or whitespace.', ['status' => 400]);
    }

    $umbcPdo = db_connect_root('umbc_db');
    try {
        $stmt = $umbcPdo->prepare("SELECT 
                                       c.course_id,
                                       c.course_subject,
                                       s.subject_name,
                                       c.course_code,
                                       c.course_name
                                   FROM umbc_courses c
                                   JOIN umbc_subjects s ON c.course_subject = s.subject_code
                                   WHERE 
                                       c.course_subject LIKE :search
                                       OR s.subject_name LIKE :search
                                       OR c.course_code LIKE :search
                                       OR c.course_name LIKE :search
                                   ORDER BY c.course_subject, c.course_code");

        if (!$stmt) {
            error_log('UMBC course search: Failed to prepare statement.');
            return new WP_Error('db_error', 'Query preparation failed.', ['status' => 500]);
        }

        $stmt->bindValue(':search', '%' . $search_str . '%', PDO::PARAM_STR);
        $stmt->execute();

        $umbc_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        return new WP_Error('db_error', 'Failed to retrieve courses.', ['status' => 500]);
    }

    return rest_ensure_response(['success' => true, 'umbc_courses' => $umbc_courses]);
}


function get_umbc_accounts(WP_REST_Request $request) {
    $search_str = $request->get_param('search_str');

    if (strlen($search_str) < 1) {
        return new WP_Error('invalid_param', 'search_str cannot be empty or whitespace.', ['status' => 400]);
    }

    $umbcPdo = db_connect_root('umbc_db');
    try {
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
                                   ORDER BY last_name, first_name");

        if (!$stmt) {
            error_log('UMBC account search: Failed to prepare statement.');
            return new WP_Error('db_error', 'Query preparation failed.', ['status' => 500]);
        }

        $stmt->bindValue(':search', '%' . $search_str . '%', PDO::PARAM_STR);
        $stmt->execute();

        $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log('UMBC account search query failed: ' . $e->getMessage());
        return new WP_Error('db_error', 'Failed to retrieve accounts.', ['status' => 500]);
    }

    return rest_ensure_response(['success' => true, 'umbc_accounts' => $accounts]);
}
//---------------------------------------------------------------------------------------------------------------------