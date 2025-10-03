<?php
session_start();
$currentDate = date('Y-m-d');

if (!isset($_SESSION['Usuario'], $_SESSION['UsuarioFecha']) || $_SESSION['Usuario'] === '' || $_SESSION['UsuarioFecha'] !== $currentDate) {
    header('Location: ../Innet/505.html');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Juego de Snake</title>
    <style>
        #game-board {
            border: 1px solid #000;
            width: 400px;
            height: 400px;
            position: relative;
        }

        .dot {
            width: 20px;
            height: 20px;
            position: absolute;
        }

        .snake-dot {
            background-color: #000;
        }

        .food-dot {
            background-color: #f00;
        }
    </style>
</head>
<body>
<h1>Juego de Snake</h1>
<div id="game-board"></div>

<script>
    // Tamaño del tablero
    const boardSize = 20;

    // Direcciones
    const direction = {
        UP: "UP",
        DOWN: "DOWN",
        LEFT: "LEFT",
        RIGHT: "RIGHT"
    };

    // Estado del juego
    let snake = [{ x: 10, y: 10 }];
    let food = {};
    let currentDirection = direction.RIGHT;
    let interval;

    // Inicializar el juego
    function init() {
        createFood();
        interval = setInterval(gameLoop, 100);
    }

    // Generar comida en una posición aleatoria
    function createFood() {
        food = {
            x: Math.floor(Math.random() * boardSize),
            y: Math.floor(Math.random() * boardSize)
        };
    }

    // Dibujar la serpiente y la comida
    function draw() {
        const gameBoard = document.getElementById("game-board");
        gameBoard.innerHTML = "";

        // Dibujar la serpiente
        snake.forEach(dot => {
            const snakeDot = createDot(dot.x, dot.y, "snake-dot");
            gameBoard.appendChild(snakeDot);
        });

        // Dibujar la comida
        const foodDot = createDot(food.x, food.y, "food-dot");
        gameBoard.appendChild(foodDot);
    }

    // Crear un punto en el tablero
    function createDot(x, y, className) {
        const dot = document.createElement("div");
        dot.className = "dot " + className;
        dot.style.left = x * 20 + "px";
        dot.style.top = y * 20 + "px";
        return dot;
    }

    // Bucle principal del juego
    function gameLoop() {
        moveSnake();
        if (checkCollision()) {
            clearInterval(interval);
            alert("¡Juego terminado!");
            snake = [{ x: 10, y: 10 }];
            currentDirection = direction.RIGHT;
            init();
        } else if (checkFoodCollision()) {
            snake.push({});
            createFood();
        }
        draw();
    }

    // Mover la serpiente en función de la dirección actual
    function moveSnake() {
        const head = Object.assign({}, snake[0]);

        switch (currentDirection) {
            case direction.UP:
                head.y--;
                break;
            case direction.DOWN:
                head.y++;
                break;
            case direction.LEFT:
                head.x--;
                break;
            case direction.RIGHT:
                head.x++;
                break;
        }

        snake.unshift(head);
        snake.pop();
    }

    // Verificar colisión con las paredes o con la serpiente misma
    function checkCollision() {
        const head = snake[0];

        return (
            head.x < 0 ||
            head.x >= boardSize ||
            head.y < 0 ||
            head.y >= boardSize ||
            snake.slice(1).some(dot => dot.x === head.x && dot.y === head.y)
        );
    }

    // Verificar colisión con la comida
    function checkFoodCollision() {
        const head = snake[0];
        return head.x === food.x && head.y === food.y;
    }

    // Capturar las teclas de dirección para cambiar la dirección de la serpiente
    document.addEventListener("keydown", event => {
        switch (event.key) {
            case "ArrowUp":
                if (currentDirection !== direction.DOWN)
                    currentDirection = direction.UP;
                break;
            case "ArrowDown":
                if (currentDirection !== direction.UP)
                    currentDirection = direction.DOWN;
                break;
            case "ArrowLeft":
                if (currentDirection !== direction.RIGHT)
                    currentDirection = direction.LEFT;
                break;
            case "ArrowRight":
                if (currentDirection !== direction.LEFT)
                    currentDirection = direction.RIGHT;
                break;
        }
    });

    // Iniciar el juego
    init();
</script>
    <script>
        // Establece el tiempo de inactividad en milisegundos (5 minutos = 300,000 milisegundos)
        const tiempoInactividad = 300000;

        // Función que redirige al usuario a la página específica
        function redirigir() {
            window.location.href = 'index.php'; // Reemplaza 'pagina-destino.html' con la URL de la página a la que deseas redirigir al usuario.
        }

        let temporizadorInactividad;

        // Función que reinicia el temporizador de inactividad
        function reiniciarTemporizador() {
            clearTimeout(temporizadorInactividad);
            temporizadorInactividad = setTimeout(redirigir, tiempoInactividad);
        }

        // Agrega eventos para rastrear la actividad del usuario
        document.addEventListener('mousemove', reiniciarTemporizador);
        document.addEventListener('keypress', reiniciarTemporizador);

        // Inicia el temporizador de inactividad al cargar la página
        reiniciarTemporizador();
    </script> 
</body>
</html>