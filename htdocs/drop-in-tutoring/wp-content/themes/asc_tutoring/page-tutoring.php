<?php
/*
Template Name: Drop-In Tutoring
*/
get_header();

$static_file_path = get_template_directory() . TUTORING_SNAPSHOT_PATH;
$is_static_render = defined('TUTORING_IS_STATIC_RENDER') && TUTORING_IS_STATIC_RENDER;

try {
    [$uSubjects, $uCourses, $uSchedule, $eventTypes, $uEvents] = user_query();
} catch (Throwable $e) {
    if (file_exists($static_file_path)) {
        readfile($static_file_path);
    } else {
        get_template_part('sidebar', 'tutoring');
        echo '<main id="main" class="container"><div class="main-content">';
        echo '<article id="post-dropin" class="page type-page status-publish hentry">';
        echo '<header class="entry-header"><h1 class="entry-title">Drop-In Tutoring</h1></header>';
        echo '<div class="entry-content"><blockquote><p style="text-align:center">';
        echo '<strong>The tutoring schedule is temporarily unavailable.</strong><br>';
        echo 'Please try again shortly or email <a href="mailto:tutoring@umbc.edu">tutoring@umbc.edu</a> for assistance.';
        echo '</p></blockquote></div></article></div></main>';
    }
    get_footer();
    exit;
}

?>
<main id="main" class="container">
  <?php get_template_part('sidebar', 'tutoring'); ?>
  <div class="main-content">
    <article id="post-dropin" class="page type-page status-publish hentry">
      <header class="entry-header">
        <h1 class="entry-title">Drop-In Tutoring</h1>
      </header>
      <div class="entry-content">

        <?php if ($is_static_render): ?>
          <div class="tutoring-static-notice" role="alert">
            <?php echo T_WARNING_ICON_SVG; ?>
            <div class="tutoring-static-notice-body">
              <strong>You are viewing a cached version of this schedule.</strong>
              Real-time tutor availability is not shown. This snapshot was last updated on
              <?php
                if (file_exists($static_file_path)) {
                    echo esc_html(
                        (new DateTime('@' . filemtime($static_file_path)))
                            ->setTimezone(new DateTimeZone('America/New_York'))
                            ->format('F j, Y \a\t g:i a T')
                    );
                } else {
                    echo 'an unknown time';
                }
              ?>.
              For questions, email <a href="mailto:tutoring@umbc.edu">tutoring@umbc.edu</a>.
            </div>
          </div>
        <?php endif; ?>

        <blockquote>
          <p style="text-align: center">
            <b>Welcome to the Spring 2026 semester!</b><br/>
            Tutoring will be available between Monday, February 9th, and Tuesday, May 12, 2026.<br/>
            Wishing you a successful semester, and we look forward to seeing you!
          </p>
          <p style="text-align: center">
            <a class="button button-primary" href="http://traccloud.go-redrock.com/umbc/">
              Undergraduate Students - Schedule ASC Services Here
            </a>
          </p>
        </blockquote>
        <p>
          Drop-in tutoring (located in the Library) is recommended for students who have quick questions.
          Students who would like more in-depth assistance should make a
          <a href="https://academicsuccess.umbc.edu/appointment-tutoring/">tutoring appointment</a>.
        </p>
        <p>
          Peer support for specific courses depends on resources available each semester.
          Looking for a different course? Please visit our
          <a href="https://academicsuccess.umbc.edu/tutoring/"><strong>main tutoring page</strong></a>
          to view other support options.
        </p>
        <p>
          If you have any questions about drop-in tutoring, please email
          <a href="mailto:tutoring@umbc.edu">tutoring@umbc.edu</a>.
        </p>
        <h2>Available Courses</h2>
        <ul 
          aria-label="Available courses navigation" 
          class="list-inline"
          style="display: flex; flex-wrap: wrap; justify-content: center; gap: 0px 12px; padding: 0; list-style: none;"
        >
          <li>
            <button type="button" class="button button-secondary subject-filter-button active" data-subject="all">
              Show All
            </button>
          </li>
          <?php foreach ($uSubjects as $subject): ?>
            <?php if (!empty($uCourses[$subject['subject_code']])): ?>
              <li>
                <button
                  type="button"
                  class="button button-primary subject-filter-button"
                  data-subject="<?php echo esc_attr($subject['subject_code']); ?>">
                  <?php echo esc_html($subject['subject_name']); ?>
                </button>
              </li>
            <?php endif; ?>
          <?php endforeach; ?>
        </ul>
        <div class="day-filter-strip">
          <h3>Available Days</h3>
          <div class="day-filter-days" role="group" aria-labelledby="day-filter-label">
            <?php
            $days = [
              'MON' => 'Monday',
              'TUE' => 'Tuesday',
              'WED' => 'Wednesday',
              'THU' => 'Thursday',
              'FRI' => 'Friday',
            ];
            foreach ($days as $code => $label):
            ?>
              <button
                type="button"
                class="button button-primary day-filter-tile active"
                data-day="<?php echo esc_attr($code); ?>"
                aria-pressed="true"
              >
                <span class="day-filter-check" aria-hidden="true">✓</span>
                <span class="day-filter-text">
                  <?php echo esc_html($label); ?>
                </span>
              </button>
            <?php endforeach; ?>
          </div>
        </div>
        <?php
        $expander_id = 1000;
        foreach ($uSubjects as $subject):
          $subject_code = $subject['subject_code'];
          $subject_courses = $uCourses[$subject_code] ?? [];
          if (empty($subject_courses)) {
              continue;
          }
        ?>
        <div
          class="subject-section"
          data-subject="<?php echo esc_attr($subject['subject_code']); ?>"
          id="<?php echo esc_attr(tutoring_anchor_from_subject($subject)); ?>"
        >
          <h3><?php echo tutoring_subject_heading($subject); ?></h3>
          <?php foreach ($subject_courses as $course): ?>
            <?php
              $course_schedule = $uSchedule[$course['course_id']] ?? [];
              $days = [];
              foreach ($course_schedule as $row) {
                  $days[$row['day_of_week']][] = $row;
              }
              $header_id = 'sights-expander-header-' . $expander_id;
              $content_id = 'sights-expander-content-' . $expander_id;
              $expander_id++;
            ?>
            <div class="sights-expander-wrapper mceNonEditable">
              <div
                aria-controls="<?php echo esc_attr($content_id); ?>"
                aria-expanded="false"
                class="sights-expander-trigger mceNonEditable"
                id="<?php echo esc_attr($header_id); ?>"
                role="button"
                tabindex="0"
              >
                <div class="mceEditable">
                  <?php echo esc_html($course['course_subject'] . ' ' . $course['course_code'] . ' - ' . $course['course_name']); ?>
                </div>
              </div>
              <div
                aria-labelledby="<?php echo esc_attr($header_id); ?>"
                class="sights-expander-content sights-expander-hidden mceNonEditable"
                id="<?php echo esc_attr($content_id); ?>"
                role="region"
              >
                <div class="mceEditable">
                  <?php if (!empty($course_schedule)): ?>
                    <p>
                      <strong>
                        Tutors for <?php echo esc_html($course['course_subject'] . ' ' . $course['course_code']); ?>
                        are available on the following days and times:
                      </strong>
                    </p>
                    <div class="umbc-table-wrapper">
                      <table class="umbc-table">
                        <thead>
                          <tr>
                            <th>Day</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Tutor</th>
                            <?php if (!$is_static_render): ?>
                              <th>Status</th>
                            <?php endif; ?>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                          $day_order = ['MON', 'TUE', 'WED', 'THU', 'FRI'];
                          foreach ($day_order as $day):
                            if (empty($days[$day])) {
                                continue;
                            }
                            $entries = $days[$day];
                            $rowspan = count($entries);
                            $first_row = true;
                            foreach ($entries as $entry):
                          ?>
                            <tr>
                              <?php if ($first_row): ?>
                                <td rowspan="<?php echo esc_attr($rowspan); ?>" class="tutoring-day-cell">
                                  <?php echo esc_html(tutoring_day_label($day)); ?>
                                </td>
                              <?php $first_row = false; endif; ?>
                              <td><?php echo esc_html(tutoring_format_time($entry['start_time'])); ?></td>
                              <td><?php echo esc_html(tutoring_format_time($entry['end_time'])); ?></td>
                              <td><?php echo esc_html($entry['first_name']); ?></td>
                              <?php if (!$is_static_render): ?>
                                <?php
                                  $status = tutoring_get_tutor_status(
                                      $entry['day_of_week'],
                                      $entry['start_time'],
                                      $entry['end_time'],
                                      $eventTypes,
                                      $uEvents[$entry['user_id']] ?? []
                                  );
                                ?>
                                <td class="tutoring-status-cell">
                                  <div class="tutoring-status-stack">
                                    <div class="tutoring-status-main" style="color: <?php echo esc_attr($status['color']); ?>;">
                                      <?php if ($status['icon']): ?>
                                        <span class="tutoring-status-icon" aria-hidden="true"><?php echo $status['icon']; ?></span>
                                      <?php endif; ?>
                                      <span class="tutoring-status-label"><?php echo esc_html($status['label']); ?></span>
                                    </div>
                                    <?php if (!empty($status['leaving_early_note'])): ?>
                                      <div class="tutoring-status-sub tutoring-leaving-early-note">
                                        <?php echo $status['leaving_early_icon']; ?>
                                        <?php echo esc_html($status['leaving_early_note']); ?>
                                      </div>
                                    <?php endif; ?>
                                    <?php if (!empty($status['call_out_note'])): ?>
                                      <div class="tutoring-status-sub tutoring-call-out-note">
                                        <?php echo esc_html($status['call_out_note']); ?>
                                      </div>
                                    <?php endif; ?>
                                  </div>
                                </td>
                              <?php endif; ?>
                            </tr>
                          <?php
                            endforeach;
                          endforeach;
                          ?>
                        </tbody>
                      </table>
                    </div>
                  <?php else: ?>
                    <p>
                      <strong>
                        A tutoring schedule will be available for
                        <?php echo esc_html($course['course_subject'] . ' ' . $course['course_code']); ?>
                        soon. Please check back frequently for updates.
                      </strong>
                    </p>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </article>
  </div>
</main>
<?php get_footer(); ?>