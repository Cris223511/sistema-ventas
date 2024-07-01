<?php
require "../config/Conexion.php";

class Cliente
{
	public function __construct()
	{
	}

	public function agregar($idusuario, $nombre, $tipo_documento, $num_documento, $direccion, $descripcion, $telefono, $email)
	{
		date_default_timezone_set("America/Lima");
		$sql = "INSERT INTO clientes (idusuario, nombre, tipo_documento, num_documento, direccion, descripcion, telefono, email, fecha_hora, estado, eliminado)
				VALUES ('$idusuario','$nombre','$tipo_documento','$num_documento','$direccion','$descripcion','$telefono', '$email', SYSDATE(),'activado','0')";
		return ejecutarConsulta_retornarID($sql);
	}

	public function verificarDniExiste($num_documento)
	{
		$sql = "SELECT * FROM clientes WHERE num_documento = '$num_documento' AND eliminado = '0'";
		$resultado = ejecutarConsulta($sql);
		if (mysqli_num_rows($resultado) > 0) {
			// El número documento ya existe en la tabla
			return true;
		}
		// El número documento no existe en la tabla
		return false;
	}

	public function verificarDniEditarExiste($num_documento, $idcliente)
	{
		$sql = "SELECT * FROM clientes WHERE num_documento = '$num_documento' AND idcliente != '$idcliente' AND eliminado = '0'";
		$resultado = ejecutarConsulta($sql);
		if (mysqli_num_rows($resultado) > 0) {
			// El número documento ya existe en la tabla
			return true;
		}
		// El número documento no existe en la tabla
		return false;
	}

	public function editar($idcliente, $nombre, $tipo_documento, $num_documento, $direccion, $descripcion, $telefono, $email)
	{
		$sql = "UPDATE clientes SET nombre='$nombre',tipo_documento='$tipo_documento',num_documento='$num_documento',direccion='$direccion',descripcion='$descripcion',telefono='$telefono',email='$email' WHERE idcliente='$idcliente'";
		return ejecutarConsulta($sql);
	}

	public function desactivar($idcliente)
	{
		$sql = "UPDATE clientes SET estado='desactivado' WHERE idcliente='$idcliente'";
		return ejecutarConsulta($sql);
	}

	public function activar($idcliente)
	{
		$sql = "UPDATE clientes SET estado='activado' WHERE idcliente='$idcliente'";
		return ejecutarConsulta($sql);
	}

	public function eliminar($idcliente)
	{
		$sql = "UPDATE clientes SET eliminado = '1' WHERE idcliente='$idcliente'";
		return ejecutarConsulta($sql);
	}

	public function mostrar($idcliente)
	{
		$sql = "SELECT * FROM clientes WHERE idcliente='$idcliente'";
		return ejecutarConsultaSimpleFila($sql);
	}

	public function listarClientes()
	{
		$sql = "SELECT c.idcliente, c.nombre, c.tipo_documento, c.num_documento, c.direccion, c.descripcion, c.telefono, c.email, u.idusuario, u.nombre as usuario, u.cargo as cargo,
				DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, c.estado
				FROM clientes c
				LEFT JOIN usuario u ON c.idusuario = u.idusuario
				WHERE c.eliminado = '0' ORDER BY c.idcliente DESC";
		return ejecutarConsulta($sql);
	}

	/* ======================= LISTAR CLIENTES (INCLUIDO AL PUBLICO GENERAL) ======================= */

	public function listarClientesGeneral()
	{
		$sql = "SELECT c.idcliente, c.nombre, c.tipo_documento, c.num_documento, c.direccion, c.descripcion, c.telefono, c.email, u.idusuario, u.nombre as usuario, u.cargo as cargo,
				DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, c.estado
				FROM clientes c
				LEFT JOIN usuario u ON c.idusuario = u.idusuario
				WHERE c.eliminado = '0' ORDER BY c.idcliente DESC";
		return ejecutarConsulta($sql);
	}

	/* ======================= REPORTE DE VENTAS POR CLIENTE ======================= */

	public function listarVentasCliente($idcliente)
	{
		$sql = "SELECT
				  v.idventa,
				  DATE_FORMAT(v.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,
				  v.idcliente,
				  c.nombre AS cliente,
				  c.tipo_documento AS cliente_tipo_documento,
				  c.num_documento AS cliente_num_documento,
				  c.direccion AS cliente_direccion,
				  u.idusuario,
				  u.nombre AS usuario,
				  u.cargo AS cargo,
				  v.tipo_comprobante,
				  v.num_comprobante,
				  v.vuelto,
				  v.impuesto,
				  v.total_venta,
				  v.comentario_interno,
				  v.estado
				FROM venta v
				LEFT JOIN clientes c ON v.idcliente = c.idcliente
				LEFT JOIN usuario u ON v.idusuario = u.idusuario
				WHERE v.idcliente = '$idcliente'
				ORDER by v.idventa DESC";

		return ejecutarConsulta($sql);
	}

	/* ======================= REPORTE DE VENTAS POR CLIENTE ======================= */

	public function listarProformasCliente($idcliente)
	{
		$sql = "SELECT
				  p.idproforma,
				  DATE_FORMAT(p.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,
				  p.idcliente,
				  c.nombre AS cliente,
				  c.tipo_documento AS cliente_tipo_documento,
				  c.num_documento AS cliente_num_documento,
				  c.direccion AS cliente_direccion,
				  u.idusuario,
				  u.nombre AS usuario,
				  u.cargo AS cargo,
				  p.tipo_comprobante,
				  p.num_comprobante,
				  p.vuelto,
				  p.impuesto,
				  p.total_venta,
				  p.comentario_interno,
				  p.estado
				FROM proforma p
				LEFT JOIN clientes c ON p.idcliente = c.idcliente
				LEFT JOIN usuario u ON p.idusuario = u.idusuario
				WHERE p.idcliente = '$idcliente'
				ORDER by p.idproforma DESC";

		return ejecutarConsulta($sql);
	}
}
