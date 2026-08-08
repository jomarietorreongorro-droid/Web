<?php
/**
 * Database configuration.
 *
 * By default this uses SQLite so the app runs immediately with zero setup
 * (just PHP, no separate database server required). The schema is created
 * automatically on first run.
 *
 * To use MySQL instead, uncomment the MySQL block below and comment out
 * the SQLite block. You'll need to create the database and import schema.sql
 * yourself first (e.g. `mysql -u root -p taskmanager < schema.sql`).
 */

function getDbConnection(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    // ---------- SQLite (default, zero setup) ----------
    $dbFile = __DIR__ . '/data/taskmanager.sqlite';
    $needsInit = !file_exists($dbFile);

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');

    if ($needsInit) {
        $schema = file_get_contents(__DIR__ . '/schema_sqlite.sql');
        $pdo->exec($schema);
    }

    // ---------- MySQL (alternative) ----------
    // $host = '127.0.0.1';
    // $db   = 'taskmanager';
    // $user = 'root';
    // $pass = '';
    // $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    // $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $pdo;
}
