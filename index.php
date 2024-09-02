<?php
session_start();

function generarSudoku($dificultad) {
    $sudoku = array_fill(0, 9, array_fill(0, 9, 0));
    rellenarDiagonal($sudoku);
    resolver($sudoku);
    eliminarNumeros($sudoku, $dificultad);
    return $sudoku;
}

function rellenarDiagonal(&$sudoku) {
    for ($i = 0; $i < 9; $i += 3) {
        rellenarBloque($sudoku, $i, $i);
    }
}

function rellenarBloque(&$sudoku, $fila, $columna) {
    $numeros = range(1, 9);
    shuffle($numeros);
    for ($i = 0; $i < 3; $i++) {
        for ($j = 0; $j < 3; $j++) {
            $sudoku[$fila + $i][$columna + $j] = array_pop($numeros);
        }
    }
}

function resolver(&$sudoku) {
    for ($fila = 0; $fila < 9; $fila++) {
        for ($columna = 0; $columna < 9; $columna++) {
            if ($sudoku[$fila][$columna] == 0) {
                for ($num = 1; $num <= 9; $num++) {
                    if (esValido($sudoku, $fila, $columna, $num)) {
                        $sudoku[$fila][$columna] = $num;
                        if (resolver($sudoku)) {
                            return true;
                        }
                        $sudoku[$fila][$columna] = 0;
                    }
                }
                return false;
            }
        }
    }
    return true;
}

function esValido($sudoku, $fila, $columna, $num) {
    for ($i = 0; $i < 9; $i++) {
        if ($sudoku[$fila][$i] == $num) return false;
        if ($sudoku[$i][$columna] == $num) return false;
    }

    $bloqueF = floor($fila / 3) * 3;
    $bloqueC = floor($columna / 3) * 3;
    for ($i = 0; $i < 3; $i++) {
        for ($j = 0; $j < 3; $j++) {
            if ($sudoku[$bloqueF + $i][$bloqueC + $j] == $num) return false;
        }
    }

    return true;
}

function eliminarNumeros(&$sudoku, $dificultad) {
    switch ($dificultad) {
        case 'facil':
            $celdasAEliminar = 30;
            break;
        case 'medio':
            $celdasAEliminar = 40;
            break;
        case 'dificil':
            $celdasAEliminar = 50;
            break;
        default:
            $celdasAEliminar = 40;
    }

    while ($celdasAEliminar > 0) {
        $fila = rand(0, 8);
        $columna = rand(0, 8);
        if ($sudoku[$fila][$columna] != 0) {
            $sudoku[$fila][$columna] = 0;
            $celdasAEliminar--;
        }
    }
}

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['accion'])) {
        if ($_POST['accion'] == 'generar') {
            $dificultad = isset($_POST['dificultad']) ? $_POST['dificultad'] : 'medio';
            $sudokuGenerado = generarSudoku($dificultad);
            $_SESSION['sudoku'] = $sudokuGenerado; // Guardar el Sudoku generado en la sesión
            $_SESSION['dificultad'] = $dificultad;
        } elseif ($_POST['accion'] == 'resolver' && isset($_SESSION['sudoku'])) {
            $sudokuGenerado = $_SESSION['sudoku'];
            resolver($sudokuGenerado); // Resolver el Sudoku guardado
            $dificultad = $_SESSION['dificultad'];
        }
    }
} else {
    $dificultad = 'medio';
    $sudokuGenerado = null;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generador y Solucionador de Sudoku</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Generador y Solucionador de Sudoku</h1>

    <form method="post">
        <label for="dificultad">Selecciona la dificultad:</label>
        <select name="dificultad" id="dificultad">
            <option value="facil" <?php echo $dificultad == 'facil' ? 'selected' : ''; ?>>Fácil</option>
            <option value="medio" <?php echo $dificultad == 'medio' ? 'selected' : ''; ?>>Medio</option>
            <option value="dificil" <?php echo $dificultad == 'dificil' ? 'selected' : ''; ?>>Difícil</option>
        </select>
        <input type="submit" name="accion" value="generar">
    </form>

    <?php
    if ($sudokuGenerado) {
        echo "<h2>Sudoku " . ($_POST['accion'] == 'resolver' ? "resuelto" : "generado") . " (Dificultad: " . ucfirst($dificultad) . ")</h2>";
        echo "<table border='1' cellpadding='3'>";
        for ($i = 0; $i < 9; $i++) {
            echo "<tr>";
            for ($j = 0; $j < 9; $j++) {
                $valor = $sudokuGenerado[$i][$j];
                $clase = ($_POST['accion'] == 'resolver' && $valor != 0 && $valor != $_SESSION['sudoku'][$i][$j]) ? 'resuelto' : '';
                echo "<td class='$clase'><input type='number' value = $valor == 0 ? '&nbsp;' : $valor)></td>";
            }
            echo "</tr>";
        }
        echo "</table>";

        // Mostrar el botón de resolver solo si hay un Sudoku generado y no ha sido resuelto
        if ($_POST['accion'] != 'resolver') {
            echo "<form method='post'>";
            echo "<input type='submit' name='accion' value='resolver'>";
            echo "</form>";
        }
    }
    ?>
</body>
</html>
