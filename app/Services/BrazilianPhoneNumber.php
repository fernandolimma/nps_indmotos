<?php

namespace App\Services;

use RuntimeException;

class BrazilianPhoneNumber
{
    public static function normalize(string $phone): string
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
