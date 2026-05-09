<?php

function getStoragePath($filename) {
    $baseDir = dirname(__DIR__);
    return $baseDir . '/storage/' . $filename;
}

function readJsonFile($filepath) {
    if (!file_exists($filepath)) {
        return [];
    }
    $content = file_get_contents($filepath);
    if (empty($content)) {
        return [];
    }
    $result = json_decode($content, true);
    return is_array($result) ? $result : [];
}

function writeJsonFile($filepath, $data) {
    if (!is_array($data)) {
        $data = [];
    }
    $dir = dirname($filepath);
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true)) {
            return false;
        }
    }
    return file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT));
}

function getPatientsFile() {
    $date = date('Y-m-d');
    return getStoragePath('patients_' . $date . '.json');
}

function readPatients() {
    return readJsonFile(getPatientsFile());
}

function writePatients($patients) {
    return writeJsonFile(getPatientsFile(), $patients);
}

function requireLogin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
    }
}

function isStaff() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'staff';
}

function isDoctor() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'doctor';
}