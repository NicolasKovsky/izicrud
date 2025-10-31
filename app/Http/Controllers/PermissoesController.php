<?php

namespace App\Http\Controllers;

use App\Models\Permissoes;
use Illuminate\Http\Request;

use App\Models\User;
use Inertia\Inertia;

class PermissoesController extends Controller
{
    public function index()
    {
        $items = Permissoes::where('deleted', 0)->orderBy('id', 'desc')->paginate(9);
        $allItems = Permissoes::where('deleted', 0)->orderBy('id', 'desc')->get();

        return inertia('Permissoes/index', [
            'itens' => $items,
            'allItens' => $allItems,
            'totalItensDeletados' => Permissoes::where('deleted', 1)->count(),
            'sidebarNavItems' => $this->getSidebarNavItems(),
        ]);
    }

    public function create()
    {
        return inertia('Permissoes/create', [
            'sidebarNavItems' => $this->getSidebarNavItems(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'nivel' => 'required|integer|max:255',
            'descricao' => 'required|string|max:255',
            'ativo' => 'required|boolean|max:255',
        ]);

        Permissoes::create($request->all());

        return redirect()->route('permissoes.index')->with('success', 'Permissoes criado com sucesso.');
    }

    public function edit(Permissoes $permissoes)
    {
        if ($permissoes->deleted) {
            return redirect()->route('permissoes.index')->with('error', 'Permissoes excluído.');
        }

        return inertia('Permissoes/create', [
            'item' => $permissoes->toArray(),
            'sidebarNavItems' => $this->getSidebarNavItems(),
        ]);
    }

    public function update(Request $request, Permissoes $permissoes)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'nivel' => 'required|integer|max:255',
            'descricao' => 'required|string|max:255',
            'ativo' => 'required|boolean|max:255',
        ]);

        $permissoes->update($request->all());

        return redirect()->route('permissoes.index')->with('success', 'Permissoes atualizado com sucesso.');
    }

    public function destroy(Permissoes $permissoes)
    {
        $permissoes->update(['deleted' => 1]);

        return redirect()->route('permissoes.index')->with('success', 'Permissoes excluído com sucesso.');
    }

    private function getSidebarNavItems(): array
    {
        return [
            ['title' => 'Todos os Permissoes', 'href' => '/permissoes'],
            ['title' => 'Criar Novo Permissoes', 'href' => '/permissoes/create'],
            ['title' => 'Atribuir', 'href' => '/permissoes/atribuir'],
            ['title' => 'Usuarios', 'href' => '/permissoes/usuarios'],
        ];
    }

    public function atribuirPermissoes()
    {
        $permissoesOptions = Permissoes::where('deleted', 0)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($permissao) {
            return ['value' => $permissao->id, 'label' => $permissao->nome];
            });

        $usuariosOptions = User::query() // Fixed: Use query builder instead of collection
            ->orderBy('id', 'desc')
            ->get(['id', 'name', 'permissao_id']) // Fetch only required columns
            ->map(function ($usuario) {
            return ['value' => $usuario->id, 'label' => $usuario->name, 'permissaoAtual' => $usuario->permissao_id];
            });

        return inertia('Permissoes/atribuir', [
            'permissoesOptions' => $permissoesOptions,
            'usuariosOptions' => $usuariosOptions,
            'sidebarNavItems' => $this->getSidebarNavItems(),
        ]);
    }

    public function atribuirStore(Request $request)
    {
        $request->validate([
            'permissoes_id' => 'required|integer|max:255',
            'usuarios_id' => 'required|integer|max:255',
        ]);

        $permissoes = Permissoes::find($request->permissoes_id);

        // Update the user's permissao_id field directly
        $usuario = User::find($request->usuarios_id);
        if ($usuario) {
            $usuario->update(['permissao_id' => $request->permissoes_id]);
        } else {
            return redirect()->route('permissoes.atribuir')->with('error', 'Usuário não encontrado.');
        }

        return redirect()->route('permissoes.atribuir')->with('success', 'Permissoes atribuído com sucesso.');
    }


    public function listUsuarios()
    {
        $usuarios = User::with('permissao:id,nome')
            ->orderBy('id', 'desc')
            ->get(['id', 'name', 'permissao_id']);

        return inertia::render('Permissoes/listUsuarios', [
            'usuarios' => $usuarios,
            'sidebarNavItems' => $this->getSidebarNavItems(),
        ]);
    }

}