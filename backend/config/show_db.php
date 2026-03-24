<?php
$host = 'localhost';
$db   = 'ueples_scheduling_system';
$user = 'root';
$pass = ''; 

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $sql = "
        SELECT 
            TABLE_NAME,
            COLUMN_NAME,
            COLUMN_TYPE,
            IS_NULLABLE,
            COLUMN_KEY,
            COLUMN_DEFAULT,
            EXTRA
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = :db
        ORDER BY TABLE_NAME, ORDINAL_POSITION
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['db' => $db]);

    $currentTable = null;

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($currentTable !== $row['TABLE_NAME']) {
            $currentTable = $row['TABLE_NAME'];
            echo "\n\n=== TABLE: {$currentTable} ===\n";
        }

        echo "- {$row['COLUMN_NAME']} ({$row['COLUMN_TYPE']})";
        echo " | NULL: {$row['IS_NULLABLE']}";
        echo " | KEY: {$row['COLUMN_KEY']}";
        echo " | DEFAULT: " . ($row['COLUMN_DEFAULT'] ?? 'NULL');
        echo " | EXTRA: {$row['EXTRA']}\n";
    }

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
