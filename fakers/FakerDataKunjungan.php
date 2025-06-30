<?php

require_once __DIR__ . '/../vendor/autoload.php'; // pastikan composer autoload sudah ada
require_once __DIR__ . '/../db.php'; // pastikan file db.php mengembalikan objek PDO di $pdo

$faker = Faker\Factory::create('id_ID');
$db = new Database();
$pdo = $db->openConnection();

// Ambil semua dokter dan pasien id
$dokterIds = $pdo->query("SELECT id FROM doctors")->fetchAll(PDO::FETCH_COLUMN);
$pasienIds = $pdo->query("SELECT id FROM patients")->fetchAll(PDO::FETCH_COLUMN);

if (!$dokterIds || !$pasienIds) {
    die("Data dokter atau pasien kosong!\n");
}

for ($i = 0; $i < 1000; $i++) {
    $dokter_id = $faker->randomElement($dokterIds);
    $pasien_id = $faker->randomElement($pasienIds);
    $tanggal_waktu = $faker->dateTimeBetween('-1 years', 'now')->format('Y-m-d H:i:s');
    $diagnosa = "string";
    $keluhan = "string";
    $tindakan = "string";
    $catatan = "string";

    $stmt = $pdo->prepare("INSERT INTO kunjungans 
        (dokter_id, pasien_id, tanggal_waktu, diagnosa, keluhan, tindakan, catatan) 
        VALUES (:dokter_id, :pasien_id, :tanggal_waktu, :diagnosa, :keluhan, :tindakan, :catatan)");
    $stmt->execute([
        ':dokter_id' => $dokter_id,
        ':pasien_id' => $pasien_id,
        ':tanggal_waktu' => $tanggal_waktu,
        ':diagnosa' => $diagnosa,
        ':keluhan' => $keluhan,
        ':tindakan' => $tindakan,
        ':catatan' => $catatan
    ]);
}

echo "Berhasil insert 1000 data kunjungan.\n";