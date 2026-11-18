<?php

require __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('America/Sao_Paulo');

$baseUrl = getenv('NPS_PUBLIC_BASE_URL');
if (!is_string($baseUrl) || $baseUrl === '') {
    fwrite(STDERR, "NPS_PUBLIC_BASE_URL não definida.\n");
    exit(1);
}

(new App\Services\WhatsappDispatcher())->run($baseUrl);
