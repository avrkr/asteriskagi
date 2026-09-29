<?php

function agi_read_env(string $key, ?string $default = null): ?string
{
    static $env = null;
    if ($env === null) {
        $path = '/avrkrapp/.env';
        if (!is_readable($path)) {
            return $default;
        }
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (!str_contains($line, '=') || str_starts_with(trim($line), '#')) {
                continue;
            }
            [$k, $v] = explode('=', $line, 2);
            $env[trim($k)] = trim($v);
        }
    }
    return $env[$key] ?? $default;
}

function agi_pdo(): PDO
{
    static $pdo = null;
    if ($pdo) {
        return $pdo;
    }
    $url = agi_read_env('DATABASE_URL');
    if (!$url) {
        throw new RuntimeException('DATABASE_URL missing');
    }
    $url = str_replace('mysql://', '', $url);
    [$auth, $rest] = explode('@', $url, 2);
    [$user, $pass] = explode(':', $auth, 2);
    [$hostPort, $db] = explode('/', $rest, 2);
    [$host, $port] = array_pad(explode(':', $hostPort, 2), 2, 3306);
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $db);
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    return $pdo;
}

function agi_db_fetch_one(string $sql, array $params): ?array
{
    $stmt = agi_pdo()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function agi_set_var(string $name, string $value): void
{
    echo "SET VARIABLE {$name} \"{$value}\"\n";
}

function agi_verbose(string $msg): void
{
    echo "VERBOSE \"{$msg}\" 1\n";
}
