<?php

require_once __DIR__ . '/../observers/BpjsObserver.php';

// Contoh test pencarian peserta BPJS berdasarkan NIK
$bpjs = new BpjsObserver();

$cons_id = '16606'; // Ganti dengan cons_id BPJS Anda
$secret_key = '8fN87CB58A'; // Ganti dengan secret_key BPJS Anda
$timestamp = $bpjs->getTimestamp();

$nik = '9202125210070001'; // Ganti dengan NIK yang valid
$tglSep = date('Y-m-d');

echo "Test cari peserta by NIK:\n";
$endpoint = "Peserta/nik/{$nik}/tglSEP/{$tglSep}";
$result = $bpjs->request($endpoint, 'GET');

print_r($result);

$key = $cons_id . $secret_key . $timestamp;
$response = $result['response'];

$decompress = $bpjs->decompress($bpjs->stringDecrypt($key, $response));

$data = json_decode($decompress);

print_r($data);
