<?php

declare(strict_types=1);

/**
 * Database connection — returns a PDO instance.
 * Configuration is read from environment variables with safe defaults.
 */
function getDbConnection(): PDO
{
    $host    = $_ENV['DB_HOST']     ?? getenv('DB_HOST')     ?: 'localhost';
    $dbName  = $_ENV['DB_NAME']     ?? getenv('DB_NAME')     ?: 'rapportquest';
    $user    = $_ENV['DB_USER']     ?? getenv('DB_USER')     ?: 'rapportquest';
    $pass    = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: 'secret';
    $charset = 'utf8mb4';

    $dsn = "mysql:host={$host};dbname={$dbName};charset={$charset}";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    return new PDO($dsn, $user, $pass, $options);
}
