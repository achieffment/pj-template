<?php

$db_conn = [
    "host" => "127.0.0.1",
    "name" => "test",
    "user" => "root",
    "password" => "",
    "charset" => "utf8"
];
$dsn = "mysql:host={$db_conn["host"]};dbname={$db_conn["name"]};charset={$db_conn["charset"]}";
$opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
$pdo = new PDO($dsn, $db_conn["user"], $db_conn["password"], $opt);

/*
 * PDO example
 *
$stmt = $pdo->query('SELECT id, name FROM user');
while ($row = $stmt->fetch())
    echo $row['id'] . " : " . $row['name'] . "\n";
*/