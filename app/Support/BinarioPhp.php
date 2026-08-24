<?php

namespace App\Support;

/**
 * Resolve o caminho do executável PHP de linha de comando.
 *
 * Quando o PHP roda como FPM/CGI (caso do Herd/Nginx), PHP_BINARY aponta para o
 * binário errado para invocar o artisan; aqui tentamos descobrir o `php` real.
 */
class BinarioPhp
{
    public static function caminho(): string
    {
        $php = PHP_BINARY;

        if (! preg_match('/php-fpm|php-cgi/i', $php)) {
            return $php;
        }

        $candidatos = [
            str_replace(['php-fpm', 'php-cgi', 'sbin'], ['php', 'php', 'bin'], $php),
            str_replace(['php-fpm', 'php-cgi'], 'php', $php),
            '/usr/local/bin/php',
            '/usr/bin/php',
            'php',
        ];

        foreach ($candidatos as $candidato) {
            if (file_exists($candidato) && is_executable($candidato)) {
                return $candidato;
            }
        }

        return 'php';
    }
}
