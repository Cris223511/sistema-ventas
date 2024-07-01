<?php
ob_start();
if (strlen(session_id()) < 1) {
	session_start(); //Validamos si existe o no la sesión
}

if (!isset($_SESSION["nombre"])) {
	header("Location: ../vistas/login.html");
} else {
	if ($_SESSION['ventas'] == 1) {
		require_once "../modelos/Clientes.php";

		$clientes = new Cliente();

		// Variables de sesión a utilizar.
		$idusuario = $_SESSION["idusuario"];
		$cargo = $_SESSION["cargo"];

		$idcliente = isset($_POST["idcliente"]) ? limpiarCadena($_POST["idcliente"]) : "";
		$nombre = isset($_POST["nombre"]) ? limpiarCadena($_POST["nombre"]) : "";
		$tipo_documento = isset($_POST["tipo_documento"]) ? limpiarCadena($_POST["tipo_documento"]) : "";
		$num_documento = isset($_POST["num_documento"]) ? limpiarCadena($_POST["num_documento"]) : "";
		$direccion = isset($_POST["direccion"]) ? limpiarCadena($_POST["direccion"]) : "";
		$descripcion = isset($_POST["descripcion"]) ? limpiarCadena($_POST["descripcion"]) : "";
		$telefono = isset($_POST["telefono"]) ? limpiarCadena($_POST["telefono"]) : "";
		$email = isset($_POST["email"]) ? limpiarCadena($_POST["email"]) : "";

		switch ($_GET["op"]) {
			case 'guardaryeditar':
				if (empty($idcliente)) {
					$nombreExiste = $clientes->verificarDniExiste($num_documento);
					if ($nombreExiste && $num_documento != '') {
						echo "El número de documento que ha ingresado ya existe.";
					} else {
						$rspta = $clientes->agregar($idusuario, $nombre, $tipo_documento, $num_documento, $direccion, $descripcion, $telefono, $email);
						if (is_numeric($rspta)) {
							echo $rspta;
						} else {
							echo "El cliente no se pudo registrar";
						}
					}
				} else {
					$nombreExiste = $clientes->verificarDniEditarExiste($nombre, $idcliente);
					if ($nombreExiste && $num_documento != '') {
						echo "El número de documento que ha ingresado ya existe.";
					} else {
						$rspta = $clientes->editar($idcliente, $nombre, $tipo_documento, $num_documento, $direccion, $descripcion, $telefono, $email);
						echo $rspta ? "Cliente actualizado correctamente" : "El cliente no se pudo actualizar";
					}
				}
				break;

			case 'desactivar':
				$rspta = $clientes->desactivar($idcliente);
				echo $rspta ? "Cliente desactivado" : "El cliente no se pudo desactivar";
				break;

			case 'activar':
				$rspta = $clientes->activar($idcliente);
				echo $rspta ? "Cliente activado" : "El cliente no se pudo activar";
				break;

			case 'eliminar':
				$rspta = $clientes->eliminar($idcliente);
				echo $rspta ? "Cliente eliminado" : "El cliente no se pudo eliminar";
				break;

			case 'mostrar':
				$rspta = $clientes->mostrar($idcliente);
				echo json_encode($rspta);
				break;

			case 'listar':
				$rspta = $clientes->listarClientes();

				$data = array();

				function mostrarBoton($reg, $cargo, $idusuario, $buttonType)
				{
					if ($cargo == "admin" || ($cargo == "vendedor" && $idusuario == $_SESSION["idusuario"])) {
						return $buttonType;
					} else {
						return '';
					}
				}

				while ($reg = $rspta->fetch_object()) {
					$cargo_detalle = "";

					switch ($reg->cargo) {
						case 'admin':
							$cargo_detalle = "Administrador";
							break;
						case 'vendedor':
							$cargo_detalle = "Vendedor";
							break;
						case 'cliente':
							$cargo_detalle = "Cliente";
							break;
						default:
							break;
					}

					$telefono = ($reg->telefono == '') ? 'Sin registrar' : number_format($reg->telefono, 0, '', ' ');
					$reg->descripcion = (strlen($reg->descripcion) > 100) ? substr($reg->descripcion, 0, 100) . "..." : $reg->descripcion;

					$data[] = array(
						"0" => '<div style="display: flex; flex-wrap: nowrap; gap: 3px">' .
							'<button class="btn btn-bcp" style="margin-right: 3px; width: 35px; height: 35px; color: white !important;" onclick="verificarModalCliente(' . $reg->idcliente . ', \'' . $reg->nombre . '\', \'' . $reg->tipo_documento . '\', \'' . $reg->num_documento . '\')"><i class="fa fa-file-text"></i></button>' .
							(($reg->idcliente != 0) ? (mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-warning" style="margin-right: 3px; height: 35px;" onclick="mostrar(' . $reg->idcliente . ')"><i class="fa fa-pencil"></i></button>') .
							(($reg->estado == 'activado') ?
								(mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-danger" style="margin-right: 3px; height: 35px;" onclick="desactivar(' . $reg->idcliente . ')"><i class="fa fa-close"></i></button>')) : (mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-success" style="margin-right: 3px; width: 35px; height: 35px;" onclick="activar(' . $reg->idcliente . ')"><i style="margin-left: -2px" class="fa fa-check"></i></button>'))) .
							mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-danger" style="height: 35px;" onclick="eliminar(' . $reg->idcliente . ')"><i class="fa fa-trash"></i></button>')) : ("")) .
							'</div>',
						"1" => ucwords($reg->nombre),
						"2" => $reg->tipo_documento,
						"3" => $reg->num_documento,
						"4" => ($reg->direccion == "") ? "Sin registrar" : $reg->direccion,
						"5" => $telefono,
						"6" => ($reg->email == "") ? "Sin registrar" : $reg->email,
						"7" => ($reg->descripcion == "") ? "Sin registrar" : $reg->descripcion,
						"8" => ucwords($reg->usuario),
						"9" => ucwords($cargo_detalle),
						"10" => $reg->fecha,
						"11" => ($reg->estado == 'activado') ? '<span class="label bg-green">Activado</span>' :
							'<span class="label bg-red">Desactivado</span>'
					);
				}
				$results = array(
					"sEcho" => 1,
					"iTotalRecords" => count($data),
					"iTotalDisplayRecords" => count($data),
					"aaData" => $data
				);

				echo json_encode($results);
				break;

			case 'listarVentasCliente':
				$idcliente = $_GET["idcliente"];

				$rspta = $clientes->listarVentasCliente($idcliente);

				$data = array();

				function mostrarBoton($reg, $cargo, $idusuario, $buttonType)
				{
					if ($cargo == "admin" || ($cargo == "vendedor" && $idusuario == $_SESSION["idusuario"])) {
						return $buttonType;
					} else {
						return '';
					}
				}

				$firstIteration = true;
				$totalPrecioVenta = 0;

				while ($reg = $rspta->fetch_object()) {
					$cargo_detalle = "";

					switch ($reg->cargo) {
						case 'admin':
							$cargo_detalle = "Administrador";
							break;
						case 'vendedor':
							$cargo_detalle = "Vendedor";
							break;
						case 'cliente':
							$cargo_detalle = "Cliente";
							break;
						default:
							break;
					}

					$data[] = array(
						"0" => '<div style="display: flex; flex-wrap: nowrap; gap: 3px; justify-content: center;">' .
							'<a data-toggle="modal" href="#myModal5"><button class="btn btn-success" style="margin-right: 3px; width: 35px; height: 35px; color: white !important;" onclick="modalImpresion(' . $reg->idventa . ', \'' . $reg->num_comprobante . '\')"><i class="fa fa-print"></i></button></a>' .
							'<a data-toggle="modal" href="#myModal2"><button class="btn btn-info" style="margin-right: 3px; width: 35px; height: 35px; color: white !important;" onclick="modalDetalles(' . $reg->idventa . ', \'' . $reg->usuario . '\', \'' . $reg->num_comprobante . '\', \'' . $reg->cliente . '\', \'' . $reg->cliente_tipo_documento . '\', \'' . $reg->cliente_num_documento . '\', \'' . $reg->cliente_direccion . '\', \'' . $reg->impuesto . '\', \'' . $reg->total_venta . '\', \'' . $reg->vuelto . '\', \'' . $reg->comentario_interno . '\')"><i class="fa fa-info-circle"></i></button></a>' .
							'</div>',
						"1" => '<a target="_blank" href="../reportes/exA4Venta.php?id=' . $reg->idventa . '"> <button class="btn btn-info" style="margin-right: 3px; height: 35px; color: white !important;"><i class="fa fa-save"></i></button></a>',
						"2" => $reg->fecha,
						"3" => $reg->tipo_comprobante,
						"4" => 'N° ' . $reg->num_comprobante,
						"5" => $reg->total_venta,
						"6" => $reg->usuario . ' - ' . $cargo_detalle,
						"7" => ($reg->estado == 'Iniciado') ? '<span class="label bg-blue">Iniciado</span>' : (($reg->estado == 'Entregado') ? '<span class="label bg-green">Entregado</span>' : (($reg->estado == 'Por entregar') ? '<span class="label bg-orange">Por entregar</span>' : (($reg->estado == 'En transcurso') ? '<span class="label bg-yellow">En transcurso</span>' : (($reg->estado == 'Finalizado') ? ('<span class="label bg-green">Finalizado</span>') : ('<span class="label bg-red">Anulado</span>'))))),
					);

					$totalPrecioVenta += $reg->total_venta;
					$firstIteration = false; // Marcar que ya no es la primera iteración
				}

				if (!$firstIteration) {
					$data[] = array(
						"0" => "",
						"1" => "",
						"2" => "",
						"3" => "",
						"4" => "<strong>TOTAL</strong>",
						"5" => '<strong>' . number_format($totalPrecioVenta, 2) . '</strong>',
						"6" => "",
						"7" => "",
					);
				}

				$results = array(
					"sEcho" => 1, //Información para el datatables
					"iTotalRecords" => count($data), //enviamos el total registros al datatable
					"iTotalDisplayRecords" => count($data), //enviamos el total registros a visualizar
					"aaData" => $data
				);
				echo json_encode($results);

				break;

			case 'listarProformasCliente':
				$idcliente = $_GET["idcliente"];

				$rspta = $clientes->listarProformasCliente($idcliente);

				$data = array();

				function mostrarBoton($reg, $cargo, $idusuario, $buttonType)
				{
					if ($cargo == "admin" || ($cargo == "vendedor" && $idusuario == $_SESSION["idusuario"])) {
						return $buttonType;
					} else {
						return '';
					}
				}

				$firstIteration = true;
				$totalPrecioVenta = 0;

				while ($reg = $rspta->fetch_object()) {
					$cargo_detalle = "";

					switch ($reg->cargo) {
						case 'admin':
							$cargo_detalle = "Administrador";
							break;
						case 'vendedor':
							$cargo_detalle = "Vendedor";
							break;
						case 'cliente':
							$cargo_detalle = "Cliente";
							break;
						default:
							break;
					}

					$data[] = array(
						"0" => '<div style="display: flex; flex-wrap: nowrap; gap: 3px; justify-content: center;">' .
							'<a data-toggle="modal" href="#myModal6"><button class="btn btn-success" style="margin-right: 3px; width: 35px; height: 35px; color: white !important;" onclick="modalImpresion2(' . $reg->idproforma . ', \'' . $reg->num_comprobante . '\')"><i class="fa fa-print"></i></button></a>' .
							'<a data-toggle="modal" href="#myModal4"><button class="btn btn-info" style="margin-right: 3px; width: 35px; height: 35px; color: white !important;" onclick="modalDetalles2(' . $reg->idproforma . ', \'' . $reg->usuario . '\', \'' . $reg->num_comprobante . '\', \'' . $reg->cliente . '\', \'' . $reg->cliente_tipo_documento . '\', \'' . $reg->cliente_num_documento . '\', \'' . $reg->cliente_direccion . '\', \'' . $reg->impuesto . '\', \'' . $reg->total_venta . '\', \'' . $reg->vuelto . '\', \'' . $reg->comentario_interno . '\')"><i class="fa fa-info-circle"></i></button></a>' .
							'</div>',
						"1" => '<a target="_blank" href="../reportes/exA4Proforma.php?id=' . $reg->idproforma . '"> <button class="btn btn-info" style="margin-right: 3px; height: 35px; color: white !important;"><i class="fa fa-save"></i></button></a>',
						"2" => $reg->fecha,
						"3" => $reg->tipo_comprobante,
						"4" => 'N° ' . $reg->num_comprobante,
						"5" => $reg->total_venta,
						"6" => $reg->usuario . ' - ' . $cargo_detalle,
						"7" => ($reg->estado == 'Iniciado') ? '<span class="label bg-blue">Iniciado</span>' : (($reg->estado == 'Entregado') ? '<span class="label bg-green">Entregado</span>' : (($reg->estado == 'Por entregar') ? '<span class="label bg-orange">Por entregar</span>' : (($reg->estado == 'En transcurso') ? '<span class="label bg-yellow">En transcurso</span>' : (($reg->estado == 'Finalizado') ? ('<span class="label bg-green">Finalizado</span>') : ('<span class="label bg-red">Anulado</span>'))))),
					);

					$totalPrecioVenta += $reg->total_venta;
					$firstIteration = false; // Marcar que ya no es la primera iteración
				}

				if (!$firstIteration) {
					$data[] = array(
						"0" => "",
						"1" => "",
						"2" => "",
						"3" => "<strong>TOTAL</strong>",
						"4" => '<strong>' . number_format($totalPrecioVenta, 2) . '</strong>',
						"5" => "",
						"6" => "",
						"7" => "",
					);
				}

				$results = array(
					"sEcho" => 1, //Información para el datatables
					"iTotalRecords" => count($data), //enviamos el total registros al datatable
					"iTotalDisplayRecords" => count($data), //enviamos el total registros a visualizar
					"aaData" => $data
				);
				echo json_encode($results);

				break;

			case 'selectClientes':
				$rspta = $clientes->listarClientesGeneral();

				echo '<option value="">- Seleccione -</option>';
				while ($reg = $rspta->fetch_object()) {
					echo '<option value="' . $reg->nombre . '"> ' . $reg->nombre . '</option>';
				}
				break;
		}
	} else {
		require 'noacceso.php';
	}
}
ob_end_flush();
