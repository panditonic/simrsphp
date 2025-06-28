<?php

require_once __DIR__ . '/../vendor/autoload.php'; // Pastikan path sesuai struktur project Anda
use LZCompressor\LZString;

require_once __DIR__ . '/../observers/BpjsObserver.php';

// Inisialisasi BpjsObserver
$bpjs = new BpjsObserver();

$cons_id = '16606'; // Ganti dengan cons_id BPJS Anda
$secret_key = '8fN87CB58A'; // Ganti dengan secret_key BPJS Anda
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