<?php
/*
Template Name: Drop-In Tutoring
*/
get_header();

[$uSubjects, $uCourses, $uSchedule, $eventTypes, $uEvents] = user_query();

function tutoring_anchor_from_subject($subject) {
    return strtolower($subject['subject_code']);
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
    return ucwords($formatted);
}

function tutoring_subject_heading($subject) {
    return esc_html($subject['subject_name']) . ' Courses';
}

?>

<main id="main" class="container">
  <?php get_template_part('sidebar', 'tutoring'); ?>

  <div class="main-content">
    <article id="post-dropin" class="page type-page status-publish hentry">
      <a class="button button-primary admin-nav-button" href="<?php echo esc_url(get_permalink(get_page_by_path('tutoring-admin'))); ?>">
        Admin Login
      </a>

      <header class="entry-header">
        <h1 class="entry-title">Drop-In Tutoring</h1>
      </header>

      <div class="entry-content">
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
        <ul aria-label="Available courses navigation" class="list-inline">
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

        <?php
        $expander_id = 1000;
        foreach ($uSubjects as $subject):
          $subject_code = $subject['subject_code'];
          $subject_courses = $uCourses[$subject_code] ?? [];

          if (empty($subject_courses)) {
              continue;
          }
        ?>
        <div class="subject-section" data-subject="<?php echo esc_attr($subject['subject_code']); ?>">
          <a id="<?php echo esc_attr(tutoring_anchor_from_subject($subject)); ?>"></a>
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
                            <th>Status</th>
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
                              <td class="tutoring-status-available">Available</td>
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

<style>
.entry-content .umbc-table td.tutoring-status-available {
  color: #2e7d32 !important;
  font-weight: 700 !important;
}

.subject-filter-button.active {
  outline: 2px solid #000;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const triggers = document.querySelectorAll('.sights-expander-trigger');

  triggers.forEach(function (trigger) {
    trigger.addEventListener('click', function () {
      const contentId = trigger.getAttribute('aria-controls');
      const content = document.getElementById(contentId);
      if (!content) return;

      const isExpanded = trigger.getAttribute('aria-expanded') === 'true';

      trigger.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');
      content.classList.toggle('sights-expander-hidden', isExpanded);
    });

    trigger.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        trigger.click();
      }
    });
  });
});

document.addEventListener('DOMContentLoaded', function () {
  const filterButtons = document.querySelectorAll('.subject-filter-button');
  const subjectSections = document.querySelectorAll('.subject-section');

  filterButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      const subject = button.getAttribute('data-subject');

      filterButtons.forEach(function (btn) {
        btn.classList.remove('active');
      });
      button.classList.add('active');

      subjectSections.forEach(function (section) {
        const sectionSubject = section.getAttribute('data-subject');

        if (subject === 'all' || subject === sectionSubject) {
          section.style.display = '';
        } else {
          section.style.display = 'none';
        }
      });
    });
  });
});
</script>

<?php get_footer(); ?>