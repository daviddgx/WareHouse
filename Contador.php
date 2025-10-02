<?php
function calculateTimeDifference($targetDateTime) {
    $currentDate = new DateTime();
    $targetDateTime = new DateTime($targetDateTime);
    $interval = $currentDate->diff($targetDateTime);

    return [
        'days' => $interval->d,
        'hours' => $interval->h,
        'minutes' => $interval->i,
        'seconds' => $interval->s,
        'isPast' => $currentDate > $targetDateTime
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['targetDateTime'])) {
    $targetDateTime = $_POST['targetDateTime'];
    $timeDifference = calculateTimeDifference($targetDateTime);
    echo json_encode($timeDifference);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contador en Reversa</title>
    <style>
        body {
            font-family: 'Roboto Mono', monospace;
            text-align: center;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #ffb347, #ffcc33, #ff9966);
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        h1 {
            font-size: 3em;
            margin-bottom: 20px;
            color: #fff;
        }
        .container {
            background: rgba(255, 255, 255, 0.2);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 400px;
        }
        input[type="date"],
        input[type="time"] {
            padding: 10px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            margin-bottom: 10px;
            width: 100%;
        }
        button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #ff6f61;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background-color: #e65c50;
        }
        .countdown {
            font-size: 4em;
            margin-top: 20px;
            color: #fff;
            font-weight: bold;
            font-family: 'Roboto Mono', monospace;
            letter-spacing: 2px;
        }
        .countdown span {
            display: inline-block;
            min-width: 80px;
        }
        @media (max-width: 600px) {
            h1 {
                font-size: 2.5em;
            }
            .countdown {
                font-size: 2.5em;
            }
        }
    </style>
</head>
<body>
    <h1>Contador en Reversa</h1>
    <div class="container">
        <input type="date" id="targetDate">
        <input type="time" id="targetTime">
        <button onclick="startCountdown()">Calcular</button>
        <div class="countdown" id="countdownDisplay">Selecciona una fecha y hora, luego presiona Calcular</div>
    </div>
    <script>
        function startCountdown() {
            const targetDate = document.getElementById('targetDate').value;
            const targetTime = document.getElementById('targetTime').value;

            if (!targetDate || !targetTime) {
                alert('Por favor selecciona una fecha y una hora.');
                return;
            }

            const targetDateTime = `${targetDate}T${targetTime}`;

            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `targetDateTime=${encodeURIComponent(targetDateTime)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.isPast) {
                    document.getElementById('countdownDisplay').innerText = 'La fecha y hora seleccionadas ya pasaron.';
                } else {
                    updateCountdown(data);
                    startTimer(targetDateTime);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function updateCountdown(data) {
            document.getElementById('countdownDisplay').innerHTML = `
                <span>${data.days}D</span>
                <span>${String(data.hours).padStart(2, '0')}H</span>
                <span>${String(data.minutes).padStart(2, '0')}M</span>
                <span>${String(data.seconds).padStart(2, '0')}S</span>
            `;
        }

        function startTimer(targetDateTime) {
            const interval = setInterval(() => {
                fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `targetDateTime=${encodeURIComponent(targetDateTime)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.isPast) {
                        clearInterval(interval);
                        document.getElementById('countdownDisplay').innerText = '¡La fecha y hora han llegado!';
                    } else {
                        updateCountdown(data);
                    }
                })
                .catch(error => console.error('Error:', error));
            }, 1000);
        }
    </script>
</body>
</html>