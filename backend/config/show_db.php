<?php
$host = 'localhost';
$db   = 'ueples_scheduling_system';
$user = 'root';
$pass = 'your_password';

try {
    // If run from a web browser, wrap the ASCII table in a <pre> tag so newlines render correctly!
    if (php_sapi_name() !== 'cli') {
        echo "<pre style='background: #1e1e1e; color: #d4d4d4; padding: 20px; border-radius: 8px; font-family: monospace; font-size: 14px; overflow-x: auto;'>\n";
    }

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
            if ($currentTable !== null) {
                echo "+---------------------------+--------------------+--------+-------+-------------------+-------------------+\n\n";
            }
            $currentTable = $row['TABLE_NAME'];
            
            $title = " TABLE: {$currentTable} ";
            // Total width minus 2 for borders = 103
            echo "+" . str_repeat('-', 103) . "+\n";
            echo "|" . str_pad($title, 103) . "|\n";
            echo "+---------------------------+--------------------+--------+-------+-------------------+-------------------+\n";
            
            // Print column headers
            printf(
                "| %-25s | %-18s | %-6s | %-5s | %-17s | %-17s |\n",
                'COLUMN', 'TYPE', 'NULL', 'KEY', 'DEFAULT', 'EXTRA'
            );
            echo "+---------------------------+--------------------+--------+-------+-------------------+-------------------+\n";
        }

        printf(
            "| %-25s | %-18s | %-6s | %-5s | %-17s | %-17s |\n",
            substr($row['COLUMN_NAME'], 0, 25),
            substr($row['COLUMN_TYPE'], 0, 18),
            $row['IS_NULLABLE'],
            $row['COLUMN_KEY'] ?: '---',
            substr($row['COLUMN_DEFAULT'] ?? 'NULL', 0, 17),
            substr($row['EXTRA'], 0, 17)
        );
    }
    if ($currentTable !== null) {
        echo "+---------------------------+--------------------+--------+-------+-------------------+-------------------+\n";
    }
    echo "\n";

    if (php_sapi_name() !== 'cli') {
        echo "</pre>";
    }

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage() . "\n");
}