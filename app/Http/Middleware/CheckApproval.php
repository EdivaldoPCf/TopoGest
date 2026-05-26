<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckApproval
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Libera se for o email mestre ou um admin já aprovado
        if ($user && ($user->email === 'edifilho25022004@gmail.com' || ($user->role === 'admin' && $user->approved == 1))) {
            return $next($request);
        }

        // Se não tiver permissão, redireciona de volta para a dashboard dele
        return redirect()->route('dashboard');
    }
}