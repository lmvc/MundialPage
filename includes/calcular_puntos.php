<?php

require_once "funciones.php";

$resultados = leerCSV(
    __DIR__.'/../data/resultados.csv'
);

$pronosticos = leerCSV(
    __DIR__.'/../data/pronosticos.csv'
);

$participantes = leerCSV(
    __DIR__.'/../data/participantes.csv'
);

$puntos = [];
$evolucion = [];
$detalle = [];
$estadisticas = [];

/*
--------------------------------------------------
Inicialización
--------------------------------------------------
*/

foreach($participantes as $p)
{
    $id = $p['id'];

    $puntos[$id] = 0;

    $evolucion[$id] = [];

    $estadisticas[$id] = [

        'exactos' => 0,
        'ganador' => 0

    ];
}

/*
--------------------------------------------------
Indice de pronósticos
--------------------------------------------------
*/

$indicePronosticos = [];

foreach($pronosticos as $pron)
{
    $indicePronosticos[
        $pron['participante']
    ][
        $pron['partido']
    ] = $pron;
}

/*
--------------------------------------------------
Procesar partidos
--------------------------------------------------
*/

foreach($resultados as $resultado)
{
    $partido = $resultado['partido'];

    $real1 = intval(
        $resultado['goles1']
    );

    $real2 = intval(
        $resultado['goles2']
    );

    foreach($participantes as $participante)
    {
        $id = $participante['id'];

        $puntosPartido = 0;

        $pronosticoValido = false;

        $pronosticoTexto = 'x-x';

        if(
            isset(
                $indicePronosticos[$id][$partido]
            )
        )
        {
            $pron =
                $indicePronosticos[$id][$partido];

            $pronosticoTexto =
                $pron['goles1']
                . '-'
                . $pron['goles2'];

            if(
                strtolower(trim($pron['goles1'])) != 'x'
                &&
                strtolower(trim($pron['goles2'])) != 'x'
            )
            {
                $pronosticoValido = true;

                $pred1 = intval(
                    $pron['goles1']
                );

                $pred2 = intval(
                    $pron['goles2']
                );
            }
        }

        /*
        ------------------------------------------
        Cálculo de puntos
        ------------------------------------------
        */

        if($pronosticoValido)
        {
            if(
                $real1 == $pred1
                &&
                $real2 == $pred2
            )
            {
                $puntosPartido = 5;

                $estadisticas[$id]['exactos']++;
            }
            else
            {
                $ganadorReal =
                    ganador(
                        $real1,
                        $real2
                    );

                $ganadorPred =
                    ganador(
                        $pred1,
                        $pred2
                    );

                if(
                    $ganadorReal
                    ==
                    $ganadorPred
                )
                {
                    $puntosPartido = 3;

                    $estadisticas[$id]['ganador']++;
                }
            }
        }

        $puntos[$id] += $puntosPartido;

        /*
        ------------------------------------------
        Evolución sincronizada
        ------------------------------------------
        */

        $evolucion[$id][] =
            $puntos[$id];

        /*
        ------------------------------------------
        Historial
        ------------------------------------------
        */

        $detalle[] = [

            'participante' => $id,

            'partido' => $partido,

            'equipo1' =>
                $resultado['equipo1'],

            'equipo2' =>
                $resultado['equipo2'],

            'pronostico' =>
                $pronosticoTexto,

            'resultado' =>
                $real1.'-'.$real2,

            'puntos' =>
                $puntosPartido

        ];
    }
}

/*
--------------------------------------------------
Return
--------------------------------------------------
*/

return [

    'puntos' => $puntos,

    'evolucion' => $evolucion,

    'participantes' => $participantes,

    'detalle' => $detalle,

    'estadisticas' => $estadisticas

];
?>
