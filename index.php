<?php

include
"includes/actualizar_si_necesario.php";

$datos = include "includes/calcular_puntos.php";


$puntos        = $datos['puntos'];
$participantes = $datos['participantes'];
$detalle       = $datos['detalle'];

arsort($puntos);

/*
--------------------------------------------------
Mapa id => nombre
--------------------------------------------------
*/

$nombres = [];

foreach ($participantes as $p) {
    $nombres[$p['id']] = $p['nombre'];
}

/*
--------------------------------------------------
Ranking
--------------------------------------------------
*/

$ranking = [];

foreach ($puntos as $id => $pts) {
    $ranking[] = [

        'id'     => $id,
        'nombre' => $nombres[$id],
        'puntos' => $pts

    ];
}

$totalRanking = count($ranking);

$top5 = array_slice(
    $ranking,
    0,
    min(5, $totalRanking)
);

$ultimoLugar =
    $ranking[$totalRanking - 1] ?? null;

/*
--------------------------------------------------
Estadísticas Generales
--------------------------------------------------
*/

$totalParticipantes =
    count($participantes);

$liderID =
    array_key_first($puntos);

$liderNombre =
    $nombres[$liderID] ?? '';

$liderPuntos =
    $puntos[$liderID] ?? 0;

/*
--------------------------------------------------
Agrupar partidos
--------------------------------------------------
*/

$partidos = [];

foreach ($detalle as $fila) {
    $idPartido = $fila['partido'];

    if (!isset($partidos[$idPartido])) {
        $partidos[$idPartido] = [

            'partido' => $idPartido,

            'equipo1' => $fila['equipo1'],

            'equipo2' => $fila['equipo2'],

            'resultado' => $fila['resultado'],

            'pronosticos' => []

        ];
    }

    $partidos[$idPartido]['pronosticos'][] =
        $fila;
}

ksort($partidos);

$totalPartidos =
    count($partidos);

/*
--------------------------------------------------
Últimos 4 partidos
--------------------------------------------------
*/

$ultimosPartidos =
    array_slice(
        $partidos,
        -4,
        4,
        true
    );

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1">

    <title>
        🏆 Quiniela Mundial 2026
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link rel="stylesheet"
        href="css/style.css">

</head>

<body>

    <!-- ==========================================
NAVBAR
========================================== -->

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">

        <div class="container">

            <a class="navbar-brand"
                href="index.php">

                🏆 Mundial 2026

            </a>

            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNav">

                <span class="navbar-toggler-icon">
                </span>

            </button>

            <div class="collapse navbar-collapse"
                id="navbarNav">

                <ul class="navbar-nav ms-auto">

                    <li class="nav-item">

                        <a class="nav-link active"
                            href="index.php">

                            Inicio

                        </a>

                    </li>

                    <li class="nav-item">

                        <a class="nav-link"
                            href="grafica.php">

                            📈 Evolución

                        </a>

                    </li>

                    <li class="nav-item">

                        <a class="nav-link"
                            href="historial.php">

                            📋 Historial

                        </a>

                    </li>

                </ul>

            </div>

        </div>

    </nav>

    <!-- ==========================================
CONTENIDO
========================================== -->

    <div class="container container-main">

        <!-- ==========================================
TOP 5
========================================== -->
        <div class="card-custom">

            <h2 class="mb-4">

                🏆 Top 5 General

            </h2>

            <table
                class="table table-striped table-hover ranking-table">

                <thead>

                    <tr>

                        <th>Pos</th>
                        <th>Participante</th>
                        <th>Puntos</th>

                    </tr>

                </thead>

                <tbody>

                    <?php

                    foreach ($top5 as $indice => $jugador) {
                        $posicion = $indice + 1;

                        $clase = '';

                        if ($posicion == 1)
                            $clase = 'gold';

                        elseif ($posicion == 2)
                            $clase = 'silver';

                        elseif ($posicion == 3)
                            $clase = 'bronze';

                    ?>

                        <tr class="<?= $clase ?>">

                            <td>

                                <?php

                                switch ($posicion) {
                                    case 1:
                                        echo "🥇";
                                        break;

                                    case 2:
                                        echo "🥈";
                                        break;

                                    case 3:
                                        echo "🥉";
                                        break;

                                    default:
                                        echo "#" . $posicion;
                                }

                                ?>

                            </td>

                            <td>

                                <?= htmlspecialchars(
                                    $jugador['nombre']
                                ) ?>

                            </td>

                            <td>

                                <?= $jugador['puntos'] ?>

                            </td>

                        </tr>

                    <?php
                    }
                    ?>

                    <!-- Último lugar -->
                    <?php if ($ultimoLugar): ?>

                        <tr class="last-place">

                            <td>

                                💀

                            </td>

                            <td>

                                <?= htmlspecialchars(
                                    $ultimoLugar['nombre']
                                ) ?>

                            </td>

                            <td>

                                <?= $ultimoLugar['puntos'] ?>

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

            <div
                class="small text-muted text-end">

                💀 Último lugar actual

            </div>

        </div>

        <!-- ==========================================
ESTADISTICAS
========================================== -->

        <div class="card-custom">

            <h2 class="mb-4">

                📊 Estadísticas

            </h2>

            <div class="row">

                <div class="col-md-3">

                    <div
                        class="stat-card bg-leader">

                        <h3>

                            <?= htmlspecialchars(
                                $liderNombre
                            ) ?>

                        </h3>

                        <p>

                            Líder Actual

                        </p>

                    </div>

                </div>

                <div class="col-md-3">

                    <div
                        class="stat-card bg-points">

                        <h3>

                            <?= $liderPuntos ?>

                        </h3>

                        <p>

                            Puntos Líder

                        </p>

                    </div>

                </div>

                <div class="col-md-3">

                    <div
                        class="stat-card bg-players">

                        <h3>

                            <?= $totalParticipantes ?>

                        </h3>

                        <p>

                            Participantes

                        </p>

                    </div>

                </div>

                <div class="col-md-3">

                    <div
                        class="stat-card bg-matches">

                        <h3>

                            <?= $totalPartidos ?>

                        </h3>

                        <p>

                            Partidos Jugados

                        </p>

                    </div>

                </div>

            </div>

        </div>

        <!-- ==========================================
ULTIMOS 4 PARTIDOS
========================================== -->

        <div class="card-custom">

            <div class="d-flex justify-content-between align-items-center">

                <h2>

                    ⚽ Últimos 4 Partidos

                </h2>

                <a
                    href="historial.php"
                    class="btn btn-primary">

                    Ver Historial Completo

                </a>

            </div>

            <hr>

            <?php

            foreach ($ultimosPartidos as $partido) {
                $exactos = 0;
                $ganador = 0;

                $maxPuntos = -1;

                $mejores = [];

                foreach (
                    $partido['pronosticos']
                    as $pron
                ) {
                    if ($pron['puntos'] == 5) {
                        $exactos++;
                    }

                    if ($pron['puntos'] == 3) {
                        $ganador++;
                    }

                    if (
                        $pron['puntos']
                        >
                        $maxPuntos
                    ) {
                        $maxPuntos =
                            $pron['puntos'];

                        $mejores = [

                            $nombres[$pron['participante']]

                        ];
                    } elseif (
                        $pron['puntos']
                        ==
                        $maxPuntos
                    ) {
                        $mejores[] =

                            $nombres[$pron['participante']];
                    }
                }

                $mejores =
                    array_unique(
                        $mejores
                    );

            ?>

                <div
                    class="card match-card mb-3">

                    <div class="card-body">

                        <h5
                            class="match-title">

                            <?= htmlspecialchars(
                                $partido['equipo1']
                            ) ?>

                            vs

                            <?= htmlspecialchars(
                                $partido['equipo2']
                            ) ?>

                        </h5>

                        <p
                            class="match-result">

                            Resultado:
                            <strong>

                                <?= $partido['resultado'] ?>

                            </strong>

                        </p>

                        <div class="row">

                            <div class="col-md-4">

                                🎯 Exactos

                                <br>

                                <strong>

                                    <?= $exactos ?>

                                </strong>

                            </div>

                            <div class="col-md-4">

                                ✅ Ganador

                                <br>

                                <strong>

                                    <?= $ganador ?>

                                </strong>

                            </div>

                            <div class="col-md-4">

                                🏅 Mejor(es)

                                <br>

                                <strong>

                                    <?= implode(
                                        ", ",
                                        $mejores
                                    ) ?>

                                </strong>

                            </div>

                        </div>

                    </div>

                </div>

            <?php

            }

            ?>

        </div>

        <!-- ==========================================
ACCESOS RAPIDOS
========================================== -->

        <div class="card-custom">

            <h2 class="mb-4">

                🚀 Accesos Rápidos

            </h2>

            <div
                class="d-flex gap-3 flex-wrap">

                <a
                    href="grafica.php"
                    class="btn btn-success btn-lg">

                    📈 Evolución de Puntos

                </a>

                <a
                    href="historial.php"
                    class="btn btn-primary btn-lg">

                    📋 Historial Completo

                </a>

            </div>

        </div>

        <!-- ==========================================
FOOTER
========================================== -->

        <div class="footer">

            Quiniela Mundial 2026 · CIO AGS

        </div>

    </div>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>

</body>

</html>