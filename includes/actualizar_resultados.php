<?php

/*
--------------------------------------------------
CONFIG
--------------------------------------------------
*/

require_once "config.php";

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
    die("Error ESPN: "
        .
        curl_error($ch));
}

// curl_close($ch);

$datosApi =
    json_decode(
        $json,
        true
    );

if (
    !$datosApi
    ||
    empty($datosApi['events'])
) {
    die(date('Y-m-d H:i:s')
        .
        " - No se recibieron eventos.");
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

    $db->exec(
        "DELETE FROM resultados"
    );

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

    foreach (
        $datosApi['events']
        as $evento
    ) {
        $competencia =
            $evento['competitions'][0];

        /*
        Solo terminados
        */

        $estado =
            $competencia['status']['type']['name'];

        $estadosFinalizados = [

            'STATUS_FULL_TIME',
            'STATUS_FINAL_PEN',

        ];

        if (
            !in_array(
                $estado,
                $estadosFinalizados
            )
        ) {
            continue;
        }
        // if(
        //     $competencia
        //     ['status']
        //     ['type']
        //     ['name']
        //     !==
        //     "STATUS_FULL_TIME"
        // )
        // {
        //     continue;
        // }

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

        $stmt->bindValue(
            ':goles1',
            intval(
                $home['score']
            ),
            SQLITE3_INTEGER
        );

        $stmt->bindValue(
            ':goles2',
            intval(
                $away['score']
            ),
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

    $db->exec(
        "COMMIT"
    );

    echo
    date('Y-m-d H:i:s')
        .
        " - Resultados actualizados: "
        .
        ($contador - 1)
        .
        " partidos.";
} catch (Exception $e) {
    $db->exec(
        "ROLLBACK"
    );

    die("Error: "
        .
        $e->getMessage());
}

$db->close();
