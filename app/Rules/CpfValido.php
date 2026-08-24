<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class CpfValido implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $documento = preg_replace('/[^0-9]/', '', $value);

        if (strlen($documento) === 11) {
            if (! $this->validCpf($documento)) {
                $fail('O CPF informado é inválido.');
            }
            return;
        }

        if (strlen($documento) === 14) {
            if (! $this->validCnpj($documento)) {
                $fail('O CNPJ informado é inválido.');
            }
            return;
        }

        $fail('O CPF ou CNPJ informado é inválido.');
    }

    private function validCpf(string $cpf): bool
    {
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }

    private function validCnpj(string $cnpj): bool
    {
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        $tabelas = [5, 6, 7, 8, 9, 2, 3, 4, 5, 6, 7, 8, 9];

        for ($pos = 12; $pos < 14; $pos++) {
            $sum = 0;
            $weight = ($pos === 12) ? 5 : 6;

            for ($i = 0; $i < $pos; $i++) {
                $sum += $cnpj[$i] * $weight;
                $weight--;
                if ($weight < 2) {
                    $weight = 9;
                }
            }

            $result = $sum % 11;
            $digit = ($result < 2) ? 0 : 11 - $result;

            if ($cnpj[$pos] != $digit) {
                return false;
            }
        }

        return true;
    }
}
