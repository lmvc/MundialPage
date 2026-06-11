<?php

// function leerCSV($archivo)
// {
//     $datos = [];

//     if (($handle = fopen($archivo, "r")) !== FALSE)
//     {
//         $cabecera = fgetcsv($handle);

//         while (($fila = fgetcsv($handle)) !== FALSE)
//         {
//             $datos[] = array_combine($cabecera, $fila);
//         }

//         fclose($handle);
//     }

//     return $datos;
// }
function leerCSV($archivo)
{
    if(!file_exists($archivo))
    {
        die("Archivo no encontrado: ".$archivo);
    }

    $datos = [];

    if(($handle = fopen($archivo, "r")) !== FALSE)
    {
        $cabecera = fgetcsv($handle, 0, ",", "\"", "");

        while(($fila = fgetcsv($handle, 0, ",", "\"", "")) !== FALSE)
        {
            // Ignorar líneas vacías
            // if(
            //     count($fila) == 1 &&
            //     trim($fila[0]) == ''
            // )
            // {
            //     continue;
            // }
            if (
                count($fila) === 1 && ($fila[0] === null || trim((string)$fila[0]) === ''))
            {
                continue;
            }

            // Ignorar filas corruptas
            if(count($fila) != count($cabecera))
            {
                continue;
            }

            $datos[] = array_combine($cabecera, $fila);
        }

        fclose($handle);
    }

    return $datos;
}

function ganador($g1, $g2)
{
    if($g1 > $g2)
        return 1;

    if($g2 > $g1)
        return 2;

    return 0;
}

?>