<?php
const U_SCHEDULE_CACHE_KEY   = "user_schedule";
const EVENTS_CACHE_KEY       = "events";
const USER_CACHE_GROUP       = "user_group";

const M_SCHEDULE_CACHE_KEY   = "management_schedule";
const MANAGEMENT_CACHE_GROUP = "management_group";


function user_query() {
    $uScheduleObj = wp_cache_get(U_SCHEDULE_CACHE_KEY, USER_CACHE_GROUP);

    if ($uScheduleObj === false) {
        $uScheduleObj = u_schedule_db_query();
        wp_cache_set(U_SCHEDULE_CACHE_KEY, $uScheduleObj, USER_CACHE_GROUP, HOUR_IN_SECONDS);
    }

    $eventsObj = wp_cache_get(EVENTS_CACHE_KEY, USER_CACHE_GROUP);
    if ($eventsObj === false) {
        $eventsObj = events_db_query();
        wp_cache_set(EVENTS_CACHE_KEY, $eventsObj, USER_CACHE_GROUP, HOUR_IN_SECONDS);
    }

    [$uSubjects, $uCourses, $uSchedule] = u_get_schedule_data($uScheduleObj);
    [$eventTypes, $uEvents]             = u_get_events_data($eventsObj);

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
function management_query() {
    $mScheduleObj = wp_cache_get(M_SCHEDULE_CACHE_KEY, MANAGEMENT_CACHE_GROUP);

    if ($mScheduleObj === false) {
        $mScheduleObj = m_schedule_db_query();
        wp_cache_set(M_SCHEDULE_CACHE_KEY, $mScheduleObj, MANAGEMENT_CACHE_GROUP, HOUR_IN_SECONDS);
    }

    $eventsObj = wp_cache_get(EVENTS_CACHE_KEY, USER_CACHE_GROUP);
    if ($eventsObj === false) {
        $eventsObj = events_db_query();
        wp_cache_set(EVENTS_CACHE_KEY, $eventsObj, USER_CACHE_GROUP, HOUR_IN_SECONDS);
    }

    [$mSubjects, $mCourses, $users, $mSchedule] = m_get_schedule_data($mScheduleObj);
    [$eventTypes, $mEvents]                     = m_get_events_data($eventsObj);

    return [$mSubjects, $mCourses, $users, $mSchedule, $eventTypes, $mEvents];
}


function m_schedule_db_query() {
    global $wpdb;

    $mScheduleObj = $wpdb->get_results("
        SELECT
            s.schedule_id,
            s.user_id,
            s.day_of_week,
            s.start_time,
            s.end_time,
            c.course_id,
            c.course_subject,
            c.course_code,
            c.course_name,
            c.course_count,
            sub.subject_code,
            sub.subject_name,
            sub.subject_count,
            u.user_login,
            u.user_email,
            MAX(CASE WHEN um.meta_key = 'first_name'    THEN um.meta_value END) AS first_name,
            MAX(CASE WHEN um.meta_key = 'last_name'     THEN um.meta_value END) AS last_name,
            MAX(CASE WHEN um.meta_key = 'wp_capabilities' THEN um.meta_value END) AS capabilities
        FROM schedule s
        JOIN courses c      ON s.course_id      = c.course_id
        JOIN subjects sub   ON c.course_subject = sub.subject_code
        JOIN wp_users u        ON s.user_id     = u.ID
        JOIN wp_usermeta um    ON u.ID          = um.user_id
                          AND um.meta_key      IN ('first_name', 'last_name', 'wp_capabilities')
        GROUP BY
            s.schedule_id,
            s.user_id,
            s.day_of_week,
            s.start_time,
            s.end_time,
            c.course_id,
            c.course_subject,
            c.course_code,
            c.course_name,
            c.course_count,
            sub.subject_code,
            sub.subject_name,
            sub.subject_count,
            u.user_login,
            u.user_email
        ORDER BY
            sub.subject_code,
            c.course_code,
            s.day_of_week,
            s.start_time
    ");

    return $mScheduleObj;
}


function m_get_schedule_data($mScheduleObj) {
    $mSubjects = []; $mCourses = []; $users = []; $mSchedule = [];
    foreach ($mScheduleObj as $row) {
        if (!isset($mSubjects[$row->subject_code])) {
            $mSubjects[$row->subject_code] = [
                'subject_code'  => $row->subject_code,
                'subject_name'  => $row->subject_name,
                'subject_count' => $row->subject_count
            ];
        }

        if (!isset($mCourses[$row->course_id])) {
            $mCourses[$row->course_id] = [
                'course_id'      => $row->course_id,
                'course_code'    => $row->course_code,
                'course_name'    => $row->course_name,
                'course_subject' => $row->course_subject,
                'course_count'   => $row->course_count
            ];
        }

        if (!isset($users[$row->user_id])) {
            $users[$row->user_id] = [
                'user_id'    => $row->user_id,
                'user_login' => $row->user_login,
                'user_email' => $row->user_email,
                'first_name' => $row->first_name,
                'last_name'  => $row->last_name,
                'roles'      => array_key_first(unserialize($row->capabilities))
            ];
        }

        $mSchedule[] = [
            "schedule_id"    => $row->schedule_id,
            "user_id"        => $row->user_id,
            "course_id"      => $row->course_id,  
            "day_of_week"    => $row->day_of_week,
            "start_time"     => $row->start_time,
            "end_time"       => $row->end_time
        ];
    }
    return [array_values($mSubjects), array_values($mCourses), array_values($users), $mSchedule];
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
// Test for user_query()
// Login as WordPress Admin
// Navigate to localhost/drop-in-tutoring/?test_schedule=1
add_action('template_redirect', function() {
    if (!isset($_GET['test_user']) || !current_user_can('administrator')) {
        return;
    }

    wp_cache_delete(U_SCHEDULE_CACHE_KEY, USER_CACHE_GROUP);
    wp_cache_delete(EVENTS_CACHE_KEY, USER_CACHE_GROUP);

    [$uSubjects, $uCourses, $uSchedule, $eventTypes, $uEvents] = user_query();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Schedule Debug</title>
        <style>
            body     { font-family: monospace; padding: 2rem; background: #f1f1f1; }
            h2       { color: #333; }
            pre      { background: #fff; padding: 1rem; border: 1px solid #ddd; overflow-x: auto; }
            .meta    { color: #666; font-size: 0.9rem; margin-bottom: 1rem; }
            .pass    { color: green; }
            .fail    { color: red; }
            .empty   { color: orange; }
            .section { margin-bottom: 2rem; }
        </style>
    </head>
    <body>

        <h2>DB Queries</h2>
        <div class="meta section">
            <p>Last query:</p>
            <pre><?php global $wpdb; echo esc_html($wpdb->last_query); ?></pre>
            <?php if ($wpdb->last_error): ?>
                <p class="fail">DB Error: <?php echo esc_html($wpdb->last_error); ?></p>
            <?php else: ?>
                <p class="pass">✓ No DB errors</p>
            <?php endif; ?>
        </div>

        <h2>Cache Status</h2>
        <div class="section">
            <?php $cachedSchedule = wp_cache_get(U_SCHEDULE_CACHE_KEY, USER_CACHE_GROUP); ?>
            <?php if (false !== $cachedSchedule): ?>
                <p class="pass">✓ Schedule cache populated (<?php echo count($cachedSchedule); ?> raw rows)</p>
            <?php else: ?>
                <p class="fail">✗ Schedule cache empty after query</p>
            <?php endif; ?>

            <?php $cachedEvents = wp_cache_get(EVENTS_CACHE_KEY, USER_CACHE_GROUP); ?>
            <?php if (false !== $cachedEvents): ?>
                <p class="pass">✓ Events cache populated (<?php echo count($cachedEvents); ?> raw rows)</p>
            <?php else: ?>
                <p class="fail">✗ Events cache empty after query</p>
            <?php endif; ?>
        </div>

        <h2>Subjects</h2>
        <div class="section">
            <?php if (!empty($uSubjects)): ?>
                <p class="pass">✓ <?php echo count($uSubjects); ?> subjects extracted</p>
                <?php
                $codes = array_column($uSubjects, 'subject_code');
                if (count($codes) === count(array_unique($codes))): ?>
                    <p class="pass">✓ No duplicate subject codes</p>
                <?php else: ?>
                    <p class="fail">✗ Duplicate subject codes found</p>
                <?php endif; ?>
                <pre><?php var_dump($uSubjects); ?></pre>
            <?php else: ?>
                <p class="fail">✗ u_get_schedule_data() returned no subjects</p>
            <?php endif; ?>
        </div>

        <h2>Courses</h2>
        <div class="section">
            <?php if (!empty($uCourses)): ?>
                <p class="pass">✓ <?php echo count($uCourses); ?> courses extracted</p>
                <?php
                $ids = array_column($uCourses, 'course_id');
                if (count($ids) === count(array_unique($ids))): ?>
                    <p class="pass">✓ No duplicate course IDs</p>
                <?php else: ?>
                    <p class="fail">✗ Duplicate course IDs found</p>
                <?php endif; ?>
                <?php
                $subjectCodes    = array_column($uSubjects, 'subject_code');
                $orphanedCourses = array_filter($uCourses, fn($c) => !in_array($c['course_subject'], $subjectCodes));
                if (empty($orphanedCourses)): ?>
                    <p class="pass">✓ All courses reference a known subject</p>
                <?php else: ?>
                    <p class="fail">✗ <?php echo count($orphanedCourses); ?> course(s) reference an unknown subject</p>
                <?php endif; ?>
                <pre><?php var_dump($uCourses); ?></pre>
            <?php else: ?>
                <p class="fail">✗ u_get_schedule_data() returned no courses</p>
            <?php endif; ?>
        </div>

        <h2>Schedule</h2>
        <div class="section">
            <?php if (!empty($uSchedule)): ?>
                <p class="pass">✓ <?php echo count($uSchedule); ?> schedule rows</p>
                <?php
                $courseIds     = array_column($uCourses, 'course_id');
                $orphanedRows  = array_filter($uSchedule, fn($r) => !in_array($r['course_id'], $courseIds));
                if (empty($orphanedRows)): ?>
                    <p class="pass">✓ All schedule rows reference a known course</p>
                <?php else: ?>
                    <p class="fail">✗ <?php echo count($orphanedRows); ?> schedule row(s) reference an unknown course</p>
                <?php endif; ?>
                <?php
                $missingNames = array_filter($uSchedule, fn($r) => empty($r['first_name']));
                if (empty($missingNames)): ?>
                    <p class="pass">✓ All schedule rows have a first name</p>
                <?php else: ?>
                    <p class="fail">✗ <?php echo count($missingNames); ?> schedule row(s) missing a first name</p>
                <?php endif; ?>
                <pre><?php var_dump($uSchedule); ?></pre>
            <?php else: ?>
                <p class="fail">✗ u_get_schedule_data() returned no schedule rows</p>
            <?php endif; ?>
        </div>

        <h2>Event Types</h2>
        <div class="section">
            <?php if (!empty($eventTypes)): ?>
                <p class="pass">✓ <?php echo count($eventTypes); ?> event types extracted</p>
                <?php
                $typeIds = array_column($eventTypes, 'event_type_id');
                if (count($typeIds) === count(array_unique($typeIds))): ?>
                    <p class="pass">✓ No duplicate event type IDs</p>
                <?php else: ?>
                    <p class="fail">✗ Duplicate event type IDs found</p>
                <?php endif; ?>
                <pre><?php var_dump($eventTypes); ?></pre>
            <?php else: ?>
                <p class="empty">⚠ Event types table is empty</p>
            <?php endif; ?>
        </div>

        <h2>Events</h2>
        <div class="section">
            <?php if (!empty($uEvents)): ?>
                <p class="pass">✓ <?php echo count($uEvents); ?> events extracted</p>
                <?php
                $typeIds        = array_column($eventTypes, 'event_type_id');
                $orphanedEvents = array_filter($uEvents, fn($e) => !in_array($e['event_type_id'], $typeIds));
                if (empty($orphanedEvents)): ?>
                    <p class="pass">✓ All events reference a known event type</p>
                <?php else: ?>
                    <p class="fail">✗ <?php echo count($orphanedEvents); ?> event(s) reference an unknown event type</p>
                <?php endif; ?>
                <pre><?php var_dump($uEvents); ?></pre>
            <?php else: ?>
                <p class="empty">⚠ Events table is empty</p>
            <?php endif; ?>
        </div>

    </body>
    </html>
    <?php
    exit;
});


// Test for management_query()
// Login as WordPress Admin
// Navigate to localhost/drop-in-tutoring/?test_management=1
add_action('template_redirect', function() {
    if (!isset($_GET['test_management']) || !current_user_can('administrator')) {
        return;
    }

    wp_cache_delete(M_SCHEDULE_CACHE_KEY, MANAGEMENT_CACHE_GROUP);
    wp_cache_delete(EVENTS_CACHE_KEY, USER_CACHE_GROUP);

    [$mSubjects, $mCourses, $users, $mSchedule, $eventTypes, $mEvents] = management_query();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Management Debug</title>
        <style>
            body     { font-family: monospace; padding: 2rem; background: #f1f1f1; }
            h2       { color: #333; }
            pre      { background: #fff; padding: 1rem; border: 1px solid #ddd; overflow-x: auto; }
            .meta    { color: #666; font-size: 0.9rem; margin-bottom: 1rem; }
            .pass    { color: green; }
            .fail    { color: red; }
            .empty   { color: orange; }
            .section { margin-bottom: 2rem; }
        </style>
    </head>
    <body>

        <h2>DB Queries</h2>
        <div class="meta section">
            <p>Last query:</p>
            <pre><?php global $wpdb; echo esc_html($wpdb->last_query); ?></pre>
            <?php if ($wpdb->last_error): ?>
                <p class="fail">DB Error: <?php echo esc_html($wpdb->last_error); ?></p>
            <?php else: ?>
                <p class="pass">✓ No DB errors</p>
            <?php endif; ?>
        </div>

        <h2>Cache Status</h2>
        <div class="section">
            <?php $cachedSchedule = wp_cache_get(M_SCHEDULE_CACHE_KEY, MANAGEMENT_CACHE_GROUP); ?>
            <?php if (false !== $cachedSchedule): ?>
                <p class="pass">✓ Schedule cache populated (<?php echo count($cachedSchedule); ?> raw rows)</p>
            <?php else: ?>
                <p class="fail">✗ Schedule cache empty after query</p>
            <?php endif; ?>

            <?php $cachedEvents = wp_cache_get(EVENTS_CACHE_KEY, USER_CACHE_GROUP); ?>
            <?php if (false !== $cachedEvents): ?>
                <p class="pass">✓ Events cache populated (<?php echo count($cachedEvents); ?> raw rows)</p>
            <?php else: ?>
                <p class="fail">✗ Events cache empty after query</p>
            <?php endif; ?>
        </div>

        <h2>Subjects</h2>
        <div class="section">
            <?php if (!empty($mSubjects)): ?>
                <p class="pass">✓ <?php echo count($mSubjects); ?> subjects extracted</p>
                <?php
                $codes = array_column($mSubjects, 'subject_code');
                if (count($codes) === count(array_unique($codes))): ?>
                    <p class="pass">✓ No duplicate subject codes</p>
                <?php else: ?>
                    <p class="fail">✗ Duplicate subject codes found</p>
                <?php endif; ?>
                <pre><?php var_dump($mSubjects); ?></pre>
            <?php else: ?>
                <p class="fail">✗ m_get_schedule_data() returned no subjects</p>
            <?php endif; ?>
        </div>

        <h2>Courses</h2>
        <div class="section">
            <?php if (!empty($mCourses)): ?>
                <p class="pass">✓ <?php echo count($mCourses); ?> courses extracted</p>
                <?php
                $ids = array_column($mCourses, 'course_id');
                if (count($ids) === count(array_unique($ids))): ?>
                    <p class="pass">✓ No duplicate course IDs</p>
                <?php else: ?>
                    <p class="fail">✗ Duplicate course IDs found</p>
                <?php endif; ?>
                <?php
                $subjectCodes    = array_column($mSubjects, 'subject_code');
                $orphanedCourses = array_filter($mCourses, fn($c) => !in_array($c['course_subject'], $subjectCodes));
                if (empty($orphanedCourses)): ?>
                    <p class="pass">✓ All courses reference a known subject</p>
                <?php else: ?>
                    <p class="fail">✗ <?php echo count($orphanedCourses); ?> course(s) reference an unknown subject</p>
                <?php endif; ?>
                <pre><?php var_dump($mCourses); ?></pre>
            <?php else: ?>
                <p class="fail">✗ m_get_schedule_data() returned no courses</p>
            <?php endif; ?>
        </div>

        <h2>Users</h2>
        <div class="section">
            <?php if (!empty($users)): ?>
                <p class="pass">✓ <?php echo count($users); ?> users extracted</p>
                <?php
                $userIds = array_column($users, 'user_id');
                if (count($userIds) === count(array_unique($userIds))): ?>
                    <p class="pass">✓ No duplicate user IDs</p>
                <?php else: ?>
                    <p class="fail">✗ Duplicate user IDs found</p>
                <?php endif; ?>
                <?php
                $missingRoles = array_filter($users, fn($u) => empty($u['roles']));
                if (empty($missingRoles)): ?>
                    <p class="pass">✓ All users have a role</p>
                <?php else: ?>
                    <p class="fail">✗ <?php echo count($missingRoles); ?> user(s) missing a role</p>
                <?php endif; ?>
                <pre><?php var_dump($users); ?></pre>
            <?php else: ?>
                <p class="fail">✗ m_get_schedule_data() returned no users</p>
            <?php endif; ?>
        </div>

        <h2>Schedule</h2>
        <div class="section">
            <?php if (!empty($mSchedule)): ?>
                <p class="pass">✓ <?php echo count($mSchedule); ?> schedule rows</p>
                <?php
                $courseIds    = array_column($mCourses, 'course_id');
                $orphanedRows = array_filter($mSchedule, fn($r) => !in_array($r['course_id'], $courseIds));
                if (empty($orphanedRows)): ?>
                    <p class="pass">✓ All schedule rows reference a known course</p>
                <?php else: ?>
                    <p class="fail">✗ <?php echo count($orphanedRows); ?> schedule row(s) reference an unknown course</p>
                <?php endif; ?>
                <?php
                $userIds      = array_column($users, 'user_id');
                $orphanedRows = array_filter($mSchedule, fn($r) => !in_array($r['user_id'], $userIds));
                if (empty($orphanedRows)): ?>
                    <p class="pass">✓ All schedule rows reference a known user</p>
                <?php else: ?>
                    <p class="fail">✗ <?php echo count($orphanedRows); ?> schedule row(s) reference an unknown user</p>
                <?php endif; ?>
                <pre><?php var_dump($mSchedule); ?></pre>
            <?php else: ?>
                <p class="fail">✗ m_get_schedule_data() returned no schedule rows</p>
            <?php endif; ?>
        </div>

        <h2>Event Types</h2>
        <div class="section">
            <?php if (!empty($eventTypes)): ?>
                <p class="pass">✓ <?php echo count($eventTypes); ?> event types extracted</p>
                <?php
                $typeIds = array_column($eventTypes, 'event_type_id');
                if (count($typeIds) === count(array_unique($typeIds))): ?>
                    <p class="pass">✓ No duplicate event type IDs</p>
                <?php else: ?>
                    <p class="fail">✗ Duplicate event type IDs found</p>
                <?php endif; ?>
                <pre><?php var_dump($eventTypes); ?></pre>
            <?php else: ?>
                <p class="empty">⚠ Event types table is empty</p>
            <?php endif; ?>
        </div>

        <h2>Events</h2>
        <div class="section">
            <?php if (!empty($mEvents)): ?>
                <p class="pass">✓ <?php echo count($mEvents); ?> events extracted</p>
                <?php
                $typeIds        = array_column($eventTypes, 'event_type_id');
                $orphanedEvents = array_filter($mEvents, fn($e) => !in_array($e['event_type_id'], $typeIds));
                if (empty($orphanedEvents)): ?>
                    <p class="pass">✓ All events reference a known event type</p>
                <?php else: ?>
                    <p class="fail">✗ <?php echo count($orphanedEvents); ?> event(s) reference an unknown event type</p>
                <?php endif; ?>
                <pre><?php var_dump($mEvents); ?></pre>
            <?php else: ?>
                <p class="empty">⚠ Events table is empty</p>
            <?php endif; ?>
        </div>

    </body>
    </html>
    <?php
    exit;
});