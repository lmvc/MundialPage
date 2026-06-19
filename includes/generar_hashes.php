<?php

require_once "config.php";
/*
--------------------------------------------------
CONFIGURACIÓN
--------------------------------------------------
*/

$archivoParticipantes =
    DATA_PATH . "participantes.csv";

$archivoClaves =
    DATA_PATH . "claves_temporales.csv";

/*
--------------------------------------------------
LEER CSV
--------------------------------------------------
*/

$participantes = [];

$fp = fopen(
    $archivoParticipantes,
    "r"
);

$cabecera =
    fgetcsv(
        $fp,
        1000,
        ",",
        '"',
        "\\"
    );

while (
    ($fila =
        fgetcsv(
            $fp,
            1000,
            ",",
            '"',
            "\\"
        )) !== false
) {
    if (
        count($fila)
        != count($cabecera)
    ) {
        continue;
    }

    $participantes[] =
        array_combine(
            $cabecera,
            $fila
        );
}

fclose($fp);

/*
--------------------------------------------------
RESPALDO
--------------------------------------------------
*/

copy(
    $archivoParticipantes,
    $archivoParticipantes .
        ".bak"
);

/*
--------------------------------------------------
REESCRIBIR participantes.csv
--------------------------------------------------
*/

$fp =
    fopen(
        $archivoParticipantes,
        "w"
    );

fputcsv(
    $fp,
    [
        'id',
        'nombre',
        'password_hash'
    ],
    ",",
    '"',
    "\\"
);

/*
--------------------------------------------------
ARCHIVO DE CLAVES
--------------------------------------------------
*/

$fpClaves =
    fopen(
        $archivoClaves,
        "w"
    );

fputcsv(
    $fpClaves,
    [
        'id',
        'nombre',
        'password_temporal'
    ],
    ",",
    '"',
    "\\"
);

/*
--------------------------------------------------
GENERAR HASHES
--------------------------------------------------
*/

foreach (
    $participantes as $p
) {
    $id =
        trim(
            $p['id']
        );

    $nombre =
        trim(
            $p['nombre']
        );

    /*
    Eliminar espacios y acentos
    */

    $base =
        iconv(
            'UTF-8',
            'ASCII//TRANSLIT',
            $nombre
        );

    $base =
        preg_replace(
            '/[^A-Za-z0-9]/',
            '',
            $base
        );

    $passwordTemporal =
        $base .
        str_pad(
            random_int(0, 9999),
            4,
            '0',
            STR_PAD_LEFT
        );

    $hash =
        password_hash(
            $passwordTemporal,
            PASSWORD_DEFAULT
        );

    /*
    participantes.csv
    */

    fputcsv(
        $fp,
        [
            $id,
            $nombre,
            $hash
        ],
        ",",
        '"',
        "\\"
    );

    /*
    claves_temporales.csv
    */

    fputcsv(
        $fpClaves,
        [
            $id,
            $nombre,
            $passwordTemporal
        ],
        ",",
        '"',
        "\\"
    );

    echo
    "Generado: "
        .
        $nombre
        .
        " -> "
        .
        $passwordTemporal
        .
        PHP_EOL;
}

fclose($fp);

fclose($fpClaves);

echo PHP_EOL;

echo
"participantes.csv actualizado"
    .
    PHP_EOL;

echo
"claves_temporales.csv generado"
    .
    PHP_EOL;

echo
"Respaldo creado: participantes.csv.bak"
    .
    PHP_EOL;
