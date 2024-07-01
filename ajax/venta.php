<?php
ob_start();
if (strlen(session_id()) < 1) {
	session_start(); //Validamos si existe o no la sesión
}

if (!isset($_SESSION["nombre"])) {
	header("Location: ../vistas/login.html"); //Validamos el acceso solo a los usuarios logueados al sistema.
} else {
	//Validamos el acceso solo al usuario logueado y autorizado.
	if ($_SESSION['ventas'] == 1) {
		require_once "../modelos/Venta.php";

		$venta = new Venta();

		$idusuario = $_SESSION["idusuario"];
		$cargo = $_SESSION["cargo"];

		$idventa = isset($_POST["idventa"]) ? limpiarCadena($_POST["idventa"]) : "";

		$idcliente = isset($_POST["idcliente"]) ? limpiarCadena($_POST["idcliente"]) : "";
		$tipo_comprobante = isset($_POST["tipo_comprobante"]) ? limpiarCadena($_POST["tipo_comprobante"]) : "";
		$num_comprobante = isset($_POST["num_comprobante"]) ? limpiarCadena($_POST["num_comprobante"]) : "";
		$impuesto = isset($_POST["impuesto"]) ? limpiarCadena($_POST["impuesto"]) : "";
		$total_venta = isset($_POST["total_venta"]) ? limpiarCadena($_POST["total_venta"]) : "";
		$vuelto = isset($_POST["vuelto"]) ? limpiarCadena($_POST["vuelto"]) : "";
		$comentario_interno = isset($_POST["comentario_interno"]) ? limpiarCadena($_POST["comentario_interno"]) : "";
		$comentario_externo = isset($_POST["comentario_externo"]) ? limpiarCadena($_POST["comentario_externo"]) : "";

		$estado = isset($_POST["estado"]) ? limpiarCadena($_POST["estado"]) : "";

		$sunat = isset($_POST["sunat"]) ? limpiarCadena($_POST["sunat"]) : "";

		switch ($_GET["op"]) {
			case 'guardaryeditar':
				if (empty($idventa)) {
					$rspta = $venta->insertar($idusuario, $idcliente, $tipo_comprobante, $num_comprobante, $impuesto, $total_venta, $vuelto, $comentario_interno, $comentario_externo, $_POST["detalles"], $_POST["cantidad"], $_POST["precio_venta"], $_POST["descuento"], $_POST["metodo_pago"], $_POST["monto"]);
					if (is_array($rspta) && $rspta[0] === true) {
						echo json_encode($rspta);
					} else {
						echo $rspta;
					}
				} else {
				}
				break;

			case 'anular':
				$rspta = $venta->anular($idventa);
				echo $rspta ? "Venta anulada" : "Venta no se puede anular";
				break;

			case 'cambiarEstado':
				$rspta = $venta->cambiarEstado($idventa, $estado);
				echo $rspta ? "Estado de la venta actualizada con éxito." : "El estado de la venta no se puede actualizar.";
				break;

			case 'eliminar':
				$rspta = $venta->eliminar($idventa);
				echo $rspta ? "Venta eliminada" : "Venta no se puede eliminar";
				break;

			case 'listar':
				$fecha_inicio = $_GET["fecha_inicio"];
				$fecha_fin = $_GET["fecha_fin"];
				$estado = $_GET["estado"];

				if ($fecha_inicio == "" && $fecha_fin == "" && $estado == "") {
					$rspta = $venta->listar();
				} else if ($fecha_inicio == "" && $fecha_fin == ""  && $estado != "") {
					$rspta = $venta->listarEstado($estado);
				} else if ($fecha_inicio != "" && $fecha_fin != ""  && $estado == "") {
					$rspta = $venta->listarPorFecha($fecha_inicio, $fecha_fin);
				} else {
					$rspta = $venta->listarPorFechaEstado($fecha_inicio, $fecha_fin, $estado);
				}

				$data = array();

				function mostrarBoton($reg, $cargo, $idusuario, $buttonType)
				{
					if ($cargo == "admin" || ($cargo == "vendedor" && $idusuario == $_SESSION["idusuario"])) {
						return $buttonType;
					} else {
						return '';
					}
				}

				// para que no le salga ninguna opción al vendedor y cliente pero a los demás sí.
				function mostrarBoton2($reg, $cargo, $idusuario, $buttonType)
				{
					if ($cargo == "admin") {
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
						"0" => '<div style="display: flex; flex-wrap: nowrap; gap: 3px">' .
							'<a data-toggle="modal" href="#myModal9"><button class="btn btn-success" style="margin-right: 3px; width: 35px; height: 35px; color: white !important;" onclick="modalImpresion(' . $reg->idventa . ', \'' . $reg->num_comprobante . '\')"><i class="fa fa-print"></i></button></a>' .
							'<a data-toggle="modal" href="#myModal10"><button class="btn btn-info" style="margin-right: 3px; width: 35px; height: 35px; color: white !important;" onclick="modalDetalles(' . $reg->idventa . ', \'' . $reg->usuario . '\', \'' . $reg->num_comprobante . '\', \'' . $reg->cliente . '\', \'' . $reg->cliente_tipo_documento . '\', \'' . $reg->cliente_num_documento . '\', \'' . $reg->cliente_direccion . '\', \'' . $reg->impuesto . '\', \'' . $reg->total_venta . '\', \'' . $reg->vuelto . '\', \'' . $reg->comentario_interno . '\')"><i class="fa fa-info-circle"></i></button></a>' .
							(($reg->estado == 'Iniciado' || $reg->estado == 'Entregado' || $reg->estado == 'Por entregar' || $reg->estado == 'En transcurso' || $reg->estado == 'Finalizado') ?
								((($_SESSION["cargo"] == 'admin') ? (mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<a data-toggle="modal" href="#myModal11"><button class="btn btn-bcp" style="margin-right: 3px; height: 35px;" onclick="modalEstadoVenta(' . $reg->idventa . ', \'' . $reg->num_comprobante . '\')"><i class="fa fa-gear"></i></button></a>')) : ("")) .
									(mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-danger" style="margin-right: 3px; height: 35px;" onclick="anular(' . $reg->idventa . ')"><i class="fa fa-close"></i></button>'))) : ('')) .
							mostrarBoton2($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-danger" style="margin-right: 3px; height: 35px;" onclick="eliminar(' . $reg->idventa . ')"><i class="fa fa-trash"></i></button>') .
							'</div>',
						"1" => '<a target="_blank" href="../reportes/exA4Venta.php?id=' . $reg->idventa . '"> <button class="btn btn-info" style="margin-right: 3px; height: 35px; color: white !important;"><i class="fa fa-save"></i></button></a>',
						"2" => $reg->cliente,
						"3" => $reg->tipo_comprobante,
						"4" => 'N° ' . $reg->num_comprobante,
						"5" => $reg->total_venta,
						"6" => $reg->usuario . ' - ' . $cargo_detalle,
						"7" => $reg->fecha,
						"8" => ($reg->estado == 'Iniciado') ? '<span class="label bg-blue">Iniciado</span>' : (($reg->estado == 'Entregado') ? '<span class="label bg-green">Entregado</span>' : (($reg->estado == 'Por entregar') ? '<span class="label bg-orange">Por entregar</span>' : (($reg->estado == 'En transcurso') ? '<span class="label bg-yellow">En transcurso</span>' : (($reg->estado == 'Finalizado') ? ('<span class="label bg-green">Finalizado</span>') : ('<span class="label bg-red">Anulado</span>'))))),
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
						"8" => "",
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

				/* ======================= SUNAT ======================= */

			case 'consultaSunat':
				// Token para la API
				$token = 'apis-token-8814.1Tq4Gy-yKM7ZSWPx6eQC0feuDpVKbuEZ';

				$data = "";
				$curl = curl_init();

				try {
					if (strlen($sunat) == 8) {
						// DNI
						$url = 'https://api.apis.net.pe/v2/reniec/dni?numero=' . $sunat;
						$referer = 'https://apis.net.pe/consulta-dni-api';
					} elseif (strlen($sunat) == 11) {
						// RUC
						$url = 'https://api.apis.net.pe/v2/sunat/ruc?numero=' . $sunat;
						$referer = 'http://apis.net.pe/api-ruc';
					} elseif (strlen($sunat) < 8) {
						// Mensaje para DNI no válido
						$data = "El DNI debe tener 8 caracteres.";
						echo $data;
						break;
					} elseif (strlen($sunat) > 8 && strlen($sunat) < 11) {
						// Mensaje para RUC no válido
						$data = "El RUC debe tener 11 caracteres.";
						echo $data;
						break;
					}

					// configuración de cURL
					curl_setopt_array($curl, array(
						CURLOPT_URL => $url,
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_SSL_VERIFYPEER => 0,
						CURLOPT_ENCODING => '',
						CURLOPT_MAXREDIRS => 2,
						CURLOPT_TIMEOUT => 0,
						CURLOPT_FOLLOWLOCATION => true,
						CURLOPT_CUSTOMREQUEST => 'GET',
						CURLOPT_HTTPHEADER => array(
							'Referer: ' . $referer,
							'Authorization: Bearer ' . $token
						),
					));

					$response = curl_exec($curl);

					if ($response === false) {
						throw new Exception(curl_error($curl));
					}

					if (stripos($response, 'Not Found') !== false || stripos($response, '{"message":"ruc no valido"}') !== false) {
						$data = (strlen($sunat) == 8) ? "DNI no valido" : "RUC no valido";
					} else {
						$data = $response;
					}
				} catch (Exception $e) {
					$data = "Error al procesar la solicitud: " . $e->getMessage();
				} finally {
					curl_close($curl);
				}

				echo $data;
				break;

			case 'getLastNumComprobante':
				$row = mysqli_fetch_assoc($venta->getLastNumComprobante());
				if ($row != null) {
					$last_num_comprobante = $row["last_num_comprobante"];
					echo $last_num_comprobante;
				} else {
					echo $row;
				}
				break;

				/* ======================= SELECTS ======================= */

			case 'listarTodosActivos':
				$rspta = $venta->listarTodosActivos();

				$result = mysqli_fetch_all($rspta, MYSQLI_ASSOC);

				$data = [];
				foreach ($result as $row) {
					$tabla = $row['tabla'];
					unset($row['tabla']);
					$data[$tabla][] = $row;
				}

				echo json_encode($data);
				break;

			case 'listarArticulosPorCategoria':
				$idcategoria = isset($_POST["idcategoria"]) ? limpiarCadena($_POST["idcategoria"]) : "";

				$rspta = $venta->listarArticulosPorCategoria($idcategoria);

				$result = mysqli_fetch_all($rspta, MYSQLI_ASSOC);

				$data = [];
				foreach ($result as $row) {
					$tabla = $row['tabla'];
					unset($row['tabla']);
					$data[$tabla][] = $row;
				}

				echo json_encode($data);
				break;

			case 'listarDetallesProductoVenta':
				$rspta1 = $venta->listarDetallesProductoVenta($idventa);
				$rspta2 = $venta->listarDetallesMetodosPagoVenta($idventa);

				$articulos = array();
				$pagos = array();

				while ($row = mysqli_fetch_assoc($rspta1)) {
					$articulos[] = $row;
				}

				while ($row = mysqli_fetch_assoc($rspta2)) {
					$pagos[] = $row;
				}

				$data = array(
					"articulos" => $articulos,
					"pagos" => $pagos
				);

				echo json_encode($data);
				break;


			case 'listarMetodosDePago':
				$rspta = $venta->listarMetodosDePago();

				$result = mysqli_fetch_all($rspta, MYSQLI_ASSOC);

				$data = [];
				foreach ($result as $row) {
					$tabla = $row['tabla'];
					unset($row['tabla']);
					$data[$tabla][] = $row;
				}

				echo json_encode($data);
				break;

			case 'listarClientes':
				$rspta = $venta->listarClientes();

				$result = mysqli_fetch_all($rspta, MYSQLI_ASSOC);

				$data = [];
				foreach ($result as $row) {
					$tabla = $row['tabla'];
					unset($row['tabla']);
					$data[$tabla][] = $row;
				}

				echo json_encode($data);
				break;
		}
		//Fin de las validaciones de acceso
	} else {
		require 'noacceso.php';
	}
}
ob_end_flush();
