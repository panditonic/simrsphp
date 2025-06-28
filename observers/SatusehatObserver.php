<?php

use GuzzleHttp\Client;

class SatusehatObserver
{
    private $client;
    private $baseUrl;
    private $token;

    public function __construct()
    {
        $this->baseUrl = 'https://api-satusehat.kemkes.go.id/fhir-r4/v1';
        $this->token = $this->getAccessToken();
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Accept'        => 'application/json',
            ]
        ]);
    }

    public function getAccessToken()
    {
        // Ambil credential dari file .env
        $env = parse_ini_file(__DIR__ . '/../.env');
        $clientId = $env['SATUSEHAT_CLIENT_ID'] ?? '';
        $clientSecret = $env['SATUSEHAT_CLIENT_SECRET'] ?? '';

        $client = new \GuzzleHttp\Client();
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $options = [
            'form_params' => [
                'client_id' => $clientId,
                'client_secret' => $clientSecret
            ]
        ];
        $url = 'https://api-satusehat.kemkes.go.id/oauth2/v1/accesstoken?grant_type=client_credentials';
        $response = $client->post($url, $options + ['headers' => $headers]);
        $data = json_decode($response->getBody(), true);
        return $data['access_token'] ?? '';
    }

    public function searchSatusehatIdByNik($nik)
    {
        $url = 'Patient?identifier=https://fhir.kemkes.go.id/id/nik|' . $nik;
        try {
            $response = $this->client->get($this->baseUrl . '/' . $url);
            $data = json_decode($response->getBody(), true);
            if (isset($data['entry'][0]['resource'])) {
                return $data['entry'][0]['resource']; // return full resource array
            }
            return null;
        } catch (\Exception $e) {
            error_log('SATUSEHAT searchSatusehatIdByNik error: ' . $e->getMessage());
            return null;
        }
    }
}
