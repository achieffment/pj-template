<?php
$dbConn = [
    "host" => "127.0.0.1",
    "name" => "projecttemplate",
    "user" => "projecttemplate",
    "password" => "projecttemplate",
    "charset" => "utf8"
];

$dsn = "mysql:host={$dbConn["host"]};dbname={$dbConn["name"]};charset={$dbConn["charset"]}";

$opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$pdo = new PDO($dsn, $dbConn["user"], $dbConn["password"], $opt);

/**
 * PDO example
 * $stmt = $pdo->query('SELECT id, name FROM user');
 * while ($row = $stmt->fetch()) {
 *     echo $row['id'] . " : " . $row['name'] . "\n";
 * }
*/
