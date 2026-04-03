<?php
/*
Template Name: Tutoring Admin
*/
get_header();

$can_staff = current_user_can('staff_control');
$can_admin = current_user_can('admin_control');

if ($can_staff || $can_admin) {
    [$mSubjects, $mCourses, $users, $mSchedule, $eventTypes, $mEvents] = management_query();
} else {
    $mSubjects = [];
    $mCourses = [];
    $users = [];
    $mSchedule = [];
    $eventTypes = [];
    $mEvents = [];
}

function tutoring_admin_day_label($day) {
    $map = [
        'MON' => 'Monday',
        'TUE' => 'Tuesday',
        'WED' => 'Wednesday',
        'THU' => 'Thursday',
        'FRI' => 'Friday',
    ];
    return $map[$day] ?? $day;
}

function tutoring_admin_time_label($time) {
    if (!$time) {
        return '';
    }

    $dt = DateTime::createFromFormat('H:i:s', $time);
    if (!$dt) {
        return esc_html($time);
    }

    $formatted = strtolower($dt->format('g:i a'));
    $formatted = str_replace(['am', 'pm'], ['a.m.', 'p.m.'], $formatted);
    $formatted = str_replace(['12:00 a.m.', '12:00 p.m.'], ['Midnight', 'Noon'], $formatted);

    return $formatted;
}

function tutoring_admin_user_label($user) {
    $name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
    if ($name === '') {
        $name = $user['user_login'];
    }
    return $name . ' (' . $user['user_login'] . ')';
}

$users_by_id = [];
foreach ($users as $user) {
    $users_by_id[$user['user_id']] = $user;
}

$courses_by_id = [];
foreach ($mCourses as $course) {
    $courses_by_id[$course['course_id']] = $course;
}

$event_types_by_id = [];
foreach ($eventTypes as $eventType) {
    $event_types_by_id[$eventType['event_type_id']] = $eventType;
}
?>

<main id="main" class="container">
  <?php get_template_part('sidebar', 'tutoring'); ?>

  <div class="main-content">
    <article id="post-tutoring-admin" class="page type-page status-publish hentry">
      <a class="button button-secondary admin-nav-button" href="<?php echo esc_url(get_permalink(get_page_by_path('drop-in-tutoring'))); ?>">
        Drop-In Tutoring Page
      </a>

      <header class="entry-header">
        <h1 class="entry-title">Tutoring Admin</h1>
      </header>

      <div class="entry-content">
        <?php if (!$can_staff && !$can_admin) : ?>
          <section class="admin-panel">
            <p>You must be logged in with an authorized staff or admin account to access tutoring controls.</p>
          </section>
        <?php else : ?>

          <div class="tutoring-admin-message" id="tutoring-admin-message" hidden></div>

          <nav class="tutoring-admin-tabs" aria-label="Admin sections">
            <?php if ($can_staff || $can_admin) : ?>
              <button type="button" class="button button-primary admin-tab active" data-tab="events">Tutor Events</button>
            <?php endif; ?>
            <?php if ($can_admin) : ?>
              <button type="button" class="button button-primary admin-tab" data-tab="schedule">Schedule</button>
              <button type="button" class="button button-primary admin-tab" data-tab="accounts">Accounts</button>
            <?php endif; ?>
          </nav>

          <?php if ($can_staff || $can_admin) : ?>
          <section class="admin-section active" id="admin-tab-events">
            <h2>Tutor Events</h2>
            <p>Create, update, or remove late arrivals, call-outs, early departures, and related shift events.</p>

            <form class="tutoring-admin-form" id="event-form">
              <input type="hidden" id="event_id" name="event_id" />

              <div class="admin-grid">
                <div>
                  <label for="event_user_id"><strong>Tutor</strong></label>
                  <select id="event_user_id" name="user_id" required>
                    <option value="">Select tutor</option>
                    <?php foreach ($users as $user) : ?>
                      <?php if (($user['roles'] ?? '') === 'tutor' || str_contains((string)($user['roles'] ?? ''), 'tutor')) : ?>
                        <option value="<?php echo esc_attr($user['user_id']); ?>">
                          <?php echo esc_html(tutoring_admin_user_label($user)); ?>
                        </option>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div>
                  <label for="event_type"><strong>Event Type</strong></label>
                  <select id="event_type" name="event_type" required>
                    <option value="">Select type</option>
                    <?php foreach ($eventTypes as $eventType) : ?>
                      <option value="<?php echo esc_attr($eventType['event_type_id']); ?>">
                        <?php echo esc_html($eventType['event_name']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div>
                  <label for="start_day"><strong>Start Date</strong></label>
                  <input type="date" id="start_day" name="start_day" required />
                </div>

                <div>
                  <label for="final_day"><strong>Final Date</strong></label>
                  <input type="date" id="final_day" name="final_day" />
                </div>

                <div>
                  <label for="duration"><strong>Duration (minutes)</strong></label>
                  <input type="number" id="duration" name="duration" min="0" step="1" />
                </div>
              </div>

              <div class="admin-actions">
                <button type="submit" class="button button-primary">Save Event</button>
                <button type="button" class="button button-secondary" id="reset-event-form">Clear</button>
              </div>
            </form>

            <div class="umbc-table-wrapper">
              <table class="umbc-table admin-table" id="events-table">
                <thead>
                  <tr>
                    <th>Event ID</th>
                    <th>Tutor</th>
                    <th>Type</th>
                    <th>Start</th>
                    <th>Final</th>
                    <th>Duration</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($mEvents as $event) : ?>
                    <?php $event_user = $users_by_id[$event['user_id']] ?? null; ?>
                    <?php $event_type = $event_types_by_id[$event['event_type_id']] ?? null; ?>
                    <tr
                      data-event-id="<?php echo esc_attr($event['event_id']); ?>"
                      data-user-id="<?php echo esc_attr($event['user_id']); ?>"
                      data-event-type="<?php echo esc_attr($event['event_type_id']); ?>"
                      data-start-day="<?php echo esc_attr($event['start_day']); ?>"
                      data-final-day="<?php echo esc_attr($event['final_day'] ?? ''); ?>"
                      data-duration="<?php echo esc_attr($event['duration'] ?? ''); ?>"
                    >
                      <td><?php echo esc_html($event['event_id']); ?></td>
                      <td><?php echo esc_html($event_user ? tutoring_admin_user_label($event_user) : $event['user_id']); ?></td>
                      <td><?php echo esc_html($event_type['event_name'] ?? $event['event_type_id']); ?></td>
                      <td><?php echo esc_html($event['start_day']); ?></td>
                      <td><?php echo esc_html($event['final_day'] ?: '—'); ?></td>
                      <td><?php echo esc_html($event['duration'] !== null ? $event['duration'] : '—'); ?></td>
                      <td>
                        <button type="button" class="button button-secondary admin-edit-event">Edit</button>
                        <button type="button" class="button button-secondary admin-delete-event">Delete</button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </section>
          <?php endif; ?>

          <?php if ($can_admin) : ?>
          <section class="admin-section" id="admin-tab-schedule">
            <h2>Schedule Management</h2>
            <p>Add, update, and remove tutoring time slots. New courses can also be added through the same form.</p>

            <form class="tutoring-admin-form" id="schedule-form">
              <input type="hidden" id="schedule_id" name="schedule_id" />

              <div class="admin-grid">
                <div>
                  <label for="schedule_user_id"><strong>Tutor</strong></label>
                  <select id="schedule_user_id" name="user_id" required>
                    <option value="">Select tutor</option>
                    <?php foreach ($users as $user) : ?>
                      <?php if (($user['roles'] ?? '') === 'tutor' || str_contains((string)($user['roles'] ?? ''), 'tutor')) : ?>
                        <option value="<?php echo esc_attr($user['user_id']); ?>">
                          <?php echo esc_html(tutoring_admin_user_label($user)); ?>
                        </option>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div>
                  <label for="schedule_course_id"><strong>Course ID</strong></label>
                  <input type="number" id="schedule_course_id" name="course_id" required />
                </div>

                <div>
                  <label for="schedule_day_of_week"><strong>Day</strong></label>
                  <select id="schedule_day_of_week" name="day_of_week" required>
                    <option value="">Select day</option>
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                  </select>
                </div>

                <div>
                  <label for="schedule_start_time"><strong>Start Time</strong></label>
                  <input type="text" id="schedule_start_time" name="start_time" placeholder="ex: 1:00 pm" required />
                </div>

                <div>
                  <label for="schedule_end_time"><strong>End Time</strong></label>
                  <input type="text" id="schedule_end_time" name="end_time" placeholder="ex: 2:00 pm" required />
                </div>
              </div>

              <details class="admin-details">
                <summary><strong>New course / subject fields</strong> (only needed when the course ID does not already exist)</summary>
                <div class="admin-grid">
                  <div>
                    <label for="course_subject"><strong>Subject Code</strong></label>
                    <input type="text" id="course_subject" name="course_subject" placeholder="CMSC" />
                  </div>
                  <div>
                    <label for="subject_name"><strong>Subject Name</strong></label>
                    <input type="text" id="subject_name" name="subject_name" placeholder="Computer Science" />
                  </div>
                  <div>
                    <label for="course_code"><strong>Course Code</strong></label>
                    <input type="text" id="course_code" name="course_code" placeholder="201" />
                  </div>
                  <div>
                    <label for="course_name"><strong>Course Name</strong></label>
                    <input type="text" id="course_name" name="course_name" placeholder="Computer Science I" />
                  </div>
                </div>
              </details>

              <div class="admin-actions">
                <button type="submit" class="button button-primary">Save Schedule Entry</button>
                <button type="button" class="button button-secondary" id="reset-schedule-form">Clear</button>
              </div>
            </form>

            <div class="umbc-table-wrapper">
              <table class="umbc-table admin-table" id="schedule-table">
                <thead>
                  <tr>
                    <th>Schedule ID</th>
                    <th>Tutor</th>
                    <th>Course</th>
                    <th>Day</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($mSchedule as $row) : ?>
                    <?php $schedule_user = $users_by_id[$row['user_id']] ?? null; ?>
                    <?php $schedule_course = $courses_by_id[$row['course_id']] ?? null; ?>
                    <tr
                      data-schedule-id="<?php echo esc_attr($row['schedule_id']); ?>"
                      data-user-id="<?php echo esc_attr($row['user_id']); ?>"
                      data-course-id="<?php echo esc_attr($row['course_id']); ?>"
                      data-day-of-week="<?php echo esc_attr($row['day_of_week']); ?>"
                      data-start-time="<?php echo esc_attr($row['start_time']); ?>"
                      data-end-time="<?php echo esc_attr($row['end_time']); ?>"
                    >
                      <td><?php echo esc_html($row['schedule_id']); ?></td>
                      <td><?php echo esc_html($schedule_user ? tutoring_admin_user_label($schedule_user) : $row['user_id']); ?></td>
                      <td>
                        <?php
                        echo esc_html(
                          $schedule_course
                            ? ($schedule_course['course_subject'] . ' ' . $schedule_course['course_code'] . ' - ' . $schedule_course['course_name'])
                            : $row['course_id']
                        );
                        ?>
                      </td>
                      <td><?php echo esc_html(tutoring_admin_day_label($row['day_of_week'])); ?></td>
                      <td><?php echo esc_html(tutoring_admin_time_label($row['start_time'])); ?></td>
                      <td><?php echo esc_html(tutoring_admin_time_label($row['end_time'])); ?></td>
                      <td>
                        <button type="button" class="button button-secondary admin-edit-schedule">Edit</button>
                        <button type="button" class="button button-secondary admin-delete-schedule">Delete</button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </section>

          <section class="admin-section" id="admin-tab-accounts">
            <h2>Account Management</h2>
            <p>Create local tutoring accounts and update or remove assigned roles.</p>

            <form class="tutoring-admin-form" id="account-form">
              <input type="hidden" id="account_user_id" name="user_id" />

              <div class="admin-grid">
                <div>
                  <label for="user_login"><strong>UMBC ID</strong></label>
                  <input type="text" id="user_login" name="user_login" placeholder="AB12345" />
                </div>

                <div>
                  <label for="user_email"><strong>Email</strong></label>
                  <input type="email" id="user_email" name="user_email" placeholder="student@umbc.edu" />
                </div>

                <div>
                  <label for="first_name"><strong>First Name</strong></label>
                  <input type="text" id="first_name" name="first_name" />
                </div>

                <div>
                  <label for="last_name"><strong>Last Name</strong></label>
                  <input type="text" id="last_name" name="last_name" />
                </div>

                <div class="admin-role-box">
                  <strong>Roles</strong>
                  <label><input type="checkbox" name="roles[]" value="tutor" /> Tutor</label>
                  <label><input type="checkbox" name="roles[]" value="asc_staff" /> Staff</label>
                  <label><input type="checkbox" name="roles[]" value="asc_admin" /> Admin</label>
                </div>
              </div>

              <div class="admin-actions">
                <button type="submit" class="button button-primary">Save Account</button>
                <button type="button" class="button button-secondary" id="reset-account-form">Clear</button>
              </div>
            </form>

            <div class="umbc-table-wrapper">
              <table class="umbc-table admin-table" id="accounts-table">
                <thead>
                  <tr>
                    <th>User ID</th>
                    <th>UMBC ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($users as $user) : ?>
                    <tr
                      data-user-id="<?php echo esc_attr($user['user_id']); ?>"
                      data-user-login="<?php echo esc_attr($user['user_login']); ?>"
                      data-user-email="<?php echo esc_attr($user['user_email']); ?>"
                      data-first-name="<?php echo esc_attr($user['first_name']); ?>"
                      data-last-name="<?php echo esc_attr($user['last_name']); ?>"
                      data-roles="<?php echo esc_attr($user['roles'] ?? ''); ?>"
                    >
                      <td><?php echo esc_html($user['user_id']); ?></td>
                      <td><?php echo esc_html($user['user_login']); ?></td>
                      <td><?php echo esc_html(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))); ?></td>
                      <td><?php echo esc_html($user['user_email']); ?></td>
                      <td><?php echo esc_html($user['roles'] ?? '—'); ?></td>
                      <td>
                        <button type="button" class="button button-secondary admin-edit-account">Edit Roles</button>
                        <button type="button" class="button button-secondary admin-delete-account">Delete</button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </section>
          <?php endif; ?>

        <?php endif; ?>
      </div>
    </article>
  </div>
</main>

<?php if ($can_staff || $can_admin) : ?>
  
<style>
.tutoring-admin-tabs,
.admin-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin: 1rem 0;
}

.admin-tab.active {
  outline: 2px solid #000;
}

.admin-section {
  display: none;
  margin-top: 2rem;
  padding: 1rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  background: #fff;
}

.admin-section.active {
  display: block;
}

.admin-grid,
.admin-lookup-grid {
  display: grid;
  gap: 16px;
}

.admin-grid {
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
}

.admin-lookup-grid {
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
}

.tutoring-admin-form input,
.tutoring-admin-form select {
  width: 100%;
  margin-top: 6px;
}

.admin-role-box {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.admin-details {
  margin: 1rem 0;
}

.admin-table td,
.admin-table th {
  vertical-align: top;
}

.tutoring-admin-message {
  margin-bottom: 1rem;
  padding: 12px 16px;
  border-radius: 6px;
  font-weight: 600;
}

.tutoring-admin-message.success {
  background: #e8f5e9;
  color: #1b5e20;
}

.tutoring-admin-message.error {
  background: #ffebee;
  color: #b71c1c;
}
</style>

<?php endif; ?>

<?php get_footer(); ?>