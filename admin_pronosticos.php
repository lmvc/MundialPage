<?php

session_start();

require_once "includes/config.php";

/*
--------------------------------------------------
CONFIG ADMIN
--------------------------------------------------
*/

define(
    'ADMIN_PASSWORD_HASH',
    '$2y$12$N5qKYBuUbtuE/17/NdBe1u6R341TlUGpAFMulIYVS96OXcWHNBY9i'
);

/*
--------------------------------------------------
LOGIN ADMIN
--------------------------------------------------
*/

if (
    isset($_POST['admin_login'])
) {
    if (
        password_verify(
            $_POST['password'],
            ADMIN_PASSWORD_HASH
        )
    ) {
        $_SESSION['admin'] = true;
    } else {
        $error =
            "Clave incorrecta";
    }
}

if (
    isset($_GET['logout'])
) {
    session_destroy();

    header(
        "Location: admin_pronosticos.php"
    );

    exit;
}

/*
--------------------------------------------------
SI NO ESTÁ LOGUEADO
--------------------------------------------------
*/

if (
    !isset($_SESSION['admin'])
) {
?>

    <!DOCTYPE html>

    <html lang="es">

    <head>

        <meta charset="UTF-8">

        <title>Admin Pronósticos</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    </head>

    <body class="bg-light">

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

                        <a class="nav-link active"
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
                            href="capturar_pronosticos.php">

                            ⚽ Registrar Pronósticos

                        </a>

                    </li>

                    <!-- <li class="nav-item ms-2">

                        <a
                            class="btn btn-danger fw-bold"
                            href="admin_pronosticos.php">

                            ⚙ Admin

                        </a>

                    </li> -->

                </ul>

            </div>

        </div>

    </nav>

        <div class="container mt-5">

            <div class="card shadow">

                <div class="card-body">

                    <h3>

                        🔐 Acceso Administrador

                    </h3>

                    <?php
                    if (isset($error)) {
                        echo
                        "<div class='alert alert-danger'>
        {$error}
    </div>";
                    }
                    ?>

                    <form method="post">

                        <input
                            type="password"
                            name="password"
                            class="form-control mb-3"
                            placeholder="Clave de administrador"
                            required>

                        <button
                            class="btn btn-primary"
                            name="admin_login">

                            Ingresar

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </body>

    </html>
<?php
    exit;
}

## /*

## SQLITE

## */

$db =
    new SQLite3(
        DATA_PATH .
            'quiniela.db'
    );

$db->busyTimeout(5000);

## /*

## GUARDAR

##*/

if (
    isset($_POST['guardar'])
) {
    $participante =
        intval(
            $_POST['participante']
        );


    $partido =
        intval(
            $_POST['partido']
        );

    $g1 =
        trim(
            $_POST['goles1']
        );

    $g2 =
        trim(
            $_POST['goles2']
        );

    if (
        $g1 === ''
        ||
        $g2 === ''
    ) {
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
        $participante,
        SQLITE3_INTEGER
    );

    $stmt->bindValue(
        ':partido',
        $partido,
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

    $guardado = true;
}

## /*

## PARTICIPANTES

## */

$participantes = [];

$result =
    $db->query(
        "
SELECT *
FROM participantes
ORDER BY nombre
"
    );

while (
    $fila =
    $result->fetchArray(
        SQLITE3_ASSOC
    )
) {
    $participantes[] =
        $fila;
}

## /*

## PARTIDOS

## */

$partidos = [];

$result =
    $db->query(
        "
SELECT *
FROM partidos
ORDER BY partido DESC
"
    );

while (
    $fila =
    $result->fetchArray(
        SQLITE3_ASSOC
    )
) {
    $partidos[] =
        $fila;
}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>

        ⚙ Administración de Pronósticos

    </title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

    <div class="container mt-4">

        <div class="d-flex justify-content-between">

            <h2>

                ⚙ Administración de Pronósticos

            </h2>

            <a
                class="btn btn-danger"
                href="?logout=1">

                Cerrar sesión

            </a>

        </div>

        <hr>

        <div class="card">

            <div class="card-body">

                <form method="post">

                    <div class="row">

                        <div class="col-md-4">

                            <label>

                                Participante

                            </label>

                            <select
                                name="participante"
                                class="form-select"
                                required>

                                <?php

                                foreach (
                                    $participantes
                                    as $p
                                ) {
                                    echo
                                    "<option value='{$p['id']}'>
        {$p['nombre']}
     </option>";
                                }

                                ?>

                            </select>

                        </div>

                        <div class="col-md-4">

                            <label>

                                Partido

                            </label>

                            <select
                                name="partido"
                                class="form-select"
                                required>

                                <?php

                                foreach (
                                    $partidos
                                    as $p
                                ) {
                                    echo
                                    "<option value='{$p['partido']}'>
        {$p['partido']} -
        {$p['equipo1']}
        vs
        {$p['equipo2']}
     </option>";
                                }

                                ?>

                            </select>

                        </div>

                        <div class="col-md-2">

                            <label>

                                Goles 1

                            </label>

                            <input
                                type="text"
                                name="goles1"
                                class="form-control">

                        </div>

                        <div class="col-md-2">

                            <label>

                                Goles 2

                            </label>

                            <input
                                type="text"
                                name="goles2"
                                class="form-control">

                        </div>

                    </div>

                    <div class="mt-3">

                        <button
                            class="btn btn-success"
                            name="guardar">

                            💾 Guardar Pronóstico

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php if (isset($guardado)): ?>

        <script>
            Swal.fire({

                icon: 'success',

                title: 'Pronóstico guardado',

                text: 'El registro fue actualizado correctamente.'

            });
        </script>

    <?php endif; ?>

</body>

</html>

<?php

$db->close();

?>