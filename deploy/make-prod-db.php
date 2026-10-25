<?php
// Builds a CLEAN production database (schema + default roles/rules + one admin, no demo data)
// in a temporary local database, so the dev database is never touched.
// Env: ADMIN_USERNAME, ADMIN_PASSWORD (required); ADMIN_NAME, ADMIN_EMAIL, PROD_DB (optional)
// Usage: php deploy/make-prod-db.php

$root = dirname(__DIR__);
define('BASE_PATH', $root);
require $root . '/config.php';   // local DB host/user only

$user = getenv('ADMIN_USERNAME');
$pass = getenv('ADMIN_PASSWORD');
$name = getenv('ADMIN_NAME')  ?: 'Administrator';
$mail = getenv('ADMIN_EMAIL') ?: ($user . '@incentive.local');
$db   = getenv('PROD_DB')     ?: 'incentive_system_prod';

if (!$user || !$pass || strlen($pass) < 8) {
    fwrite(STDERR, "ADMIN_USERNAME and ADMIN_PASSWORD (min 8 chars) are required.\n");
    exit(1);
}

$pdo = new PDO(sprintf('mysql:host=%s;port=%d;charset=%s', DB_HOST, DB_PORT, DB_CHARSET),
    DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec("DROP DATABASE IF EXISTS `$db`");
$pdo->exec("CREATE DATABASE `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `$db`");

$sql = preg_replace('/^\s*--.*$/m', '', file_get_contents($root . '/database/schema.sql'));
foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
    $pdo->exec($stmt);
}

$roleId = $pdo->query("SELECT id FROM roles WHERE name='Admin'")->fetchColumn();
$pdo->prepare("INSERT INTO users (role_id, name, email, username, password_hash, status) VALUES (?,?,?,?,?,'active')")
    ->execute([$roleId, $name, $mail, $user, password_hash($pass, PASSWORD_BCRYPT)]);
$pdo->prepare("UPDATE incentive_rules SET created_by = ?")->execute([(int)$pdo->lastInsertId()]);

foreach (['users', 'deals', 'agent_incentives', 'payout_batches', 'audit_log', 'teams', 'incentive_rules', 'roles'] as $t) {
    echo str_pad($t, 18), $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn(), "\n";
}
