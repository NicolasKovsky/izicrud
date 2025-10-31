<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;



class Permissoes extends Model
{
    protected $fillable = ['nome', 'nivel', 'descricao', 'ativo', 'deleted'];

    public function usuarios()
    {
        return $this->hasMany(User::class, 'permissao_id', 'id');
    }

}