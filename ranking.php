<?php

$datos = include "includes/calcular_puntos.php";

$puntos = $datos['puntos'];
$participantes = $datos['participantes'];

arsort($puntos);

?>

<!DOCTYPE html>

<html>

<head>

<link rel="stylesheet" href="css/style.css">

</head>

<body>

<h1>Ranking General</h1>

<table>

<tr>
<th>Posición</th>
<th>Jugador</th>
<th>Puntos</th>
</tr>

<?php

$pos=1;

foreach($puntos as $id=>$pts)
{
    $nombre='';

    foreach($participantes as $p)
    {
        if($p['id']==$id)
        {
            $nombre=$p['nombre'];
            break;
        }
    }

    echo "
    <tr>
    <td>$pos</td>
    <td>$nombre</td>
    <td>$pts</td>
    </tr>";

    $pos++;
}

?>

</table>

<br>

<a href="grafica.php">
Ver Evolución
</a>

</body>
</html>