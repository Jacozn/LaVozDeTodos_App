<?php
    header('Content-Type: application/json; charset=utf-8');
    
    if ($_SERVER["REQUEST_METHOD"] == "GET") {

        if (!isset($_GET['idUsuario']) || !is_numeric($_GET['idUsuario'])) {
            echo json_encode([
                "success" => false,
                "message" => "Falta el parámetro idUsuario o no es un valor válido."
            ]);
            exit;
        }
        $idAutor = intval($_GET['idUsuario']);

        require('conexion.php');
        $cnx = new Conexion();
        $conexion = $cnx->AbrirConexion(); 
        
        if ($conexion === false || !($conexion instanceof PDO)) {
            echo json_encode([
                "success" => false,
                "message" => "Error de conexión: El objeto de conexión no es válido o falló al inicializar."
            ]);
            exit;
        }

        try {
            $query = "EXEC spObtenerDenunciasPorUsuario @inIdAutor = :idAutor";
            $stmt = $conexion->prepare($query);
            
            $stmt->bindParam(':idAutor', $idAutor, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $denuncias = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    "success" => true,
                    "denuncias" => $denuncias 
                ]); 
                
            } else {
                $pdoError = $stmt->errorInfo();
                error_log("Error de PDO al ejecutar spObtenerDenunciasPorUsuario: " . $pdoError[2]);
                echo json_encode([
                    "success" => false,
                    "message" => "Error al ejecutar la consulta: " . $pdoError[2]
                ]);
            }

        } catch (PDOException $e) {
            error_log("Error de base de datos (PDO): " . $e->getMessage());
            echo json_encode([
                "success" => false,
                "message" => "Error de base de datos (PDO): " . $e->getMessage()
            ]);
        } finally {
             if (isset($cnx)) {
                 $cnx->CerrarConexion($conexion);
             }
        }
        
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Método no permitido. Solo se acepta GET."
        ]);
    }
?>