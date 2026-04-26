<?php
    header('Content-Type: application/json; charset=utf-8');
    
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        require('conexion.php');        
        $cnx = new Conexion();
        $conexion = $cnx->AbrirConexion(); 
        if ($conexion === false || !($conexion instanceof PDO)) {
             echo json_encode([
                "status" => "error",
                "message" => "Error de conexión: El objeto de conexión no es válido o falló al inicializar."
            ]);
            exit;
        }

        try {
            $query = "EXEC spMostrarEventos";
            $stmt = $conexion->prepare($query);

            if ($stmt->execute()) {
                $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode([
                    "success" => true,
                    "eventos" => $eventos
                ]); 
                
            } else {
                $pdoError = $stmt->errorInfo();
                echo json_encode([
                    "success" => false,
                    "message" => "Error al ejecutar la consulta: " . $pdoError[2]
                ]);
            }

        } catch (PDOException $e) {
            echo json_encode([
                "success" => false,
                "message" => "Error de base de datos (PDO): " . $e->getMessage()
            ]);
        }
        
        $cnx->CerrarConexion($conexion);
        
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Método no permitido. Solo se acepta GET."
        ]);
    }
?>
