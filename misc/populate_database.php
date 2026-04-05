<?php
require_once(__DIR__ . "/drop-in-tutoring/wp-load.php");
require_once(__DIR__ . "/drop-in-tutoring/wp-admin/includes/user.php");

const TABLES = ["subjects", "courses", "wp_users", "schedule", "event_types", "events"];
const COURSE_ID_BASE = 10000;

$ascPdo = db_connect_root("asc_website_db");
$umbcPdo = db_connect_root("umbc_db");

if (($file = fopen(__DIR__ . "/output.csv", 'r')) == false) {
    die("Error opening file!");
}

$stage = 0;
$courseId = COURSE_ID_BASE;
$courses = [];
$users = [];
$eventTypes = [];

role_setup();
drop_tables($umbcPdo, $ascPdo);
flush_cache();

while (($row = fgetcsv($file)) !== false) {
    if (in_array($row[0], TABLES)) {
        $stage++;
        $headers = fgetcsv($file);
        $row = fgetcsv($file);
    }

    if (($data = array_combine($headers, $row)) == false) {
        die("Header/data mismatch");
    }

    switch ($stage) {
        case 1:
            populate_subjects($data, $umbcPdo, $ascPdo);
            break;

        case 2:
            populate_courses($data, $umbcPdo, $ascPdo, $courses, $courseId);
            break;

        case 3:
            populate_users($data, $umbcPdo, $ascPdo, $users);
            break;

        case 4:
            populate_schedule($data, $umbcPdo, $ascPdo, $courses, $users);
            break;

        case 5:
            populate_event_types($data, $ascPdo, $eventTypes);
            break;

        case 6:
            populate_events($data, $ascPdo, $users, $eventTypes);
            break;
    }
}

fclose($file);
echo "Database Populated";

//---------------------------------------------------------------------------------------------------------------------

function role_setup() {
    if (get_role("tutor") !== null ||
        get_role("asc_staff") !== null ||
        get_role("asc_admin") !== null) {
        
        remove_role("tutor");
        remove_role("asc_staff");
        remove_role("asc_admin");

        add_role("tutor", "Tutor", [
            "read" => true
        ]);
        
        add_role("asc_staff", "ASC Staff", [
            "read" => true,
            "staff_control" => true
        ]);

        add_role("asc_admin", "ASC Admin", [
            "read" => true,
            "staff_control" => true,
            "admin_control" => true
        ]);
    }
    
    $admin = get_role("administrator");
    if ($admin) {
        $admin->add_cap("staff_control");
        $admin->add_cap("admin_control");
    }
    
    return;
}

function drop_tables($umbcPdo, $ascPdo) {
    $umbcPdo->exec("DROP TABLE IF EXISTS umbc_courses");
    $umbcPdo->exec("DROP TABLE IF EXISTS umbc_subjects");
    $umbcPdo->exec("DROP TABLE IF EXISTS umbc_accounts");
    $ascPdo->exec("DROP TABLE IF EXISTS schedule");
    $ascPdo->exec("DROP TABLE IF EXISTS courses");
    $ascPdo->exec("DROP TABLE IF EXISTS subjects");
    $ascPdo->exec("DROP TABLE IF EXISTS events");
    $ascPdo->exec("DROP TABLE IF EXISTS event_types");
}

function populate_subjects(&$data, $umbcPdo, $ascPdo) {
    static $umbcStmt = null;
    static $ascStmt = null;

    if ($umbcStmt == null) {
        create_umbc_subjects($umbcPdo);
        create_asc_subjects($ascPdo);

        $umbcStmt = $umbcPdo->prepare("INSERT INTO umbc_subjects 
                                       (subject_code, subject_name) 
                                       VALUES 
                                       (:subject_code, :subject_name)");
        $ascStmt = $ascPdo->prepare("INSERT INTO subjects 
                                     (subject_code, subject_name, subject_count) 
                                     VALUES 
                                     (:subject_code, :subject_name, :subject_count)");
    }

    $stmtArr = [
        ":subject_code" => $data["subject_code"],
        ":subject_name" => $data["subject_name"],
        ":subject_count" => 0
    ];
    
    $umbcStmt->execute(array_slice($stmtArr, 0, 2, true));
    $ascStmt->execute($stmtArr);
    return;
}

function populate_courses(&$data, $umbcPdo, $ascPdo, &$courses, &$courseId) {
    static $umbcStmt = null;
    static $ascStmt = null;
    static $updateStmt = null;

    if ($umbcStmt == null) {
        create_umbc_courses($umbcPdo);
        create_asc_courses($ascPdo);

        $umbcStmt = $umbcPdo->prepare("INSERT INTO umbc_courses 
                                       (course_id, course_subject, course_code, course_name) 
                                       VALUES 
                                       (:course_id, :course_subject, :course_code, :course_name)");
        $ascStmt = $ascPdo->prepare("INSERT INTO courses 
                                     (course_id, course_subject, course_code, course_name, course_count) 
                                     VALUES 
                                     (:course_id, :course_subject, :course_code, :course_name, :course_count)");
        $updateStmt = $ascPdo->prepare("UPDATE subjects 
                                        SET subject_count = subject_count + 1 
                                        WHERE subject_code = :course_subject");
    }

    $stmtArr = [
        ":course_id" => $courseId,
        ":course_subject" => $data["course_subject"],
        ":course_code" => $data["course_code"],
        ":course_name" => $data["course_name"],
        ":course_count" => 0
    ];
    
    $umbcStmt->execute(array_slice($stmtArr, 0, 4, true));
    $ascStmt->execute($stmtArr);
    $updateStmt->execute(array_slice($stmtArr, 1, 1, true));

    $courses[$data["course_subject"] . $data["course_code"]] = $courseId;
    $courseId++;
    return;
}


function populate_users(&$data, $umbcPdo, $ascPdo, &$users) {
    static $umbcStmt = null;
    $asc_admins = ["Justin", "Sam", "Samuel"];
    $sha2_hash = "5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8";  // "password"
    
    if ($umbcStmt == null) {
        create_umbc_accounts($umbcPdo);
        clean_wp_users();
        
        $umbcStmt = $umbcPdo->prepare("INSERT INTO umbc_accounts 
                                       (umbc_id, umbc_email, first_name, last_name, pword_hash) 
                                       VALUES 
                                       (:umbc_id, :umbc_email, :first_name, :last_name, :pword_hash)");
    }

    $umbcStmt->execute([
        ":umbc_id" => $data["umbc_id"],
        ":umbc_email" => $data["umbc_email"],
        ":first_name" => $data["first_name"],
        ":last_name"  => $data["last_name"],
        ":pword_hash" => $sha2_hash
    ]);

    $user_arr = [
        "user_login" => $data["umbc_id"],
        "user_email" => $data["umbc_email"],
        "first_name" => $data["first_name"],
        "last_name"  => $data["last_name"],
        "user_pass"  => wp_generate_password(64),
    ];

    $user_arr["role"] = in_array($data["first_name"], $asc_admins, true) ? "asc_admin" : "tutor";
    
    $userId = wp_insert_user($user_arr);
    if (is_wp_error($userId)) {
        die("Error creating user: " . $userId->get_error_message());
    }
    $users[$data["first_name"]] = $userId;
    return;
}


function populate_schedule(&$data, $umbcPdo, $ascPdo, &$courses, &$users) {
    static $ascStmt = null;
    static $updateStmt = null;

    if ($ascStmt == null) {
        create_asc_schedule($ascPdo);
    
        $ascStmt = $ascPdo->prepare("INSERT INTO schedule
                                     (user_id, course_id, day_of_week, start_time, end_time)
                                     VALUES
                                     (:user_id, :course_id, :day_of_week, :start_time, :end_time)");
        $updateStmt = $ascPdo->prepare("UPDATE courses 
                                        SET course_count = course_count + 1 
                                        WHERE course_id = :course_id");
    }
    
    $data["start_time"] = convert_time($data["start_time"]);
    $data["end_time"] = convert_time($data["end_time"]);

    $stmtArr = [
        ":user_id" => $users[$data["first_name"]],
        ":course_id" => $courses[$data["course_subject"] . $data["course_code"]],
        ":day_of_week" => get_day_abbr($data["day_of_week"]),
        ":start_time" => $data["start_time"],
        ":end_time" => $data["end_time"]
    ];

    $ascStmt->execute($stmtArr);
    $updateStmt->execute(array_slice($stmtArr, 1, 1, true));

    return;
}


function populate_event_types($data, $ascPdo, &$eventTypes) {
    static $ascStmt = null;
    static $event_id = 1;
    if ($ascStmt == null) {
        create_asc_event_types($ascPdo);
    
        $ascStmt = $ascPdo->prepare("INSERT INTO event_types (event_name) VALUES (:event_name)");
    }
    
    $ascStmt->execute([
        ":event_name" => $data["event_name"]
    ]);

    $eventTypes[$data["event_name"]] = $event_id;
    $event_id++;
    return;
}


function populate_events($data, $ascPdo, &$users, &$eventTypes) {
    static $ascStmt = null;
    if ($ascStmt == null) {
        create_asc_events($ascPdo);
    
        $ascStmt = $ascPdo->prepare("INSERT INTO events 
                                     (event_type, user_id, start_day, final_day, duration) 
                                     VALUES 
                                     (:event_type, :user_id, :start_day, :final_day, :duration)");
    }

    $data["start_day"] = create_datetime($data["start_day"]);
    $data["final_day"] = create_datetime($data["final_day"]);

    $ascStmt->execute([
        ":event_type" => $eventTypes[$data["event_name"]],
        ":user_id"    => $users[$data["first_name"]],
        ":start_day"  => $data["start_day"],
        ":final_day"  => $data["final_day"],
        ":duration"   => $data["duration"] !== "NULL" ? intval($data["duration"]) : null
    ]);

}
    
function create_umbc_subjects($umbcPdo) {
    $umbcPdo->exec("CREATE TABLE umbc_subjects (
                    subject_code varchar(8) NOT NULL,
                    subject_name varchar(128) NOT NULL) 
                    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    $umbcPdo->exec("ALTER TABLE umbc_subjects
                    ADD PRIMARY KEY (subject_code)");
    return;
}


function create_asc_subjects($ascPdo) {
    $ascPdo->exec("CREATE TABLE subjects (
                   subject_code varchar(8) NOT NULL,
                   subject_name varchar(128) NOT NULL,
                   subject_count int(11) NOT NULL) 
                   ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    $ascPdo->exec("ALTER TABLE subjects
                   ADD PRIMARY KEY (subject_code)");
    return;
}


function create_umbc_courses($umbcPdo) {
    $umbcPdo->exec("CREATE TABLE umbc_courses (
                    course_id int(11) NOT NULL,
                    course_subject varchar(8) NOT NULL,
                    course_code varchar(8) NOT NULL,
                    course_name varchar(255) NOT NULL)
                    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    $umbcPdo->exec("ALTER TABLE umbc_courses
                    ADD PRIMARY KEY (course_id),
                    ADD KEY idx_course_subject (course_subject),
                    ADD KEY idx_course_code (course_code)");
    $umbcPdo->exec("ALTER TABLE umbc_courses
                    ADD CONSTRAINT fk_course_subject FOREIGN KEY (course_subject) 
                    REFERENCES umbc_subjects (subject_code)");
    return;
}


function create_asc_courses($ascPdo) {
    $ascPdo->exec("CREATE TABLE courses (
                   course_id int(11) NOT NULL,
                   course_subject varchar(8) NOT NULL,
                   course_code varchar(8) NOT NULL,
                   course_name varchar(255) NOT NULL,
                   course_count int(11) NOT NULL)
                   ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    $ascPdo->exec("ALTER TABLE courses
                   ADD PRIMARY KEY (course_id),
                   ADD KEY idx_course_subject (course_subject),
                   ADD KEY idx_course_code (course_code)");
    $ascPdo->exec("ALTER TABLE courses
                   ADD CONSTRAINT fk_course_subject FOREIGN KEY (course_subject) 
                   REFERENCES subjects (subject_code)");
    return;
}


function create_umbc_accounts($umbcPdo) {
    $umbcPdo->exec("CREATE TABLE umbc_accounts (
                    umbc_id varchar(60) NOT NULL,
                    first_name varchar(60) NOT NULL,
                    last_name varchar(60) NOT NULL,
                    umbc_email varchar(100) NOT NULL,
                    pword_hash varchar(320) NOT NULL) 
                    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    $umbcPdo->exec("ALTER TABLE umbc_accounts
                    ADD PRIMARY KEY (umbc_id),
                    ADD UNIQUE KEY umbc_email (umbc_email),
                    ADD KEY idx_first_name (first_name)");
    return;
}


function clean_wp_users() {
    $admin = get_user_by("login", "Admin");
    if (!$admin) {
        die("Admin user not found");
    }
    $users = get_users(["exclude" => [$admin->ID]]);
    foreach ($users as $user) {
        wp_delete_user($user->ID);
    }
    return;
}


function create_asc_schedule($ascPdo) {
    $ascPdo->exec("CREATE TABLE schedule (
                   schedule_id int(11) NOT NULL,
                   user_id bigint(20) UNSIGNED NOT NULL,
                   course_id int(11) NOT NULL,
                   day_of_week enum('MON','TUE','WED','THU','FRI') NOT NULL,
                   start_time time NOT NULL,
                   end_time time NOT NULL) 
                   ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    $ascPdo->exec("ALTER TABLE schedule
                   ADD PRIMARY KEY (schedule_id),
                   ADD KEY idx_user_id (user_id),
                   ADD KEY idx_course_id (course_id),
                   ADD KEY idx_day_of_week (day_of_week)");
    $ascPdo->exec("ALTER TABLE schedule
                   MODIFY schedule_id int(11) NOT NULL AUTO_INCREMENT");
    $ascPdo->exec("ALTER TABLE schedule
                   ADD CONSTRAINT fk_course_id FOREIGN KEY (course_id) 
                   REFERENCES courses (course_id),
                   ADD CONSTRAINT fk_user_id FOREIGN KEY (user_id) 
                   REFERENCES wp_users (ID)");
    return;
}


function convert_time($timeStr) {
    if ($timeStr == "Noon") {
        $timeStr = "12:00:00";
    }
    else {
        $time = DateTime::createFromFormat('g:i a', str_replace('.', '', strtolower($timeStr)));
        $timeStr = $time->format('H:i:s');
    }
    return $timeStr;
}


function create_asc_event_types($ascPdo) {
    $ascPdo->exec("CREATE TABLE event_types (
                   event_type_id int(11) NOT NULL,
                   event_name varchar(16) NOT NULL)
                   ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    $ascPdo->exec("ALTER TABLE event_types
                   ADD PRIMARY KEY (event_type_id),
                   ADD UNIQUE KEY event_name (event_name)");
    $ascPdo->exec("ALTER TABLE event_types
                   MODIFY event_type_id int(11) NOT NULL AUTO_INCREMENT");
    return;
}


function create_asc_events($ascPdo) {
    $ascPdo->exec("CREATE TABLE events (
                   event_id int(11) NOT NULL,
                   event_type int(11) NOT NULL,
                   user_id bigint(20) UNSIGNED NOT NULL,
                   start_day date NOT NULL,
                   final_day date DEFAULT NULL,
                   duration int(11) DEFAULT NULL) 
                   ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    $ascPdo->exec("ALTER TABLE events
                   ADD PRIMARY KEY (event_id),
                   ADD UNIQUE KEY uq_events (user_id,event_type,start_day),
                   ADD KEY fk_event_type (event_type),
                   ADD KEY idx_user_id (user_id),
                   ADD KEY idx_start_day (start_day)");
    $ascPdo->exec("ALTER TABLE events
                   MODIFY event_id int(11) NOT NULL AUTO_INCREMENT");
    $ascPdo->exec("ALTER TABLE events
                   ADD CONSTRAINT fk_event_type FOREIGN KEY (event_type) REFERENCES event_types (event_type_id),
                   ADD CONSTRAINT fk_user_id_event FOREIGN KEY (user_id) REFERENCES wp_users (ID)");
    return;
}

function create_datetime($dayShift) {
    if ($dayShift != "NULL") {
        $dayShift = intval($dayShift);
        return (new DateTime())->modify("+{$dayShift} days")->format('Y-m-d');
    }
    return null;
}