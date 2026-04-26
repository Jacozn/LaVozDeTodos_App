<?php
    header('Content-Type:Application/json; charset="utf-8"');
    if($_SERVER["REQUEST_METHOD"]=="POST"){
        
        require_once ('conexion.php');
        
      	$cnx = new Conexion();
    		
        $conexion = $cnx->AbrirConexion();

    	$idRol = 1;
        $nombres = $_REQUEST['nombres'];
        $apellidos = $_REQUEST['apellidos'];
        $dni = $_REQUEST['dni'];
        $fechaNacimiento = $_REQUEST['fechaNacimiento'];
        $email = $_REQUEST['email'];
        $telefono = $_REQUEST['telefono'];
        $contrasena = $_REQUEST['contrasena'];        
        $direccion = $_REQUEST['direccion'];

        try {
            $stmt = $conexion->prepare("
                EXEC spCrearUsuarios 
                    :idRol,
                    :nombres,
                    :apellidos,
                    :dni,
                    :fechaNacimiento,
                    :email,
                    :telefono,
                    :contrasena,
                    :direccion
            ");

            $stmt->bindParam(':idRol', $idRol);
            $stmt->bindParam(':nombres', $nombres);
            $stmt->bindParam(':apellidos', $apellidos);
            $stmt->bindParam(':dni', $dni);
            $stmt->bindParam(':fechaNacimiento', $fechaNacimiento);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':contrasena', $contrasena);
            $stmt->bindParam(':direccion', $direccion);

            $stmt->execute();

            echo json_encode(["estado" => "ok", "mensaje" => "Cuenta creada exitosamente"]);

        } catch (PDOException $e) {
            echo json_encode(["estado" => "error", "mensaje" => $e->getMessage()]);
        }
        
        $cnx->CerrarConexion($conexion); 	
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Método no permitido. Solo se acepta POST."
        ]);
    }  
?>
