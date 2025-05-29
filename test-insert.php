<?php
require 'modules.php';

$data = [
    'service' => 'Test Masaje',
    'name' => 'Cliente Prueba',
    'email' => 'test@correo.com',
    'date' => '2025-05-25',
    'time' => '14:00',
    'payment' => 'yape',
    'status' => 'Pagado'
];

if (saveReservationDb($data)) {
    echo "✅ Guardado en la base de datos correctamente";
} else {
    echo "❌ Falló al guardar en la base de datos";
}
