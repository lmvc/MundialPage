<?php

require_once "funciones.php";

$resultados = leerCSV(__DIR__ . '/../data/resultados.csv');
$pronosticos = leerCSV(__DIR__ . '/../data/pronosticos.csv');
$participantes = leerCSV(__DIR__ . '/../data/participantes.csv');


$puntos = [];
$evolucion = [];
$detalle = [];

foreach($participantes as $p)
{
    $puntos[$p['id']] = 0;
    $evolucion[$p['id']] = [];
}

foreach($resultados as $resultado)
{
    $partido = $resultado['partido'];

    foreach($pronosticos as $pron)
    {
        if($pron['partido'] != $partido)
            continue;

        $id = $pron['participante'];

        $puntosPartido = 0;

        $real1 = intval($resultado['goles1']);
        $real2 = intval($resultado['goles2']);

        $pred1 = intval($pron['goles1']);
        $pred2 = intval($pron['goles2']);

        // Marcador exacto
        if($real1 == $pred1 && $real2 == $pred2)
        {
            $puntosPartido = 5;
        }
        else
        {
            $ganadorReal = ganador($real1,$real2);
            $ganadorPred = ganador($pred1,$pred2);

            if($ganadorReal == $ganadorPred)
                $puntosPartido = 3;
        }

        $puntos[$id] += $puntosPartido;

        $evolucion[$id][] = $puntos[$id];

        $detalle[] = [
            'participante' => $id,
            'partido'      => $partido,
            'equipo1'      => $resultado['equipo1'],
            'equipo2'      => $resultado['equipo2'],
            'pronostico'   => $pred1 . '-' . $pred2,
            'resultado'    => $real1 . '-' . $real2,
            'puntos'       => $puntosPartido
        ];
    }
}

return [
    'puntos'=>$puntos,
    'evolucion'=>$evolucion,
    'participantes'=>$participantes,
    'detalle'=>$detalle
];
