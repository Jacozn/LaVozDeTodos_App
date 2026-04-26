<?php
    header('Content-Type: application/json; charset=utf-8');
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        require('conexion.php');
        $cnx = new Conexion();
        $conexion = $cnx->AbrirConexion();

        $email = $_REQUEST['email'];
        $contrasena = $_REQUEST['contrasena'];

        try {
            $stmt = $conexion->prepare("
                EXEC spIniciarSesion :email
            ");

            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {

                if ($usuario['contraseña'] == $contrasena) {

                    echo json_encode([
                        "success" => true,
                        "mensaje" => "Inicio de sesión exitoso",
                        "usuario" => [
                            "id_usuario" => $usuario['id'],
                            "nombres" => $usuario['nombres'],
                            "apellidos" => $usuario['apellidos'],
                            "email" => $usuario['email'],
                            "telefono" => $usuario['telefono'],
                            "fechaNacimiento" => $usuario['fechaNacimiento']
                        ]
                    ]);

                } else {
                    echo json_encode([
                        "success" => false,
                        "mensaje" => "Contraseña incorrecta"
                    ]);
                }

            } else {
                echo json_encode([
                    "success" => false,
                    "mensaje" => "Correo no registrado"
                ]);
            }

        } catch (PDOException $e) {
            echo json_encode([
                "success" => false,
                "mensaje" => $e->getMessage()
            ]);
        }

        $cnx->CerrarConexion();

    } else {

        echo json_encode([
            "success" => false,
            "mensaje" => "Método no permitido"
        ]);

    }
?>

