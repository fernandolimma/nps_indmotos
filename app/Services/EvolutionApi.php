<?php

namespace App\Services;

use RuntimeException;

class EvolutionApi
{
    public function __construct(
        private string $baseUrl,
        private string $apiKey,
        private string $instance
    ) {
        $this->baseUrl = rtrim(trim($this->baseUrl), '/');
        $this->apiKey = trim($this->apiKey);
        $this->instance = trim($this->instance);

        if (!filter_var($this->baseUrl, FILTER_VALIDATE_URL) || !in_array(parse_url($this->baseUrl, PHP_URL_SCHEME), ['http', 'https'], true)) {
            throw new RuntimeException('A URL base da Evolution API é inválida.');
        }

        if ($this->apiKey === '' || $this->instance === '') {
            throw new RuntimeException('Configure a API key e a instância da Evolution API.');
        }
    }

    public function sendText(string $phone, string $message): void
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('A extensão cURL do PHP precisa estar habilitada para usar a Evolution API.');
        }

        $number = $this->normalizePhone($phone);
        $url = $this->baseUrl . '/message/sendText/' . rawurlencode($this->instance);
        $payload = json_encode([
            'number' => $number,
            'text' => $message
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($payload === false) {
            throw new RuntimeException('Não foi possível preparar a mensagem de WhatsApp.');
        }

        $curl = curl_init($url);
        if ($curl === false) {
            throw new RuntimeException('Não foi possível iniciar a conexão com a Evolution API.');
        }

        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'apikey: ' . $this->apiKey
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $httpStatus = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($response === false) {
            throw new RuntimeException('Falha de comunicação com a Evolution API: ' . $curlError);
        }

        if ($httpStatus < 200 || $httpStatus >= 300) {
            throw new RuntimeException('A Evolution API retornou HTTP ' . $httpStatus . '.');
        }
    }

    private function normalizePhone(string $phone): string
    {
        $number = preg_replace('/\D+/', '', $phone);
        if ($number === null || $number === '') {
            throw new RuntimeException('O telefone de WhatsApp está vazio ou inválido.');
        }

        if (strlen($number) === 10 || strlen($number) === 11) {
            $number = '55' . $number;
        }

        if (!preg_match('/^55\d{10,11}$/', $number)) {
            throw new RuntimeException('O telefone deve conter um número brasileiro válido com DDD.');
        }

        return $number;
    }
}
