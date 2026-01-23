<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

/**
 * Model: Militar
 * 
 * Representa um militar cadastrado no sistema.
 * Utilizado para autenticação via Active Directory e controle de permissões.
 * 
 * Campos obrigatórios:
 * - login (AD)
 * - nome_guerra
 * - posto_graduacao
 * - setor
 * - cpf
 * - saram
 * - email
 * - perfil
 */
class Militar extends Authenticatable
{
    use HasFactory, SoftDeletes, Notifiable, LogsActivity;

    /**
     * Nome da tabela
     */
    protected $table = 'militares';

    /**
     * Campos que podem ser preenchidos em massa
     */
    protected $fillable = [
        'login',
        'nome_guerra',
        'posto_graduacao',
        'setor',
        'ramal',
        'whatsapp',
        'cpf',
        'saram',
        'email',
        'perfil',
        'password',
        'ativo',
    ];

    /**
     * Campos que devem ser ocultos em arrays/JSON
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts de tipos
     */
    protected $casts = [
        'ativo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Mutator para hash da senha (apenas em desenvolvimento)
     */
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    /**
     * Configuração de logs de atividade
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nome_guerra', 'perfil', 'ativo'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Relacionamento: Um militar pode ter várias cautelas como retirante
     */
    public function cautelasRetirante()
    {
        return $this->hasMany(Cautela::class, 'militar_id');
    }

    /**
     * Relacionamento: Um militar pode ter várias cautelas como ferramenteiro
     */
    public function cautelasFerramenteiro()
    {
        return $this->hasMany(Cautela::class, 'ferramenteiro_id');
    }

    /**
     * Relacionamento: Todas as cautelas do militar (como retirante)
     * Alias para cautelasRetirante para facilitar o uso
     */
    public function cautelas()
    {
        return $this->cautelasRetirante();
    }

    /**
     * Verifica se o militar é administrador
     */
    public function isAdministrador(): bool
    {
        return $this->perfil === 'Administrador';
    }

    /**
     * Verifica se o militar é ferramenteiro
     */
    public function isFerramenteiro(): bool
    {
        return $this->perfil === 'Ferramenteiro';
    }

    /**
     * Verifica se o militar é da manutenção
     */
    public function isManutencao(): bool
    {
        return $this->perfil === 'Manutenção';
    }

    /**
     * Verifica se o militar pode criar cautelas
     */
    public function podeCriarCautelas(): bool
    {
        return $this->isAdministrador() || $this->isFerramenteiro();
    }

    /**
     * Scope: Apenas militares ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope: Filtrar por perfil
     */
    public function scopePorPerfil($query, string $perfil)
    {
        return $query->where('perfil', $perfil);
    }
}


