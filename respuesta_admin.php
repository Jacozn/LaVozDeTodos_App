<?php
    header('Content-Type: application/json; charset=utf-8');

    if ($_SERVER["REQUEST_METHOD"] == "GET") {

        if (!isset($_GET['idPublicacion'])) {
            echo json_encode([
                "success" => false,
                "message" => "Falta el parámetro idPublicacion."
            ]);
            exit;
        }

        $idPublicacion = intval($_GET['idPublicacion']);
        if ($idPublicacion <= 0) {
             echo json_encode(["success" => false, "message" => "idPublicacion no válido."]);
             exit;
        }

        require('conexion.php');
        $cnx = new Conexion();
        $conexion = $cnx->AbrirConexion();
        $contenidoRespuesta = '';

        if ($conexion === false || !($conexion instanceof PDO)) {
            echo json_encode([
                "success" => false,
                "message" => "Error de conexión: El objeto de conexión no es válido o falló al inicializar."
            ]);
            exit;
        }

        $query = "EXEC spRespuestaAdmin @inIdPublicacion = :idPublicacion"; 

        try {
            $stmt = $conexion->prepare($query);
            $stmt->bindParam(':idPublicacion', $idPublicacion, PDO::PARAM_INT);
            $stmt->execute();

            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt = null; 

            if ($fila) {
                if (array_key_exists('contenido', $fila) && !empty($fila['contenido'])) {
                    $contenidoRespuesta = $fila['contenido'];
                } elseif (array_key_exists('Contenido', $fila) && !empty($fila['Contenido'])) {
                    $contenidoRespuesta = $fila['Contenido'];
                }
            }

            echo json_encode([
                "success" => true,
                "respuestaAdmin" => $contenidoRespuesta 
            ]);

        } catch (PDOException $e) {
            error_log("Error al ejecutar spRespuestaAdmin: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                "message" => "Error de base de datos al obtener la respuesta del admin: " . $e->getMessage()
            ]);
        } finally {
            $cnx->CerrarConexion($conexion);
        }

    } else {
        echo json_encode([
            "success" => false,
            "message" => "Método no permitido. Solo se acepta GET."
        ]);
    }
?>
