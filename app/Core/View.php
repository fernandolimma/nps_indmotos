<?php

namespace App\Core;

use App\Models\Configuracao;

class View
{
    private static ?string $appName = null;

    public static function render($view, $data = [], $return = false)
    {
        extract($data);
        $viewPath = __DIR__ . '/../Views/' . $view . '.php';
        
        if (!file_exists($viewPath)) {
            die('View não encontrada: ' . $view);
        }
        
        if ($return) {
            ob_start();
            require $viewPath;
            return ob_get_clean();
        }
        
        require $viewPath;
    }

    public static function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    public static function appName(): string
    {
        if (self::$appName !== null) {
            return self::$appName;
        }

        try {
            $config = (new Configuracao())->get();
            $nomeRemetente = trim((string) ($config['nome_remetente'] ?? ''));

            self::$appName = $nomeRemetente !== '' ? $nomeRemetente : 'Indmotos';
        } catch (\Throwable) {
            self::$appName = 'Indmotos';
        }

        return self::$appName;
    }
}
