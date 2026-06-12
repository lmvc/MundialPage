<?php

$datos = include "includes/calcular_puntos.php";

$evolucion = $datos['evolucion'];
$participantes = $datos['participantes'];
$puntos = $datos['puntos'];

/*
Determinar el número máximo de partidos jugados
*/
$maxPartidos = 0;

foreach($evolucion as $historial)
{
    $maxPartidos = max($maxPartidos, count($historial));
}

/*
Etiquetas:
Partido 1, Partido 2, ...
*/
$labels = [];

for($i=1; $i<=$maxPartidos; $i++)
{
    $labels[] = "P".$i;
}

arsort($puntos);

$nombres = [];
$ranking = [];

foreach($participantes as $p)
{
    $nombres[$p['id']] = $p['nombre'];

    $id = $p['id'];

    $ranking[] = [
        'id'       => $id,
        'nombre'   => $p['nombre'],
        'puntos'   => $puntos[$id] ?? 0,
        'exactos'  => $estadisticas[$id]['exactos'] ?? 0,
        'ganador'  => $estadisticas[$id]['ganador'] ?? 0
    ];
}

?>
<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<title>Evolución de Puntos</title>

<link rel="stylesheet" href="css/style.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

body{
    font-family: Arial;
    padding:20px;
}

.chart-container{
    width:95%;
    max-width:1400px;
    margin:auto;
}

</style>

</head>

<body>

<div class="navbar">
    <a href="index.php" class="btn-home">🏠 Inicio</a>
</div>

<h1>Evolución de la Quiniela</h1>

<div class="chart-container">
    <canvas id="rankingChart"></canvas>
</div>

<?php

$ranking = [];

foreach($participantes as $p)
{
    $id = $p['id'];

    $ranking[] = [
        'nombre'   => $p['nombre'],
        'puntos'   => $puntos[$id] ?? 0,
        'exactos'  => $estadisticas[$id]['exactos'] ?? 0,
        'ganador'  => $estadisticas[$id]['ganador'] ?? 0
    ];
}

usort($ranking, function($a, $b){

    if($a['puntos'] != $b['puntos'])
        return $b['puntos'] <=> $a['puntos'];

    if($a['exactos'] != $b['exactos'])
        return $b['exactos'] <=> $a['exactos'];

    return $b['ganador'] <=> $a['ganador'];
});

?>

<div class="ranking-container">

    <div class="ranking-title">
        🏆 Ranking General
    </div>

    <table class="ranking-table">

        <thead>
            <tr>
                <th>Posición</th>
                <th>Participante</th>
                <th>Puntos</th>
                <th>Marcador Exacto</th>
                <th>Solo Ganador/Empate</th>
            </tr>
        </thead>

        <tbody>

        <?php

        $totalJugadores = count($ranking);

        foreach($ranking as $indice => $jugador)
        {
            $posicion = $indice + 1;

            $clase = '';

            if($indice == 0)
                $clase = 'first-place';

            elseif($indice == 1)
                $clase = 'second-place';

            elseif($indice == 2)
                $clase = 'third-place';

            elseif($indice == ($totalJugadores - 1))
                $clase = 'last-place';

            $icono = $posicion;

            if($posicion == 1)
                $icono = '🥇';

            elseif($posicion == 2)
                $icono = '🥈';

            elseif($posicion == 3)
                $icono = '🥉';

            echo "
            <tr class='{$clase}'>
                <td class='position-badge'>{$icono}</td>
                <td>{$jugador['nombre']}</td>
                <td>{$jugador['puntos']}</td>
                <td>{$jugador['exactos']}</td>
                <td>{$jugador['ganador']}</td>
            </tr>";
        }

        ?>

        </tbody>

    </table>

</div>

<script>

const labels = <?= json_encode($labels) ?>;

const datasets = [

<?php

$colores = [
'#FF6384',
'#36A2EB',
'#FFCE56',
'#4BC0C0',
'#9966FF',
'#FF9F40',
'#8BC34A',
'#E91E63',
'#795548',
'#009688'
];

$indiceColor = 0;

foreach($participantes as $participante)
{
    $id = $participante['id'];

    $historial = $evolucion[$id];

    $color = $colores[$indiceColor % count($colores)];

    echo "
    {
        label: '".$participante['nombre']."',
        data: ".json_encode($historial).",
        borderColor: '$color',
        backgroundColor: '$color',
        tension: 0.2,
        fill:false
    },
    ";

    $indiceColor++;
}

?>

];

const ctx = document.getElementById('rankingChart');

new Chart(ctx, {

    type: 'line',

    data: {
        labels: labels,
        datasets: datasets
    },

    options: {

        responsive: true,

        plugins: {

            title: {
                display: true,
                text: 'Evolución de puntos por participante'
            },

            legend: {
                position: 'bottom'
            }

        },

        interaction: {
            mode: 'index',
            intersect: false
        },

        scales: {

            x: {
                title: {
                    display:true,
                    text:'Partidos Jugados'
                }
            },

            y: {
                beginAtZero:true,
                title: {
                    display:true,
                    text:'Puntos Acumulados'
                }
            }

        }

    }

});


</script>

</body>
</html>