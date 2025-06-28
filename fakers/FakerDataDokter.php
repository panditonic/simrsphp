<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../db.php'; // pastikan file db.php mengembalikan objek PDO di $pdo

// Database connection (pastikan $pdo adalah instance PDO)
$db = new Database();
$pdo = $db->openConnection();

$faker = Faker\Factory::create('id_ID');

function generateFakeDoctor() {
    global $faker;
    $spesialisasiList = [
        'Umum', 'Anak', 'Bedah', 'Kandungan', 'Mata', 'THT', 'Saraf', 'Jantung', 'Gigi', 'Kulit'
    ];
    $jenisKelamin = $faker->randomElement(['L', 'P']);
    $nama = $jenisKelamin === 'L' ? $faker->name('male') : $faker->name('female');
    $spesialisasi = $faker->randomElement($spesialisasiList);

    return [
        'nama'           => $nama,
        'spesialisasi'   => $spesialisasi,
        'nomor_str'      => $faker->numerify('STR##########'),
        'jenis_kelamin'  => $jenisKelamin,
        'tanggal_lahir'  => $faker->date('Y-m-d', '-30 years'),
        'alamat'         => $faker->address,
        'telepon'        => $faker->phoneNumber,
        'email'          => $faker->unique()->safeEmail,
        'foto'           => $faker->imageUrl(300, 300, 'people', true, 'doctor'),
        'created_at'     => $faker->dateTimeThisYear()->format('Y-m-d H:i:s'),
    ];
}

// Generate dan insert 10 data dokter
$jumlah = 10;
$sql = "INSERT INTO doctors (nama, spesialisasi, nomor_str, jenis_kelamin, tanggal_lahir, alamat, telepon, email, foto, created_at)
        VALUES (:nama, :spesialisasi, :nomor_str, :jenis_kelamin, :tanggal_lahir, :alamat, :telepon, :email, :foto, :created_at)";
$stmt = $pdo->prepare($sql);

for ($i = 0; $i < $jumlah; $i++) {
    $dokter = generateFakeDoctor();
    $stmt->execute($dokter);
}

echo "Sukses insert $jumlah data dokter ke tabel doctors.\n";