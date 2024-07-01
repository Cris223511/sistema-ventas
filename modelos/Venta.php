<?php
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

class Venta
{
	//Implementamos nuestro constructor
	public function __construct()
	{
	}

	public function insertar($idusuario, $idcliente, $tipo_comprobante, $num_comprobante, $impuesto, $total_venta, $vuelto, $comentario_interno, $comentario_externo, $detalles, $cantidad, $precio_venta, $descuento, $metodo_pago, $monto)
	{
		// Inicializar variable de mensaje
		$mensajeError = "";

		// Convertir $detalles a un array si es una cadena JSON
		$detalles = json_decode($detalles, true);

		// Validaciones
		$error = $this->validarStock($detalles, $cantidad);
		if ($error) {
			$mensajeError = "Una de las cantidades superan al stock 0 del artículo.";
		}

		$error = $this->validarSubtotalNegativo($detalles, $cantidad, $precio_venta, $descuento);
		if ($error) {
			$mensajeError = "El subtotal de uno de los artículos no puede ser menor a 0.";
		}

		// Si hay un mensaje de error, retornar false y mostrar el mensaje en el script principal
		if ($mensajeError !== "") {
			return $mensajeError;
		}

		// Continuar con el registro de la venta
		$sql = "INSERT INTO venta (idusuario,idcliente,tipo_comprobante,num_comprobante,fecha_hora,impuesto,total_venta,vuelto,comentario_interno,comentario_externo,estado,eliminado)
		VALUES ('$idusuario','$idcliente','$tipo_comprobante','$num_comprobante',SYSDATE(),'$impuesto','$total_venta','$vuelto','$comentario_interno','$comentario_externo','Finalizado','0')";
		$idventanew = ejecutarConsulta_retornarID($sql);

		$sw = true;

		foreach ($detalles as $i => $detalle) {
			$esArticulo = strpos($detalle, '_producto') !== false;

			$id = str_replace(['_producto'], '', $detalle);

			$cantidadItem = $cantidad[$i];
			$precioVentaItem = $precio_venta[$i];
			$descuentoItem = $descuento[$i];

			$idArticulo = $esArticulo ? $id : 0;

			$sql_detalle = "INSERT INTO detalle_venta(idventa,idarticulo,cantidad,precio_venta,descuento,impuesto,fecha_hora) VALUES ('$idventanew','$idArticulo','$cantidadItem','$precioVentaItem','$descuentoItem','$impuesto',SYSDATE())";

			ejecutarConsulta($sql_detalle) or $sw = false;

			$actualizar_art = "UPDATE articulo SET precio_venta='$precioVentaItem' WHERE idarticulo='$id'";
			ejecutarConsulta($actualizar_art) or $sw = false;
		}

		$num_elementos = 0;

		while ($num_elementos < count($metodo_pago)) {
			$sql_detalle = "INSERT INTO detalle_venta_pagos(idventa,idmetodopago,monto) VALUES ('$idventanew','$metodo_pago[$num_elementos]','$monto[$num_elementos]')";
			ejecutarConsulta($sql_detalle) or $sw = false;

			$num_elementos = $num_elementos + 1;
		}

		return [$sw, $idventanew];
	}

	public function validarStock($detalles, $cantidad)
	{
		if (!is_array($detalles)) {
			$detalles = json_decode($detalles, true);
		}

		$idarticulos = array_filter($detalles, function ($detalle) {
			return strpos($detalle, '_producto') !== false;
		});

		foreach ($idarticulos as $indice => $idarticulo) {
			$id = str_replace('_producto', '', $idarticulo);
			$sql = "SELECT stock FROM articulo WHERE idarticulo = '$id'";
			$res = ejecutarConsultaSimpleFila($sql);
			$stockActual = $res['stock'];
			if ($cantidad[$indice] > $stockActual) {
				return true;
			}
		}
		return false;
	}

	public function validarSubtotalNegativo($detalles, $cantidad, $precio_venta, $descuento)
	{
		if (!is_array($detalles)) {
			$detalles = json_decode($detalles, true);
		}

		$idarticulos_servicios = array_filter($detalles, function ($detalle) {
			return strpos($detalle, '_producto') !== false;
		});

		foreach ($idarticulos_servicios as $indice => $id_detalle) {
			$tipo = '_producto';
			$id = str_replace($tipo, '', $id_detalle);

			if ((($cantidad[$indice] * $precio_venta[$indice]) - $descuento[$indice]) < 0) {
				return true;
			}
		}
		return false;
	}

	//Implementamos un método para cambiar el estado de la venta
	public function cambiarEstado($idventa, $estado)
	{
		$sql = "UPDATE venta SET estado='$estado' WHERE idventa='$idventa'";
		return ejecutarConsulta($sql);
	}

	//Implementamos un método para anular la venta
	public function anular($idventa)
	{
		$sql_anular_venta = "UPDATE venta SET estado='Anulado' WHERE idventa='$idventa'";
		return ejecutarConsulta($sql_anular_venta);
	}

	//Implementamos un método para eliminar la venta
	public function eliminar($idventa)
	{
		$sql_eliminar_venta = "UPDATE venta SET eliminado = '1' WHERE idventa='$idventa'";
		return ejecutarConsulta($sql_eliminar_venta);
	}

	public function listar()
	{
		$sql = "SELECT v.idventa,DATE_FORMAT(v.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,v.idcliente,p.nombre AS cliente,p.tipo_documento AS cliente_tipo_documento,p.num_documento AS cliente_num_documento,p.direccion AS cliente_direccion,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,v.tipo_comprobante,v.num_comprobante,v.total_venta,v.vuelto,v.comentario_interno,v.comentario_externo,v.impuesto,v.estado FROM venta v LEFT JOIN clientes p ON v.idcliente=p.idcliente LEFT JOIN usuario u ON v.idusuario=u.idusuario WHERE v.eliminado = '0' ORDER by v.idventa DESC";
		return ejecutarConsulta($sql);
	}

	public function listarEstado($estado)
	{
		$sql = "SELECT v.idventa,DATE_FORMAT(v.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,v.idcliente,p.nombre AS cliente,p.tipo_documento AS cliente_tipo_documento,p.num_documento AS cliente_num_documento,p.direccion AS cliente_direccion,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,v.tipo_comprobante,v.num_comprobante,v.total_venta,v.vuelto,v.comentario_interno,v.comentario_externo,v.impuesto,v.estado FROM venta v LEFT JOIN clientes p ON v.idcliente=p.idcliente LEFT JOIN usuario u ON v.idusuario=u.idusuario WHERE v.estado = '$estado' AND v.eliminado = '0' ORDER by v.idventa DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorFecha($fecha_inicio, $fecha_fin)
	{
		$sql = "SELECT v.idventa,DATE_FORMAT(v.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,v.idcliente,p.nombre AS cliente,p.tipo_documento AS cliente_tipo_documento,p.num_documento AS cliente_num_documento,p.direccion AS cliente_direccion,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,v.tipo_comprobante,v.num_comprobante,v.total_venta,v.vuelto,v.comentario_interno,v.comentario_externo,v.impuesto,v.estado FROM venta v LEFT JOIN clientes p ON v.idcliente=p.idcliente LEFT JOIN usuario u ON v.idusuario=u.idusuario WHERE DATE(v.fecha_hora) >= '$fecha_inicio' AND DATE(v.fecha_hora) <= '$fecha_fin' AND v.eliminado = '0' ORDER by v.idventa DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorFechaEstado($fecha_inicio, $fecha_fin, $estado)
	{
		$sql = "SELECT v.idventa,DATE_FORMAT(v.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,v.idcliente,p.nombre AS cliente,p.tipo_documento AS cliente_tipo_documento,p.num_documento AS cliente_num_documento,p.direccion AS cliente_direccion,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,v.tipo_comprobante,v.num_comprobante,v.total_venta,v.vuelto,v.comentario_interno,v.comentario_externo,v.impuesto,v.estado FROM venta v LEFT JOIN clientes p ON v.idcliente=p.idcliente LEFT JOIN usuario u ON v.idusuario=u.idusuario WHERE DATE(v.fecha_hora) >= '$fecha_inicio' AND DATE(v.fecha_hora) <= '$fecha_fin' AND v.estado = '$estado' AND v.eliminado = '0' ORDER by v.idventa DESC";
		return ejecutarConsulta($sql);
	}

	public function listarTodosActivos()
	{
		$sql = "SELECT 'metodo_pago' AS tabla, m.idmetodopago AS id, m.titulo AS nombre, m.imagen AS imagen, NULL AS tipo_documento, NULL AS num_documento, NULL AS cantidad, NULL AS codigo_producto, NULL AS precio_venta, NULL AS stock, NULL AS stock_minimo FROM metodo_pago m WHERE m.eliminado='0' AND m.estado='activado'
				UNION
				SELECT 'clientes' AS tabla, c.idcliente AS id, c.nombre AS nombre, NULL AS imagen, c.tipo_documento AS tipo_documento, c.num_documento AS num_documento, NULL AS cantidad, NULL AS codigo_producto, NULL AS precio_venta, NULL AS stock, NULL AS stock_minimo FROM clientes c WHERE c.eliminado='0' AND c.estado='activado'
				UNION
				SELECT 'categoria' AS tabla, ca.idcategoria AS id, ca.titulo AS nombre, NULL AS imagen, NULL AS tipo_documento, NULL AS num_documento, COUNT(CASE WHEN a.eliminado = '0' THEN a.idcategoria END) AS cantidad, NULL AS codigo_producto, NULL AS precio_venta, NULL AS stock, NULL AS stock_minimo FROM categoria ca LEFT JOIN articulo a ON ca.idcategoria = a.idcategoria WHERE ca.eliminado = '0' AND ca.estado='activado' GROUP BY ca.idcategoria, ca.titulo
				UNION
				SELECT 'articulo' AS tabla, a.idarticulo AS id, a.nombre AS nombre, a.imagen AS imagen, NULL AS tipo_documento, NULL AS num_documento, NULL AS cantidad, a.codigo_producto AS codigo_producto, a.precio_venta AS precio_venta, a.stock AS stock, a.stock_minimo AS stock_minimo FROM articulo a WHERE a.eliminado = '0'";
		return ejecutarConsulta($sql);
	}

	public function listarArticulosPorCategoria($idcategoria)
	{
		$sql = "SELECT 'articulo' AS tabla, a.idarticulo AS id, a.nombre AS nombre, a.imagen AS imagen, NULL AS cantidad, a.codigo_producto AS codigo_producto, a.precio_venta AS precio_venta, a.stock AS stock, a.stock_minimo AS stock_minimo FROM articulo a WHERE a.idcategoria = '$idcategoria' AND a.eliminado = '0'";
		return ejecutarConsulta($sql);
	}

	public function listarMetodosDePago()
	{
		$sql = "SELECT 'metodo_pago' AS tabla, m.idmetodopago AS id, m.titulo AS nombre, m.imagen AS imagen, NULL AS cantidad, NULL AS codigo_producto, NULL AS precio_venta, NULL AS stock FROM metodo_pago m WHERE m.eliminado='0' AND m.estado='activado'";
		return ejecutarConsulta($sql);
	}

	public function listarClientes()
	{
		$sql = "SELECT 'clientes' AS tabla, c.idcliente AS id, c.nombre AS nombre, NULL AS imagen, c.tipo_documento AS tipo_documento, c.num_documento AS num_documento, NULL AS cantidad, NULL AS codigo_producto, NULL AS precio_venta, NULL AS stock FROM clientes c WHERE c.eliminado='0' AND c.estado='activado'";
		return ejecutarConsulta($sql);
	}

	public function getLastNumComprobante()
	{
		$sql = "SELECT MAX(num_comprobante) AS last_num_comprobante FROM venta WHERE eliminado = '0'";
		return ejecutarConsulta($sql);
	}

	// MOSTRAR LOS DATOS POR VENTA

	public function listarDetallesVenta($idventa)
	{
		$sql = "SELECT
				  v.idventa,
				  v.idusuario,
				  v.idcliente,
				  u.nombre AS usuario,
				  u.tipo_documento AS tipo_documento_usuario,
				  u.num_documento AS num_documento_usuario,
				  u.direccion AS direccion_usuario,
				  u.telefono AS telefono_usuario,
				  u.email AS email_usuario,
				  c.nombre AS cliente,
				  c.telefono AS telefono,
				  c.tipo_documento AS tipo_documento,
				  c.num_documento AS num_documento,
				  v.tipo_comprobante,
				  v.num_comprobante,
				  DATE_FORMAT(v.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha_hora,
				  v.impuesto,
				  v.total_venta,
				  v.vuelto,
				  v.comentario_interno,
				  v.comentario_externo,
				  v.estado
				FROM venta v
				LEFT JOIN usuario u ON v.idusuario = u.idusuario
				LEFT JOIN clientes c ON v.idcliente = c.idcliente
				WHERE v.idventa = '$idventa'";

		return ejecutarConsulta($sql);
	}

	public function listarDetallesProductoVenta($idventa)
	{
		$sql = "SELECT
				  dv.idventa,
				  dv.idarticulo,
				  a.nombre AS articulo,
				  a.codigo_producto AS codigo_articulo,
				  dv.cantidad,
				  dv.precio_venta,
				  dv.descuento
				FROM detalle_venta dv
				LEFT JOIN articulo a ON dv.idarticulo = a.idarticulo
				WHERE dv.idventa='$idventa'";

		return ejecutarConsulta($sql);
	}

	public function listarDetallesMetodosPagoVenta($idventa)
	{
		$sql = "SELECT
				  dvp.idventa,
				  dvp.idmetodopago,
				  m.titulo AS metodo_pago,
				  dvp.monto
				FROM detalle_venta_pagos dvp
				LEFT JOIN metodo_pago m ON dvp.idmetodopago = m.idmetodopago
				WHERE dvp.idventa='$idventa'";

		return ejecutarConsulta($sql);
	}
}
