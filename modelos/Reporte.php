<?php
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

class Reporte
{
	/* ======================= REPORTE DE VENTAS ======================= */

	public function listarVentas($condiciones = "")
	{
		$sql = "SELECT DISTINCT
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
				LEFT JOIN detalle_venta_pagos dvp ON v.idventa = dvp.idventa
				WHERE $condiciones
				ORDER by v.idventa DESC";

		return ejecutarConsulta($sql);
	}

	/* ======================= REPORTE DE PROFORMAS ======================= */

	public function listarProformas($condiciones = "")
	{
		$sql = "SELECT DISTINCT
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
				LEFT JOIN detalle_proforma_pagos dpp ON p.idproforma = dpp.idproforma
				WHERE $condiciones
				ORDER by p.idproforma DESC";

		return ejecutarConsulta($sql);
	}

	/* ======================= MÉTODOS DE PAGO POR VENTA ======================= */

	public function listarVentasMetodosPago($condiciones = "")
	{
		$sql = "SELECT 
					dvp.idventa,
					mp.titulo AS metodo_pago_titulo,
					dvp.monto AS metodo_pago_monto,
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
					v.estado
				FROM detalle_venta_pagos dvp
				INNER JOIN venta v ON dvp.idventa = v.idventa
				LEFT JOIN metodo_pago mp ON dvp.idmetodopago = mp.idmetodopago
				LEFT JOIN clientes c ON v.idcliente = c.idcliente
				LEFT JOIN usuario u ON v.idusuario = u.idusuario
				WHERE $condiciones
				ORDER BY v.idventa DESC";

		return ejecutarConsulta($sql);
	}

	/* ======================= MÉTODOS DE PAGO POR PROFORMA ======================= */

	public function listarProformasMetodosPago($condiciones = "")
	{
		$sql = "SELECT 
					dpp.idproforma,
					mp.titulo AS metodo_pago_titulo,
					dpp.monto AS metodo_pago_monto,
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
					p.estado
				FROM detalle_proforma_pagos dpp
				INNER JOIN proforma p ON dpp.idproforma = p.idproforma
				LEFT JOIN metodo_pago mp ON dpp.idmetodopago = mp.idmetodopago
				LEFT JOIN clientes c ON p.idcliente = c.idcliente
				LEFT JOIN usuario u ON p.idusuario = u.idusuario
				WHERE $condiciones
				ORDER BY p.idproforma DESC";

		return ejecutarConsulta($sql);
	}

	/* ======================= REPORTE DE ARTICULOS MÁS VENDIDOS ======================= */

	public function listarArticulosMasVendidos($condiciones = "")
	{
		$sql = "SELECT
				  a.idarticulo,
				  a.idusuario,
				  a.idcategoria,
				  SUM(dv.cantidad) as total_cantidad,
				  u.nombre as usuario,
				  u.cargo as cargo,
				  u.cargo,
				  c.titulo as categoria,
				  a.codigo_producto,
				  a.nombre,
				  a.stock,
				  a.stock_minimo,
				  a.descripcion,
				  a.imagen,
				  a.precio_venta,
				  a.estado
				FROM detalle_venta dv
				LEFT JOIN articulo a ON dv.idarticulo = a.idarticulo
				LEFT JOIN categoria c ON a.idcategoria = c.idcategoria
				LEFT JOIN usuario u ON a.idusuario = u.idusuario
				LEFT JOIN venta v ON dv.idventa = v.idventa
				WHERE $condiciones
				GROUP BY dv.idarticulo
				ORDER BY cantidad DESC";

		return ejecutarConsulta($sql);
	}
}
