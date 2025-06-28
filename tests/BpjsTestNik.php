<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../observers/BpjsObserver.php';

// Load .env
$env = parse_ini_file(__DIR__ . '/../.env');
$cons_id = $env['BPJS_CONS_ID'] ?? '';
$secret_key = $env['BPJS_SECRET_KEY'] ?? '';

$bpjs = new BpjsObserver();
$timestamp = $bpjs->getTimestamp();

$nik = '9202125210070001'; // Ganti dengan NIK yang valid
$tglSep = date('Y-m-d');

echo "Test cari peserta by NIK:\n";
$result = $bpjs->cariPesertaByNoNik($nik, $tglSep);

print_r($result);

$key = $cons_id . $secret_key . $timestamp;
$response = $result['response'];

echo "hasil: " . ($bpjs->decompress($bpjs->stringDecrypt($key, $response)));