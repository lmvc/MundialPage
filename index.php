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

<title>Quiniela Mundial 2026</title>

<link rel="stylesheet" href="css/style.css">

<!-- <style>

body{
    font-family: Arial, sans-serif;
    background:#f5f7fa;
    margin:0;
}

.container{
    width:95%;
    max-width:1200px;
    margin:auto;
    padding:20px;
}

.header{
    text-align:center;
    margin-bottom:30px;
}

.card{
    background:white;
    border-radius:10px;
    padding:20px;
    margin-bottom:20px;
    box-shadow:0 2px 8px rgba(0,0,0,.1);
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#003366;
    color:white;
}

th,td{
    padding:12px;
    border:1px solid #ddd;
    text-align:center;
}

tr:nth-child(even){
    background:#f8f8f8;
}

.btn{
    display:inline-block;
    padding:10px 20px;
    background:#003366;
    color:white;
    text-decoration:none;
    border-radius:5px;
    margin-top:10px;
}

.btn:hover{
    background:#0055aa;
}

.gold{
    background:#ffd700;
}

.silver{
    background:#c0c0c0;
}

.bronze{
    background:#cd7f32;
}

</style> -->

</head>

<body>

<div class="container">

<div class="header">
    <h1>🏆 Quiniela Mundial 2026</h1>
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

<table>

<tr>
    <th>Participante</th>
    <th>Juego</th>
    <th>Pronóstico</th>
    <th>Resultado</th>
    <th>Puntos</th>
</tr>

<?php

foreach($detalle as $fila)
{
    $nombre = $nombres[$fila['participante']];

    $juego =
        $fila['equipo1']
        ." vs ".
        $fila['equipo2'];

    echo "
    <tr>
        <td>{$nombre}</td>
        <td>{$juego}</td>
        <td>{$fila['pronostico']}</td>
        <td>{$fila['resultado']}</td>
        <td>{$fila['puntos']}</td>
    </tr>";
}

?>

</table>

</div>

<a class="btn" href="grafica.php">
📈 Ver Evolución de Puntos
</a>

</div>

</div>

</body>
</html>