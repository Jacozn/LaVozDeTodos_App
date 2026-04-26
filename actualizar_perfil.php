<?php
    header('Content-Type: application/json; charset=utf-8');
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['id']) && isset($_POST['nombres']) && isset($_POST['apellidos']) && 
            isset($_POST['telefono']) && isset($_POST['fechaNacimiento'])) {
            
            require('conexion.php');
            
            $id = $_POST['id'];
            $nombres = $_POST['nombres']; 
            $apellidos = $_POST['apellidos'];
            $telefono = $_POST['telefono'];
            $fechaNacimiento = $_POST['fechaNacimiento'];

            $cnx = new Conexion();
            $conexion = $cnx->AbrirConexion(); 

            if ($conexion === false || !($conexion instanceof PDO)) {
                 echo json_encode([
                    "status" => "error",
                    "message" => "Error de conexión: El objeto de conexión no es válido."
                ]);
                exit;
            }

            try {
                $query = "EXEC spActualizarPerfil @inId = ?, @inNombres = ?, @inApellidos = ?, @inTelefono = ?, @inFechaNacimiento = ?";
                $stmt = $conexion->prepare($query);

                $params = [
                    $id, 
                    $nombres, 
                    $apellidos, 
                    $telefono, 
                    $fechaNacimiento
                ];

                $ejecucion_exitosa = $stmt->execute($params);
                
                $filas_afectadas = $stmt->rowCount(); 

                if ($ejecucion_exitosa && $filas_afectadas > 0) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "Perfil actualizado correctamente."
                    ]);

                } elseif ($ejecucion_exitosa && $filas_afectadas === 0) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Perfil no actualizado. El ID de usuario no fue encontrado."
                    ]);
                } else {
                    $pdoError = $stmt->errorInfo();
                    echo json_encode([
                        "status" => "error",
                        "message" => "Error al ejecutar la actualización: " . $pdoError[2] 
                    ]);
                }

            } catch (PDOException $e) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error de base de datos (PDO): " . $e->getMessage()
                ]);
            }

            $cnx->CerrarConexion($conexion);
            
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Faltan parámetros requeridos para actualizar el perfil."
            ]);
        }
        
    } else {
        // Error si no es POST
        echo json_encode([
            "status" => "error",
            "message" => "Método no permitido. Solo se acepta POST."
        ]);
    }
?>