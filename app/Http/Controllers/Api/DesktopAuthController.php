<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DesktopAuthController extends Controller
{
    /**
     * Autentica o usuário e retorna o Token Sanctum
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        // O aplicativo desktop requer nível Admin. Verificamos 'role' (se aplicável)
        // No TopoGest os usuários são admins ou clientes? Vamos assumir que apenas admins/funcionários logam aqui.
        // Se existe uma flag 'role' => 'admin', podemos usar. No banco atual 'role' => 'admin' ou 'funcionario'?
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => 'As credenciais fornecidas estão incorretas.'
            ], 401);
        }

        // Deleta tokens antigos do desktop para esse usuário, para não acumular lixo
        $user->tokens()->where('name', 'desktop-app')->delete();

        // Cria o novo token
        $token = $user->createToken('desktop-app')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Autenticado com sucesso no TopoGest Desktop.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $token
        ]);
    }
}
