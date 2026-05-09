<?php
session_start();
require_once 'includes/file_helper.php';

requireLogin();

$role = $_SESSION['user_role'];
$name = $_SESSION['user_name'];

if ($role === 'staff') {
    header('Location: staff_dashboard.php');
    exit;
} elseif ($role === 'doctor') {
    header('Location: doctor_dashboard.php');
    exit;
}

header('Location: index.php');
exit;