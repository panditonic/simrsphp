<?php

include_once __DIR__ . '/db.php';

$db = new Database();
$pdo = $db->openConnection();

$totalPatients = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
$totalDoctors = $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
$totalKunjungans = $pdo->query("SELECT COUNT(*) FROM kunjungans")->fetchColumn();

// Data kunjungan 7 hari terakhir
$kunjunganData = [];
$labels = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $labels[] = $date; // tampilkan tanggal penuh, misal: 2025-06-24
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM kunjungans WHERE DATE(tanggal_waktu) = :date");
    $stmt->execute([':date' => $date]);
    $kunjunganData[] = (int)$stmt->fetchColumn();
}
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-700">Welcome to SIMRS Dashboard</h2>
    <p class="text-gray-500 mt-2">Manage hospital information efficiently and effectively.</p>
</div>
<!-- Example widgets -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="text-sm text-gray-500">Total Patients</div>
        <div class="text-2xl font-bold text-blue-600 mt-2"><?= $totalPatients ?></div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="text-sm text-gray-500">Total Doctors</div>
        <div class="text-2xl font-bold text-green-600 mt-2"><?= $totalDoctors ?></div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="text-sm text-gray-500">Kunjungan</div>
        <div class="text-2xl font-bold text-purple-600 mt-2"><?= $totalKunjungans ?></div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div class="bg-white p-6 rounded-lg shadow mb-8">
    <div class="text-sm text-gray-500 mb-2">Grafik Kunjungan 7 Hari Terakhir</div>
    <canvas id="kunjunganChart" height="80"></canvas>
</div>
<script>
const ctx = document.getElementById('kunjunganChart').getContext('2d');
const kunjunganChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Jumlah Kunjungan',
            data: <?= json_encode($kunjunganData) ?>,
            borderColor: '#8b5cf6',
            backgroundColor: 'rgba(139,92,246,0.1)',
            tension: 0.4,
            fill: true,
            pointRadius: 5,
            pointBackgroundColor: '#8b5cf6'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 }
            }
        }
    }
});
</script>