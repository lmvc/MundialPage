<?php

/*
--------------------------------------------------
CONFIG
--------------------------------------------------
*/

require_once "config.php";

$resultadosManuales = [];

if (file_exists(__DIR__ . '/resultados_manuales.php')) {
    include __DIR__ . '/resultados_manuales.php';
}

if (!is_array($resultadosManuales)) {
    $resultadosManuales = [];
}

/*
--------------------------------------------------
SQLITE
--------------------------------------------------
*/

$db = new SQLite3(
    DATA_PATH . 'quiniela.db'
);

$db->busyTimeout(5000);

/*
--------------------------------------------------
API ESPN
--------------------------------------------------
*/

$fechaInicio = "20260601";
$fechaFin    = "20260730";

$url =
    "https://site.api.espn.com/apis/site/v2/sports/soccer/fifa.world/scoreboard?dates="
    .
    $fechaInicio
    .
    "-"
    .
    $fechaFin;

$ch = curl_init();

curl_setopt(
    $ch,
    CURLOPT_URL,
    $url
);

curl_setopt(
    $ch,
    CURLOPT_RETURNTRANSFER,
    true
);

curl_setopt(
    $ch,
    CURLOPT_TIMEOUT,
    15
);

curl_setopt(
    $ch,
    CURLOPT_USERAGENT,
    'Mozilla/5.0'
);

$json =
    curl_exec($ch);

if (curl_errno($ch)) {
    $datosApi = null;
} else {
    $datosApi =
        json_decode(
            $json,
            true
        );
}

/*
Si API falla, verifica si hay resultados en BD
*/
if (
    !$datosApi
    ||
    empty($datosApi['events'])
) {
    $result = $db->query(
        "SELECT COUNT(*) as c FROM resultados"
    );
    $row = $result->fetchArray(SQLITE3_ASSOC);
    
    if ($row['c'] == 0) {
        die(date('Y-m-d H:i:s')
            .
            " - No se recibieron eventos de API y BD vacía.");
    }
    
    $datosApi = null;
}

/*
--------------------------------------------------
TABLA RESULTADOS
--------------------------------------------------
*/

$db->exec(
    "
CREATE TABLE IF NOT EXISTS resultados
(
    partido INTEGER PRIMARY KEY,
    equipo1 TEXT,
    equipo2 TEXT,
    goles1 INTEGER,
    goles2 INTEGER,
    fase TEXT
)
"
);

/*
--------------------------------------------------
TRANSACCION
--------------------------------------------------
*/

$db->exec(
    "BEGIN IMMEDIATE TRANSACTION"
);

try {
    /*
    Vaciar resultados previos
    para reconstruirlos
    */

    if ($datosApi !== null) {
        $db->exec(
            "DELETE FROM resultados"
        );
    }

    $stmt =
        $db->prepare(
            "
        INSERT OR REPLACE INTO resultados
        (
            partido,
            equipo1,
            equipo2,
            goles1,
            goles2,
            fase
        )
        VALUES
        (
            :partido,
            :equipo1,
            :equipo2,
            :goles1,
            :goles2,
            :fase
        )
        "
        );

    $contador = 1;

    if ($datosApi !== null) {
        foreach (
            $datosApi['events']
            as $evento
        ) {
            $competencia =
                $evento['competitions'][0];

            $estado =
                $competencia['status']['type']['name'];

            $estadosFinalizados = [
                'STATUS_FULL_TIME',
                'STATUS_FINAL_PEN',
                'STATUS_FINAL_AET',
            ];

            if (
                !in_array(
                    $estado,
                    $estadosFinalizados
                )
            ) {
                continue;
            }

            $competitors =
                $competencia['competitors'];

            $home =
                (
                    $competitors[0]['homeAway']
                    ===
                    'home'
                )
                ?
                $competitors[0]
                :
                $competitors[1];

            $away =
                (
                    $competitors[0]['homeAway']
                    ===
                    'away'
                )
                ?
                $competitors[0]
                :
                $competitors[1];

            $stmt->bindValue(
                ':partido',
                $contador,
                SQLITE3_INTEGER
            );

            $stmt->bindValue(
                ':equipo1',
                $home['team']['displayName'],
                SQLITE3_TEXT
            );

            $stmt->bindValue(
                ':equipo2',
                $away['team']['displayName'],
                SQLITE3_TEXT
            );

            $homeScore =
                intval(
                    $home['score']
                );

            $awayScore =
                intval(
                    $away['score']
                );

            $marcadoresManuales = [
                81 => [2, 2],
                87 => [1, 1],
                99 => [1, 1],
                100 => [1, 1],
            ];

            if (
                isset(
                    $marcadoresManuales[$contador]
                )
            ) {
                $homeScore =
                    $marcadoresManuales[$contador][0];

                $awayScore =
                    $marcadoresManuales[$contador][1];
            }

            $stmt->bindValue(
                ':goles1',
                $homeScore,
                SQLITE3_INTEGER
            );

            $stmt->bindValue(
                ':goles2',
                $awayScore,
                SQLITE3_INTEGER
            );

            $stmt->bindValue(
                ':fase',
                $evento['season']['slug'],
                SQLITE3_TEXT
            );

            $stmt->execute();

            $contador++;
        }
    }

    foreach ($resultadosManuales as $manual) {
        if (!isset($manual['partido'])) {
            continue;
        }

        $stmt->bindValue(
            ':partido',
            (int) $manual['partido'],
            SQLITE3_INTEGER
        );

        $stmt->bindValue(
            ':equipo1',
            (string) ($manual['equipo1'] ?? ''),
            SQLITE3_TEXT
        );

        $stmt->bindValue(
            ':equipo2',
            (string) ($manual['equipo2'] ?? ''),
            SQLITE3_TEXT
        );

        $stmt->bindValue(
            ':goles1',
            (int) ($manual['goles1'] ?? 0),
            SQLITE3_INTEGER
        );

        $stmt->bindValue(
            ':goles2',
            (int) ($manual['goles2'] ?? 0),
            SQLITE3_INTEGER
        );

        $stmt->bindValue(
            ':fase',
            (string) ($manual['fase'] ?? ''),
            SQLITE3_TEXT
        );

        $stmt->execute();
    }

    $db->exec(
        "COMMIT"
    );

    echo
    date('Y-m-d H:i:s')
        .
        " - Resultados actualizados.";
} catch (Exception $e) {
    $db->exec(
        "ROLLBACK"
    );

    die("Error: "
        .
        $e->getMessage());
}

$db->close();

/*
--------------------------------------------------
RECALCULAR PUNTOS
--------------------------------------------------
*/

echo PHP_EOL . date('Y-m-d H:i:s')
    .
    " - Recalculando puntos...";

include_once __DIR__ . "/calcular_puntos.php";

echo PHP_EOL . date('Y-m-d H:i:s')
    .
    " - ✓ Actualización completa.";
