<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Filtra uma coleção de usuários por nome ou CPF (busca usada nas telas de admin).
 */
class FiltroUsuarios
{
    public static function aplicar(Collection $usuarios, ?string $busca): Collection
    {
        if (empty($busca)) {
            return $usuarios;
        }

        $numero = preg_replace('/[^0-9]/', '', $busca);

        return $usuarios->filter(function ($usuario) use ($busca, $numero) {
            $nomeBate = ! empty($usuario->name) && mb_stripos($usuario->name, $busca) !== false;
            $cpfBate = ! empty($numero)
                && strlen($numero) >= 4
                && str_contains(preg_replace('/[^0-9]/', '', (string) $usuario->cpf), $numero);

            return $nomeBate || $cpfBate;
        })->values();
    }
}
