<?php
// modules.php

require_once __DIR__ . '/vendor/autoload.php';

use Google\Client as Google_Client;
use Google\Service\Sheets as Google_Service_Sheets;
use Google\Service\Sheets\ValueRange as Google_Service_Sheets_ValueRange;

// 1) Conexión a la base de datos
function getDbConnection() {
    $host = 'localhost';
    $db   = 'cuscospa_db';
    $user = 'root';
    $pass = '';
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    try {
        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    } catch (PDOException $e) {
        die('Error BD: ' . $e->getMessage());
    }
}

// 2) Guardar reserva en MySQL
function saveReservationDb($data) {
    try {
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
            error_log("ERROR SQL: " . $error[2]);
            echo "ERROR SQL: " . $error[2];
        }

        return $executed;
    } catch (PDOException $e) {
        error_log("PDO ERROR: " . $e->getMessage());
        echo "PDO ERROR: " . $e->getMessage();
        return false;
    }
}


// 3) Integración con Google Sheets
function getSheetsService() {
    $client = new Google_Client();
    $client->setApplicationName('CuscoSpaReservations');
    $client->setScopes([Google_Service_Sheets::SPREADSHEETS]);
    $client->setAuthConfig(__DIR__ . '/credentials.json'); // Ruta correcta a tus credenciales
    $client->setAccessType('offline');
    return new Google_Service_Sheets($client);
}

function saveReservationSheet($data) {
    $service = getSheetsService();
    $spreadsheetId = '19pjH8crSXwojnZpOqRK2XTqtPv0M2EOcpV5D2NnXhyw';

    $range = 'hoja1!A:H'; // Incluimos más columnas si se requiere

    $values = [[
        date('Y-m-d H:i:s'),
        $data['service'],
        $data['name'],
        $data['email'],
        $data['date'],
        $data['time'],
        $data['payment'],
        $data['status']
    ]];

    $body = new Google_Service_Sheets_ValueRange(['values' => $values]);
    $params = ['valueInputOption' => 'RAW'];

    $result = $service->spreadsheets_values->append($spreadsheetId, $range, $body, $params);

    return $result;
}
