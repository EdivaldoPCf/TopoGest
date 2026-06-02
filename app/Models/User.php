<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Adicionado 'tipo' ao Fillable para salvar se é PF ou PJ
#[Fillable(['name', 'cpf', 'tipo', 'email', 'phone', 'password', 'role', 'approved'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected static function booted()
    {
        static::saved(function ($user) {
            $cpfLimpo = preg_replace('/[^0-9]/', '', $user->cpf);
            if ($cpfLimpo) {
                if ($user->wasChanged('cpf')) {
                    // Se o CPF mudou, migra os imóveis antigos (vinculados ao id dele) para o novo CPF
                    \App\Models\Pasta::where('cliente_id', $user->id)
                        ->update(['identificador_cliente' => $cpfLimpo]);
                }

                if ($user->wasChanged('cpf') || $user->wasRecentlyCreated) {
                    // Vincula as pastas que têm o novo CPF ao cliente
                    \App\Models\Pasta::where('identificador_cliente', $cpfLimpo)
                        ->update(['cliente_id' => $user->id]);
                    
                    // Vincula os contratos em fila que pertencem a este CPF
                    \App\Models\Contrato::where('cpf_proprietario', $cpfLimpo)
                        ->where('status', 'fila')
                        ->update(['cliente_id' => $user->id, 'status' => 'vinculado']);
                }
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'approved' => 'boolean', // Garante que o Laravel trate como verdadeiro/falso
        ];
    }

    /**
     * Formata o documento dependendo se é CPF ou CNPJ.
     */
    public function getFormattedCpfAttribute()
    {
        $documento = $this->cpf;
        
        // Verifica o tamanho da string para determinar a formatação
        if (strlen($documento) === 11) {
            // Formato CPF: 000.000.000-00
            return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "$1.$2.$3-$4", $documento);
        } elseif (strlen($documento) === 14) {
            // Formato CNPJ: 00.000.000/0000-00
            return preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "$1.$2.$3/$4-$5", $documento);
        }

        // Retorna sem formatação caso não seja 11 nem 14 dígitos (segurança)
        return $documento;
    }

    /**
     * Formata o telefone.
     */
    public function getFormattedPhoneAttribute()
    {
        return preg_replace("/(\d{2})(\d{1})(\d{4})(\d{4})/", "($1) $2 $3-$4", $this->phone);
    }

    /**
     * Retorna o nome abreviado do usuário: primeiro nome + iniciais dos demais.
     */
    public function getAbbreviatedNameAttribute()
    {
        $name = trim($this->name);
        $parts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY);

        if (count($parts) <= 1) {
            return $name;
        }

        $firstName = array_shift($parts);
        $initials = implode('', array_map(function ($part) {
            return mb_strtoupper(mb_substr($part, 0, 1));
        }, $parts));

        return trim("{$firstName} {$initials}");
    }

    public function pastas()
    {
        return $this->hasMany(Pasta::class, 'cliente_id');
    }

    public function contratos()
    {
        return $this->hasMany(Contrato::class, 'cliente_id');
    }
}