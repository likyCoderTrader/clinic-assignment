<?php
session_start();
require_once 'includes/file_helper.php';

requireLogin();
if (!isStaff()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register_patient') {
    $patientName = trim($_POST['patient_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $nin = trim($_POST['nin'] ?? '');
    $problem = trim($_POST['problem'] ?? '');
    $startDate = $_POST['start_date'] ?? date('Y-m-d');
    $symptoms = trim($_POST['symptoms'] ?? '');
    
    if (empty($patientName) || empty($nin) || empty($problem)) {
        $error = 'Please fill in all required fields';
    } else {
        $patients = readPatients();
        
        $newPatient = [
            'id' => 'PT' . strtoupper(substr(uniqid(), -8)),
            'patient_name' => $patientName,
            'email' => $email,
            'nin' => $nin,
            'problem' => $problem,
            'start_date' => $startDate,
            'symptoms' => $symptoms,
            'status' => 'unseen',
            'doctor_notes' => '',
            'registered_by' => $_SESSION['user_name'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $patients[] = $newPatient;
        writePatients($patients);
        $success = 'Patient has been registered successfully';
        
        $_POST = [];
    }
}

$patients = readPatients();
$totalPatients = count($patients);
$unseenPatients = count(array_filter($patients, fn($p) => $p['status'] === 'unseen'));
$today = date('l, F j, Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - City Clinic</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
            </svg>
            City Clinic
        </div>
        <div class="nav-user">
            <span><span class="user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></span> &bull; Reception</span>
            <a href="logout.php" class="btn btn-logout">Sign Out</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="page-header">
            <h1>Reception Dashboard</h1>
            <p class="date-display"><?= $today ?></p>
        </div>
        
        <div class="stats-container">
            <div class="stat-card primary">
                <div class="stat-value"><?= $totalPatients ?></div>
                <div class="stat-label">Total Patients</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-value"><?= $unseenPatients ?></div>
                <div class="stat-label">Awaiting Review</div>
            </div>
            <div class="stat-card success">
                <div class="stat-value"><?= $totalPatients > 0 ? round(($totalPatients - $unseenPatients) / $totalPatients * 100) : 0 ?>%</div>
                <div class="stat-label">Completion Rate</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= count(array_filter($patients, fn($p) => $p['status'] === 'in progress')) ?></div>
                <div class="stat-label">In Treatment</div>
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <div class="content-grid">
            <div class="card form-card">
                <h2>Register New Patient</h2>
                <form method="POST" action="staff_dashboard.php">
                    <input type="hidden" name="action" value="register_patient">
                    
                    <div class="form-group">
                        <label for="patient_name">Full Name *</label>
                        <input type="text" id="patient_name" name="patient_name" required value="<?= htmlspecialchars($_POST['patient_name'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="nin">National ID Number *</label>
                        <input type="text" id="nin" name="nin" required value="<?= htmlspecialchars($_POST['nin'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="problem">Primary Complaint *</label>
                        <textarea id="problem" name="problem" required rows="3"><?= htmlspecialchars($_POST['problem'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="start_date">Visit Date</label>
                        <input type="date" id="start_date" name="start_date" value="<?= date('Y-m-d') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="symptoms">Observed Symptoms</label>
                        <textarea id="symptoms" name="symptoms" rows="2"><?= htmlspecialchars($_POST['symptoms'] ?? '') ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Register Patient</button>
                    <p class="required-note">* Required fields</p>
                </form>
            </div>
            
            <div class="card">
                <h2>Today's Patient Queue</h2>
                <?php if (empty($patients)): ?>
                    <p class="no-data">No patients have been registered today</p>
                <?php else: ?>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>ID</th>
                                    <th>Complaint</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($patients as $patient): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($patient['patient_name']) ?></td>
                                        <td><code><?= htmlspecialchars($patient['id']) ?></code></td>
                                        <td><?= htmlspecialchars($patient['problem']) ?></td>
                                        <td>
                                            <span class="status status-<?= $patient['status'] ?>">
                                                <?= str_replace(' ', '', ucwords($patient['status'])) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>