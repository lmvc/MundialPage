<?php


require_once "includes/config.php";

session_start();

/*
--------------------------------------------------
FUNCIONES
--------------------------------------------------
*/

// function leerCSV($archivo)
// {
//     $datos = [];

//     if (!file_exists($archivo))
//         return $datos;

//     $fp = fopen($archivo, "r");

//     $cabecera =
//         fgetcsv(
//             $fp,
//             1000,
//             ",",
//             '"',
//             "\\"
//         );

//     while (
//         ($fila =
//             fgetcsv(
//                 $fp,
//                 1000,
//                 ",",
//                 '"',
//                 "\\"
//             )) !== false
//     ) {
//         if (
//             count($fila)
//             !=
//             count($cabecera)
//         ) {
//             continue;
//         }

//         $datos[] =
//             array_combine(
//                 $cabecera,
//                 $fila
//             );
//     }

//     fclose($fp);

//     return $datos;
// }

/*
--------------------------------------------------
ARCHIVOS
--------------------------------------------------
*/

$db = new SQLite3(
    DATA_PATH . "quiniela.db"
);

$db->busyTimeout(5000);

/*
--------------------------------------------------
PARTICIPANTES
--------------------------------------------------
*/

$participantes = [];

$result =
$db->query(
"
SELECT *
FROM participantes
ORDER BY nombre
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
PARTIDOS
--------------------------------------------------
*/

$partidos = [];

$result =
$db->query(
"
SELECT *
FROM partidos
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
    $partidos[] =
        $fila;
}

// $archivoParticipantes =
//     DATA_PATH . "participantes.csv";

// $archivoPartidos =
//     DATA_PATH . "partidos.csv";

// $archivoPronosticos =
//     DATA_PATH . "pronosticos.csv";

// $participantes =
//     leerCSV(
//         $archivoParticipantes
//     );

// $partidos =
//     leerCSV(
//         $archivoPartidos
//     );

// $pronosticos =
//     leerCSV(
//         $archivoPronosticos
//     );

/*
--------------------------------------------------
LOGIN
--------------------------------------------------
*/

if (isset($_POST['login'])) 
{
    $id =
        trim(
            $_POST['participante']
        );

    $clave =
        trim(
            $_POST['clave']
        );

    $stmt =    
        $db->prepare(
        "
        SELECT *
        FROM participantes
        WHERE id = :id
        "
    );

    $stmt->bindValue(
        ':id',
        intval($id),
        SQLITE3_INTEGER
    );

    $result = $stmt->execute();

    $p = $result->fetchArray(
        SQLITE3_ASSOC
    );

    if(
        $p &&
        password_verify(
            $clave,
            $p['password_hash']
        )
    )
    {

    /*
    --------------------------------------------------
    COMPLETAR PARTIDOS FALTANTES
    --------------------------------------------------
    */

    $stmt =
    $db->prepare(
    "
    INSERT OR IGNORE INTO pronosticos
    (
        participante,
        partido,
        goles1,
        goles2
    )
    SELECT
        :participante,
        partido,
        'x',
        'x'
    FROM partidos
    WHERE
    (
        estado <> 'STATUS_SCHEDULED'
        OR
        datetime(
            substr(fecha,7,4)||'-'||
            substr(fecha,4,2)||'-'||
            substr(fecha,1,2)||' '||
            substr(fecha,12,5)
        ) <= datetime('now')
    )
    "
    );

    $stmt->bindValue(
        ':participante',
        intval($id),
        SQLITE3_INTEGER
    );

    $stmt->execute();


        $_SESSION['participante'] = $id;

        header(
            "Location: capturar_pronosticos.php"
        );

        exit;
    }

    $error =
    "Clave incorrecta";
    // foreach ($participantes as $p ) 
    // {
    //     if (
    //         $p['id'] == $id
    //         &&
    //         password_verify(
    //             $clave,
    //             $p['password_hash']
    //         )
    //     ) {
    //         $_SESSION['participante'] =
    //             $id;

    //         header(
    //             "Location: capturar_pronosticos.php"
    //         );

    //         exit;
    //     }
    // }

    // $error =
    //     "Clave incorrecta";
}

/*
--------------------------------------------------
GUARDAR
--------------------------------------------------
*/

if(
    isset($_POST['guardar'])
    &&
    isset($_SESSION['participante'])
)
{
    $id =
    $_SESSION['participante'];

    $db->exec(
        "BEGIN IMMEDIATE TRANSACTION"
    );

    try
    {
        foreach(
            $partidos as $partido
        )
        {
            $num =
                $partido['partido'];

            $inicio =
                DateTime::createFromFormat(
                    'd-m-Y H:i',
                    trim(
                        $partido['fecha']
                    )
                );

            $inicioTimestamp =
                $inicio
                ?
                $inicio->getTimestamp()
                :
                0;

            $limite =
                $inicioTimestamp - 300;

            $estado =
                strtoupper(
                    trim(
                        $partido['estado']
                    )
                );

            if(
                time() > $limite
                ||
                $estado != 'STATUS_SCHEDULED'
            )
            {
                continue;
            }

            $g1 =
                trim(
                    $_POST[
                        'g1_'.$num
                    ] ?? ''
                );

            $g2 =
                trim(
                    $_POST[
                        'g2_'.$num
                    ] ?? ''
                );

            if(
                $g1 === ''
                ||
                $g2 === ''
            )
            {
                $g1 = 'x';
                $g2 = 'x';
            }

            $stmt =
            $db->prepare(
            "
            INSERT OR REPLACE INTO
            pronosticos
            (
                participante,
                partido,
                goles1,
                goles2
            )
            VALUES
            (
                :participante,
                :partido,
                :goles1,
                :goles2
            )
            "
            );

            $stmt->bindValue(
                ':participante',
                intval($id),
                SQLITE3_INTEGER
            );

            $stmt->bindValue(
                ':partido',
                intval($num),
                SQLITE3_INTEGER
            );

            $stmt->bindValue(
                ':goles1',
                $g1,
                SQLITE3_TEXT
            );

            $stmt->bindValue(
                ':goles2',
                $g2,
                SQLITE3_TEXT
            );

            $stmt->execute();
        }

        
        $stmt =
        $db->prepare(
        "
        INSERT OR IGNORE INTO pronosticos
        (
            participante,
            partido,
            goles1,
            goles2
        )
        SELECT
            :participante,
            partido,
            'x',
            'x'
        FROM partidos
        "
        );

        $stmt->bindValue(
            ':participante',
            intval($id),
            SQLITE3_INTEGER
        );

        $stmt->execute();


        $db->exec(
            "COMMIT"
        );

        $guardadoExitosamente =
            true;
    }
    catch(Exception $e)
    {
        $db->exec(
            "ROLLBACK"
        );

        $error =
            $e->getMessage();
    }
}

// if (isset($_POST['guardar'])  &&  isset($_SESSION['participante'])) 
// {
//     $id = $_SESSION['participante'];

//     /*
//     índice existente
//     */

//     $indice = [];

//     foreach ($pronosticos as $k => $p) 
//     {
//         $indice[$p['participante']][$p['partido']] = $k;
//     }

//     foreach($partidos as $partido)
//     {
//         $num = $partido['partido'];


//         /*
//         --------------------------------------------------
//         VALIDAR CIERRE
//         --------------------------------------------------
//         */

//         $inicio =
//             DateTime::createFromFormat(
//                 'd-m-Y H:i',
//                 trim($partido['fecha'])
//             );

//         $inicioTimestamp =
//             $inicio
//             ?
//             $inicio->getTimestamp()
//             :
//             0;

//         $limite = $inicioTimestamp - 300;

//         $estado =
//             strtoupper(
//                 trim(
//                     $partido['estado']
//                 )
//             );

//         if( time() > $limite || $estado != 'STATUS_SCHEDULED')
//         {
//             continue;
//         }

//         /*
//         --------------------------------------------------
//         LEER FORMULARIO
//         --------------------------------------------------
//         */

//         $g1 =
//             trim(
//                 $_POST[
//                     'g1_'.$num
//                 ] ?? ''
//             );

//         $g2 =
//             trim(
//                 $_POST[
//                     'g2_'.$num
//                 ] ?? ''
//             );

//         /*
//         --------------------------------------------------
//         SI NO CAPTURÓ NADA
//         GUARDAR x-x
//         --------------------------------------------------
//         */

//         if( $g1 === '' ||  $g2 === '')
//         {
//             $g1 = 'x';
//             $g2 = 'x';
//         }

//         /*
//         --------------------------------------------------
//         ACTUALIZAR O INSERTAR
//         --------------------------------------------------
//         */

//         if( isset( $indice[$id][$num]) )
//         {
//             $k = $indice[$id][$num];

//             $pronosticos[$k]['goles1'] = $g1;

//             $pronosticos[$k]['goles2'] = $g2;
//         }
//         else
//         {
//             $pronosticos[] = [

//                 'participante' => $id,

//                 'partido' => $num,

//                 'goles1' => $g1,

//                 'goles2' => $g2

//             ];
//         }

//     }

//     foreach($partidos as $partido)
//     {
//         $num = $partido['partido'];

//         if( !isset( $indice[$id][$num]))
//         {
//             $pronosticos[] = [

//                 'participante' => $id,

//                 'partido' => $num,

//                 'goles1' => 'x',

//                 'goles2' => 'x'

//             ];
//         }

//     }


//     /*
//     Guardar CSV
//     */

//     $fp = fopen( $archivoPronosticos, "w" );

//     fputcsv(
//         $fp,
//         [
//             'participante',
//             'partido',
//             'goles1',
//             'goles2'
//         ],
//         ",",
//         '"',
//         "\\"
//     );

//     foreach ($pronosticos as $p) 
//     {
//         fputcsv(
//             $fp,
//             $p,
//             ",",
//             '"',
//             "\\"
//         );
//     }

//     fclose($fp);

//     // $mensaje = "Pronósticos guardados";
//     $guardadoExitosamente = true;
// }

/*
--------------------------------------------------
LOGOUT
--------------------------------------------------
*/

if (
    isset($_GET['logout'])
) {
    session_destroy();

    header(
        "Location: capturar_pronosticos.php"
    );

    exit;
}

?>

<!DOCTYPE html>

<html lang="es">


<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1">

    <title>
        Pronósticos Mundial 2026
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link rel="stylesheet"
        href="css/style.css">

</head>

<body>

    <!-- ==========================================
NAVBAR
========================================== -->

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">

        <div class="container">

            <a class="navbar-brand"
                href="index.php">
                🏆 Mundial 2026
            </a>

            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNav">

                <span class="navbar-toggler-icon">
                </span>

            </button>

            <div class="collapse navbar-collapse"
                id="navbarNav">

                <ul class="navbar-nav ms-auto">

                    <li class="nav-item">

                        <a class="nav-link"
                            href="index.php">

                            Inicio

                        </a>

                    </li>


                    <li class="nav-item">

                        <a class="nav-link"
                            href="grafica.php">

                            📈 Evolución de Puntos

                        </a>

                    </li>

                    <li class="nav-item">

                        <a class="nav-link"
                            href="historial.php">

                            📋 Historial

                        </a>

                    </li>

                    <li class="nav-item ms-2">

                        <a
                            class="btn btn-warning text-dark fw-bold"
                            href="#">

                            ⚽ Registrar Pronósticos

                        </a>

                    </li>

                </ul>

            </div>

        </div>

    </nav>


    <div class="container mt-4">

        <?php if (!isset($_SESSION['participante'])): ?>

            <div class="card">

                <div class="card-header">

                    Ingreso

                </div>

                <div class="card-body">

                    <?php
                    if (isset($error)) {
                        echo
                        "<div class='alert alert-danger'>
                            $error
                        </div>";
                    }
                    ?>

                    <form method="post">

                        <div class="mb-3">

                            <label>
                                Participante
                            </label>

                            <select
                                name="participante"
                                class="form-control">

                                <?php

                                foreach (
                                    $participantes as $p
                                ) {
                                    echo
                                    "<option value='{$p['id']}'>
                                        {$p['nombre']}
                                    </option>";
                                }

                                ?>

                            </select>

                        </div>

                        <div class="mb-3">

                            <label>
                                Clave
                            </label>

                            <input
                                type="password"
                                name="clave"
                                class="form-control">

                        </div>

                        <button
                            class="btn btn-primary"
                            name="login">

                            Ingresar

                        </button>

                    </form>

                </div>

            </div>

        <?php else: ?>

            <form method="post">

                <div class="d-flex justify-content-between">

                    <h2>

                        Pronósticos

                    </h2>

                    <a
                        href="?logout=1"
                        class="btn btn-danger">

                        Salir

                    </a>

                </div>

                <hr>

                <?php

                $id = $_SESSION['participante'];

                $existentes = [];

                $stmt =
                $db->prepare(
                    "
                    SELECT *
                    FROM pronosticos
                    WHERE participante = :id
                    "
                );

                $stmt->bindValue(
                    ':id',
                    intval($id),
                    SQLITE3_INTEGER
                );

                $result =
                $stmt->execute();

                while(
                    $fila =
                    $result->fetchArray(
                        SQLITE3_ASSOC
                    )
                )
                {
                    $existentes[
                        $fila['partido']
                    ] = $fila;
                }
                // $existentes = [];

                // foreach ($pronosticos as $p) 
                // {
                //     if (
                //         $p['participante']
                //         == $id
                //     ) {
                //         $existentes[$p['partido']] = $p;
                //     }
                // }

                foreach (
                    $partidos as $partido
                ) {
                    $num =
                        $partido['partido'];


                    $inicio =
                        DateTime::createFromFormat(
                            'd-m-Y H:i',
                            trim($partido['fecha'])
                        );

                    if ($inicio) {
                        $inicioTimestamp =
                            $inicio->getTimestamp();
                    } else {
                        $inicioTimestamp = 0;
                    }

                    $limite =
                        $inicioTimestamp - 300;


                    /*
                    STATUS_SCHEDULED
                    STATUS_IN_PROGRESS
                    STATUS_FULL_TIME
                    */

                    $estado =
                        strtoupper(
                            trim($partido['estado'])
                        );

                    $cerrado =
                        (
                            time() > $limite
                            ||
                            $estado != 'STATUS_SCHEDULED'
                        );

                    $g1 =
                        $existentes[$num]['goles1']
                        ?? '';

                    $g2 =
                        $existentes[$num]['goles2']
                        ?? '';

                ?>

                    <div class="card mb-3">

                        <div class="card-body">

                            <h5>

                                <?= $partido['equipo1'] ?>

                                vs

                                <?= $partido['equipo2'] ?>

                            </h5>

                            <p>

                                <!-- <?= $partido['fecha'] ?> -->
                                <!-- <?= $partido['hora'] ?> -->

                                <?php
                                $fecha =
                                    DateTime::createFromFormat(
                                        'd-m-Y H:i',
                                        $partido['fecha']
                                    );
                                if($fecha)
                                {
                                    $fecha->modify('-6 hours');

                                    echo $fecha->format(
                                        'd/m/Y H:i'
                                    );
                                }
                                else
                                {
                                    echo $partido['fecha'];
                                }
                                
                                ?>

                            </p>

                            <div class="row">

                                <div class="col-md-2">

                                    <input
                                        type="number"
                                        name="g1_<?= $num ?>"
                                        value="<?= $g1 ?>"
                                        class="form-control"

                                        <?= $cerrado ? 'disabled' : '' ?>>

                                </div>

                                <div class="col-md-2">

                                    <input
                                        type="number"
                                        name="g2_<?= $num ?>"
                                        value="<?= $g2 ?>"
                                        class="form-control"

                                        <?= $cerrado ? 'disabled' : '' ?>>

                                </div>

                            </div>

                            <?php

                            if ($cerrado) {
                                echo
                                "<div class='text-danger mt-2'>
                                    Pronóstico cerrado
                                </div>";
                            }

                            ?>

                        </div>

                    </div>

                <?php

                }

                ?>

                <button
                    class="btn btn-success"
                    name="guardar">

                    Guardar Pronósticos

                </button>

            </form>

        <?php endif; ?>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if(isset($guardadoExitosamente)): ?>

        <script>
        
            Swal.fire({

                icon: 'success',

                title: 'Pronósticos guardados',

                text: 'Tus marcadores fueron almacenados correctamente.',

                confirmButtonText: 'Aceptar',

                timer: 3000,

                timerProgressBar: true

            });

        </script>

    <?php endif; ?> 
</body>

</html>