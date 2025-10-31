<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\User;
use App\Models\Permissao;

class ChechPermissao
{
    public function handle(Request $request, Closure $next, $nivelRequisitado): Response
    {



        $user = $request->user();

        

        if (!$user || !$user->hasPermission($nivelRequisitado)) {
            return redirect('/sempermissao')->with('error', 'Você não tem permissão para acessar esta página.');
        }

        return $next($request);
    }
}
