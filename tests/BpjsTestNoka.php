<?php

require_once __DIR__ . '/../observers/BpjsObserver.php';

// Contoh test sederhana untuk BpjsObserver
$bpjs = new BpjsObserver();

// Test: Cari peserta berdasarkan nomor kartu (isi dengan data BPJS yang valid)
$noKartu = '0000000000000'; // Ganti dengan nomor kartu BPJS yang valid
$tglSep = date('Y-m-d');

echo "Test cari peserta by no kartu:\n";
$result = $bpjs->cariPesertaByNoKartu($noKartu, $tglSep);
print_r($result);

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
