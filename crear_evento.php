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

        $imagenUrl = null; 
        $uploadDir = 'uploads/';
        
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            
            $fileTmpPath = $_FILES['imagen']['tmp_name'];
            $fileExtension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            
            $newFileName = uniqid('evento_', true) . '.' . strtolower($fileExtension);
            $destPath = $uploadDir . $newFileName;
            
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true); 
            }
            
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $imagenUrl = $destPath;
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Fallo al guardar la imagen en el servidor. Revise los permisos del directorio 'uploads/'."
                ]); 
                exit;
            }
        }
        
        $idEstado = 2;         
        $idCategoria = 2;
        $autor = $_POST['autor'] ?? null;
        $titulo = $_POST['titulo'] ?? '';
        $contenido = $_POST['contenido'] ?? '';
        $ubicacion = $_POST['ubicacion'] ?? '';
        $latitud = $_POST['latitud'] ?? null;
        $longitud = $_POST['longitud'] ?? null;
        $idSubcategoria = $_POST['idSubcategoria'] ?? null;
        $horaInicio = $_POST['horaInicio'] ?? '';
        $horaFin = $_POST['horaFin'] ?? '';
        $fechaEvento = $_POST['fechaEvento'] ?? ''; 
        $capacidadMaxima = $_POST['capacidadMaxima'] ?? null;

        try {
            $query = "EXEC spCrearEvento 
                         @inIdEstado = ?, 
                         @inIdCategoria = ?, 
                         @inAutor = ?, 
                         @inTitulo = ?, 
                         @inContenido = ?, 
                         @inImagen = ?, 
                         @inUbicacion = ?,
                         @inLatitud = ?, 
                         @inLongitud = ?, 
                         @inIdSubcategoria = ?,
                         @inHoraInicio = ?,
                         @inHoraFin = ?,
                         @inFechaEvento = ?,
                         @inCapacidadMaxima = ?";
            
            $stmt = $conexion->prepare($query);
            
            $params = [
                $idEstado, 
                $idCategoria, 
                $autor, 
                $titulo, 
                $contenido, 
                $imagenUrl,
                $ubicacion,
                $latitud, 
                $longitud,
                $idSubcategoria,
                $horaInicio,
                $horaFin,
                $fechaEvento,
                $capacidadMaxima
            ];

            $ejecucion_exitosa = $stmt->execute($params);

            if ($ejecucion_exitosa) {
                $resultadoSP = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($resultadoSP === false || !isset($resultadoSP['idPublicacion'])) {
                     $idInsertado = null;
                     echo json_encode([
                         'success' => false, 
                         "message" => "Error interno: El ID del Evento no pudo ser recuperado del SP."
                     ]);
                     $cnx->CerrarConexion($conexion); 
                     exit;
                } else {
                    $idInsertado = $resultadoSP['idPublicacion'];
                }
                
                $estado = 'Pendiente'; 
                $fechaCreacion = date('Y-m-d H:i:s');

                echo json_encode([
                    'success' => true, 
                    'message' => 'Evento creado exitosamente.',
                    'data' => [
                        'idEventoRemota' => $idInsertado,
                        'estado' => $estado,
                        'fechaCreacion' => $fechaCreacion,
                        'imagen_url_servidor' => $imagenUrl,
                    ]
                ]);
            } else {
                $pdoError = $stmt->errorInfo();
                echo json_encode([
                    'success' => false, 
                    "message" => "Error de BD al crear el evento. Código: " . $pdoError[1] . " | Mensaje: " . $pdoError[2]
                ]);
                if ($imagenUrl && file_exists($imagenUrl)) {
                    @unlink($imagenUrl);
                }
            }

        } catch (PDOException $e) {
            echo json_encode([
                'success' => false, 
                "message" => "Error de base de datos (PDO): " . $e->getMessage()
            ]);
            if ($imagenUrl && file_exists($imagenUrl)) {
                @unlink($imagenUrl);
            }
        } finally {
            $cnx->CerrarConexion($conexion);    
        }
        
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Método no permitido. Solo se acepta POST."
        ]);
    }
?>

