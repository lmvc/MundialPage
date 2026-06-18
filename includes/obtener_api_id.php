<?php

// Endpoint público y oficial de ESPN para el Mundial (Trae datos reales actuales e históricos)
$url = "https://site.api.espn.com/apis/site/v2/sports/soccer/fifa.world/scoreboard";

// 1. Inicializar cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'); // Simulamos un navegador por seguridad

$response = curl_exec($ch);

// Manejo de errores de conexión
if (curl_errno($ch)) {
    die("Error al conectar con el servidor de ESPN: " . curl_error($ch));
}

// 2. Decodificar el JSON
$data = json_decode($response, true);

if ($data === null) {
    echo "<h3>Error: No se pudo decodificar el JSON de ESPN.</h3>";
    echo "<p>Respuesta cruda del servidor (primeros 200 caracteres):</p>";
    echo "<pre>" . htmlspecialchars(substr($response, 0, 200)) . "</pre>";
    exit;
}

// 3. Procesar y mostrar los partidos
echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Partidos del Mundial - ESPN API</title>
    <style>
        body { font-family: sans-serif; background: #f4f6f9; color: #333; margin: 20px; }
        .contenedor { background: #fff; border: 1px solid #ddd; padding: 15px; border-radius: 6px; max-width: 800px; margin: 0 auto; }
        h1 { color: #cc0000; border-bottom: 2px solid #cc0000; padding-bottom: 10px; font-size: 24px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #f8f9fa; font-weight: bold; }
        .score { font-weight: bold; color: #cc0000; text-align: center; background: #fff3f3; border-radius: 4px; padding: 4px 8px; }
        .estado { font-size: 11px; color: #666; text-transform: uppercase; }
    </style>
</head>
<body>

<div class='contenedor'>";

// ESPN organiza el nombre de la liga en el nodo leagues
$league_name = isset($data['leagues'][0]['name']) ? $data['leagues'][0]['name'] : "Copa Mundial de la FIFA";
echo "<h1>" . htmlspecialchars($league_name) . " - Calendario y Resultados</h1>";

if (!empty($data['events'])) {
    echo "<table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Local</th>
                    <th style='text-align:center;'>Resultado</th>
                    <th>Visitante</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>";
            
    foreach ($data['events'] as $event) {
        // Fecha del partido
        $fecha_raw = htmlspecialchars($event['date']);
        $fecha = date("d-m-Y H:i", strtotime($fecha_raw)); // Formateamos la fecha a algo legible
        
        // Competidores (Equipos)
        $competitors = $event['competitions'][0]['competitors'];
        
        // ESPN suele poner al Home (local) y Away (visitante) indexados. 
        // Buscamos cuál es cuál dinámicamente.
        $homeTeam = $competitors[0]['homeAway'] == 'home' ? $competitors[0] : $competitors[1];
        $awayTeam = $competitors[0]['homeAway'] == 'away' ? $competitors[0] : $competitors[1];
        
        $team1 = htmlspecialchars($homeTeam['team']['displayName']);
        $team2 = htmlspecialchars($awayTeam['team']['displayName']);
        
        // Marcadores y Estado del partido
        $score1 = htmlspecialchars($homeTeam['score']);
        $score2 = htmlspecialchars($awayTeam['score']);
        $status = htmlspecialchars($event['competitions'][0]['status']['type']['description']);
        
        // Si el juego no ha empezado (Status: "Scheduled"), mostramos VS
        if ($event['competitions'][0]['status']['type']['name'] === "STATUS_SCHEDULED") {
            $resultado = "vs";
        } else {
            $resultado = "$score1 - $score2";
        }

        echo "<tr>
                <td>{$fecha} h</td>
                <td><strong>{$team1}</strong></td>
                <td class='score'>{$resultado}</td>
                <td><strong>{$team2}</strong></td>
                <td class='estado'>{$status}</td>
              </tr>";
    }
    
    echo "</tbody></table>";
} else {
    echo "<p>No hay partidos disponibles en este momento en el feed de ESPN.</p>";
}

echo "</div>
</body>
</html>";