<?php

include
    "includes/actualizar_si_necesario.php";

$datos = include "includes/calcular_puntos.php";

$participantes = $datos['participantes'];
$detalle = $datos['detalle'];

/*
    Mapa id => nombre
*/
$nombres = [];

foreach ($participantes as $p) {
    $nombres[$p['id']] = $p['nombre'];
}

/*
    Ordenar por partido
*/
usort($detalle, function ($a, $b) {

    if ($a['partido'] == $b['partido'])
        return 0;

    return ($a['partido'] < $b['partido']) ? -1 : 1;
});

/*
    Agrupar por fase y por partido
*/
function normalizarFase($fase, $partido)
{
    $fase = strtolower(trim((string) $fase));

    if (strpos($fase, 'group') !== false) {
        return 'Group';
    }

    if (strpos($fase, 'round of 32') !== false || strpos($fase, 'round-of-32') !== false) {
        return 'Round of 32';
    }

    if (strpos($fase, 'round of 16') !== false || strpos($fase, 'round-of-16') !== false) {
        return 'Round of 16';
    }

    if (strpos($fase, 'quarterfinals') !== false || strpos($fase, 'quarter-finals') !== false) {
        return 'Quarterfinals';
    }

    if (strpos($fase, 'semifinal') !== false || strpos($fase, 'semi-final') !== false || strpos($fase, 'semi-finals') !== false) {
        return 'Semifinals';
    }

    if (strpos($fase, 'final') !== false) {
        return 'Final';
    }

    // Fallback por número de partido
    // if ($partido <= 48) {
    //     return 'Group';
    // }

    // if ($partido <= 64) {
    //     return 'Round of 32';
    // }

    // if ($partido <= 80) {
    //     return 'Round of 16';
    // }

    // if ($partido <= 88) {
    //     return 'Quarterfinals';
    // }

    // if ($partido <= 90) {
    //     return 'Semifinals';
    // }

    // return 'Final';
}

$partidosPorFase = [];

foreach ($detalle as $fila) {
    $idPartido = $fila['partido'];
    $nombreFase = normalizarFase($fila['fase'] ?? '', $idPartido);

    if (!isset($partidosPorFase[$nombreFase])) {
        $partidosPorFase[$nombreFase] = [
            'nombre' => $nombreFase,
            'partidos' => []
        ];
    }

    if (!isset($partidosPorFase[$nombreFase]['partidos'][$idPartido])) {
        $partidosPorFase[$nombreFase]['partidos'][$idPartido] = [
            'partido' => $idPartido,
            'equipo1' => $fila['equipo1'],
            'equipo2' => $fila['equipo2'],
            'resultado' => $fila['resultado'],
            'pronosticos' => []
        ];
    }

    $partidosPorFase[$nombreFase]['partidos'][$idPartido]['pronosticos'][] = $fila;
}

$gruposFase = array_values($partidosPorFase);

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>📋 Historial de Partidos</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <!-- NAVBAR -->

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">


        <div class="container">

            <a class="navbar-brand" href="index.php">
                🏆 Mundial 2026
            </a>

            <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNav">

                <span class="navbar-toggler-icon"></span>

            </button>

            <div class="collapse navbar-collapse" id="navbarNav">

                <ul class="navbar-nav ms-auto">

                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            Inicio
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="grafica.php">
                            📈 Evolución de Puntos
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link active" href="historial.php">
                            📋 Historial
                        </a>
                    </li>

                    <li class="nav-item ms-2">

                        <a
                            class="btn btn-warning text-dark fw-bold"
                            href="capturar_pronosticos.php">

                            ⚽ Registrar Pronósticos

                        </a>

                    </li>

                </ul>

            </div>

        </div>


    </nav>

    <!-- CONTENIDO -->

    <div class="container container-main">


        <div class="card-custom">

            <div class="d-flex justify-content-between align-items-center">

                <h2>
                    📋 Historial Completo de Pronósticos
                </h2>

                <a href="index.php"
                    class="btn btn-outline-primary">

                    ← Regresar al Inicio

                </a>

            </div>

            <hr>

<div class="accordion" id="accordionFases">

                <?php foreach ($gruposFase as $indexFase => $faseGrupo): ?>

                    <div class="accordion-item phase-section">

                        <h2 class="accordion-header"
                            id="headingFase<?= $indexFase ?>">

                            <button
                                class="accordion-button collapsed phase-button"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseFase<?= $indexFase ?>">

                                <div class="d-flex justify-content-between align-items-center w-100 gap-3">
                                    <div class="text-start">
                                        <span class="phase-title">
                                            <?= htmlspecialchars($faseGrupo['nombre']) ?>
                                        </span>
                                        <div class="phase-meta">
                                            <?= count($faseGrupo['partidos']) ?> partidos
                                        </div>
                                    </div>

                                    <span class="badge bg-primary rounded-pill">
                                        Ver partidos
                                    </span>
                                </div>

                            </button>

                        </h2>

                        <div id="collapseFase<?= $indexFase ?>"
                            class="accordion-collapse collapse"
                            data-bs-parent="#accordionFases">

                            <div class="accordion-body">

                                <div class="accordion" id="accordionPartidos<?= $indexFase ?>">

                                    <?php foreach ($faseGrupo['partidos'] as $partido): ?>

                                        <?php

                                        $accordionId = "partido_" . $partido['partido'] . "_" . $indexFase;

                                        ?>

                                        <div class="accordion-item">

                                            <h3 class="accordion-header"
                                                id="heading<?= $accordionId ?>">

                                                <button
                                                    class="accordion-button collapsed"
                                                    type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#collapse<?= $accordionId ?>">

                                                    ⚽ Partido <?= $partido['partido'] ?>

                                                    &nbsp;&nbsp;|

                                                    &nbsp;&nbsp;

                                                    <?= htmlspecialchars($partido['equipo1']) ?>

                                                    vs

                                                    <?= htmlspecialchars($partido['equipo2']) ?>

                                                    &nbsp;&nbsp;—

                                                    &nbsp;&nbsp;

                                                    Resultado:
                                                    <?= htmlspecialchars($partido['resultado']) ?>

                                                </button>

                                            </h3>

                                            <div id="collapse<?= $accordionId ?>"
                                                class="accordion-collapse collapse"
                                                data-bs-parent="#accordionPartidos<?= $indexFase ?>">

                                                <div class="accordion-body">

                                                    <table class="table table-striped table-bordered history-table">

                                                        <thead>

                                                            <tr>

                                                                <th>Participante</th>
                                                                <th>Pronóstico</th>
                                                                <th>Puntos</th>

                                                            </tr>

                                                        </thead>

                                                        <tbody>

                                                            <?php

                                                            foreach ($partido['pronosticos'] as $pron):

                                                                $nombre =
                                                                    $nombres[$pron['participante']]
                                                                    ?? 'Desconocido';

                                                            ?>

                                                                <tr>

                                                                    <td>

                                                                        <?= htmlspecialchars($nombre) ?>

                                                                    </td>

                                                                    <td>

                                                                        <?= htmlspecialchars($pron['pronostico']) ?>

                                                                    </td>

                                                                    <td>

                                                                        <?php

                                                                        // echo "Fase: " . print_r($faseGrupo['nombre'], true) . "<br>";


                                                                        if (
                                                                            strpos($faseGrupo['nombre'], 'Quarterfinals') !== false
                                                                            || strpos($faseGrupo['nombre'], 'quarter-finals') !== false
                                                                        ) {
                                                                            if ($pron['puntos'] === 8) {
                                                                                $icono = '🎯';
                                                                            } elseif ($pron['puntos'] === 5) {
                                                                                $icono = '✅';
                                                                            } else {
                                                                                $icono = '❌';
                                                                            }
                                                                        } elseif (
                                                                            strpos($faseGrupo['nombre'], 'Semifinals') !== false
                                                                            || strpos($faseGrupo['nombre'], 'semifinals') !== false
                                                                            || strpos($faseGrupo['nombre'], 'semi-finals') !== false
                                                                        ) {
                                                                            if ($pron['puntos'] === 10) {
                                                                                $icono = '🎯';
                                                                            } elseif ($pron['puntos'] === 6) {
                                                                                $icono = '✅';
                                                                            } else {
                                                                                $icono = '❌';
                                                                            }
                                                                        } elseif (strpos($faseGrupo['nombre'], 'final') !== false) {
                                                                            if ($pron['puntos'] === 15) {
                                                                                $icono = '🎯';
                                                                            } elseif ($pron['puntos'] === 8) {
                                                                                $icono = '✅';
                                                                            } else {
                                                                                $icono = '❌';
                                                                            }
                                                                        } else {
                                                                            if ($pron['puntos'] === 5) {
                                                                                $icono = '🎯';
                                                                            } elseif ($pron['puntos'] === 3) {
                                                                                $icono = '✅';
                                                                            } else {
                                                                                $icono = '❌';
                                                                            }
                                                                        }

                                                                        echo $icono . ' ' . $pron['puntos'];

                                                                        ?>

                                                                    </td>

                                                                </tr>

                                                            <?php endforeach; ?>

                                                        </tbody>

                                                    </table>

                                                </div>

                                            </div>

                                        </div>

                                    <?php endforeach; ?>

                                </div>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        </div>

        <div class="footer">

            Quiniela Mundial 2026 · CIO AGS

        </div>


    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>