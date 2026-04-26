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
        
        if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
             echo json_encode([
                 "success" => false,
                 "message" => "Debe adjuntar una imagen para la denuncia."
             ]);
             exit;
        }
        
        $fileTmpPath = $_FILES['imagen']['tmp_name'];
        $fileExtension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        
        $newFileName = uniqid('denuncia_', true) . '.' . strtolower($fileExtension);
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
        
        $idEstado = 2;         
        $idCategoria = 1;
        $autor = $_POST['autor'] ?? null;
        $titulo = $_POST['titulo'] ?? '';
        $contenido = $_POST['contenido'] ?? '';
        $ubicacion = $_POST['ubicacion'] ?? '';
        $latitud = $_POST['latitud'] ?? null;
        $longitud = $_POST['longitud'] ?? null;
        $idSubcategoria = $_POST['idSubcategoria'] ?? null;

        try {
            $query = "EXEC spCrearDenuncia 
                         @inIdEstado = ?, 
                         @inIdCategoria = ?, 
                         @inAutor = ?, 
                         @inTitulo = ?, 
                         @inContenido = ?, 
                         @inImagen = ?, 
                         @inUbicacion = ?,
                         @inLatitud = ?, 
                         @inLongitud = ?, 
                         @inIdSubcategoria = ?";
            
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
                $idSubcategoria
            ];

            $ejecucion_exitosa = $stmt->execute($params);

            if ($ejecucion_exitosa) {
                $stmt->nextRowset();
                $resultadoSP = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($resultadoSP === false || !isset($resultadoSP['idPublicacion'])) {
                     $idInsertado = null;
                     echo json_encode([
                         'success' => false, 
                         "message" => "Error interno: El ID de la denuncia no pudo ser recuperado del SP."
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
                    'message' => 'Denuncia creada exitosamente.',
                    'data' => [
                        'idDenunciaRemota' => $idInsertado,
                        'estado' => $estado,
                        'fechaCreacion' => $fechaCreacion,
                        'imagen_url_servidor' => $imagenUrl
                    ]
                ]);
            } else {
                $pdoError = $stmt->errorInfo();
                echo json_encode([
                    'success' => false, 
                    "message" => "Error de BD al crear la denuncia: " . $pdoError[2]
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
            "success" => false,
            "message" => "Método no permitido. Solo se acepta POST."
        ]);
    }
?>

