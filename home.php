<?php
include_once __DIR__ . '/db.php';

$db = new Database();
$pdo = $db->openConnection();

$totalPatients = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
$totalDoctors = $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-700">Welcome to SIMRS Dashboard</h2>
    <p class="text-gray-500 mt-2">Manage hospital information efficiently and effectively.</p>
</div>
<!-- Example widgets -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="text-sm text-gray-500">Total Patients</div>
        <div class="text-2xl font-bold text-blue-600 mt-2"><?= $totalPatients ?></div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="text-sm text-gray-500">Total Doctors</div>
        <div class="text-2xl font-bold text-green-600 mt-2"><?= $totalDoctors ?></div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="text-sm text-gray-500">Appointments Today</div>
        <div class="text-2xl font-bold text-purple-600 mt-2">18</div>
    </div>
</div>