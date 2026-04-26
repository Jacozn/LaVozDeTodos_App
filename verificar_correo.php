<?php
    header('Content-Type: application/json; charset=utf-8');

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        require('conexion.php');
        $cnx = new Conexion();
        $conexion = $cnx->AbrirConexion();

        $email = $_REQUEST['email'];

        try {
            $stmt = $conexion->prepare("
                EXEC spVerificarCorreo :email
            ");

            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                echo json_encode([
                    "estado" => "ok",
                    "mensaje" => "Correo válido"
                ]);
            } else {
                echo json_encode([
                    "estado" => "error",
                    "mensaje" => "Correo no registrado"
                ]);
            }

        } catch (PDOException $e) {
            echo json_encode([
                "estado" => "error",
                "mensaje" => $e->getMessage()
            ]);
        }

        $cnx->CerrarConexion();

    } else {
        echo json_encode([
            "estado" => "error",
            "mensaje" => "Método no permitido"
        ]);
    }
?>