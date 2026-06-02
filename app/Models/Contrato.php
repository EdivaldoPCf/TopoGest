<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    protected $fillable = [
        'codigo_contrato',
        'nome_proprietario',
        'cpf_proprietario',
        'nome_imovel',
        'localizacao',
        'municipio',
        'codigo_incra',
        'matricula',
        'tipo_servico',
        'valor_servico',
        'valor_entrada',
        'forma_pagamento',
        'detalhes_pagamento',
        'nome_contratado',
        'cpf_cnpj_contratado',
        'endereco_contratado',
        'cliente_id',
        'pasta_id',
        'status',
        'pdf_path',
        'assinado_em',
        'assinatura_hash',
        'assinante_nome',
        'assinante_ip',
        'admin_assinado_em',
        'admin_assinatura_hash',
        'admin_assinante_nome',
        'admin_assinante_ip',
    ];

    /**
     * Gera automaticamente o código único do contrato no formato GETEC/YYYY/NNNNN
     * na criação do registro.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Contrato $contrato) {
            if (empty($contrato->codigo_contrato)) {
                $ano = now()->year;
                // Conta quantos contratos já existem no ano atual
                $sequencial = static::whereYear('created_at', $ano)->count() + 1;
                $contrato->codigo_contrato = 'GETEC/' . $ano . '/' . str_pad($sequencial, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    protected $casts = [
        'assinado_em' => 'datetime',
        'admin_assinado_em' => 'datetime',
        'valor_servico' => 'decimal:2',
    ];

    public function pasta()
    {
        return $this->belongsTo(Pasta::class);
    }

    public function recibos()
    {
        return $this->hasMany(Recibo::class);
    }

    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    /**
     * Tenta vincular o contrato diretamente ao Cliente (User) pelo CPF do proprietário.
     * Retorna true se vinculou, false se ficou em fila.
     */
    public function tentarVincular(): bool
    {
        $cpfLimpo = preg_replace('/[^0-9]/', '', $this->cpf_proprietario);

        $cliente = User::where('cpf', $cpfLimpo)->first();

        if ($cliente) {
            $this->cliente_id = $cliente->id;
            $this->status     = 'vinculado';
            $this->save();
            return true;
        }

        $this->status = 'fila';
        $this->save();
        return false;
    }

    public function cpfFormatado(): string
    {
        $n = preg_replace('/[^0-9]/', '', $this->cpf_proprietario);
        return strlen($n) === 11
            ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $n)
            : $this->cpf_proprietario;
    }

    public function valorFormatado(): string
    {
        return 'R$ ' . number_format($this->valor_servico, 2, ',', '.');
    }
}
