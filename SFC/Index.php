<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Montacargas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            background: url('MEDIA/wallpaper.jpg') no-repeat center center fixed;
            background-size: cover;
            background-attachment: fixed;
        }
        .parallax-container {
            background: rgba(255, 255, 255, 0.8);
            padding: 30px;
            border-radius: 10px;
        }
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
        }
    </style>
</head>
<body>
    <header class="bg-dark text-white p-3">
        <div class="container">
            <h1 class="text-center">Sistema de Gestión de Montacargas</h1>
        </div>
    </header>

    <div class="login-container">
        <div class="parallax-container col-md-4 col-sm-6 col-10">
            <h3 class="text-center mb-4">Login Montacargas</h3>
            <form action="Admin/index.php" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Usuario</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Ingresar</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="bg-dark text-white p-3 mt-5">
        <div class="container">
            <p class="text-center mb-0">&copy; 2024 Sistema de Gestión de Montacargas. Todos los derechos reservados. Propiedad de Sertero. Desarrollado por Qbit-Labs.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
