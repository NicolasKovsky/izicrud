<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Permissoes;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'permissao_id',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
        ];
    }

    public function permissao()
    {
        return $this->hasOne(Permissoes::class, 'id', 'permissao_id');
    }

    public function hasPermission($nivelRequisitado) : bool
    {
        $permissao = $this->permissao()->first();

        if ($permissao) {
            return $permissao->nivel <= $nivelRequisitado;
        }

        return false;
    }

    public function getNivel() : int
    {
        $permissao = $this->permissao()->first();

        if ($permissao) {
            return $permissao->nivel;
        }

        return 0;
    }
}
