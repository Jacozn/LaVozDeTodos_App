<?php
    header('Content-Type: application/json; charset=utf-8');

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        require ('conexion.php');
        $cnx = new Conexion();
        $conexion = $cnx->AbrirConexion();
        $noticias = [];

        if ($conexion === false || !($conexion instanceof PDO)) {
            echo json_encode([
                "success" => false,
                "message" => "Error de conexión: El objeto de conexión no es válido o falló al inicializar."
            ]);
            exit;
        }

        $query = "EXEC spNoticias"; 

        try {
            $stmt = $conexion->query($query);
            
            if ($stmt) {            
                while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {                
                    $noticias[] = [
                        "id" => $fila['id'],
                        "titulo" => $fila['titulo'],
                        "contenido" => $fila['contenido'],
                        "imagenUrl" => $fila['imagenUrl'],
                        "fecha" => $fila['fecha'],
                        "idSubcategoria" => $fila['idSubcategoria'],
                        "nombreAutor" => $fila['nombreAutor'] 
                    ];
                }
                $stmt = null; 
            }
            echo json_encode([
                "success" => true, 
                "noticias" => $noticias
            ]);

        } catch (PDOException $e) {
            error_log("Error al ejecutar spNoticias: " . $e->getMessage());
            echo json_encode([
                'success' => false, 
                "message" => "Error de base de datos al listar noticias: " . $e->getMessage()
            ]);
        } finally {
            $cnx->CerrarConexion($conexion); 
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Método no permitido. Solo se acepta GET."
        ]);
    }
?>

