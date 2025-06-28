<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../observers/BpjsObserver.php';

// Load .env
$env = parse_ini_file(__DIR__ . '/../.env');
$cons_id = $env['BPJS_CONS_ID'] ?? '';
$secret_key = $env['BPJS_SECRET_KEY'] ?? '';

$bpjs = new BpjsObserver();
$timestamp = $bpjs->getTimestamp();

$noKartu = '0001078287658'; // Ganti dengan nomor kartu BPJS yang valid
$tglSep = date('Y-m-d');

echo "Test cari peserta by no kartu:\n";
$result = $bpjs->cariPesertaByNoKartu($noKartu, $tglSep);

print_r($result);

$key = $cons_id . $secret_key . $timestamp;
$response = $result['response'];

echo "hasil: " . ($bpjs->decompress($bpjs->stringDecrypt($key, $response)));

// // Test: Insert SEP (isi dengan data sesuai format BPJS VClaim)
// $dataInsert = [
//     "request" => [
//         "t_sep" => [
//             // Isi data sesuai kebutuhan dan format BPJS VClaim
//             "noKartu" => $noKartu,
//             "tglSep" => $tglSep,
//             "ppkPelayanan" => "0001R001",
//             "jnsPelayanan" => "2",
//             "klsRawat" => [
//                 "klsRawatHak" => "3",
//                 "klsRawatNaik" => "",
//                 "pembiayaan" => "",
//                 "penanggungJawab" => ""
//             ],
//             "noMR" => "123456",
//             "rujukan" => [
//                 "asalRujukan" => "1",
//                 "tglRujukan" => $tglSep,
//                 "noRujukan" => "1234567",
//                 "ppkRujukan" => "00010001"
//             ],
//             "catatan" => "Test SEP",
//             "diagAwal" => "A00.1",
//             "poli" => [
//                 "tujuan" => "ANA",
//                 "eksekutif" => "0"
//             ],
//             "cob" => [
//                 "cob" => "0"
//             ],
//             "katarak" => [
//                 "katarak" => "0"
//             ],
//             "jaminan" => [
//                 "lakaLantas" => "0",
//                 "penjamin" => [
//                     "tglKejadian" => "",
//                     "keterangan" => "",
//                     "suplesi" => [
//                         "suplesi" => "0",
//                         "noSepSuplesi" => "",
//                         "lokasiLaka" => [
//                             "kdPropinsi" => "",
//                             "kdKabupaten" => "",
//                             "kdKecamatan" => ""
//                         ]
//                     ]
//                 ]
//             ],
//             "skdp" => [
//                 "noSurat" => "",
//                 "kodeDPJP" => ""
//             ],
//             "noTelp" => "08123456789",
//             "user" => "SIMRS"
//         ]
//     ]
// ];

// echo "\nTest insert SEP:\n";
// $resultInsert = $bpjs->insertSEP($dataInsert);
// print_r($resultInsert);
