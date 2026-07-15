<?php

require_once "config.php";

$archivo = DATA_PATH . 'quiniela.db';

$MINUTOS_ACTUALIZACION = 180; // 3 horas (180)

$actualizar = false;

if(!file_exists($archivo))
{
    $actualizar = true;
}
else
{
    $edad =
        time()
        -
        filemtime(
            $archivo
        );

    if($edad > $MINUTOS_ACTUALIZACION * 60)
    {
        $actualizar = true;
    }
}

if($actualizar)
{
    include
    __DIR__
    .
    "/actualizar_resultados.php";
}