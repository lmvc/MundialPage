<?php

require_once "funciones.php";
require_once "config.php";

/*
--------------------------------------------------
SQLite
--------------------------------------------------
*/

$db = new SQLite3(
    DATA_PATH . 'quiniela.db'
);

$db->busyTimeout(5000);

/*
--------------------------------------------------
Participantes
--------------------------------------------------
*/

$participantes = [];

$result =
$db->query(
"
SELECT *
FROM participantes
ORDER BY id
"
);

while(
    $fila =
    $result->fetchArray(
        SQLITE3_ASSOC
    )
)
{
    $participantes[] =
        $fila;
}

/*
--------------------------------------------------
Resultados
--------------------------------------------------
*/

$resultados = [];

$result =
$db->query(
"
SELECT *
FROM resultados
ORDER BY partido
"
);

while(
    $fila =
    $result->fetchArray(
        SQLITE3_ASSOC
    )
)
{
    $resultados[] =
        $fila;
}

/*
--------------------------------------------------
Pronósticos
--------------------------------------------------
*/

$indicePronosticos = [];

$result =
$db->query(
"
SELECT *
FROM pronosticos
"
);

while(
    $fila =
    $result->fetchArray(
        SQLITE3_ASSOC
    )
)
{
    $indicePronosticos[
        $fila['participante']
    ][
        $fila['partido']
    ] = $fila;
}

/*
--------------------------------------------------
Inicialización
--------------------------------------------------
*/

$puntos = [];
$evolucion = [];
$detalle = [];
$estadisticas = [];

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
Procesar Resultados
--------------------------------------------------
*/

foreach($resultados as $resultado)
{
    $partido =
        $resultado['partido'];

    $real1 =
        intval(
            $resultado['goles1']
        );

    $real2 =
        intval(
            $resultado['goles2']
        );

    foreach($participantes as $participante)
    {
        $id =
            $participante['id'];

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
                strtolower(
                    trim(
                        $pron['goles1']
                    )
                ) != 'x'
                &&
                strtolower(
                    trim(
                        $pron['goles2']
                    )
                ) != 'x'
            )
            {
                $pronosticoValido = true;

                $pred1 =
                    intval(
                        $pron['goles1']
                    );

                $pred2 =
                    intval(
                        $pron['goles2']
                    );
            }
        }

        /*
        ------------------------------------------
        Puntuación
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

        /*
        ------------------------------------------
        Acumulado
        ------------------------------------------
        */

        $puntos[$id] +=
            $puntosPartido;

        $evolucion[$id][] =
            $puntos[$id];

        /*
        ------------------------------------------
        Historial
        ------------------------------------------
        */

        $detalle[] = [

            'participante' =>
                $id,

            'partido' =>
                $partido,

            'equipo1' =>
                $resultado['equipo1'],

            'equipo2' =>
                $resultado['equipo2'],

            'fase' =>
                $resultado['fase'] ?? '',

            'pronostico' =>
                $pronosticoTexto,

            'resultado' =>
                $real1
                .
                '-'
                .
                $real2,

            'puntos' =>
                $puntosPartido

        ];
    }
}

/*
--------------------------------------------------
Cerrar SQLite
--------------------------------------------------
*/

$db->close();

/*
--------------------------------------------------
Return
--------------------------------------------------
*/

return [

    'puntos' =>
        $puntos,

    'evolucion' =>
        $evolucion,

    'participantes' =>
        $participantes,

    'detalle' =>
        $detalle,

    'estadisticas' =>
        $estadisticas

];
?>
