<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Ferramentaria GLOG</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            background-color: #2d2d2d;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            border: 1px solid #3a3a3a;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header i {
            font-size: 48px;
            color: #0d6efd;
            margin-bottom: 15px;
        }

        .login-header h2 {
            color: #ffffff;
            font-weight: 600;
        }

        .login-header p {
            color: #b0b0b0;
            margin: 0;
        }

        .form-control {
            background-color: #3a3a3a;
            border: 1px solid #555;
            color: #ffffff;
            padding: 12px;
        }

        .form-control:focus {
            background-color: #3a3a3a;
            border-color: #0d6efd;
            color: #ffffff;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

        .form-label {
            color: #b0b0b0;
            font-weight: 500;
        }

        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
            padding: 12px;
            font-weight: 600;
            width: 100%;
        }

        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }

        .alert {
            border: none;
        }

        .invalid-feedback {
            color: #f8d7da;
            display: block;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="bi bi-shield-lock"></i>
            <h2>Ferramentaria GLOG</h2>
            <p>Sistema de Controle de Ferramentas</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i>
                @foreach($errors->all() as $error)
                    {{ $error }}
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label for="login" class="form-label">
                    <i class="bi bi-person"></i> Login (BANT.INTRAER)
                </label>
                <input 
                    type="text" 
                    class="form-control @error('login') is-invalid @enderror" 
                    id="login" 
                    name="login" 
                    value="{{ old('login') }}" 
                    required 
                    autofocus
                    placeholder="Digite seu login (nome de guerra e iniciais)"
                >
                @error('login')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">
                    <i class="bi bi-lock"></i> Senha
                </label>
                <input 
                    type="password" 
                    class="form-control @error('password') is-invalid @enderror" 
                    id="password" 
                    name="password" 
                    required
                    placeholder="Digite sua senha AD"
                >
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember" style="color: #b0b0b0;">
                    Lembrar-me
                </label>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-box-arrow-in-right"></i> Entrar
            </button>
        </form>

        <div class="text-center mt-4">
            <small style="color: #b0b0b0;">
                Base Aérea de Natal - GLOG<br>
                Autenticação via Active Directory<br>
                Criado por Sgt Vanoni | ETIC
            </small>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


