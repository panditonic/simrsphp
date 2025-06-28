<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../db.php'; // pastikan file db.php mengembalikan objek PDO di $pdo

use Faker\Factory;
use Faker\Provider\id_ID\Person;
use Faker\Provider\id_ID\Address;
use Faker\Provider\id_ID\PhoneNumber;
use Faker\Provider\id_ID\Internet;

// Database connection (pastikan $pdo adalah instance PDO)
$db = new Database();
$pdo = $db->openConnection();

$faker = Factory::create('id_ID');
$faker->addProvider(new Person($faker));
$faker->addProvider(new Address($faker));
$faker->addProvider(new PhoneNumber($faker));
$faker->addProvider(new Internet($faker));

$stmt = $pdo->prepare("
    INSERT INTO patients 
    (no_rm, nama_lengkap, nama_panggilan, alamat, telepon, foto, nama_ayah, nama_ibu, nama_penanggung_jawab, kontak_penanggung_jawab, no_nik, no_bpjs)
    VALUES 
    (:no_rm, :nama_lengkap, :nama_panggilan, :alamat, :telepon, :foto, :nama_ayah, :nama_ibu, :nama_penanggung_jawab, :kontak_penanggung_jawab, :no_nik, :no_bpjs)
");

for ($i = 0; $i < 10; $i++) {
    $gender = $faker->randomElement(['male', 'female']);
    $nama_lengkap = $faker->name($gender);
    $nama_panggilan = $faker->firstName($gender);

    $no_rm                   = $faker->unique()->numerify('RM######');
    $alamat                  = $faker->address;
    $telepon                 = $faker->phoneNumber;
    $foto                    = $faker->imageUrl(300, 300, 'people', true, $nama_lengkap);
    $nama_ayah               = $faker->name('male');
    $nama_ibu                = $faker->name('female');
    $nama_penanggung_jawab   = $faker->name;
    $kontak_penanggung_jawab = $faker->phoneNumber;
    $no_nik                  = $faker->numerify('0000##########');
    $no_bpjs                 = $faker->boolean(70) ? $faker->numerify('0000##########') : null;

    $stmt->execute([
        ':no_rm' => $no_rm,
        ':nama_lengkap' => $nama_lengkap,
        ':nama_panggilan' => $nama_panggilan,
        ':alamat' => $alamat,
        ':telepon' => $telepon,
        ':foto' => $foto,
        ':nama_ayah' => $nama_ayah,
        ':nama_ibu' => $nama_ibu,
        ':nama_penanggung_jawab' => $nama_penanggung_jawab,
        ':kontak_penanggung_jawab' => $kontak_penanggung_jawab,
        ':no_nik' => $no_nik,
        ':no_bpjs' => $no_bpjs,
    ]);
}

echo "10 data pasien berhasil dimasukkan ke tabel patients.\n";