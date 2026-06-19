
<?php

/*
--------------------------------------------------
CONFIG
--------------------------------------------------
*/

require_once "config.php";

$archivoResultados = DATA_PATH . 'partidos.csv';

/*
--------------------------------------------------
API ESPN (Rango Completo de Fechas del Mundial)
--------------------------------------------------
*/

// Año actual 2026. Definimos el rango del torneo (ej: del 1 de junio al 30 de julio de 2026)
$fechaInicio = "20260601";
$fechaFin    = "20260730";

// Añadimos el parámetro ?dates=AAAAMMDD-AAAAMMDD para forzar a ESPN a traer todo el histórico
$url = "https://site.api.espn.com/apis/site/v2/sports/soccer/fifa.world/scoreboard?dates=" . $fechaInicio . "-" . $fechaFin;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
$json = curl_exec($ch);

if (curl_errno($ch)) {
    die("Error al conectar con la API de ESPN: " . curl_error($ch));
}

$datosApi = json_decode($json, true);

if (!$datosApi || empty($datosApi['events'])) {
    die(date('Y-m-d H:i:s') . " - No se recibieron eventos para ese rango de fechas.\n");
}

/*
--------------------------------------------------
CSV PARTIDOS
--------------------------------------------------
*/

$fpOut = fopen($archivoResultados, "w");

fputcsv($fpOut, [
    'partido',
    'equipo1',
    'equipo2',
    'fecha',
    'fase',
    'estado'
], ",", '"', "\\");

/*
--------------------------------------------------
PROCESAMIENTO DE JUEGOS TERMINADOS
--------------------------------------------------
*/

$contadorPartidos = 1;

foreach ($datosApi['events'] as $evento) {
    $competencia = $evento['competitions'][0];

    // Filtro estricto: Solo partidos terminados
    // if ($competencia['status']['type']['name'] !== "STATUS_FULL_TIME") {
    //     continue;
    // }

    // Identificar Local y Visitante
    $competitors = $competencia['competitors'];
    $homeTeam = ($competitors[0]['homeAway'] === 'home') ? $competitors[0] : $competitors[1];
    $awayTeam = ($competitors[0]['homeAway'] === 'away') ? $competitors[0] : $competitors[1];

    // $fase = "Grupos";
    // if (!empty($competencia['status']['type']['detail'])) {
    //     $fase = $competencia['status']['type']['detail'];
    // }

    // Fecha del partido
    $fecha_raw = htmlspecialchars($evento['date']);
    $fecha = date("d-m-Y H:i", strtotime($fecha_raw)); // Formateamos la fecha a algo legible

    fputcsv(
        $fpOut,
        [
            $contadorPartidos,
            $homeTeam['team']['displayName'],
            $awayTeam['team']['displayName'],
            $fecha,
            // $evento['time'],
            $evento['season']['slug'],
            $competencia['status']['type']['name']
            // $fase
        ],
        ",",
        '"',
        "\\"
    );

    $contadorPartidos++;
}

fclose($fpOut);

echo date('Y-m-d H:i:s') . " - ¡Éxito! Archivo partidos.csv actualizado con " . ($contadorPartidos - 1) . " partidos finalizados.\n";