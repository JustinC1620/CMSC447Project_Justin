<?php
/*
Template Name: Tutoring Admin
*/
get_header();

$is_staff = current_user_can('staff_control');
$is_admin = current_user_can('admin_control');

if (!$is_staff) {
    wp_redirect(home_url());
}

[$mSubjects, $mCourses, $users, $mSchedule, $eventTypes, $mEvents] = management_query();

$users_by_id = [];
foreach ($users as $user) {
    $users_by_id[$user['user_id']] = $user;
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

      <header class="entry-header">
        <h1 class="entry-title">Drop-In Tutoring Management</h1>
      </header>

      <div class="entry-content">
        <?php if (!$is_staff) : ?>
          <section class="admin-panel">
            <p>You must be logged in with an authorized staff or admin account to access tutoring controls.</p>
          </section>
        <?php else : ?>

          <?php if ($is_admin) : ?>
            <nav class="tutoring-admin-tabs" aria-label="Admin sections">
                <button type="button" class="button button-primary admin-tab active" data-tab="events">Tutor Events</button>            
                <button type="button" class="button button-primary admin-tab" data-tab="schedule">Schedule</button>
                <button type="button" class="button button-primary admin-tab" data-tab="accounts">Accounts</button>
                <button type="button" class="button button-primary admin-tab" data-tab="import">Bulk Updates</button>
                <button type="button" class="button button-primary admin-tab" data-tab="logs">Logs</button>
            </nav>
          <?php endif; ?>

          <section class="admin-section active" id="admin-tab-events">
            <h2>Tutor Events</h2>
            <p>Create, update, and delete tutor events such as late arrivals, call-outs, early departures, and other shift events.</p>
            <section class="admin-subsection">
              <h3 id="event-form-mode-label">Create New Event</h3>

              <form class="tutoring-admin-form" id="event-form">
                <input type="hidden" id="event_id" name="event_id" />

                <div class="admin-grid">
                  <div>
                    <label for="event_user_id"><strong>Tutor</strong></label>
                    <select id="event_user_id" name="user_id" required>
                      <option value="">Select tutor</option>
                      <?php foreach ($users as $user) : ?>
                        <?php if (in_array('tutor', (array) $user['roles'], true)) : ?>
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
                          <?php echo esc_html(display_snake_case($eventType['event_name'])); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div id="date-range-fields">
                    <div>
                      <label for="start_day"><strong>Start Date</strong></label>
                      <input type="date" id="start_day" name="start_day" required />
                    </div>

                    <div>
                      <label for="final_day"><strong>Final Date</strong></label>
                      <input type="date" id="final_day" name="final_day" />
                    </div>
                  </div>

                  <div id="leaving-early-field">
                    <label for="leaving_time_picker"><strong>Time</strong></label>
                    <input type="text" id="leaving_time_picker" placeholder="Select time" autocomplete="off" />
                  </div>
                  
                </div>

                <div class="admin-actions">
                  <button type="submit" class="button button-primary">Save Event</button>
                  <button type="button" class="button button-secondary" id="reset-event-form">Clear</button>
                  <span class="tutoring-admin-message" id="tutoring-admin-message" hidden></span>
                </div>
              </form>
            </section>
            <div class="umbc-table-wrapper">
              <table class="umbc-table admin-table" id="event-table">
                <thead>
                  <tr>
                    <th>Tutor</th>
                    <th>Type</th>
                    <th>Starting Day</th>
                    <th>Final Day</th>
                    <th>Time</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($mEvents as $event) : ?>
                    <?php $event_user = $users_by_id[$event['user_id']] ?? null; ?>
                    <?php $event_type = $event_types_by_id[$event['event_type']] ?? null; ?>
                    <tr
                      data-event-id="<?php echo esc_attr($event['event_id']); ?>"
                      data-user-id="<?php echo esc_attr($event['user_id']); ?>"
                      data-event-type="<?php echo esc_attr($event['event_type']); ?>"
                      data-start-day="<?php echo esc_attr($event['start_day']); ?>"
                      data-final-day="<?php echo esc_attr($event['final_day'] ?? ''); ?>"
                      data-leaving-time="<?php echo esc_attr($event['leaving_time'] ?? ''); ?>"
                    >
                      <td><?php echo esc_html($event_user ? tutoring_admin_user_label($event_user) : $event['user_id']); ?></td>
                      <td><?php echo esc_html(display_snake_case($event_type['event_name'] ?? $event['event_type'])); ?></td>
                      <td>
                        <?php echo esc_html(date('m-d-Y', strtotime($event['start_day']))); ?>
                      </td>

                      <td>
                        <?php echo esc_html($event['final_day'] ? date('m-d-Y', strtotime($event['final_day'])) : '--'); ?>
                      </td>
                      <td><?php echo esc_html($event['leaving_time'] !== null ? tutoring_admin_time_label($event['leaving_time']) : '--'); ?></td>
                      <td>
                        <button type="button" class="button button-primary admin-edit-event">Edit</button>
                        <button type="button" class="button button-secondary admin-delete-event">Delete</button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </section>
          
          <?php if ($is_admin) : ?> 
            <section class="admin-section" id="admin-tab-schedule">
              <h2>Schedule Management</h2>
              <p>Create, update, and delete drop in tutor schedule entries.</p>
              <section class="admin-subsection">
                <h3 id="schedule-form-mode-label">Create New Schedule Entry</h3>
                

                <form class="tutoring-admin-form" id="schedule-form">
                  <input type="hidden" id="schedule_id" name="schedule_id" />

                  <div class="admin-grid">
                    <div>
                      <label for="schedule_user_id"><strong>Tutor</strong></label>
                      <select id="schedule_user_id" name="user_id" required >
                        <option value="">Select tutor</option>
                        <?php foreach ($users as $user) : ?>
                          <?php if (in_array('tutor', (array) $user['roles'], true)) : ?>
                            <option value="<?php echo esc_attr($user['user_id']); ?>">
                              <?php echo esc_html(tutoring_admin_user_label($user)); ?>
                            </option>
                          <?php endif; ?>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <div>
                      <label for="schedule_course_lookup"><strong>Select Course</strong></label>
                      <select id="schedule_course_lookup" name="schedule_course_lookup" required >
                        <option value="">Select a course</option>
                        <?php foreach ($mCourses as $course) : ?>
                          <option value="<?php echo esc_attr(json_encode($course)); ?>">
                            <?php echo esc_html($course['course_subject'] . ' ' . $course['course_code']); ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
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
                      <label for="schedule_start_time_picker"><strong>Start Time</strong></label>
                      <input type="text" id="schedule_start_time_picker" placeholder="Select time" autocomplete="off" />
                    </div>

                    <div>
                      <label for="schedule_end_time_picker"><strong>End Time</strong></label>
                      <input type="text" id="schedule_end_time_picker" placeholder="Select time" autocomplete="off" />
                    </div>

                    <input type="hidden" id="schedule_course_id" name="course_id" required />

                  </div>

                  <details class="admin-details">
                    <summary><strong>New course</strong> (Search for courses not currently scheduled)</summary>
                    <div class="admin-grid">
                      <div class="account-search-wrapper">
                        <label for="course_search_query"><strong>Search Course</strong></label>
                        <div class="account-search-row">
                          <input type="text" id="course_search_query" name="course_search_query" placeholder="Search by subject, code, or name" autocomplete="off" />
                          <button type="button" class="button button-primary" id="course-search-submit">Search</button>
                        </div>
                        <div id="course_search_results" class="account-search-results" hidden>
                          <p class="account-search-status" id="course-search-status"></p>
                          <ul class="account-search-list" id="course-search-list"></ul>
                        </div>
                        <input type="hidden" id="course_lookup_results" name="course_lookup_results" />
                      </div>
                    </div>
                  </details>

                  <div class="admin-actions">
                    <button type="submit" class="button button-primary">Save Schedule Entry</button>
                    <button type="button" class="button button-secondary" id="reset-schedule-form">Clear</button>
                    <span class="tutoring-admin-message" id="tutoring-admin-message" hidden></span>
                  </div>
                </form>
              </section>
              <div class="umbc-table-wrapper">
                <table class="umbc-table admin-table" id="schedule-table">
                  <thead>
                    <tr>
                      <th>Tutor</th>
                      <th>Course</th>
                      <th>Day</th>
                      <th>Start Time</th>
                      <th>End Time</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($mSchedule as $row) : ?>
                      <?php $schedule_user = $users_by_id[$row['user_id']] ?? null; ?>
                      <?php $schedule_course = $mCourses[$row['course_id']] ?? null; ?>
                      <tr
                        data-schedule-id="<?php echo esc_attr($row['schedule_id']); ?>"
                        data-user-id="<?php echo esc_attr($row['user_id']); ?>"
                        data-course-id="<?php echo esc_attr($row['course_id']); ?>"
                        data-day-of-week="<?php echo esc_attr($row['day_of_week']); ?>"
                        data-start-time="<?php echo esc_attr($row['start_time']); ?>"
                        data-end-time="<?php echo esc_attr($row['end_time']); ?>"
                      >
                        <td><?php echo esc_html($schedule_user ? tutoring_admin_user_label($schedule_user) : $row['user_id']); ?></td>
                        <td>
                          <?php
                          echo esc_html(
                            $schedule_course
                              ? trim($schedule_course['course_subject'] . ' ' . $schedule_course['course_code'] . ' - ' . $schedule_course['course_name'])
                              : $row['course_id']
                          );
                          ?>
                        </td>
                        <td><?php echo esc_html(tutoring_day_label($row['day_of_week'])); ?></td>
                        <td><?php echo esc_html(tutoring_admin_time_label($row['start_time'])); ?></td>
                        <td><?php echo esc_html(tutoring_admin_time_label($row['end_time'])); ?></td>
                        <td>
                          <button type="button" class="button button-primary admin-edit-schedule">Edit</button>
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
              <section class="admin-subsection">
                <h3 id="account-form-mode-label">Add New Account</h3>
                <form class="tutoring-admin-form" id="account-form">
                  <input type="hidden" id="account_user_id" name="user_id" />
                  <div class="admin-grid">
                    <div class="account-search-wrapper">
                      <label for="account_search_query"><strong>Search UMBC Account</strong></label>
                      <div class="account-search-row">
                        <input type="text" id="account_search_query" name="account_search_query" placeholder="Search by name, ID, or email" autocomplete="off" />
                        <button type="button" class="button button-primary" id="account-search-submit">Search</button>
                      </div>
                      <div id="account_search_results" class="account-search-results" hidden>
                        <p class="account-search-status" id="account-search-status"></p>
                        <ul class="account-search-list" id="account-search-list"></ul>
                      </div>
                      <input type="hidden" id="account_lookup_results" name="account_lookup_results" />
                    </div>
                  </div>

                  <div class="admin-grid">
                    <div>
                      <label for="user_login"><strong>UMBC ID</strong></label>
                      <input type="text" id="user_login" name="user_login" placeholder="AB12345" readonly disabled />
                    </div>

                    <div>
                      <label for="user_email"><strong>Email</strong></label>
                      <input type="email" id="user_email" name="user_email" placeholder="student@umbc.edu" readonly disabled/>
                    </div>

                    <div>
                      <label for="first_name"><strong>First Name</strong></label>
                      <input type="text" id="first_name" name="first_name" readonly disabled/>
                    </div>

                    <div>
                      <label for="last_name"><strong>Last Name</strong></label>
                      <input type="text" id="last_name" name="last_name" readonly disabled/>
                    </div>

                    <fieldset class="admin-role-box">
                      <legend><strong>Roles</strong></legend>
                      <div class="admin-role-options">
                        <label><input type="checkbox" name="roles[]" value="tutor" /> Tutor</label>
                        <label><input type="checkbox" name="roles[]" value="asc_staff" /> Staff</label>
                        <label><input type="checkbox" name="roles[]" value="asc_admin" /> Admin</label>
                      </div>
                    </fieldset>
                  </div>

                  <div class="admin-actions">
                    <button type="submit" class="button button-primary">Save Account</button>
                    <button type="button" class="button button-secondary" id="reset-account-form">Clear</button>
                    <span class="tutoring-admin-message" id="tutoring-admin-message" hidden></span>
                  </div>
                </form>
              </section>

              <div class="umbc-table-wrapper">
                <table class="umbc-table admin-table" id="account-table">
                  <thead>
                    <tr>
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
                        data-roles="<?php echo esc_attr(implode(',', $user['roles'] ?? [])); ?>"
                      >
                        <td><?php echo esc_html($user['user_login']); ?></td>
                        <td><?php echo esc_html(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))); ?></td>
                        <td><?php echo esc_html($user['user_email']); ?></td>
                        <td><?php echo esc_html(display_roles($user['roles'])); ?></td>
                        <td>
                          <button type="button" class="button button-primary admin-edit-account">Edit</button>
                          <button type="button" class="button button-secondary admin-delete-account">Delete</button>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </section>
            
            <section class="admin-section" id="admin-tab-import">
              <h2>Bulk Updates</h2>
              <p>Import subjects, courses, tutors, and the schedule from a CSV file. Download the template for the correct format, or export the current database as a CSV.</p>

              <section class="admin-subsection" id="import-upload-section">
                <h3>Import CSV</h3>
                <p>Upload a formatted CSV to replace all subjects, courses, tutors, and schedule entries. The file will be validated before any changes are made.</p>
                <div class="admin-actions" style="margin-top: 0;">
                  <a href="#" class="button button-secondary" id="import-download-template">&#8595; Download Template</a>
                  <a href="#" class="button button-secondary" id="import-export-db">&#8595; Export Current Database</a>
                </div>
                <form class="tutoring-admin-form" id="import-form" style="margin-top: 1rem;">
                  <div class="admin-grid">
                    <div>
                      <label for="csv_file"><strong>Select CSV File</strong></label>
                      <input type="file" id="csv_file" name="csv_file" accept=".csv" />
                    </div>
                  </div>
                  <div class="admin-actions">
                    <button type="submit" class="button button-primary" id="import-upload-btn">Validate &amp; Preview</button>
                    <span class="tutoring-admin-message" id="import-message" hidden></span>
                  </div>
                </form>
                <div id="import-result-panel" hidden style="margin-top: 1rem;">
                  <div id="import-error-panel" hidden>
                    <h4 style="margin-bottom: 0.5rem; color: #b71c1c;">&#10007; Validation Failed</h4>
                    <div class="logs-box" id="import-error-box" role="log" aria-label="Import validation errors"></div>
                  </div>
                  <div id="import-success-panel" hidden>
                    <h4 style="margin-bottom: 0.5rem; color: #1b5e20;">&#10003; Validation Passed — Review &amp; Confirm</h4>
                    <div class="logs-box" id="import-preview-box" role="log" aria-label="Import preview"></div>
                    <div class="admin-actions" style="margin-top: 1rem; align-items: center;">
                      <button type="button" class="button button-primary" id="import-confirm-btn">Confirm Import</button>
                      <button type="button" class="button button-secondary" id="import-cancel-btn">Cancel</button>
                    </div>
                  </div>
                </div>
              </section>

              <section class="admin-subsection" style="margin-top: 1rem;">
                <h3>Delete Schedule Entries by Course</h3>
                <p>Select a course below and delete all associated schedule entries. This action cannot be undone.</p>
                <div class="umbc-table-wrapper">
                  <table class="umbc-table admin-table" id="import-course-table">
                    <thead>
                      <tr>
                        <th>Subject</th>
                        <th>Course ID</th>
                        <th>Course Name</th>
                        <th>Times Offered</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($mCourses as $course) : ?>
                        <tr data-course-id="<?php echo esc_attr($course['course_id']); ?>">
                          <td><?php echo esc_html($course['course_subject']); ?></td>
                          <td><?php echo esc_html($course['course_subject'] . ' ' . $course['course_code']); ?></td>
                          <td><?php echo esc_html($course['course_name']); ?></td>
                          <td><?php echo esc_html($course['times_offered'] ?? '--'); ?></td>
                          <td>
                            <button type="button" class="button button-secondary admin-delete-course-schedule">Delete</button>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </section>
            </section>

            <section class="admin-section" id="admin-tab-logs">
              <h2>Audit Logs</h2>
              <p>View a record of administrative actions taken in 7 day increments. 
                  Use the navigation buttons to move between data ranges or the Jump to Date dropdown to directly go to a date.
                  Export logs to download a .txt file of all stored logs.
              </p>

              <div class="admin-actions">
                <button type="button" class="button button-primary" id="logs-fetch-btn">Fetch Logs</button>
                <button type="button" class="button button-secondary" id="logs-export-btn" hidden>Export Logs</button>
                <span class="tutoring-admin-message" id="logs-message" hidden></span>
              </div>

              <div class="logs-viewer" id="logs-viewer" hidden>
                <div class="logs-nav">
                  <button type="button" class="button button-secondary" id="logs-prev-btn" aria-label="Previous week" disabled>&larr; Previous</button>
                  <span class="logs-date-label" id="logs-date-label"></span>
                  <button type="button" class="button button-secondary" id="logs-next-btn" aria-label="Next week" disabled>Next &rarr;</button>
                  <div class="logs-jump">
                    <label for="logs-jump-date"><strong>Jump To Date</strong></label>
                    <span></span>
                    <input type="date" id="logs-jump-date" aria-label="Jump to date" />
                    <button type="button" class="button button-secondary" id="logs-jump-btn">Go</button>
                  </div>
                </div>

                <div class="logs-box" id="logs-box" role="log" aria-live="polite" aria-label="Audit log entries">
                  <p class="logs-empty" id="logs-empty">No log entries for this day.</p>
                </div>
              </div>
            </section>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </article>
  </div>
</main>

<style>
[hidden] {
  display: none !important;
}

input:disabled {
    background-color: #eee;
}

.tutoring-admin-tabs,
.admin-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin: 0;
  margin-top: 1rem;
}

.admin-tab.active {
  outline: 2px solid #000;
}

.admin-section {
  display: none;
  margin-top: 1rem;
  padding: 1rem;
  padding-top: 0;
  border: 1px solid #ddd;
  border-radius: 8px;
  background: #fff;
}

.admin-subsection {
  padding: 1rem;
  padding-top: 0;
  border: 1px solid #ddd;
  border-radius: 8px;
  background: #fff;
}

.admin-subsection + .admin-subsection {
  margin-top: 16px;
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
}

.admin-role-box {
  display: flex;
  align-items: center;
  flex-wrap: nowrap;
  gap: 8px;
  border: 0;
  padding: 0;
  margin-inline: 0px;
}

input[type="date"] {
  border: 1px solid #737373;
  line-height: 1.4;
  font-size: 1rem;
  border-radius: .25rem;
  padding: .25rem .5rem;
  margin: 0;
}

input[type="date" i]:focus {
  box-shadow: 0 0 0 3px #1c74bc;
}

input[type="date" i]:focus-visible {
  outline: none;
}

#date-range-fields > div + div {
  margin-top: 1rem;
}

.admin-details {
  margin: 1rem 0;
}

.admin-table td,
.admin-table th {
  vertical-align: top;
}

.admin-section .umbc-table-wrapper {
  margin-bottom: 0;
}

.entry-content .admin-table td:last-child,
.entry-content .umbc-table thead th:last-child {
  width: 1%;
  white-space: nowrap;
  text-align: center;
}

#event-table td:nth-child(3),
#event-table td:nth-child(4),
#schedule-table td:nth-child(4),
#schedule-table td:nth-child(5) {
  white-space: nowrap;
}

.tutoring-admin-message {
  padding: 6px 12px;
  border-radius: 6px;
  font-weight: 600;
  align-self: center;
  white-space: nowrap;
}

.tutoring-admin-message.success {
  background: #e8f5e9;
  color: #1b5e20;
}

.tutoring-admin-message.error {
  background: #ffebee;
  color: #b71c1c;
}

.account-field-locked,
.tutoring-admin-form select.account-field-locked,
.tutoring-admin-form select:disabled {
  background: #f3f4f6 !important;
  color: #eee !important;
  cursor: not-allowed;
  opacity: 1;
  border-color: #d1d5db;
  box-shadow: none;
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
}

.time-select-row {
  display: flex;
  align-items: flex-end;
  gap: 6px;
  flex-wrap: nowrap;
}

.time-select-col {
  display: flex;
  flex-direction: column;
  gap: 3px 6px 6px 6px;
  flex: 1;
  min-width: 0;
}

.time-select-label {
  font-weight: 500;
  margin: 0;
}

.time-select-row select {
  width: 100%;
  margin-top: 0;
  text-align: center;
  box-sizing: border-box;
}

.admin-role-options {
  display: flex;
  flex-wrap: nowrap;
  width: 100%;
  justify-content: space-between;
}

.admin-role-options label {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  white-space: nowrap;
}
.admin-role-box input[type="checkbox"] {
  width: auto;
  cursor: pointer;
}

.account-search-wrapper {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.account-search-row {
  display: flex;
  gap: 8px;
  align-items: center;
}

.account-search-row input {
  flex: 1;
  margin-top: 0;
}

.account-search-results {
  border: 1px solid #ddd;
  border-radius: 6px;
  background: #fafafa;
  margin-top: 4px;
  padding: 8px;
  max-height: 300px;
  overflow-y: auto;
}

.account-search-status {
  margin: 0 0 6px;
  font-size: 0.875rem;
  color: #555;
}

.account-search-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.account-search-list .account-search-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 10px;
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  background: #fff;
  cursor: pointer;
  transition: background 0.15s, border-color 0.15s;
}

.account-search-item:hover {
  background: #f0f4ff;
  border-color: #aac;
}

.account-search-item.selected {
  background: #e8f0fe;
  border-color: #3b82f6;
}

.account-search-item-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.account-search-item-name {
  font-weight: 600;
  font-size: 0.9rem;
}

.account-search-item-meta {
  font-size: 0.8rem;
  color: #666;
}

input[readonly] {
    cursor: not-allowed;
    outline: none;
}

.logs-nav {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 10px;
}

.logs-date-label {
  font-weight: 600;
  font-size: 0.95rem;
  min-width: 120px;
  text-align: center;
}

.logs-box {
  border: 1px solid #ddd;
  border-radius: 6px;
  background: #fafafa;
  padding: 10px 14px;
  max-height: 400px;
  overflow-y: auto;
  font-family: monospace;
  font-size: 0.85rem;
  line-height: 1.6;
  white-space: pre-wrap;
  word-break: break-word;
}

.logs-empty {
  margin: 0;
  color: #888;
  font-family: inherit;
  font-style: italic;
}

.logs-entry {
  display: block;
  padding: 3px 0;
  border-bottom: 1px solid #eee;
}

.logs-entry:last-child {
  border-bottom: none;
}

.logs-viewer {
  margin-top: 1rem;
}

.logs-jump {
  display: grid;
  grid-template-columns: auto auto;
  align-items: center;
  gap: 0px 6px;
  margin-left: auto;
}

.logs-jump input[type="date"] {
  margin-top: 0;
}

.logs-jump-field {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.flatpickr-input {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right .5rem center;
  background-size: 1rem .8rem;
  cursor: pointer;
  box-sizing: border-box;
}

.flatpickr-input:focus {
  outline: 0;
  box-shadow: 0 0 0 3px #1c74bc;
  border-color: #737373;
}

.flatpickr-calendar {
  width: 100% !important;
  box-sizing: border-box;
  box-shadow: 0 0 0 3px #1c74bc;
  border: 1px solid #737373;
  border-radius: .25rem;
  font-family: "Inter var", "Helvetica Neue", Helvetica, Arial, sans-serif;
  font-size: 1rem;
}

.admin-section .flatpickr-time input.flatpickr-minute,
.admin-section .flatpickr-time input.flatpickr-hour {
  border: none;
}

.admin-section .flatpickr-time input.flatpickr-minute:focus,
.admin-section .flatpickr-time input.flatpickr-hour:focus {
  border: none;
  box-shadow: none;
}
.flatpickr-wrapper {
  width: 100%;
}

.select2-container--default .select2-selection--single {
  height: 32.39px;
  background-color: #fff;
  border: 1px solid #737373;
  border-radius: .25rem;
  box-sizing: border-box;
  cursor: pointer;
  display: block;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right .5rem center;
  background-size: 1rem .8rem;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
  display: none;
}

.select2-container--default .select2-selection--single .select2-selection__clear {
  padding-right: .75rem;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
  color: #000;
  font-family: "Inter var", "Helvetica Neue", Helvetica, Arial, sans-serif;
  font-size: 1rem;
  font-weight: 400;
  line-height: 1.4;
  padding: .25rem .5rem;
  text-align: left;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  display: block;
}

.select2-container--default .select2-selection--single .select2-selection__placeholder {
  color: #737373;
}

.select2-container--default.select2-container--focus .select2-selection--single,
.select2-container--default.select2-container--open .select2-selection--single {
  border-color: #737373;
  outline: 0;
  box-shadow: 0 0 0 3px #1c74bc;
  z-index: 0;
}

.select2-container--default .select2-dropdown,
.select2-search--dropdown .select2-search__field,
.select2-container--default .select2-results__option {
  font-family: "Inter var", "Helvetica Neue", Helvetica, Arial, sans-serif;
  font-size: 1rem;
}

.select2-container--default .select2-dropdown {
  background-color: #fff;
  border: 1px solid #737373;
  border-radius: .25rem;
}

.select2-search--dropdown .select2-search__field {
  border: 1px solid #737373;
  border-radius: .25rem;
  padding: .25rem .5rem;
  box-sizing: border-box;
  width: 100%;
}

.select2-container--default .select2-results__option {
  color: #000;
  font-weight: 400;
  padding: .25rem .5rem;
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
  background-color: #737373;
  color: #fff;
}

.select2-container--default .select2-results__option[aria-selected="true"] {
  background-color: #f0f0f0;
  color: #000;
}

.select2-container--default .select2-dropdown--below {
  box-shadow: 3px 3px 0 0 #1c74bc, -3px 3px 0 0 #1c74bc, 3px 0 0 0 #1c74bc, -3px 0 0 0 #1c74bc;
}

.select2-container--default .select2-dropdown--above {
  box-shadow: 3px -3px 0 0 #1c74bc, -3px -3px 0 0 #1c74bc, 3px 0 0 0 #1c74bc, -3px 0 0 0 #1c74bc;
}

.select2-search--dropdown .select2-search__field:focus {
  outline: none;
  box-shadow: 0 0 0 3px #1c74bc;
}

.select2-container--open {
  z-index: 1;
}

.select2-container--default.select2-container--disabled .select2-selection--single {
    background-color: #eee;
    cursor: not-allowed;
}
</style>

<?php get_footer(); ?>