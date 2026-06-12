<?php

$datos = include "includes/calcular_puntos.php";

$puntos = $datos['puntos'];
$participantes = $datos['participantes'];
$detalle = $datos['detalle'];

arsort($puntos);

/*
Mapa id => nombre
*/
$nombres = [];

foreach($participantes as $p)
{
    $nombres[$p['id']] = $p['nombre'];
}

$ranking = [];

foreach($puntos as $id => $pts)
{
    $ranking[] = [
        'id' => $id,
        'nombre' => $nombres[$id],
        'puntos' => $pts
    ];
}

$totalRanking = count($ranking);

$primerLugar = $ranking[0] ?? null;
$segundoLugar = $ranking[1] ?? null;
$ultimoLugar = $ranking[$totalRanking - 1] ?? null;


?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<title>🏆Mundial2026</title>

<link rel="stylesheet" href="css/style.css">

</head>

<body>

<div class="container">

<div class="header">
    <h1>🏆 Quiniela Mundial 2026 - CIO_AGS</h1>
</div>

<div class="card">

<h2>Ranking General</h2>

<table>

<tr>
    <th>Posición</th>
    <th>Participante</th>
    <th>Puntos</th>
</tr>


<?php if($primerLugar): ?>
<tr class="gold">
    <td>🥇 1</td>
    <td><?= $primerLugar['nombre'] ?></td>
    <td><?= $primerLugar['puntos'] ?></td>
</tr>
<?php endif; ?>

<?php if($segundoLugar): ?>
<tr class="silver">
    <td>🥈 2</td>
    <td><?= $segundoLugar['nombre'] ?></td>
    <td><?= $segundoLugar['puntos'] ?></td>
</tr>
<?php endif; ?>

<?php if($ultimoLugar): ?>
<tr class="last-place">
    <td>💀 Último</td>
    <td><?= $ultimoLugar['nombre'] ?></td>
    <td><?= $ultimoLugar['puntos'] ?></td>
</tr>
<?php endif; ?>
</table>

</div>

<div class="card">

<h2>Estadísticas</h2>

<?php

$totalParticipantes = count($participantes);

$liderID = array_key_first($puntos);

$liderNombre = $nombres[$liderID];
$liderPuntos = $puntos[$liderID];

?>

<p><strong>Líder actual:</strong> <?= $liderNombre ?></p>
<p><strong>Puntos:</strong> <?= $liderPuntos ?></p>
<p><strong>Participantes:</strong> <?= $totalParticipantes ?></p>

<div class="card">

<h2>Detalle de Pronósticos</h2>

<!-- <table> -->

<?php

usort($detalle, function($a, $b){

    if($a['partido'] == $b['partido'])
        return 0;

    return ($a['partido'] < $b['partido']) ? -1 : 1;
});


$partidoActual = '';

foreach($detalle as $fila)
{
    $nombre = $nombres[$fila['participante']];

    $juego =
        $fila['equipo1'] .
        " vs " .
        $fila['equipo2'];

    if($partidoActual != $juego)
    {
        if($partidoActual != '')
        {
            echo "</tbody></table></div>";
        }

        $partidoActual = $juego;

        $gameId = "game_" . $fila['partido'];

        echo "
            <div class='game-block'>

                <div class='game-title'
                    onclick=\"toggleGame('{$gameId}')\">

                    <div>
                        ⚽ {$juego}
                    </div>

                    <div>
                        <span class='game-result'>
                            Resultado: {$fila['resultado']}
                        </span>

                        <span
                            id='arrow_{$gameId}'
                            class='arrow'>
                            ▶
                        </span>
                    </div>

                </div>

            <div id='{$gameId}' class='game-content'>

                <table class='game-table'>

                <thead>
                    <tr>
                        <th>Participante</th>
                        <th>Pronóstico</th>
                        <th>Puntos</th>
                    </tr>
                </thead>

        <tbody>";
    }

    echo "
    <tr>
        <td>{$nombre}</td>
        <td>{$fila['pronostico']}</td>
        <td>{$fila['puntos']}</td>
    </tr>";
}

if($partidoActual != '')
{
    echo "
    </tbody>
    </table>
    </div>
    </div>";
}



?>

<!-- </table> -->

</div>

<a class="btn" href="grafica.php">
📈 Ver Evolución de Puntos
</a>

</div>

</div>

<script>

function toggleGame(id)
{
    const content = document.getElementById(id);
    const arrow = document.getElementById('arrow_'+id);

    content.classList.toggle('active');
    arrow.classList.toggle('rotate');
}

</script>

</body>
</html>