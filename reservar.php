<?php 
require 'modules.php';

date_default_timezone_set('America/Lima'); // Ajusta a tu zona horaria

$selectedService = $_GET['service'] ?? '';
$success = $error = '';

$validPaymentMethods = ['yape', 'bcp', 'paypal', 'despues'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validar que exista el método de pago y sea válido
    $payment_method = $_POST['payment_method'] ?? '';
    if (!in_array($payment_method, $validPaymentMethods)) {
        $error = 'Seleccione un método de pago válido.';
    } else {
        // Ajustar formato de hora para incluir segundos (HH:MM:SS)
        $time = $_POST['time'] ?? '';
        if (strlen($time) === 5) { // si es HH:MM
            $time .= ':00';
        }

        $data = [
            'service' => $_POST['service'] ?? '',
            'name'    => $_POST['name'] ?? '',
            'email'   => $_POST['email'] ?? '',
            'date'    => $_POST['date'] ?? '',
            'time'    => $time,
            'payment' => $payment_method,
            'status'  => ($payment_method === 'despues') ? 'Pago en espera' : 'Pagado'
        ];

        // Validar hora entre 09 y 21
        $hour = intval(substr($data['time'], 0, 2));
        if ($hour < 9 || $hour >= 21) {
            $error = 'Seleccione una hora entre 09:00 y 21:00.';
        } else {
            // Guardar en DB con manejo de errores
            try {
                $dbSaved = saveReservationDbWithErrors($data);
            } catch (Exception $e) {
                $error = "Error en la base de datos: " . $e->getMessage();
                $dbSaved = false;
            }

            $sheetSaved = saveReservationSheet($data);

            if ($dbSaved && $sheetSaved) {
                if ($data['payment'] !== 'despues') {
                    $paymentNames = [
                        'yape' => 'Yape',
                        'bcp' => 'BCP',
                        'paypal' => 'Paypal'
                    ];
                    $payName = $paymentNames[$data['payment']] ?? ucfirst($data['payment']);
                    $success = "Reserva confirmada y pago recibido por {$payName}.";
                } else {
                    $success = 'Reserva realizada. El pago está en espera.';
                }
            } elseif (!$dbSaved && $sheetSaved) {
                $error = 'Error al guardar en la base de datos, pero la reserva se guardó en la hoja.';
            } elseif ($dbSaved && !$sheetSaved) {
                $error = 'Error al guardar en la hoja, pero la reserva se guardó en la base de datos.';
            } else {
                if (!$error) {
                    $error = 'Error al guardar la reserva en la base de datos y en la hoja.';
                }
            }
        }
    }
}

/**
 * Función para guardar en la base de datos mostrando errores claros
 */
function saveReservationDbWithErrors($data) {
    $pdo = getDbConnection();

    $stmt = $pdo->prepare(
        'INSERT INTO reservations 
        (service, customer_name, customer_email, reservation_date, reservation_time, payment_method, status) 
        VALUES (:service, :name, :email, :date, :time, :payment, :status)'
    );

    $executed = $stmt->execute([
        ':service' => $data['service'],
        ':name'    => $data['name'],
        ':email'   => $data['email'],
        ':date'    => $data['date'],
        ':time'    => $data['time'],
        ':payment' => $data['payment'],
        ':status'  => $data['status']
    ]);

    if (!$executed) {
        $error = $stmt->errorInfo();
        throw new Exception($error[2]);
    }

    return true;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Reservar Masaje</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f5f7fa;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 460px;
        }
        h1 {
            text-align: center;
            color: #3e2f23;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            color: #4a3c31;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        .message {
            text-align: center;
            margin-top: 10px;
            font-weight: bold;
        }
        .error { color: #dc3545; }
        .success { color: #28a745; }
        .payment-buttons {
            margin-top: 15px;
        }
        .payment-buttons button {
            width: 100%;
            margin-bottom: 10px;
            background-color: #8b5e3c;
            border: none;
            padding: 12px;
            color: white;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
        }
        .payment-buttons button:hover {
            background-color: #704b2d;
        }
        .pay-icons {
            text-align: center;
            margin-bottom: 15px;
        }
        .pay-icons img {
            height: 32px;
            margin: 0 8px;
        }
    </style>
</head>
<body>
<div class="form-container">
    <h1>Reserva tu Masaje</h1>

    <?php if (!empty($error)): ?>
        <p class="message error"><?= htmlspecialchars($error) ?></p>
    <?php elseif (!empty($success)): ?>
        <p class="message success"><?= htmlspecialchars($success) ?></p>
        <div style="text-align: center; margin-top: 15px;">
            <a href="index.php" style="display: inline-block; margin-top: 10px; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 6px;" onmouseover="this.style.backgroundColor='#0056b3'" onmouseout="this.style.backgroundColor='#007bff'">
                Volver al inicio
            </a>
        </div>
    <?php endif; ?>

    <?php if (empty($success)): ?>
    <form method="POST" id="reservation-form" onsubmit="return validatePayment();">
        <label>Servicio:
            <select name="service" required>
                <?php
                $services = [
                    'Masaje Relajante',
                    'Masaje Neo-Sueco',
                    'Masaje Descontracturante',
                    'Masaje Inca con Piedras Calientes',
                    'Masaje Neuromuscular de Espalda',
                    'Masaje para Pies Cansados',
                    'Masaje a Cuatro Manos'
                ];
                foreach ($services as $s) {
                    $selected = ($s === $selectedService) ? 'selected' : '';
                    echo "<option value=\"" . htmlspecialchars($s) . "\" $selected>" . htmlspecialchars($s) . "</option>";
                }
                ?>
            </select>
        </label>

        <label>Nombre:
            <input type="text" name="name" required>
        </label>

        <label>Email:
            <input type="email" name="email" required>
        </label>

        <label>Fecha:
            <input type="date" name="date" required>
        </label>

        <label>Hora:
            <input type="time" name="time" min="09:00" max="21:00" required>
        </label>

        <input type="hidden" name="payment_method" id="payment_method" value="">

        <div class="pay-icons">
            <img src="icons/yape.png" alt="Yape">
            <img src="icons/bcp.png" alt="BCP">
            <img src="icons/paypal.png" alt="Paypal">
        </div>

        <div class="payment-buttons">
            <button type="button" onclick="submitWithPayment('yape')">Pagar ahora con Yape</button>
            <button type="button" onclick="submitWithPayment('bcp')">Pagar ahora con BCP</button>
            <button type="button" onclick="submitWithPayment('paypal')">Pagar ahora con Paypal</button>
            <button type="button" onclick="submitWithPayment('despues')">Pagar después</button>
        </div>
    </form>
    <?php endif; ?>
</div>

<script>
    function submitWithPayment(method) {
        document.getElementById('payment_method').value = method;
        document.getElementById('reservation-form').submit();
    }

    function validatePayment() {
        const payment = document.getElementById('payment_method').value;
        if (!payment) {
            alert('Por favor seleccione un método de pago.');
            return false;
        }
        return true;
    }
</script>
</body>
</html>
