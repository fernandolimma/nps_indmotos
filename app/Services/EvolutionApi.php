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
        $number = BrazilianPhoneNumber::normalize($phone);
        $url = $this->baseUrl . '/message/sendText/' . rawurlencode($this->instance);
        $payload = json_encode([
            'number' => $number,
            'text' => $message
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($payload === false) {
            throw new RuntimeException('Não foi possível preparar a mensagem de WhatsApp.');
        }

        [$httpStatus, $response] = $this->request('POST', $url, $payload);
        $this->assertSuccessfulResponse($httpStatus, $response);
    }

    public function createInstance(): void
    {
        $payload = json_encode([
            'instanceName' => $this->instance,
            'integration' => 'WHATSAPP-BAILEYS',
            'qrcode' => true
        ], JSON_UNESCAPED_SLASHES);

        if ($payload === false) {
            throw new RuntimeException('Não foi possível preparar a criação da instância WhatsApp.');
        }

        [$httpStatus, $response] = $this->request('POST', $this->baseUrl . '/instance/create', $payload);
        $this->assertSuccessfulResponse($httpStatus, $response);
    }

    /**
     * @return array{base64: string|null, code: string|null}
     */
    public function getQrCode(): array
    {
        $url = $this->baseUrl . '/instance/connect/' . rawurlencode($this->instance);
        [$httpStatus, $response] = $this->request('GET', $url);
        $this->assertSuccessfulResponse($httpStatus, $response);

        $data = json_decode($response, true);
        if (!is_array($data)) {
            throw new RuntimeException('A API respondeu em formato inválido ao solicitar o QR code.');
        }
        if (!empty($data['error'])) {
            $detail = $this->extractErrorMessage($response);
            throw new RuntimeException($detail !== '' ? $detail : 'A Evolution API não conseguiu gerar o QR code.');
        }

        $base64 = $data['base64'] ?? $data['qrcode']['base64'] ?? null;
        if (is_string($base64) && $base64 !== '') {
            if (str_starts_with($base64, 'data:image/')) {
                if (!preg_match('#^data:image/(?:png|jpeg|webp);base64,[A-Za-z0-9+/=\r\n]+$#D', $base64)) {
                    throw new RuntimeException('A Evolution API retornou um QR code em formato inválido.');
                }
            } elseif (preg_match('/^[A-Za-z0-9+\/=\r\n]+$/', $base64)) {
                $base64 = 'data:image/png;base64,' . $base64;
            } else {
                throw new RuntimeException('A Evolution API retornou um QR code em formato inválido.');
            }
        } else {
            $base64 = null;
        }

        $code = $data['pairingCode']
            ?? $data['code']
            ?? $data['qrcode']['pairingCode']
            ?? $data['qrcode']['code']
            ?? null;
        if (!is_string($code) || $code === '') {
            $code = null;
        }
        if ($base64 === null && $code === null) {
            throw new RuntimeException('A API não retornou QR code nem código de pareamento. Tente gerar novamente.');
        }

        return ['base64' => $base64, 'code' => $code];
    }

    public function getConnectionState(): string
    {
        $url = $this->baseUrl . '/instance/connectionState/' . rawurlencode($this->instance);
        [$httpStatus, $response] = $this->request('GET', $url);
        $this->assertSuccessfulResponse($httpStatus, $response);

        $data = json_decode($response, true);
        $state = is_array($data) ? ($data['instance']['state'] ?? null) : null;
        if (!is_string($state) || $state === '') {
            throw new RuntimeException('A API respondeu, mas não informou o estado da instância.');
        }

        return $state;
    }

    public function checkConnection(): string
    {
        $state = $this->getConnectionState();
        if (strtolower($state) !== 'open') {
            throw new RuntimeException('A instância respondeu com estado "' . $state . '". Conecte o WhatsApp antes de enviar pesquisas.');
        }

        return 'Conexão com a API validada. Instância conectada.';
    }

    private function request(string $method, string $url, ?string $payload = null): array
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('A extensão cURL do PHP precisa estar habilitada para usar a Evolution API.');
        }

        $curl = curl_init($url);
        if ($curl === false) {
            throw new RuntimeException('Não foi possível iniciar a conexão com a Evolution API.');
        }

        $options = [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'apikey: ' . $this->apiKey
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30
        ];
        if ($payload !== null) {
            $options[CURLOPT_POSTFIELDS] = $payload;
        }

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $httpStatus = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($response === false) {
            throw new RuntimeException('Falha de comunicação com a Evolution API: ' . $curlError);
        }

        return [$httpStatus, $response];
    }

    private function assertSuccessfulResponse(int $httpStatus, string $response): void
    {
        if ($httpStatus < 200 || $httpStatus >= 300) {
            $detail = $this->extractErrorMessage($response);
            throw new RuntimeException(
                'A Evolution API retornou HTTP ' . $httpStatus
                . ($detail !== '' ? ': ' . $detail : '.')
            );
        }
    }

    private function extractErrorMessage(string $response): string
    {
        $data = json_decode($response, true);
        if (!is_array($data)) {
            return '';
        }

        $message = $data['error']['message'] ?? $data['message'] ?? $data['error'] ?? null;
        if (is_array($message)) {
            $message = implode('; ', array_filter($message, 'is_string'));
        }
        if (!is_string($message)) {
            return '';
        }

        $message = trim(str_replace($this->apiKey, '[ocultada]', $message));
        return strlen($message) > 300 ? substr($message, 0, 300) . '...' : $message;
    }

}
