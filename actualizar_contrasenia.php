<?php
    // Establecer el encabezado JSON
    header('Content-Type: application/json; charset=utf-8');
    
    // Verificar si la solicitud es POST
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
        // 1. Validar la existencia de los parámetros POST
        if (isset($_POST['email']) && isset($_POST['contrasena'])) {
            
            require('conexion.php');
            
            $email = $_POST['email']; 
            $nuevaContrasena = $_POST['contrasena'];

            // Instanciar la conexión
            $cnx = new Conexion();
            $conexion = $cnx->AbrirConexion(); // Retorna el objeto PDO

            if ($conexion === false || !($conexion instanceof PDO)) {
                 // Si la conexión falla o no es un objeto PDO válido
                 echo json_encode([
                    "estado" => "error",
                    "mensaje" => "Error de conexión: El objeto de conexión no es válido."
                ]);
                exit;
            }

            try {
                // 2. Usar PDO: Preparar la consulta
                // El query se mantiene igual, pero la ejecución es diferente
                $query = "EXEC spActualizarContraseña @inEmail = ?, @inNuevaContrasena = ?";
                $stmt = $conexion->prepare($query);

                // 3. Usar PDO: Ejecutar la consulta con los parámetros
                // PDO mapea el orden de los parámetros (?) con el array de execute
                $ejecucion_exitosa = $stmt->execute([$email, $nuevaContrasena]);

                if ($ejecucion_exitosa) {
                    // La ejecución fue exitosa a nivel de sentencia SQL
                    // Aquí podrías agregar lógica si el SP devuelve un resultado (ej: 1 fila afectada)
                    echo json_encode([
                        "estado" => "ok",
                        "mensaje" => "Contraseña actualizada"
                    ]);

                } else {
                    // Si hay un error de ejecución de la consulta
                    $pdoError = $stmt->errorInfo();
                    echo json_encode([
                        "estado" => "error",
                        "mensaje" => "Error al ejecutar la actualización: " . $pdoError[2] // Mensaje de error de la BD
                    ]);
                }

            } catch (PDOException $e) {
                // Capturar cualquier error específico de PDO (ej: error en el SP, error de sintaxis)
                echo json_encode([
                    "estado" => "error",
                    "mensaje" => "Error de base de datos (PDO): " . $e->getMessage()
                ]);
            }

            $cnx->CerrarConexion($conexion);
            
        } else {
            // Error si faltan parámetros (acceso por navegador o solicitud incompleta)
            echo json_encode([
                "estado" => "error",
                "mensaje" => "Faltan parámetros requeridos (email o contrasena) en la solicitud POST."
            ]);
        }
        
    } else {
        // Error si no es POST
        echo json_encode([
            "estado" => "error",
            "mensaje" => "Método no permitido. Solo se acepta POST."
        ]);
    }
?>
