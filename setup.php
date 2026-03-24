<?php
/**
 * UEP LES SCHEDULING SYSTEM v2.0 - AUTOMATED INSTALLER
 * 
 * Creates the full database schema and inserts seed/default data.
 * DELETE THIS FILE after installation is complete.
 * 
 * Roles:
 *   1 = admin      (User account manager)
 *   2 = principal  (Read-only school oversight)
 *   3 = secretary  (Master data + schedule management)
 *   4 = teacher    (Class and student management)
 */

$host   = 'localhost';
$dbuser = 'root';
$dbpass = '';
$dbname = 'ueples_scheduling_system';

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'>";
echo "<style>
    body { font-family: monospace; padding: 24px; background: #0f172a; color: #e2e8f0; }
    h1   { color: #38bdf8; }
    h2   { color: #86efac; margin-top: 24px; }
    .ok  { color: #4ade80; }
    .err { color: #f87171; }
    .warn{ color: #facc15; }
    hr   { border-color: #334155; margin: 24px 0; }
    a    { color: #38bdf8; }
    .box { background: #1e293b; padding: 16px; border-radius: 8px; margin-top: 16px; }
</style></head><body>";

echo "<h1>UEP LES Scheduling System v2.0 — Setup</h1>";

try {
    // =========================================================
    // STEP 1: Connect & Create Database
    // =========================================================
    $pdo = new PDO("mysql:host=$host", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbname`");
    echo "<p>📦 Database <strong>$dbname</strong> ... <span class='ok'>OK</span></p>";

    // =========================================================
    // STEP 2: Create Tables (in dependency order)
    // =========================================================
    echo "<h2>Building Schema...</h2>";

    $tables = [];

    // --- TABLE 1: role ---
    $tables['role'] = "CREATE TABLE IF NOT EXISTS `role` (
        `role_id`     INT          AUTO_INCREMENT PRIMARY KEY,
        `role_name`   VARCHAR(50)  NOT NULL UNIQUE,
        `description` TEXT         NULL
    )";

    // --- TABLE 2: user ---
    $tables['user'] = "CREATE TABLE IF NOT EXISTS `user` (
        `user_id`       INT          AUTO_INCREMENT PRIMARY KEY,
        `role_id`       INT          NOT NULL,
        `username`      VARCHAR(100) NOT NULL UNIQUE,
        `password_hash` VARCHAR(255) NOT NULL,
        `first_name`    VARCHAR(100) NOT NULL,
        `middle_name`   VARCHAR(100) NULL,
        `last_name`     VARCHAR(100) NOT NULL,
        `email`         VARCHAR(255) NULL,
        `academic_rank` VARCHAR(100) NULL,
        `school_college`VARCHAR(100) NULL,
        `department`    VARCHAR(100) NULL,
        `is_active`     BOOLEAN      NOT NULL DEFAULT 1,
        `date_created`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `role`(`role_id`)
    )";

    // --- TABLE 3: school_year ---
    $tables['school_year'] = "CREATE TABLE IF NOT EXISTS `school_year` (
        `school_year_id` INT         AUTO_INCREMENT PRIMARY KEY,
        `label`          VARCHAR(9)  NOT NULL UNIQUE COMMENT 'e.g. 2025-2026',
        `is_active`      BOOLEAN     NOT NULL DEFAULT 0 COMMENT 'Only one row should be active (1) at a time',
        `date_created`   DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP
    )";

    // --- TABLE 4: grade_level ---
    $tables['grade_level'] = "CREATE TABLE IF NOT EXISTS `grade_level` (
        `grade_level_id` INT         AUTO_INCREMENT PRIMARY KEY,
        `name`           VARCHAR(50) NOT NULL UNIQUE,
        `level_order`    TINYINT     NOT NULL COMMENT 'Sort order: 1 through 6'
    )";

    // --- TABLE 5: curriculum ---
    $tables['curriculum'] = "CREATE TABLE IF NOT EXISTS `curriculum` (
        `curriculum_id`  INT          AUTO_INCREMENT PRIMARY KEY,
        `grade_level_id` INT          NOT NULL,
        `name`           VARCHAR(100) NOT NULL,
        `description`    TEXT         NULL,
        `is_active`      BOOLEAN      NOT NULL DEFAULT 1,
        `date_created`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT `fk_curriculum_grade` FOREIGN KEY (`grade_level_id`) REFERENCES `grade_level`(`grade_level_id`)
    )";

    // --- TABLE 6: subject ---
    $tables['subject'] = "CREATE TABLE IF NOT EXISTS `subject` (
        `subject_id`    INT           AUTO_INCREMENT PRIMARY KEY,
        `curriculum_id` INT           NOT NULL,
        `name`          VARCHAR(150)  NOT NULL,
        `units`         DECIMAL(5,2)  NOT NULL DEFAULT 0.00,
        `description`   TEXT          NULL,
        `is_active`     BOOLEAN       NOT NULL DEFAULT 1,
        `date_created`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT `fk_subject_curriculum` FOREIGN KEY (`curriculum_id`) REFERENCES `curriculum`(`curriculum_id`) ON DELETE CASCADE
    )";

    // --- TABLE 7: teacher_subject ---
    $tables['teacher_subject'] = "CREATE TABLE IF NOT EXISTS `teacher_subject` (
        `teacher_id` INT NOT NULL,
        `subject_id` INT NOT NULL,
        PRIMARY KEY (`teacher_id`, `subject_id`),
        CONSTRAINT `fk_ts_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `user`(`user_id`)    ON DELETE CASCADE,
        CONSTRAINT `fk_ts_subject` FOREIGN KEY (`subject_id`) REFERENCES `subject`(`subject_id`) ON DELETE CASCADE
    )";

    // --- TABLE 8: building ---
    $tables['building'] = "CREATE TABLE IF NOT EXISTS `building` (
        `building_id`  INT          AUTO_INCREMENT PRIMARY KEY,
        `name`         VARCHAR(100) NOT NULL UNIQUE,
        `description`  TEXT         NULL,
        `is_active`    BOOLEAN      NOT NULL DEFAULT 1,
        `date_created` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
    )";

    // --- TABLE 9: room ---
    $tables['room'] = "CREATE TABLE IF NOT EXISTS `room` (
        `room_id`     INT          AUTO_INCREMENT PRIMARY KEY,
        `building_id` INT          NOT NULL,
        `room_name`   VARCHAR(100) NOT NULL,
        `capacity`    INT          NOT NULL DEFAULT 40,
        `room_type`   VARCHAR(50)  NULL COMMENT 'e.g. Classroom, Laboratory, Gymnasium',
        `is_active`   BOOLEAN      NOT NULL DEFAULT 1,
        `date_created`DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT `fk_room_building` FOREIGN KEY (`building_id`) REFERENCES `building`(`building_id`) ON DELETE CASCADE
    )";

    // --- TABLE 10: class_section ---
    $tables['class_section'] = "CREATE TABLE IF NOT EXISTS `class_section` (
        `class_section_id` INT          AUTO_INCREMENT PRIMARY KEY,
        `grade_level_id`   INT          NOT NULL,
        `school_year_id`   INT          NOT NULL,
        `section_name`     VARCHAR(100) NOT NULL,
        `is_active`        BOOLEAN      NOT NULL DEFAULT 1,
        `date_created`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `uq_section` (`grade_level_id`, `school_year_id`, `section_name`),
        CONSTRAINT `fk_cs_grade`  FOREIGN KEY (`grade_level_id`) REFERENCES `grade_level`(`grade_level_id`),
        CONSTRAINT `fk_cs_sy`     FOREIGN KEY (`school_year_id`) REFERENCES `school_year`(`school_year_id`)
    )";

    // --- TABLE 11: schedule ---
    $tables['schedule'] = "CREATE TABLE IF NOT EXISTS `schedule` (
        `schedule_id`      INT             AUTO_INCREMENT PRIMARY KEY,
        `schedule_type`    ENUM('LES','COED') NOT NULL COMMENT 'LES = Internal, COED = External',
        `school_year_id`   INT             NOT NULL,
        `semester`         VARCHAR(10)     NOT NULL COMMENT '1, 2, or Summer',
        `teacher_id`       INT             NOT NULL,
        `day_of_week`      VARCHAR(15)     NOT NULL,
        `time_in`          TIME            NOT NULL,
        `time_out`         TIME            NOT NULL,
        `is_active`        BOOLEAN         NOT NULL DEFAULT 1,
        `date_created`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `last_modified`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        -- LES-only columns (NULL for COED records)
        `class_section_id` INT             NULL,
        `subject_id`       INT             NULL,
        `room_id`          INT             NULL,

        -- COED-only columns (NULL for LES records)
        `coed_subject`     VARCHAR(150)    NULL,
        `coed_grade_level` VARCHAR(50)     NULL,
        `coed_building`    VARCHAR(100)    NULL,
        `coed_room`        VARCHAR(100)    NULL,
        `coed_units`       DECIMAL(5,2)    NULL,

        CONSTRAINT `fk_sched_sy`      FOREIGN KEY (`school_year_id`)   REFERENCES `school_year`(`school_year_id`),
        CONSTRAINT `fk_sched_teacher` FOREIGN KEY (`teacher_id`)        REFERENCES `user`(`user_id`),
        CONSTRAINT `fk_sched_class`   FOREIGN KEY (`class_section_id`)  REFERENCES `class_section`(`class_section_id`)  ON DELETE SET NULL,
        CONSTRAINT `fk_sched_subject` FOREIGN KEY (`subject_id`)        REFERENCES `subject`(`subject_id`)              ON DELETE SET NULL,
        CONSTRAINT `fk_sched_room`    FOREIGN KEY (`room_id`)           REFERENCES `room`(`room_id`)                    ON DELETE SET NULL
    )";

    // --- TABLE 12: student ---
    $tables['student'] = "CREATE TABLE IF NOT EXISTS `student` (
        `student_id`       INT          AUTO_INCREMENT PRIMARY KEY,
        `class_section_id` INT          NOT NULL,
        `lrn`              VARCHAR(20)  NULL UNIQUE COMMENT 'DepEd Learner Reference Number (optional)',
        `last_name`        VARCHAR(100) NOT NULL,
        `first_name`       VARCHAR(100) NOT NULL,
        `middle_name`      VARCHAR(100) NULL,
        `gender`           ENUM('Male','Female') NOT NULL,
        `date_of_birth`    DATE         NULL,
        `guardian_name`    VARCHAR(200) NULL,
        `guardian_contact` VARCHAR(20)  NULL,
        `is_active`        BOOLEAN      NOT NULL DEFAULT 1,
        `date_created`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT `fk_student_class` FOREIGN KEY (`class_section_id`) REFERENCES `class_section`(`class_section_id`) ON DELETE CASCADE
    )";

    // --- TABLE 13: audit_log ---
    $tables['audit_log'] = "CREATE TABLE IF NOT EXISTS `audit_log` (
        `log_id`      INT          AUTO_INCREMENT PRIMARY KEY,
        `user_id`     INT          NULL,
        `user_action` VARCHAR(255) NOT NULL,
        `details`     TEXT         NULL COMMENT 'Optional detail string about the action',
        `timestamp`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `ip_address`  VARCHAR(45)  NULL,
        CONSTRAINT `fk_log_user` FOREIGN KEY (`user_id`) REFERENCES `user`(`user_id`) ON DELETE SET NULL
    )";

    // --- TABLE 14: archive_schedule_threshold ---
    $tables['archive_schedule_threshold'] = "CREATE TABLE IF NOT EXISTS `archive_schedule_threshold` (
        `threshold_id`      INT NOT NULL DEFAULT 1 PRIMARY KEY,
        `archive_threshold` INT NOT NULL DEFAULT 6 COMMENT 'Months of inactivity before schedule cleanup eligibility'
    )";

    // --- TABLE 15: audit_log_deletion_threshold ---
    $tables['audit_log_deletion_threshold'] = "CREATE TABLE IF NOT EXISTS `audit_log_deletion_threshold` (
        `threshold_id`      INT NOT NULL DEFAULT 1 PRIMARY KEY,
        `deletion_threshold`INT NOT NULL DEFAULT 12 COMMENT 'Months before old audit log entries are purged'
    )";

    // Execute table creation
    foreach ($tables as $tblName => $sql) {
        $pdo->exec($sql);
        echo "<p>&nbsp;&nbsp;✔ Table <strong>`$tblName`</strong> ... <span class='ok'>OK</span></p>";
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // =========================================================
    // STEP 3: Seed Static / Default Data
    // =========================================================
    echo "<h2>Seeding Default Data...</h2>";

    // -- Roles --
    $pdo->exec("INSERT IGNORE INTO `role` (`role_id`, `role_name`, `description`) VALUES
        (1, 'admin',     'System Administrator — manages all user accounts'),
        (2, 'principal', 'School Principal — read-only oversight of the system'),
        (3, 'secretary', 'School Secretary — manages master data and schedules'),
        (4, 'teacher',   'Faculty Member — manages own class student lists')
    ");
    echo "<p>&nbsp;&nbsp;✔ Roles seeded <span class='ok'>OK</span></p>";

    // -- Grade Levels (fixed, not user-managed) --
    $pdo->exec("INSERT IGNORE INTO `grade_level` (`grade_level_id`, `name`, `level_order`) VALUES
        (1, 'Grade 1', 1),
        (2, 'Grade 2', 2),
        (3, 'Grade 3', 3),
        (4, 'Grade 4', 4),
        (5, 'Grade 5', 5),
        (6, 'Grade 6', 6)
    ");
    echo "<p>&nbsp;&nbsp;✔ Grade Levels (Grade 1–6) seeded <span class='ok'>OK</span></p>";

    // -- Default Active School Year --
    $pdo->exec("INSERT IGNORE INTO `school_year` (`school_year_id`, `label`, `is_active`) VALUES
        (1, '2025-2026', 1)
    ");
    echo "<p>&nbsp;&nbsp;✔ Default School Year (2025-2026, active) seeded <span class='ok'>OK</span></p>";

    // -- Config Thresholds --
    $pdo->exec("INSERT IGNORE INTO `archive_schedule_threshold` (`threshold_id`, `archive_threshold`) VALUES (1, 6)");
    $pdo->exec("INSERT IGNORE INTO `audit_log_deletion_threshold` (`threshold_id`, `deletion_threshold`) VALUES (1, 12)");
    echo "<p>&nbsp;&nbsp;✔ Config thresholds seeded <span class='ok'>OK</span></p>";

    // =========================================================
    // STEP 4: Create Default Admin User Accounts
    // =========================================================
    echo "<h2>Creating Default Accounts...</h2>";

    $defaultAccounts = [
        // [username, password, role_id, first_name, last_name]
        ['admin',     'password123', 1, 'Admin',     'User'],
        ['principal', 'password123', 2, 'Principal', 'User'],
        ['secretary', 'password123', 3, 'Secretary', 'User'],
        ['teacher',   'password123', 4, 'Teacher',   'User'],
    ];

    $roleLabels = [1 => 'admin', 2 => 'principal', 3 => 'secretary', 4 => 'teacher'];

    $stmt = $pdo->prepare("INSERT IGNORE INTO `user`
        (`role_id`, `username`, `password_hash`, `first_name`, `last_name`, `email`)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach ($defaultAccounts as $acc) {
        [$uname, $upass, $roleId, $fname, $lname] = $acc;
        $hash  = password_hash($upass, PASSWORD_DEFAULT);
        $email = $uname . '@uep.edu.ph';
        $stmt->execute([$roleId, $uname, $hash, $fname, $lname, $email]);

        $status = $stmt->rowCount() > 0
            ? "<span class='ok'>Created</span>"
            : "<span class='warn'>Already exists</span>";

        echo "<p>&nbsp;&nbsp;✔ <strong>$uname</strong> ({$roleLabels[$roleId]}) — Password: <code>$upass</code> ... $status</p>";
    }

    // =========================================================
    // DONE
    // =========================================================
    echo "<hr>";
    echo "<div class='box'>";
    echo "<h2 style='color:#4ade80; margin-top:0;'>✅ INSTALLATION COMPLETE</h2>";
    echo "<p><strong>Database:</strong> <code>$dbname</code><br>";
    echo "<strong>Tables created:</strong> " . count($tables) . "<br>";
    echo "<strong>Login:</strong> <a href='frontend/login/index.php'>frontend/login/index.php</a></p>";
    echo "<p style='color:#f87171;'><strong>⚠ SECURITY WARNING: Delete this file immediately after setup!<br>";
    echo "Delete: <code>" . __FILE__ . "</code></strong></p>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<h2 class='err'>❌ Setup Failed</h2>";
    echo "<p class='err'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body></html>";
?>
