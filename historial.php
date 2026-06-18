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

foreach($participantes as $p)
{
    $nombres[$p['id']] = $p['nombre'];
}

/*
    Ordenar por partido
*/
usort($detalle, function($a, $b){

    if($a['partido'] == $b['partido'])
        return 0;

    return ($a['partido'] < $b['partido']) ? -1 : 1;
});

/*
    Agrupar por partido
*/
$partidos = [];

foreach($detalle as $fila)
{
    $idPartido = $fila['partido'];

    if(!isset($partidos[$idPartido]))
    {
        $partidos[$idPartido] = [
            'partido' => $idPartido,
            'equipo1' => $fila['equipo1'],
            'equipo2' => $fila['equipo2'],
            'resultado' => $fila['resultado'],
            'pronosticos' => []
        ];
    }

    $partidos[$idPartido]['pronosticos'][] = $fila;
}

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

    <div class="accordion" id="accordionPartidos">

        <?php

        $contador = 0;

        foreach($partidos as $partido):

            $contador++;

            $accordionId = "partido_" . $partido['partido'];

        ?>

        <div class="accordion-item">

            <h2 class="accordion-header"
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

            </h2>

            <div id="collapse<?= $accordionId ?>"
                 class="accordion-collapse collapse"
                 data-bs-parent="#accordionPartidos">

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

                        foreach($partido['pronosticos'] as $pron):

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

                                if($pron['puntos'] == 5)
                                {
                                    echo "🎯 5";
                                }
                                elseif($pron['puntos'] == 3)
                                {
                                    echo "✅ 3";
                                }
                                else
                                {
                                    echo "❌ 0";
                                }

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

<div class="footer">

    Quiniela Mundial 2026 · CIO AGS

</div>


</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
