<?php

$datos = include "includes/calcular_puntos.php";

$evolucion = $datos['evolucion'];
$participantes = $datos['participantes'];

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

?>
<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<title>Evolución de Puntos</title>

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