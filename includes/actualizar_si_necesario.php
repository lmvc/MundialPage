<?php

require_once "config.php";

$archivo = DATA_PATH . 'resultados.csv';

$MINUTOS_ACTUALIZACION = 15;

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