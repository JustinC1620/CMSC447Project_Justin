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
            c.course_count,
            sub.subject_code,
            sub.subject_name,
            sub.subject_count,
            um.meta_value AS first_name
        FROM schedule s
        JOIN courses c      ON s.course_id      = c.course_id
        JOIN subjects sub   ON c.course_subject = sub.subject_code
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


// Test for schedule_query()
// Login as WordPress Admin
// Navigate to localhost/drop-in-tutoring/?test_schedule=1
// Output from query should be shown as it is stored in memory
add_action('template_redirect', function() {
    if (!isset($_GET['test_schedule']) || !current_user_can('administrator')) {
        return;
    }

    // Clear cache so we always see a fresh DB hit
    wp_cache_delete(SCHEDULE_CACHE_KEY, SCHEDULE_CACHE_GROUP);

    $schedule = schedule_query();
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
        </style>
    </head>
    <body>
        <h2>Schedule Query Debug</h2>

        <div class="meta">
            <p>Row count: <strong><?php echo count($schedule); ?></strong></p>
            <p>Last query:</p>
            <pre><?php global $wpdb; echo esc_html($wpdb->last_query); ?></pre>
            <?php if ($wpdb->last_error): ?>
                <p class="fail">DB Error: <?php echo esc_html($wpdb->last_error); ?></p>
            <?php endif; ?>
        </div>

        <h2>Cache Status</h2>
        <?php
        $cached = wp_cache_get(SCHEDULE_CACHE_KEY, SCHEDULE_CACHE_GROUP);
        if (false !== $cached): ?>
            <p class="pass">✓ Cache populated (<?php echo count($cached); ?> rows)</p>
        <?php else: ?>
            <p class="fail">✗ Cache empty after query</p>
        <?php endif; ?>

        <h2>Results</h2>
        <pre><?php var_dump($schedule); ?></pre>
    </body>
    </html>
    <?php
    exit;
});