<?php
    header('Content-Type: application/json; charset=utf-8');

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        
        require ('conexion.php');
        $cnx = new Conexion();
        $conexion = $cnx->AbrirConexion();
        
        if ($conexion === false || !($conexion instanceof PDO)) {
             echo json_encode([
                 "success" => false,
                 "message" => "Error de conexión: El objeto de conexión no es válido o falló al inicializar."
             ]);
             exit;
        }

        $idUsuario = $_POST['idUsuario'] ?? null;
        $idEvento = $_POST['idEvento'] ?? null;
        $confirmado = 1;
        
        if (empty($idUsuario) || empty($idEvento)) {
             echo json_encode([
                 'success' => false, 
                 'message' => 'Faltan parámetros requeridos (idUsuario o idEvento).'
             ]);
             $cnx->CerrarConexion($conexion);
             exit;
        }

        $query = "EXEC spInscripcion @inIdUsuario = ?, @inIdEvento = ?, @inConfirmado = ?;"; 

        try {
            $stmt = $conexion->prepare($query);
            
            $params = [
                $idUsuario, 
                $idEvento,
                $confirmado
            ];
            
            $ejecucion_exitosa = $stmt->execute($params);

            if ($ejecucion_exitosa) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Inscripción realizada exitosamente.'
                ]);
            } else {
                $pdoError = $stmt->errorInfo();
                echo json_encode([
                    'success' => false, 
                    'message' => 'Error de BD al registrar la inscripción. Código: ' . $pdoError[1] . ' | Mensaje: ' . $pdoError[2]
                ]);
            }
            
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false, 
                "message" => "Error de base de datos (PDO): " . $e->getMessage()
            ]);
        } finally {
            $cnx->CerrarConexion($conexion); 
        }
        
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Método de solicitud no permitido. Use POST.'
        ]);
    }
?>