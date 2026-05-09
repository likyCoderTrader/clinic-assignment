<?php
session_start();
require_once 'includes/file_helper.php';

requireLogin();
if (!isDoctor()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $patientId = $_POST['patient_id'] ?? '';
        $status = $_POST['status'] ?? '';
        
        $validStatuses = ['unseen', 'seen', 'in progress', 'unresolved', 'unable'];
        
        if (empty($patientId) || !in_array($status, $validStatuses)) {
            $error = 'Invalid request parameters';
        } else {
            $patients = readPatients();
            $found = false;
            
            foreach ($patients as &$patient) {
                if ($patient['id'] === $patientId) {
                    $patient['status'] = $status;
                    $found = true;
                    $message = 'Status updated for patient';
                    break;
                }
            }
            
            if ($found) {
                writePatients($patients);
                $success = $message;
            } else {
                $error = 'Patient record not found';
            }
        }
    }
    
    if ($_POST['action'] === 'update_notes') {
        $patientId = $_POST['patient_id'] ?? '';
        $doctorNotes = trim($_POST['doctor_notes'] ?? '');
        
        if (empty($patientId)) {
            $error = 'Invalid request';
        } else {
            $patients = readPatients();
            $found = false;
            
            foreach ($patients as &$patient) {
                if ($patient['id'] === $patientId) {
                    $patient['doctor_notes'] = $doctorNotes;
                    $found = true;
                    $message = 'Notes saved successfully';
                    break;
                }
            }
            
            if ($found) {
                writePatients($patients);
                $success = $message;
            } else {
                $error = 'Patient record not found';
            }
        }
    }
}

$patients = readPatients();
$totalPatients = count($patients);
$seenPatients = count(array_filter($patients, fn($p) => $p['status'] === 'seen'));
$inProgress = count(array_filter($patients, fn($p) => $p['status'] === 'in progress'));
$unresolved = count(array_filter($patients, fn($p) => $p['status'] === 'unresolved'));
$unable = count(array_filter($patients, fn($p) => $p['status'] === 'unable'));

$searchQuery = trim($_GET['search'] ?? '');
if ($searchQuery) {
    $patients = array_values(array_filter($patients, fn($p) => 
        stripos($p['patient_name'], $searchQuery) !== false || 
        stripos($p['nin'], $searchQuery) !== false
    ));
}

$today = date('l, F j, Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - City Clinic</title>
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
            <span>Dr. <span class="user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></span> &bull; Physician</span>
            <a href="logout.php" class="btn btn-logout">Sign Out</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="page-header">
            <h1>Consultation Dashboard</h1>
            <p class="date-display"><?= $today ?></p>
        </div>
        
        <div class="stats-container">
            <div class="stat-card primary">
                <div class="stat-value"><?= $totalPatients ?></div>
                <div class="stat-label">Total Assigned</div>
            </div>
            <div class="stat-card success">
                <div class="stat-value"><?= $seenPatients ?></div>
                <div class="stat-label">Completed</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-value"><?= $inProgress ?></div>
                <div class="stat-label">In Progress</div>
            </div>
            <div class="stat-card danger">
                <div class="stat-value"><?= $unresolved + $unable ?></div>
                <div class="stat-label">Pending Review</div>
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h2>Patient Queue</h2>
                <form method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Search patient name or ID..." value="<?= htmlspecialchars($searchQuery) ?>">
                    <button type="submit" class="btn btn-small btn-secondary">Search</button>
                    <?php if ($searchQuery): ?>
                        <a href="doctor_dashboard.php" class="btn btn-small">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <?php if (empty($patients)): ?>
                <p class="no-data">
                    <?= $searchQuery ? 'No patients match your search criteria' : 'No patients assigned for today' ?>
                </p>
            <?php else: ?>
                <div class="patients-list">
                    <?php foreach ($patients as $patient): ?>
                        <div class="patient-card">
                            <div class="patient-card-header">
                                <h3><?= htmlspecialchars($patient['patient_name']) ?></h3>
                                <span class="patient-id"><?= htmlspecialchars($patient['id']) ?></span>
                            </div>
                            
                            <div class="patient-info">
                                <p><strong>National ID:</strong> <?= htmlspecialchars($patient['nin']) ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($patient['email'] ?: 'Not provided') ?></p>
                                <p><strong>Visit Date:</strong> <?= htmlspecialchars($patient['start_date']) ?></p>
                                <p><strong>Registered By:</strong> <?= htmlspecialchars($patient['registered_by']) ?></p>
                                <p class="full-width"><strong>Primary Complaint:</strong> <?= htmlspecialchars($patient['problem']) ?></p>
                                <?php if ($patient['symptoms']): ?>
                                    <p class="full-width"><strong>Observed Symptoms:</strong> <?= htmlspecialchars($patient['symptoms']) ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="patient-actions">
                                <form method="POST" class="status-form">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="patient_id" value="<?= $patient['id'] ?>">
                                    <label>Update Status:</label>
                                    <select name="status" onchange="this.form.submit()">
                                        <option value="unseen" <?= $patient['status'] === 'unseen' ? 'selected' : '' ?>>Unseen</option>
                                        <option value="seen" <?= $patient['status'] === 'seen' ? 'selected' : '' ?>>Seen</option>
                                        <option value="in progress" <?= $patient['status'] === 'in progress' ? 'selected' : '' ?>>In Progress</option>
                                        <option value="unresolved" <?= $patient['status'] === 'unresolved' ? 'selected' : '' ?>>Unresolved</option>
                                        <option value="unable" <?= $patient['status'] === 'unable' ? 'selected' : '' ?>>Unable to Treat</option>
                                    </select>
                                </form>
                                
                                <span class="status status-<?= str_replace(' ', '-', $patient['status']) ?>">
                                    <?= str_replace(' ', '', ucwords($patient['status'])) ?>
                                </span>
                            </div>
                            
                            <div class="doctor-notes-section">
                                <h4>Clinical Notes</h4>
                                <form method="POST">
                                    <input type="hidden" name="action" value="update_notes">
                                    <input type="hidden" name="patient_id" value="<?= $patient['id'] ?>">
                                    <textarea name="doctor_notes" rows="3" placeholder="Enter diagnosis, treatment notes, or observations..."><?= htmlspecialchars($patient['doctor_notes'] ?? '') ?></textarea>
                                    <button type="submit" class="btn btn-small btn-primary">Save Notes</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>