<?php

session_start();


if (!isset($_SESSION["transacciones"])) {
    $_SESSION["transacciones"] = [];
}

function registrarTransaccion($id, $descripcion, $monto)
{
    $_SESSION["transacciones"][] = [
        "id" => $id,
        "descripcion" => $descripcion,
        "monto" => $monto
    ];
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = count($_SESSION["transacciones"]) + 1;
    $descripcion = $_POST["descripcion"];
    $monto = floatval($_POST["monto"]);

    registrarTransaccion($id, $descripcion, $monto);
}


function generarEstado()
{
    $trans = $_SESSION["transacciones"];
    $totalContado = 0;

    foreach ($trans as $t) {
        $totalContado += $t["monto"];
    }

    $interes = $totalContado * 0.026;
    $totalConInteres = $totalContado + $interes;
    $cashback = $totalContado * 0.001;
    $montoFinal = $totalConInteres - $cashback;

 
    $contenido  = "ESTADO DE CUENTA\n";
    $contenido .= "---------------------------\n";

    foreach ($trans as $t) {
        $contenido .= "ID {$t['id']} - {$t['descripcion']} - ₡{$t['monto']}\n";
    }

    $contenido .= "---------------------------\n";
    $contenido .= "Total Contado: ₡" . number_format($totalContado, 2) . "\n";
    $contenido .= "Total con 2.6% interés: ₡" . number_format($totalConInteres, 2) . "\n";
    $contenido .= "Cashback 0.1%: ₡" . number_format($cashback, 2) . "\n";
    $contenido .= "Monto Final a Pagar: ₡" . number_format($montoFinal, 2) . "\n";

    file_put_contents("estado_cuenta.txt", $contenido);

    
    return [
        "trans" => $trans,
        "contado" => $totalContado,
        "interes" => $totalConInteres,
        "cashback" => $cashback,
        "final" => $montoFinal
    ];
}

$estado = generarEstado();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Práctica Programada 3</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-4">

    <h1 class="text-center text-primary mb-4">
        Estado de Cuenta - Tarjeta de Crédito
    </h1>

    <div class="card p-4 shadow-sm mb-5">
        <h4 class="text-center mb-3">Registrar Transacción</h4>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <input type="text" name="descripcion" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Monto</label>
                <input type="number" name="monto" step="0.01" class="form-control" required>
            </div>

            <button class="btn btn-primary w-100">Agregar Transacción</button>
        </form>
    </div>

    <div class="card p-4 shadow-sm mb-4">
        <h4 class="text-center mb-3 text-success">Transacciones Registradas</h4>

        <?php foreach ($estado["trans"] as $t): ?>
            <div class="border rounded p-2 mb-2">
                <strong>ID:</strong> <?= $t["id"] ?> |
                <strong><?= $t["descripcion"] ?></strong> |
                <span class="text-primary">₡<?= number_format($t["monto"], 2) ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card p-4 shadow-sm mb-4">
        <h4 class="text-center text-info">Resumen del Estado de Cuenta</h4>

        <p>Total de Contado: ₡<?= number_format($estado["contado"], 2) ?></p>
        <p>Total con Interés (2.6%): ₡<?= number_format($estado["interes"], 2) ?></p>
        <p>Cashback (0.1%): ₡<?= number_format($estado["cashback"], 2) ?></p>

        <h5 class="mt-3">Monto Final a Pagar:
            <span class="text-danger">₡<?= number_format($estado["final"], 2) ?></span>
        </h5>
    </div>

    <p class="text-center text-muted">
        Archivo generado: estado_cuenta.txt
    </p>

</div>

</body>
</html>
