<?php
    header('Content-Type: application/json; charset=utf-8');
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        
        $idCategoria = 1; 
        
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
            $query = "EXEC spSeleccionarCategoria @inIdCategoria = ?";
            $stmt = $conexion->prepare($query);

            $ejecucion_exitosa = $stmt->execute([$idCategoria]);

            if ($ejecucion_exitosa) {
                $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($categorias) {
                    echo json_encode($categorias); 
                } else {
                    echo json_encode([]); 
                }
            } else {
                $pdoError = $stmt->errorInfo();
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al ejecutar la consulta: " . $pdoError[2]
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
            "message" => "Método no permitido. Solo se acepta GET."
        ]);
    }
?>
