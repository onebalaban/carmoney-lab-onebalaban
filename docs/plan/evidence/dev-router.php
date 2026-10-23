<?php

declare(strict_types=1);

// Изолированный стенд для браузерного упражнения; не заменяет MySQL-проверку.
$root = dirname(__DIR__, 3);
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$files = ['/' => ['index.html', 'text/html'], '/app.js' => ['app.js', 'application/javascript'],
    '/styles.css' => ['styles.css', 'text/css']];
if (isset($files[$path])) {
    header('Content-Type: ' . $files[$path][1] . '; charset=utf-8');
    readfile($root . '/frontend/' . $files[$path][0]);
    return;
}
require $root . '/vendor/autoload.php';
$pdo = new PDO('sqlite:' . sys_get_temp_dir() . '/carmoney-onebalaban-e2e.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('CREATE TABLE IF NOT EXISTS applications (id INTEGER PRIMARY KEY AUTOINCREMENT, applicant_ref TEXT, requested_amount INTEGER, term_months INTEGER, status TEXT, created_at TEXT DEFAULT CURRENT_TIMESTAMP)');
$pdo->exec('CREATE TABLE IF NOT EXISTS vehicles (id INTEGER PRIMARY KEY AUTOINCREMENT, application_id INTEGER, vin TEXT, production_year INTEGER, mileage_km INTEGER, market_value INTEGER)');
$pdo->exec('CREATE TABLE IF NOT EXISTS decisions (id INTEGER PRIMARY KEY AUTOINCREMENT, application_id INTEGER, ltv REAL, decision TEXT, approved_limit INTEGER)');
CarMoneyLab\AppFactory::create($pdo)->run();
