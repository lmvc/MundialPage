<?php

/*
--------------------------------------------------
TIMEZONE
--------------------------------------------------
*/

date_default_timezone_set(
    'America/Mexico_City'
);

/*
--------------------------------------------------
ESPN API
--------------------------------------------------
*/
$fechaInicio = "20260601";
$fechaFin    = "20260730";

// $url =
//     "https://site.api.espn.com/apis/site/v2/sports/soccer/fifa.world/scoreboard";

$url =
    "https://site.api.espn.com/apis/site/v2/sports/soccer/fifa.world/scoreboard?dates="
    .
    $fechaInicio
    .
    "-"
    .
    $fechaFin;

/*
--------------------------------------------------
CURL
--------------------------------------------------
*/

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

$response =
    curl_exec($ch);

/*
--------------------------------------------------
ERROR
--------------------------------------------------
*/

if (curl_errno($ch)) {
    die("Error ESPN API: "
        .
        curl_error($ch));
}

/*
--------------------------------------------------
JSON
--------------------------------------------------
*/

$data =
    json_decode(
        $response,
        true
    );

if ($data === null) {
    die("No se pudo decodificar el JSON.");
}

/*
--------------------------------------------------
LEAGUE
--------------------------------------------------
*/

$league =
    $data['leagues'][0]['name']
    ??
    'FIFA World Cup';

/*
--------------------------------------------------
DATES
--------------------------------------------------
*/

$hoy =
    date('Y-m-d');

$ayer =
    date(
        'Y-m-d',
        strtotime('-1 day')
    );

/*
--------------------------------------------------
MATCH GROUPS
--------------------------------------------------
*/

$partidosHoy = [];

$partidosAyer = [];

/*
--------------------------------------------------
PROCESS EVENTS
--------------------------------------------------
*/

if (!empty($data['events'])) {
    foreach ($data['events'] as $event) {
        $competition =
            $event['competitions'][0];

        $competitors =
            $competition['competitors'];

        /*
        ------------------------------------------
        HOME / AWAY
        ------------------------------------------
        */

        $home =
            $competitors[0]['homeAway']
            ===
            'home'
            ?
            $competitors[0]
            :
            $competitors[1];

        $away =
            $competitors[0]['homeAway']
            ===
            'away'
            ?
            $competitors[0]
            :
            $competitors[1];

        /*
        ------------------------------------------
        DATE
        ------------------------------------------
        */

        $fechaUTC =
            new DateTime(
                $event['date'],
                new DateTimeZone('UTC')
            );

        $fechaUTC->setTimezone(
            new DateTimeZone(
                'America/Mexico_City'
            )
        );

        $fechaPartido =
            $fechaUTC->format('Y-m-d');

        /*
        ------------------------------------------
        FILTER ONLY TODAY / YESTERDAY
        ------------------------------------------
        */

        if (
            $fechaPartido !== $hoy
            &&
            $fechaPartido !== $ayer
        ) {
            continue;
        }

        /*
        ------------------------------------------
        STATUS
        ------------------------------------------
        */

        $statusCode =
            $competition['status']['type']['name'];

        $statusText =
            $competition['status']['type']['description'];

        $detail =
            $competition['status']['type']['detail']
            ??
            '';

        echo
            "Partido: "
            .
            $home['team']['displayName']
            .
            " vs "
            .
            $away['team']['displayName']
            .
            " - Estado: "
            .
            $statusText
            .
            " - Detalle: "
            .
            $detail
            .
            " - Code: "
            .
            $statusCode
            .
            "\n";

        /*
        ------------------------------------------
        RESULT
        ------------------------------------------
        */

        if (
            $statusCode
            ===
            "STATUS_SCHEDULED"
        ) {
            $resultado =
                $fechaUTC->format('H:i')
                . " h";
        } else {
            $resultado =
                $home['score']
                .
                " - "
                .
                $away['score'];
        }

        /*
        ------------------------------------------
        CARD
        ------------------------------------------
        */

        $card = [

            'homeName' =>
            $home['team']['displayName'],

            'awayName' =>
            $away['team']['displayName'],

            'homeLogo' =>
            $home['team']['logo'],

            'awayLogo' =>
            $away['team']['logo'],

            'resultado' =>
            $resultado,

            'statusCode' =>
            $statusCode,

            'statusText' =>
            $statusText,

            'detail' =>
            $detail,

            'venue' =>
            $competition['venue']['fullName']
                ??
                'Sin estadio',

            'phase' =>
            strtoupper(
                $event['season']['slug']
                    ??
                    'world-cup'
            )

        ];

        /*
        ------------------------------------------
        GROUPS
        ------------------------------------------
        */

        if (
            $fechaPartido === $hoy
        ) {
            $partidosHoy[] = $card;
        } else {
            $partidosAyer[] = $card;
        }
    }
}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1">

    <title>

        🏆 <?= htmlspecialchars($league) ?>

    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <style>
        body {
            background:
                #f4f6f9;

            font-family:
                Arial,
                sans-serif;
        }

        .main-container {
            max-width:
                1200px;

            margin:
                auto;

            padding:
                30px 15px;
        }

        .section-title {
            margin:
                40px 0 20px;

            font-weight:
                bold;

            color:
                #111827;
        }

        .match-card {
            border:
                none;

            border-radius:
                18px;

            overflow:
                hidden;

            box-shadow:
                0 4px 14px rgba(0, 0, 0, .08);

            transition:
                transform .2s ease;
        }

        .match-card:hover {
            transform:
                translateY(-3px);
        }

        .match-header {
            background:
                #0d6efd;

            color:
                white;

            padding:
                10px 15px;

            font-size:
                14px;

            font-weight:
                bold;
        }

        .team-logo {
            width:
                60px;

            height:
                60px;

            object-fit:
                contain;
        }

        .team-name {
            font-size:
                18px;

            font-weight:
                bold;
        }

        .score {
            font-size:
                32px;

            font-weight:
                bold;

            color:
                #dc3545;
        }

        .match-footer {
            background:
                #f8f9fa;

            padding:
                12px;

            font-size:
                14px;

            border-top:
                1px solid #eee;
        }

        .live {
            animation:
                pulse 1s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: .5;
            }

            100% {
                opacity: 1;
            }
        }
    </style>

</head>

<body>

    <div class="main-container">

        <h1 class="mb-4">

            🏆 <?= htmlspecialchars($league) ?>

        </h1>

        <?php

        function renderMatches($matches)
        {
            if (empty($matches)) {
                echo
                "
        <div class='alert alert-secondary'>
            No hay partidos.
        </div>
        ";

                return;
            }

            echo "<div class='row g-4'>";

            foreach ($matches as $m) {

        ?>

                <div class="col-lg-6">

                    <div class="card match-card">

                        <div class="match-header d-flex justify-content-between">

                            <div>

                                <?= htmlspecialchars($m['phase']) ?>

                            </div>

                            <div>

                                <?= htmlspecialchars($m['statusText']) ?>

                            </div>

                        </div>

                        <div class="card-body">

                            <div class="row align-items-center text-center">

                                <div class="col-4">

                                    <img
                                        src="<?= htmlspecialchars($m['homeLogo']) ?>"
                                        class="team-logo">

                                    <div class="team-name mt-2">

                                        <?= htmlspecialchars($m['homeName']) ?>

                                    </div>

                                </div>

                                <div class="col-4">

                                    <div class="score">

                                        <?= htmlspecialchars($m['resultado']) ?>

                                    </div>

                                    <?php

                                    if (
                                        $m['statusCode']
                                        ===
                                        "STATUS_IN_PROGRESS"
                                    ) {
                                    ?>

                                        <span class="badge bg-danger live">

                                            🔴 EN VIVO

                                        </span>

                                    <?php
                                    } elseif (
                                        in_array(
                                            $m['statusCode'],
                                            [
                                                'STATUS_FULL_TIME',
                                                'STATUS_FINAL',
                                                'STATUS_AET',
                                                'STATUS_PENALTY'
                                            ]
                                        )
                                    ) {
                                    ?>

                                        <span class="badge bg-success">

                                            FINAL

                                        </span>

                                    <?php
                                    } else {
                                    ?>

                                        <span class="badge bg-secondary">

                                            PROGRAMADO

                                        </span>

                                    <?php
                                    }
                                    ?>

                                </div>

                                <div class="col-4">

                                    <img
                                        src="<?= htmlspecialchars($m['awayLogo']) ?>"
                                        class="team-logo">

                                    <div class="team-name mt-2">

                                        <?= htmlspecialchars($m['awayName']) ?>

                                    </div>

                                </div>

                            </div>

                        </div>

                        <div class="match-footer">

                            🏟 <?= htmlspecialchars($m['venue']) ?>

                        </div>

                    </div>

                </div>

        <?php

            }

            echo "</div>";
        }

        ?>

        <h2 class="section-title">

            🔴 Partidos de Hoy

        </h2>

        <?php

        renderMatches(
            $partidosHoy
        );

        ?>

        <h2 class="section-title">

            ✅ Partidos de Ayer

        </h2>

        <?php

        renderMatches(
            $partidosAyer
        );

        ?>

    </div>

</body>

</html>