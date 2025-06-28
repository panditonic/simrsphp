<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../observers/BpjsObserver.php';

// Load .env
$env = parse_ini_file(__DIR__ . '/../.env');
$cons_id = $env['BPJS_CONS_ID'] ?? '';
$secret_key = $env['BPJS_SECRET_KEY'] ?? '';

$bpjs = new BpjsObserver();
$timestamp = $bpjs->getTimestamp();

// Parameter referensi faskes
$namaFaskes = 'jakarta'; // Kata kunci pencarian faskes
$jenisFaskes = 2;        // 1: RS, 2: Puskesmas/Klinik

echo "Test referensi faskes VClaim BPJS:\n";
$endpoint = "referensi/faskes/{$namaFaskes}/{$jenisFaskes}";
$result = $bpjs->request($endpoint, 'GET');

print_r($result);

$key = $cons_id . $secret_key . $timestamp;
$response = $result['response'];

echo "hasil: " . ($bpjs->decompress($bpjs->stringDecrypt($key, $response)));