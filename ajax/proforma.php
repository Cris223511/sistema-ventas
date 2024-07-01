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
		require_once "../modelos/Proforma.php";

		$proforma = new Proforma();

		$idusuario = $_SESSION["idusuario"];
		$idlocalSession = $_SESSION["idlocal"];
		$cargo = $_SESSION["cargo"];

		$idproforma = isset($_POST["idproforma"]) ? limpiarCadena($_POST["idproforma"]) : "";
		$idlocal = isset($_POST["idlocal"]) ? limpiarCadena($_POST["idlocal"]) : "";

		$idcliente = isset($_POST["idcliente"]) ? limpiarCadena($_POST["idcliente"]) : "";
		$idcaja = isset($_POST["idcaja"]) ? limpiarCadena($_POST["idcaja"]) : "";
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
				if (empty($idproforma)) {
					$numeroExiste = $proforma->verificarNumeroExiste($num_comprobante, (($idlocal != "") ? $idlocal : $idlocalSession));
					if ($numeroExiste) {
						echo "El número correlativo que ha ingresado ya existe en el local seleccionado.";
					} else {
						$rspta = $proforma->insertar($idusuario, (($idlocal != "") ? $idlocal : $idlocalSession), $idcliente, $idcaja, $tipo_comprobante, $num_comprobante, $impuesto, $total_venta, $vuelto, $comentario_interno, $comentario_externo, $_POST["detalles"], $_POST["idpersonal"], $_POST["cantidad"], $_POST["precio_compra"], $_POST["precio_venta"], $_POST["comision"], $_POST["descuento"], $_POST["metodo_pago"], $_POST["monto"]);
						if (is_array($rspta) && $rspta[0] === true) {
							echo json_encode($rspta);
						} else {
							echo $rspta;
						}
					}
				} else {
				}
				break;

			case 'anular':
				$rspta = $proforma->anular($idproforma);
				echo $rspta ? "Proforma anulada" : "Proforma no se puede anular";
				break;

			case 'enviar':
				$rspta = $proforma->enviar($idproforma, $idlocal);
				echo $rspta ? "Proforma convertida en venta con éxito." : "Proforma no se pudo enviar";
				break;

			case 'cambiarEstado':
				$rspta = $proforma->cambiarEstado($idproforma, $estado);
				echo $rspta ? "Estado de la proforma actualizada con éxito." : "El estado de la proforma no se puede actualizar.";
				break;

			case 'validarCaja':
				$rspta = $proforma->validarCaja($idlocalSession);
				echo json_encode($rspta);
				break;

			case 'eliminar':
				$rspta = $proforma->eliminar($idproforma);
				echo $rspta ? "Proforma eliminada" : "Proforma no se puede eliminar";
				break;

			case 'listar':
				$fecha_inicio = $_GET["fecha_inicio"];
				$fecha_fin = $_GET["fecha_fin"];
				$estado = $_GET["estado"];

				if ($cargo == "superadmin" || $cargo == "admin_total") {
					if ($fecha_inicio == "" && $fecha_fin == "" && $estado == "") {
						$rspta = $proforma->listar();
					} else if ($fecha_inicio == "" && $fecha_fin == ""  && $estado != "") {
						$rspta = $proforma->listarEstado($estado);
					} else if ($fecha_inicio != "" && $fecha_fin != ""  && $estado == "") {
						$rspta = $proforma->listarPorFecha($fecha_inicio, $fecha_fin);
					} else {
						$rspta = $proforma->listarPorFechaEstado($fecha_inicio, $fecha_fin, $estado);
					}
				} else {
					if ($fecha_inicio == "" && $fecha_fin == "" && $estado == "") {
						$rspta = $proforma->listarPorUsuario($idlocalSession);
					} else if ($fecha_inicio == "" && $fecha_fin == ""  && $estado != "") {
						$rspta = $proforma->listarPorUsuarioEstado($idlocalSession, $estado);
					} else if ($fecha_inicio != "" && $fecha_fin != ""  && $estado == "") {
						$rspta = $proforma->listarPorUsuarioFecha($idlocalSession, $fecha_inicio, $fecha_fin);
					} else {
						$rspta = $proforma->listarPorUsuarioFechaEstado($idlocalSession, $fecha_inicio, $fecha_fin, $estado);
					}
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

				// para que no le salga ninguna opción al cajero pero a los demás sí.
				function mostrarBoton2($reg, $cargo, $idusuario, $buttonType)
				{
					if (($reg != "superadmin" && $reg != "admin_total") && $cargo == "admin") {
						return $buttonType;
					} elseif ($reg != "superadmin" && $cargo == "admin_total") {
						return $buttonType;
					} elseif ($cargo == "superadmin") {
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
							'<a data-toggle="modal" href="#myModal9"><button class="btn btn-success" style="margin-right: 3px; width: 35px; height: 35px; color: white !important;" onclick="modalImpresion(' . $reg->idproforma . ', \'' . $reg->num_comprobante . '\')"><i class="fa fa-print"></i></button></a>' .
							'<a data-toggle="modal" href="#myModal10"><button class="btn btn-info" style="margin-right: 3px; width: 35px; height: 35px; color: white !important;" onclick="modalDetalles(' . $reg->idproforma . ', \'' . $reg->usuario . '\', \'' . $reg->num_comprobante . '\', \'' . $reg->cliente . '\', \'' . $reg->cliente_tipo_documento . '\', \'' . $reg->cliente_num_documento . '\', \'' . $reg->cliente_direccion . '\', \'' . $reg->impuesto . '\', \'' . $reg->total_venta . '\', \'' . $reg->vuelto . '\', \'' . $reg->comentario_interno . '\')"><i class="fa fa-info-circle"></i></button></a>' .
							(($reg->estado == 'Iniciado' || $reg->estado == 'Entregado' || $reg->estado == 'Por entregar' || $reg->estado == 'En transcurso' || $reg->estado == 'Finalizado') ?
								((($_SESSION["cargo"] == 'superadmin' || $_SESSION["cargo"] == 'admin_total') ? (mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<a data-toggle="modal" href="#myModal11"><button class="btn btn-bcp" style="margin-right: 3px; height: 35px;" onclick="modalEstadoVenta(' . $reg->idproforma . ', \'' . $reg->num_comprobante . '\')"><i class="fa fa-gear"></i></button></a>')) : ("")) .
									(mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-warning" style="margin-right: 3px; height: 35px;" onclick="enviar(' . $reg->idproforma . ', ' . $reg->idlocal . ')"><i class="fa fa-sign-in"></i></button>')) .
									(mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-danger" style="margin-right: 3px; height: 35px;" onclick="anular(' . $reg->idproforma . ')"><i class="fa fa-close"></i></button>'))) : ('')) .
							mostrarBoton2($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-danger" style="margin-right: 3px; height: 35px;" onclick="eliminar(' . $reg->idproforma . ')"><i class="fa fa-trash"></i></button>') .
							'</div>',
						"1" => '<a target="_blank" href="../reportes/exA4Proforma.php?id=' . $reg->idproforma . '"> <button class="btn btn-info" style="margin-right: 3px; height: 35px; color: white !important;"><i class="fa fa-save"></i></button></a>',
						"2" => $reg->cliente,
						"3" => $reg->local,
						"4" => $reg->caja,
						"5" => $reg->tipo_comprobante,
						"6" => 'N° ' . $reg->num_comprobante,
						"7" => $reg->total_venta,
						"8" => $reg->usuario . ' - ' . $cargo_detalle,
						"9" => $reg->fecha,
						"10" => ($reg->estado == 'Iniciado') ? '<span class="label bg-blue">Iniciado</span>' : (($reg->estado == 'Entregado') ? '<span class="label bg-green">Entregado</span>' : (($reg->estado == 'Por entregar') ? '<span class="label bg-orange">Por entregar</span>' : (($reg->estado == 'En transcurso') ? '<span class="label bg-yellow">En transcurso</span>' : (($reg->estado == 'Finalizado') ? ('<span class="label bg-green">Finalizado</span>') : ('<span class="label bg-red">Anulado</span>'))))),
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
						"4" => "",
						"5" => "",
						"6" => "<strong>TOTAL</strong>",
						"7" => '<strong>' . number_format($totalPrecioVenta, 2) . '</strong>',
						"8" => "",
						"9" => "",
						"10" => "",
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
				$row = mysqli_fetch_assoc($proforma->getLastNumComprobante($idlocalSession));
				if ($row != null) {
					$last_num_comprobante = $row["last_num_comprobante"];
					echo $last_num_comprobante;
				} else {
					echo $row;
				}
				break;

			case 'getLastNumComprobanteLocal':
				$row1 = mysqli_fetch_assoc($proforma->getLastNumComprobante($idlocal));
				$row2 = mysqli_fetch_assoc($proforma->getCajaLocal($idlocal));
				$row3 = mysqli_fetch_assoc($proforma->verificarCajaLocal($idlocal));

				$lastNumComp = $row1["last_num_comprobante"] != null ? $row1["last_num_comprobante"] : "0";
				$idcajaLocal = $row2["idcaja"] != null ? $row2["idcaja"] : "0";

				$response = array(
					"last_num_comprobante" => $lastNumComp,
					"idcaja" => $idcajaLocal,
					"estado" => $row3["estado"],
				);
				echo json_encode($response);
				break;


				/* ======================= SELECTS ======================= */

			case 'listarTodosLocalActivosPorUsuario':
				if ($cargo == "superadmin" || $cargo == "admin_total") {
					$rspta = $proforma->listarTodosLocalActivos();
				} else {
					$rspta = $proforma->listarTodosLocalActivosPorUsuario($idlocalSession);
				}

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

				if ($cargo == "superadmin" || $cargo == "admin_total") {
					$rspta = $proforma->listarArticulosPorCategoria($idcategoria);
				} else {
					$rspta = $proforma->listarArticulosPorCategoriaLocal($idcategoria, $idlocalSession);
				}

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
				$rspta1 = $proforma->listarDetallesProductoVenta($idproforma);
				$rspta2 = $proforma->listarDetallesMetodosPagoVenta($idproforma);

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
				$rspta = $proforma->listarMetodosDePago();

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
				if ($cargo == "superadmin" || $cargo == "admin_total") {
					$rspta = $proforma->listarClientes();
				} else {
					$rspta = $proforma->listarClientesLocal($idlocalSession);
				}

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
