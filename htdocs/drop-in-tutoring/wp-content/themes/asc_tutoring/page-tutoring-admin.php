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
                    <select id="event_user_id" name="user_id">
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
                    <select id="event_type" name="event_type">
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
                      <input type="date" id="start_day" name="start_day" />
                    </div>
                    <div>
                      <label for="final_day"><strong>End Date</strong></label>
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
              <div class="admin-table-filter" data-table-id="event-table">
                <div class="admin-grid admin-grid--filter">
                  <div>
                    <label><strong>Filter by</strong></label>
                    <select class="admin-table-filter-column-select" aria-label="Select filter column for event-table">
                      <option value=""></option>
                      <option value="0">Tutor</option>
                      <option value="1">Type</option>
                      <option value="2">Start Date</option>
                      <option value="3">End Date</option>
                      <option value="4">Time</option>
                    </select>
                  </div>
                  <div class="admin-table-filter-search-field">
                    <label class="admin-table-filter-value-label"><strong>Value</strong></label>
                    <select class="admin-table-filter-search-select" aria-label="Filter search for event-table" disabled>
                      <option value=""></option>
                    </select>
                  </div>
                  <div>
                    <span class="screen-reader-text"> </span>
                    <button type="button" class="button button-primary admin-table-filter-search">Search</button>
                  </div>
                  <div>
                    <span class="screen-reader-text"> </span>
                    <button type="button" class="button button-secondary admin-table-filter-clear">Clear</button>
                  </div>
                </div>
              </div>
              <div class="umbc-table-scroll">
                <table class="umbc-table admin-table" id="event-table">
                  <thead>
                    <tr>
                      <th>Tutor<span class="table-sort-arrows"><button type="button" class="sort-up" aria-label="Sort ascending">▲</button><button type="button" class="sort-down" aria-label="Sort descending">▼</button></span></th>
                      <th>Type<span class="table-sort-arrows"><button type="button" class="sort-up" aria-label="Sort ascending">▲</button><button type="button" class="sort-down" aria-label="Sort descending">▼</button></span></th>
                      <th>Start Date<span class="table-sort-arrows"><button type="button" class="sort-up" aria-label="Sort ascending">▲</button><button type="button" class="sort-down" aria-label="Sort descending">▼</button></span></th>
                      <th>End Date<span class="table-sort-arrows"><button type="button" class="sort-up" aria-label="Sort ascending">▲</button><button type="button" class="sort-down" aria-label="Sort descending">▼</button></span></th>
                      <th>Time<span class="table-sort-arrows"><button type="button" class="sort-up" aria-label="Sort ascending">▲</button><button type="button" class="sort-down" aria-label="Sort descending">▼</button></span></th>
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
                      <select id="schedule_user_id" name="user_id">
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
                      <select id="schedule_course_lookup" name="schedule_course_lookup">
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
                      <select id="schedule_day_of_week" name="day_of_week">
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
                    <input type="hidden" id="schedule_course_id" name="course_id" />
                  </div>
                  <details class="admin-details">
                    <summary><strong>New course</strong> (Search for courses not currently scheduled)</summary>
                    <div class="admin-grid">
                      <div class="search-wrapper">
                        <label for="course_search_query"><strong>Search Course</strong></label>
                        <div class="search-row">
                          <input type="text" id="course_search_query" name="course_search_query" placeholder="Search by subject, code, or name" autocomplete="off" />
                          <button type="button" class="button button-primary" id="course-search-submit">Search</button>
                        </div>
                        <div id="course_search_results" class="search-results" hidden>
                          <p class="search-status" id="course-search-status"></p>
                          <ul class="search-list" id="course-search-list"></ul>
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
                <div class="admin-table-filter" data-table-id="schedule-table">
                  <div class="admin-grid admin-grid--filter">
                    <div>
                      <label><strong>Filter by</strong></label>
                      <select class="admin-table-filter-column-select" aria-label="Select filter column for schedule-table">
                        <option value=""></option>
                        <option value="0">Tutor</option>
                        <option value="1">Course</option>
                        <option value="2">Day</option>
                        <option value="3">Start Time</option>
                        <option value="4">End Time</option>
                      </select>
                    </div>
                    <div class="admin-table-filter-search-field">
                      <label class="admin-table-filter-value-label"><strong>Value</strong></label>
                      <select class="admin-table-filter-search-select" aria-label="Filter search for schedule-table" disabled>
                        <option value=""></option>
                      </select>
                    </div>
                    <div>
                      <span class="screen-reader-text"> </span>
                      <button type="button" class="button button-primary admin-table-filter-search">Search</button>
                    </div>
                    <div>
                      <span class="screen-reader-text"> </span>
                      <button type="button" class="button button-secondary admin-table-filter-clear">Clear</button>
                    </div>
                  </div>
                </div>
                <div class="umbc-table-scroll">
                  <table class="umbc-table admin-table" id="schedule-table">
                    <thead>
                      <tr>
                        <th>Tutor<span class="table-sort-arrows"><button type="button" class="sort-up" aria-label="Sort ascending">▲</button><button type="button" class="sort-down" aria-label="Sort descending">▼</button></span></th>
                        <th>Course<span class="table-sort-arrows"><button type="button" class="sort-up" aria-label="Sort ascending">▲</button><button type="button" class="sort-down" aria-label="Sort descending">▼</button></span></th>
                        <th>Day<span class="table-sort-arrows"><button type="button" class="sort-up" aria-label="Sort ascending">▲</button><button type="button" class="sort-down" aria-label="Sort descending">▼</button></span></th>
                        <th>Start Time<span class="table-sort-arrows"><button type="button" class="sort-up" aria-label="Sort ascending">▲</button><button type="button" class="sort-down" aria-label="Sort descending">▼</button></span></th>
                        <th>End Time<span class="table-sort-arrows"><button type="button" class="sort-up" aria-label="Sort ascending">▲</button><button type="button" class="sort-down" aria-label="Sort descending">▼</button></span></th>
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
                    <div class="search-wrapper">
                      <label for="account_search_query"><strong>Search UMBC Account</strong></label>
                      <div class="search-row">
                        <input type="text" id="account_search_query" name="account_search_query" placeholder="Search by name, ID, or email" autocomplete="off" />
                        <button type="button" class="button button-primary" id="search-submit">Search</button>
                      </div>
                      <div id="account_search_results" class="search-results" hidden>
                        <p class="search-status" id="search-status"></p>
                        <ul class="search-list" id="search-list"></ul>
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
                <div class="admin-table-filter" data-table-id="account-table">
                  <div class="admin-grid admin-grid--filter">
                    <div>
                      <label><strong>Filter by</strong></label>
                      <select class="admin-table-filter-column-select" aria-label="Select filter column for account-table">
                        <option value=""></option>
                        <option value="0">UMBC ID</option>
                        <option value="1">Name</option>
                        <option value="2">Email</option>
                        <option value="3">Role</option>
                      </select>
                    </div>
                    <div class="admin-table-filter-search-field">
                      <label class="admin-table-filter-value-label"><strong>Value</strong></label>
                      <select class="admin-table-filter-search-select" aria-label="Filter search for account-table" disabled>
                        <option value=""></option>
                      </select>
                    </div>
                    <div>
                      <span class="screen-reader-text"> </span>
                      <button type="button" class="button button-primary admin-table-filter-search">Search</button>
                    </div>
                    <div>
                      <span class="screen-reader-text"> </span>
                      <button type="button" class="button button-secondary admin-table-filter-clear">Clear</button>
                    </div>
                  </div>
                </div>
                <div class="umbc-table-scroll">
                  <table class="umbc-table admin-table" id="account-table">
                    <thead>
                      <tr>
                        <th>UMBC ID<span class="table-sort-arrows"><button type="button" class="sort-up" aria-label="Sort ascending">▲</button><button type="button" class="sort-down" aria-label="Sort descending">▼</button></span></th>
                        <th>Name<span class="table-sort-arrows"><button type="button" class="sort-up" aria-label="Sort ascending">▲</button><button type="button" class="sort-down" aria-label="Sort descending">▼</button></span></th>
                        <th>Email<span class="table-sort-arrows"><button type="button" class="sort-up" aria-label="Sort ascending">▲</button><button type="button" class="sort-down" aria-label="Sort descending">▼</button></span></th>
                        <th>Role<span class="table-sort-arrows"><button type="button" class="sort-up" aria-label="Sort ascending">▲</button><button type="button" class="sort-down" aria-label="Sort descending">▼</button></span></th>
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
              </div>
            </section>
            
            <section class="admin-section" id="admin-tab-import">
              <h2>Bulk Updates</h2>
              <p>Import subjects, courses, tutors, and the schedule from a CSV file. Download the template for the correct format, or export the current database as a CSV.</p>
              <section class="admin-subsection" id="import-upload-section">
                <h3>Import CSV</h3>
                <p>Upload a formatted CSV to replace all subjects, courses, tutors, and schedule entries. The file will be validated before any changes are made.</p>
                <div class="admin-actions">
                  <a class="button button-primary" id="import-export-db">&#8595; Export Schedule</a>
                  <a class="button button-secondary" id="import-download-template">&#8595; Download CSV Template</a>
                </div>
                <form class="tutoring-admin-form" id="import-form">
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
                <div id="import-result-panel" hidden>
                  <div id="import-error-panel" hidden>
                    <h4 style="margin-bottom: 0.5rem; color: #b71c1c;">&#10007; Validation Failed</h4>
                    <div class="logs-box" id="import-error-box" role="log" aria-label="Import validation errors"></div>
                  </div>
                  <div id="import-success-panel" hidden>
                    <h4 style="margin-bottom: 0.5rem; color: #1b5e20;">&#10003; Validation Passed — Review &amp; Confirm</h4>
                    <div class="logs-box" id="import-preview-box" role="log" aria-label="Import preview"></div>
                    <div class="admin-actions">
                      <button type="button" class="button button-primary" id="import-confirm-btn">Confirm Import</button>
                      <button type="button" class="button button-secondary" id="import-cancel-btn">Cancel</button>
                    </div>
                  </div>
                </div>
              </section>

              <section class="admin-subsection">
                <h3>Delete Schedule Entries by Course</h3>
                <p>Select a course below to delete it and all associated schedule entries.</p>
                <div class="umbc-table-wrapper">
                  <div class="admin-table-filter" data-table-id="course-table">
                    <div class="admin-grid admin-grid--filter">
                      <div>
                        <label><strong>Filter by</strong></label>
                        <select class="admin-table-filter-column-select" aria-label="Select filter column for course-table">
                          <option value=""></option>
                          <option value="0">Subject</option>
                          <option value="1">Course ID</option>
                          <option value="2">Course Name</option>
                          <option value="3">Times Offered</option>
                        </select>
                      </div>
                      <div class="admin-table-filter-search-field">
                        <label class="admin-table-filter-value-label"><strong>Value</strong></label>
                        <select class="admin-table-filter-search-select" aria-label="Filter search for course-table" disabled>
                          <option value=""></option>
                        </select>
                      </div>
                      <div>
                        <span class="screen-reader-text"> </span>
                        <button type="button" class="button button-primary admin-table-filter-search">Search</button>
                      </div>
                      <div>
                        <span class="screen-reader-text"> </span>
                        <button type="button" class="button button-secondary admin-table-filter-clear">Clear</button>
                      </div>
                    </div>
                  </div>
                  <table class="umbc-table admin-table" id="course-table">
                    <thead>
                      <tr>
                        <th>Subject<span class="table-sort-arrows"><button type="button" class="sort-up" aria-label="Sort ascending">▲</button><button type="button" class="sort-down" aria-label="Sort descending">▼</button></span></th>
                        <th>Course ID<span class="table-sort-arrows"><button type="button" class="sort-up" aria-label="Sort ascending">▲</button><button type="button" class="sort-down" aria-label="Sort descending">▼</button></span></th>
                        <th>Course Name<span class="table-sort-arrows"><button type="button" class="sort-up" aria-label="Sort ascending">▲</button><button type="button" class="sort-down" aria-label="Sort descending">▼</button></span></th>
                        <th>Times Offered<span class="table-sort-arrows"><button type="button" class="sort-up" aria-label="Sort ascending">▲</button><button type="button" class="sort-down" aria-label="Sort descending">▼</button></span></th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($mCourses as $course) : ?>
                        <tr data-course-id="<?php echo esc_attr($course['course_id']); ?>" data-course-count="<?php echo esc_attr($course['course_count'] ?? 0); ?>">
                          <td><?php echo esc_html($course['course_subject']); ?></td>
                          <td><?php echo esc_html($course['course_subject'] . ' ' . $course['course_code']); ?></td>
                          <td><?php echo esc_html($course['course_name']); ?></td>
                          <td><?php echo esc_html($course['course_count'] ?? '--'); ?></td>
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
                <button type="button" class="button button-secondary" id="logs-export-btn" hidden>&#8595; Export Logs</button>
                <span class="tutoring-admin-message" id="logs-message" hidden></span>
              </div>
              <div class="logs-viewer" id="logs-viewer" hidden>
                <div class="logs-nav">
                  <button type="button" class="button button-secondary" id="logs-prev-btn" aria-label="Previous week" disabled>&larr; Previous</button>
                  <span class="logs-date-label" id="logs-date-label"></span>
                  <button type="button" class="button button-secondary" id="logs-next-btn" aria-label="Next week" disabled>Next &rarr;</button>
                  <div class="logs-jump">
                    <div class="logs-jump-input">
                      <label for="logs-jump-date"><strong>Jump To Date</strong></label>
                      <input type="date" id="logs-jump-date" aria-label="Jump to date" />
                    </div>
                    <button type="button" class="button button-secondary" id="logs-jump-btn">Go</button>
                  </div>
                </div>
                <div class="admin-table-filter" data-logs-filter="logs-box">
                  <div class="admin-grid admin-grid--filter">
                    <div>
                      <label><strong>Filter by</strong></label>
                      <select class="admin-table-filter-column-select" aria-label="Select filter for logs">
                        <option value=""></option>
                        <option value="role">Role</option>
                        <option value="user">User</option>
                        <option value="action">Action</option>
                        <option value="type">Type</option>
                      </select>
                    </div>
                    <div class="admin-table-filter-search-field">
                      <label class="admin-table-filter-value-label"><strong>Value</strong></label>
                      <select class="admin-table-filter-search-select" aria-label="Filter search for logs" disabled>
                        <option value=""></option>
                      </select>
                    </div>
                    <div>
                      <span class="screen-reader-text"> </span>
                      <button type="button" class="button button-primary admin-table-filter-search">Search</button>
                    </div>
                    <div>
                      <span class="screen-reader-text"> </span>
                      <button type="button" class="button button-secondary admin-table-filter-clear">Clear</button>
                    </div>
                  </div>
                </div>
                <div class="umbc-table-scroll">
                  <div class="logs-box" id="logs-box" role="log" aria-live="polite" aria-label="Audit log entries">
                    <p class="logs-empty" id="logs-empty">No log entries for this day.</p>
                  </div>
                </div>
              </div>
            </section>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </article>
  </div>
</main>
<?php get_footer(); ?>