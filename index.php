<!-- index.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cusco Spa Massages</title>

    <!-- Estilos CSS internos para el diseño de la página -->
    <style>
        /* Estilos generales del cuerpo */
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f7f2eb;
            color: #4a3c31;
        }

        /* Encabezado principal */
        header {
            background-color: #3e2f23;
            padding: 30px 0;
            text-align: center;
            color: #f7f2eb;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        header h1 {
            margin: 0;
            font-size: 2.8em;
            letter-spacing: 1px;
        }

        /* Diseño en cuadrícula para mostrar las tarjetas */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
            padding: 40px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Estilo de las tarjetas de servicio */
        .card {
            background: #fffaf5;
            border-radius: 14px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            overflow: hidden;
            text-align: center;
            padding: 25px 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #e0d6ca;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 28px rgba(0,0,0,0.12);
        }

        .card h3 {
            margin-top: 0;
            font-size: 1.3em;
            color: #3e2f23;
        }

        /* Botón de reserva dentro de la tarjeta */
        .card a {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background-color: #8b5e3c;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .card a:hover {
            background-color: #6e4629;
        }

        /* Pie de página con información de contacto */
        footer {
            text-align: center;
            padding: 25px 20px;
            background: #3e2f23;
            color: #d6c9bc;
            font-size: 0.95em;
        }

        /* Adaptación para pantallas pequeñas */
        @media (max-width: 600px) {
            header h1 {
                font-size: 2em;
            }
        }
    </style>
</head>
<body>
    <!-- Encabezado con el nombre del spa -->
    <header>
        <h1>Cusco Spa Massages</h1>
    </header>

    <!-- Sección principal con servicios en formato de tarjetas -->
    <div class="grid">
        <?php
        // Lista de servicios ofrecidos
        $services = [
            'Masaje Relajante',
            'Masaje Neo-Sueco',
            'Masaje Descontracturante',
            'Masaje Inca con Piedras Calientes',
            'Masaje Neuromuscular de Espalda',
            'Masaje para Pies Cansados',
            'Masaje a Cuatro Manos'
        ];

        // Recorremos los servicios y generamos una tarjeta para cada uno
        foreach ($services as $service) {
            echo '<div class="card">';
            echo "<h3>$service</h3>"; // Título del servicio
            echo '<a href="reservar.php?service=' . urlencode($service) . '">Reservar</a>'; // Enlace a la página de reserva
            echo '</div>';
        }
        ?>
    </div>

    <!-- Pie de página con información de contacto -->
    <footer>
        📍 Calle Spa #123, Cusco - Perú | ☎️ +51 987 654 321 | ✉️ contacto@cuscospamassage.com
    </footer>
</body>
</html>
