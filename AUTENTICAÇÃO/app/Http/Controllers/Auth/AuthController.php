<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Controller: AuthController
 * 
 * Gerencia a autenticação via Active Directory.
 */
class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
        $this->middleware('guest')->except('logout');
    }

    /**
     * Exibe o formulário de login
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Processa o login
     */
    public function login(LoginRequest $request)
    {
        $militar = $this->authService->authenticate(
            $request->input('login'),
            $request->input('password')
        );

        if (!$militar) {
            return back()
                ->withErrors(['login' => 'Credenciais inválidas ou usuário não cadastrado no sistema.'])
                ->withInput($request->only('login'));
        }

        // Faz login do militar
        Auth::login($militar, $request->boolean('remember'));

        $request->session()->regenerate();

        Log::info('Login realizado', [
            'militar_id' => $militar->id,
            'perfil' => $militar->perfil,
        ]);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Processa o logout
     */
    public function logout(Request $request)
    {
        $militarId = Auth::id();

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('Logout realizado', ['militar_id' => $militarId]);

        return redirect()->route('login');
    }
}


