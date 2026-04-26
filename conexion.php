<?php
	class Conexion
	{
		private $usuario;
		private $clave;
		private $servidor;
		private $basedatos;
		private $conexion;
		
		function __construct()
		{
			$this->servidor = "lavozdetodos-app.database.windows.net";
			$this->usuario = "LavozdetodosAdmin";
			$this->clave = "abc123$.";
			$this->basedatos = "db_LaVozDeTodos";
		}
		
		function AbrirConexion()
		{
			try {
				$connectionString = "sqlsrv:server=$this->servidor,1433;Database=$this->basedatos";

				$this->conexion = new PDO(
					$connectionString,
					$this->usuario,
					$this->clave,
					[
						PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
						PDO::SQLSRV_ATTR_DIRECT_QUERY => true,
						PDO::SQLSRV_ATTR_ENCODING => PDO::SQLSRV_ENCODING_UTF8
					]
				);

				return $this->conexion;

			} catch (PDOException $e) {
				return "Error de conexión: " . $e->getMessage();
			}
		}

		function CerrarConexion()
		{
			$this->conexion = null;
		}
	}
?>
