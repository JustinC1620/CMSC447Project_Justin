<?php
const SCHEDULE_CACHE_KEY   = 'schedule';
const SCHEDULE_CACHE_GROUP = 'user_view';

function schedule_query() {
    $schedule = wp_cache_get(SCHEDULE_CACHE_KEY, SCHEDULE_CACHE_GROUP);

    if (false === $schedule) {
        $schedule = schedule_db_query();
        wp_cache_set(SCHEDULE_CACHE_KEY, $schedule, SCHEDULE_CACHE_GROUP, HOUR_IN_SECONDS);
    }
    return $schedule;
}

function schedule_db_query() {
    global $wpdb;

    $query = $wpdb->prepare("
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

    return $wpdb->get_results($query);
}

function get_subjects($schedule) {
    $subjects = [];
    foreach ($schedule as $row) {
        if (!isset($subjects[$row->subject_code])) {
            $subjects[$row->subject_code] = [
                'subject_code'  => $row->subject_code,
                'subject_name'  => $row->subject_name,
            ];
        }
    }
    return array_values($subjects);
}

function get_courses($schedule) {
    $courses = [];
    foreach ($schedule as $row) {
        if (!isset($courses[$row->course_id])) {
            $courses[$row->course_id] = [
                'course_id'      => $row->course_id,
                'course_code'    => $row->course_code,
                'course_name'    => $row->course_name,
                'course_subject' => $row->course_subject,
            ];
        }
    }
    return array_values($courses);
}

// Test for schedule_query()
// Login as WordPress Admin
// Navigate to localhost/drop-in-tutoring/?test_schedule=1
// Output from query should be shown as it is stored in memory
add_action('template_redirect', function() {
    if (!isset($_GET['test_schedule']) || !current_user_can('administrator')) {
        return;
    }

    wp_cache_delete(SCHEDULE_CACHE_KEY, SCHEDULE_CACHE_GROUP);

    $schedule = schedule_query();
    $subjects = get_subjects($schedule);
    $courses  = get_courses($schedule);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Schedule Debug</title>
        <style>
            body { font-family: monospace; padding: 2rem; background: #f1f1f1; }
            h2   { color: #333; }
            pre  { background: #fff; padding: 1rem; border: 1px solid #ddd; overflow-x: auto; }
            .meta { color: #666; font-size: 0.9rem; margin-bottom: 1rem; }
            .pass { color: green; }
            .fail { color: red; }
            .section { margin-bottom: 2rem; }
        </style>
    </head>
    <body>

        <h2>Schedule Query Debug</h2>
        <div class="meta section">
            <p>Row count: <strong><?php echo count($schedule); ?></strong></p>
            <p>Last query:</p>
            <pre><?php global $wpdb; echo esc_html($wpdb->last_query); ?></pre>
            <?php if ($wpdb->last_error): ?>
                <p class="fail">DB Error: <?php echo esc_html($wpdb->last_error); ?></p>
            <?php endif; ?>
        </div>

        <h2>Cache Status</h2>
        <div class="section">
            <?php $cached = wp_cache_get(SCHEDULE_CACHE_KEY, SCHEDULE_CACHE_GROUP); ?>
            <?php if (false !== $cached): ?>
                <p class="pass">✓ Cache populated (<?php echo count($cached); ?> rows)</p>
            <?php else: ?>
                <p class="fail">✗ Cache empty after query</p>
            <?php endif; ?>
        </div>

        <h2>Subjects</h2>
        <div class="section">
            <?php if (!empty($subjects)): ?>
                <p class="pass">✓ <?php echo count($subjects); ?> subjects extracted</p>
                <?php
                // Verify no duplicate subject codes
                $codes = array_column($subjects, 'subject_code');
                if (count($codes) === count(array_unique($codes))): ?>
                    <p class="pass">✓ No duplicate subject codes</p>
                <?php else: ?>
                    <p class="fail">✗ Duplicate subject codes found</p>
                <?php endif; ?>
                <pre><?php var_dump($subjects); ?></pre>
            <?php else: ?>
                <p class="fail">✗ get_subjects() returned empty</p>
            <?php endif; ?>
        </div>

        <h2>Courses</h2>
        <div class="section">
            <?php if (!empty($courses)): ?>
                <p class="pass">✓ <?php echo count($courses); ?> courses extracted</p>
                <?php
                // Verify no duplicate course ids
                $ids = array_column($courses, 'course_id');
                if (count($ids) === count(array_unique($ids))): ?>
                    <p class="pass">✓ No duplicate course IDs</p>
                <?php else: ?>
                    <p class="fail">✗ Duplicate course IDs found</p>
                <?php endif; ?>
                <?php
                // Verify every course subject references a known subject
                $subject_codes   = array_column($subjects, 'subject_code');
                $orphaned_courses = array_filter($courses, fn($c) => !in_array($c['course_subject'], $subject_codes));
                if (empty($orphaned_courses)): ?>
                    <p class="pass">✓ All courses reference a known subject</p>
                <?php else: ?>
                    <p class="fail">✗ <?php echo count($orphaned_courses); ?> course(s) reference an unknown subject</p>
                <?php endif; ?>
                <pre><?php var_dump($courses); ?></pre>
            <?php else: ?>
                <p class="fail">✗ get_courses() returned empty</p>
            <?php endif; ?>
        </div>

        <h2>Full Schedule Results</h2>
        <div class="section">
            <pre><?php var_dump($schedule); ?></pre>
        </div>

    </body>
    </html>
    <?php
    exit;
});