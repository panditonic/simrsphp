<?php

require_once __DIR__ . '/../vendor/autoload.php'; // Pastikan path sesuai struktur project Anda
use LZCompressor\LZString;

/**
 * Observer untuk integrasi BPJS VClaim.
 * Contoh ini menggunakan cURL untuk request ke endpoint VClaim BPJS.
 * Silakan sesuaikan base_url, cons_id, secret_key, dan user_key sesuai kredensial BPJS Anda.
 */

class BpjsObserver
{
    private $base_url;
    private $cons_id;
    private $secret_key;
    private $user_key;

    public function __construct()
    {
        if (file_exists(__DIR__ . '/../.env')) {
            $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1));
            $dotenv->load();
        }
        $this->base_url   = $_ENV['BPJS_BASE_URL']   ?? '';
        $this->cons_id    = $_ENV['BPJS_CONS_ID']    ?? '';
        $this->secret_key = $_ENV['BPJS_SECRET_KEY'] ?? '';
        $this->user_key   = $_ENV['BPJS_USER_KEY']   ?? '';
    }

    // Generate signature untuk header X-Signature
    private function generateSignature($timestamp)
    {
        return base64_encode(hash_hmac('sha256', $this->cons_id . "&" . $timestamp, $this->secret_key, true));
    }

    public function getKey()
    {
        return $this->cons_id . $this->secret_key . $this->getTimestamp();
    }

    // Generate timestamp UTC
    public function getTimestamp()
    {
        return strval(time());
    }

    // Fungsi dekripsi AES-256-CBC sesuai standar BPJS
    public function stringDecrypt($key, $string)
    {
        $encrypt_method = 'AES-256-CBC';
        $key_hash = hex2bin(hash('sha256', $key));
        $iv = substr($key_hash, 0, 16);
        $encrypted = base64_decode($string);
        $decrypted = openssl_decrypt($encrypted, $encrypt_method, $key_hash, OPENSSL_RAW_DATA, $iv);
        return $decrypted;
    }

    // Fungsi decompress jika diperlukan (pastikan LZString tersedia)
    public function decompress($string)
    {
        return LZString::decompressFromEncodedURIComponent($string);
    }

    // Request ke endpoint VClaim BPJS
    public function request($endpoint, $method = 'GET', $data = null)
    {
        $timestamp = $this->getTimestamp();
        $signature = $this->generateSignature($timestamp);

        $headers = [
            "X-cons-id: {$this->cons_id}",
            "X-timestamp: {$timestamp}",
            "X-signature: {$signature}",
            "user_key: {$this->user_key}",
            "Content-Type: application/json"
        ];

        $url = $this->base_url . ltrim($endpoint, '/');
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        }

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return [
                'metaData' => [
                    'code' => 500,
                    'message' => $err
                ]
            ];
        }

        return json_decode($response, true);
    }

    // Contoh: Cari peserta BPJS berdasarkan nomor kartu
    public function cariPesertaByNoKartu($noKartu, $tglSep)
    {
        $endpoint = "Peserta/nokartu/{$noKartu}/tglSEP/{$tglSep}";
        return $this->request($endpoint, 'GET');
    }

    // Contoh: Cari peserta BPJS berdasarkan NIK
    public function cariPesertaByNoNik($noNik, $tglSep)
    {
        $endpoint = "Peserta/nik/{$noNik}/tglSEP/{$tglSep}";
        return $this->request($endpoint, 'GET');
    }

    // Contoh: Insert SEP
    public function insertSEP($data)
    {
        $endpoint = "SEP/2.0/insert";
        return $this->request($endpoint, 'POST', $data);
    }

    // Contoh: Update SEP
    public function updateSEP($data)
    {
        $endpoint = "SEP/2.0/update";
        return $this->request($endpoint, 'PUT', $data);
    }

    // Contoh: Delete SEP
    public function deleteSEP($data)
    {
        $endpoint = "SEP/2.0/delete";
        return $this->request($endpoint, 'DELETE', $data);
    }
}
