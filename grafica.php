<?php

$datos = include "includes/calcular_puntos.php";

$puntos        = $datos['puntos'];
$evolucion     = $datos['evolucion'];
$participantes = $datos['participantes'];
$estadisticas  = $datos['estadisticas'];

/*
--------------------------------------------------
Ranking
--------------------------------------------------
*/

$ranking = [];

foreach($participantes as $p)
{
    $id = $p['id'];

    $ranking[] = [

        'id'       => $id,
        'nombre'   => $p['nombre'],
        'puntos'   => $puntos[$id] ?? 0,
        'exactos'  => $estadisticas[$id]['exactos'] ?? 0,
        'ganador'  => $estadisticas[$id]['ganador'] ?? 0

    ];
}

usort($ranking, function($a, $b){

    if($a['puntos'] != $b['puntos'])
    {
        return $b['puntos'] <=> $a['puntos'];
    }

    if($a['exactos'] != $b['exactos'])
    {
        return $b['exactos'] <=> $a['exactos'];
    }

    return $b['ganador'] <=> $a['ganador'];

});

/*
--------------------------------------------------
Cantidad de partidos
--------------------------------------------------
*/

$maxPartidos = 0;

foreach($evolucion as $historial)
{
    $maxPartidos = max(
        $maxPartidos,
        count($historial)
    );
}

$labels = [];

for($i=1; $i<=$maxPartidos; $i++)
{
    $labels[] = "P".$i;
}

/*
--------------------------------------------------
Datasets Chart.js
--------------------------------------------------
*/

$datasets = [];

foreach($ranking as $jugador)
{
    $id = $jugador['id'];

    $color = sprintf(
        '#%06X',
        mt_rand(0, 0xFFFFFF)
    );

    $datasets[] = [

        'label' => $jugador['nombre'],

        'data' => $evolucion[$id],

        'fill' => false,

        'tension' => 0.2,

        'borderColor' => $color,

        'backgroundColor' => $color,

        'borderWidth' => 3

    ];
}

$totalPartidos =
    count(
        current($evolucion)
    );

?>

<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">
<meta name="viewport"
      content="width=device-width, initial-scale=1">

<title>
📈 Evolución de Puntos
</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link rel="stylesheet"
      href="css/style.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>

<!-- NAVBAR -->

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">


<div class="container">

    <a class="navbar-brand"
       href="index.php">

        🏆 Mundial 2026

    </a>

    <button class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav">

        <span class="navbar-toggler-icon"></span>

    </button>

    <div class="collapse navbar-collapse"
         id="navbarNav">

        <ul class="navbar-nav ms-auto">

            <li class="nav-item">

                <a class="nav-link"
                   href="index.php">

                    Inicio

                </a>

            </li>

            <li class="nav-item">

                <a class="nav-link active"
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

<!-- CONTENIDO -->

<div class="container container-main">


<!-- GRAFICA -->

<div class="card-custom">

    <div class="d-flex justify-content-between align-items-center">

        <h2>
            📈 Evolución de Puntos
        </h2>

        <a href="index.php"
           class="btn btn-outline-primary">

            ← Regresar

        </a>

    </div>

    <hr>

    <div class="alert alert-info">

        <strong>
            Partidos contabilizados:
        </strong>

        <?= $totalPartidos ?>

        &nbsp; | &nbsp;

        <strong>
            Participantes:
        </strong>

        <?= count($participantes) ?>

    </div>

    <div class="chart-container">

        <canvas id="rankingChart"></canvas>

    </div>

</div>

<!-- TABLA -->

<div class="card-custom">

    <h2>
        🏆 Ranking Completo
    </h2>

    <table
        class="table table-striped table-hover ranking-full-table">

        <thead>

            <tr>

                <th>Pos</th>
                <th>Participante</th>
                <th>Puntos</th>
                <th>Exactos</th>
                <th>Ganador</th>
                <th>Efectividad</th>

            </tr>

        </thead>

        <tbody>

        <?php

        $totalJugadores =
            count($ranking);

        foreach($ranking as $indice => $jugador)
        {
            $posicion = $indice + 1;

            $clase = '';

            if($indice == 0)
            {
                $clase = 'gold';
            }

            if(
                $jugador['exactos'] == 0 &&
                $jugador['ganador'] == 0
            )
            {
                $clase = 'last-place';
            }

            if(
                $indice ==
                $totalJugadores - 1
            )
            {
                $clase = 'last-place';
            }

            $partidosJugados =
                count(
                    $evolucion[
                        $jugador['id']
                    ]
                );

            $efectividad = 0;

            if($partidosJugados > 0)
            {
                $efectividad =
                    (
                        $jugador['puntos']
                        /
                        ($partidosJugados * 5)
                    ) * 100;
            }

            ?>

            <tr class="<?= $clase ?>">

                <td>

                <?php

                switch($posicion)
                {
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
                        echo "#".$posicion;
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

                <td>

                    <?= $jugador['exactos'] ?>

                </td>

                <td>

                    <?= $jugador['ganador'] ?>

                </td>

                <td>

                    <?= number_format(
                        $efectividad,
                        1
                    ) ?>%

                </td>

            </tr>

            <?php
        }

        ?>

        </tbody>

    </table>

</div>

<div class="footer">

    Quiniela Mundial 2026 · CIO AGS

</div>


</div>

<script>

const ctx =
document.getElementById(
    'rankingChart'
);

new Chart(
ctx,
{
    type:'line',

    data:
    {
        labels:
            <?= json_encode($labels) ?>,

        datasets:
            <?= json_encode($datasets) ?>
    },

    options:
    {
        responsive:true,

        maintainAspectRatio:false,

        elements:
        {
            point:
            {
                radius:4
            }
        },

        interaction:
        {
            mode:'nearest',
            intersect:false
        },

        plugins:
        {
            legend:
            {
                position:'bottom'
            },

            tooltip:
            {
                enabled:true,

                callbacks:
                {
                    label:function(context)
                    {
                        return context.dataset.label
                            + ': '
                            + context.raw
                            + ' pts';
                    }
                }
            }
        },

        scales:
        {
            y:
            {
                beginAtZero:true,

                title:
                {
                    display:true,
                    text:'Puntos Acumulados'
                }
            },

            x:
            {
                title:
                {
                    display:true,
                    text:'Partidos'
                }
            }
        }
    }
});

</script>

<script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>
</html>
