<?php
ob_start();
if (strlen(session_id()) < 1) {
	session_start(); //Validamos si existe o no la sesión
}

if (empty($_SESSION['idusuario']) && empty($_SESSION['cargo']) && $_GET["op"] !== 'listarTodosActivos' && $_GET["op"] !== 'guardaryeditar' && $_GET["op"] !== 'getLastCodigo') {
	session_unset();
	session_destroy();
	header("Location: ../vistas/login.html");
	exit();
}

require_once "../modelos/Articulo.php";

$articulo = new Articulo();

// Variables de sesión a utilizar.
$idusuario = $_SESSION["idusuario"];
$cargo = $_SESSION["cargo"];

$idarticulo = isset($_POST["idarticulo"]) ? limpiarCadena($_POST["idarticulo"]) : "";
$idcategoria = isset($_POST["idcategoria"]) ? limpiarCadena($_POST["idcategoria"]) : "";
$codigo_producto = isset($_POST["codigo_producto"]) ? limpiarCadena($_POST["codigo_producto"]) : "";
$nombre = isset($_POST["nombre"]) ? limpiarCadena($_POST["nombre"]) : "";
$stock = isset($_POST["stock"]) ? limpiarCadena($_POST["stock"]) : "";
$stock_minimo = isset($_POST["stock_minimo"]) ? limpiarCadena($_POST["stock_minimo"]) : "";
$descripcion = isset($_POST["descripcion"]) ? limpiarCadena($_POST["descripcion"]) : "";
$talla = isset($_POST["talla"]) ? limpiarCadena($_POST["talla"]) : "";
$color = isset($_POST["color"]) ? limpiarCadena($_POST["color"]) : "";
$imagen = isset($_POST["imagen"]) ? limpiarCadena($_POST["imagen"]) : "";
$precio_venta = isset($_POST["precio_venta"]) ? limpiarCadena($_POST["precio_venta"]) : "";

switch ($_GET["op"]) {
	case 'guardaryeditar':

		if (!empty($_FILES['imagen']['name'])) {
			$uploadDirectory = "../files/articulos/";

			$tempFile = $_FILES['imagen']['tmp_name'];
			$fileExtension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
			$newFileName = sprintf("%09d", rand(0, 999999999)) . '.' . $fileExtension;
			$targetFile = $uploadDirectory . $newFileName;

			// Verificar si es una imagen y mover el archivo
			$allowedExtensions = array('jpg', 'jpeg', 'png', 'jfif', 'bmp');
			if (in_array($fileExtension, $allowedExtensions) && move_uploaded_file($tempFile, $targetFile)) {
				// El archivo se ha movido correctamente, ahora $newFileName contiene el nombre del archivo
				$imagen = $newFileName;
			} else {
				// Error en la subida del archivo
				echo "Error al subir la imagen.";
				exit;
			}
		} else {
			// No se ha seleccionado ninguna imagen
			$imagen = $_POST["imagenactual"];
		}

		if (empty($idarticulo)) {
			$codigoProductoExiste = $articulo->verificarCodigoProductoExiste($codigo_producto);
			if ($codigoProductoExiste) {
				echo "El código del producto que ha ingresado ya existe.";
			} else {
				$rspta = $articulo->insertar($idusuario, $idcategoria, $codigo_producto, $nombre, $stock, $stock_minimo, $descripcion, $talla, $color, $imagen, $precio_venta);
				echo $rspta ? "Producto registrado" : "El producto no se pudo registrar";
			}
		} else {
			$nombreExiste = $articulo->verificarCodigoProductoEditarExiste($codigo_producto, $idarticulo);
			if ($nombreExiste) {
				echo "El código del producto que ha ingresado ya existe.";
			} else {
				$rspta = $articulo->editar($idarticulo, $idcategoria, $codigo_producto, $nombre, $stock, $stock_minimo, $descripcion, $talla, $color, $imagen, $precio_venta);
				echo $rspta ? "Producto actualizado" : "El producto no se pudo actualizar";
			}
		}
		break;

	case 'guardarComision':
		$rspta = $articulo->comisionArticulo($comision);
		echo $rspta ? "Comisión de productos modificados correctamente" : "Comisión de productos no se pudieron modificar";
		break;

	case 'desactivar':
		$rspta = $articulo->desactivar($idarticulo);
		echo $rspta ? "Producto desactivado" : "El producto no se puede desactivar";
		break;

	case 'activar':
		$rspta = $articulo->activar($idarticulo);
		echo $rspta ? "Producto activado" : "El producto no se puede activar";
		break;

	case 'eliminar':
		$rspta = $articulo->eliminar($idarticulo);
		echo $rspta ? "Producto eliminado" : "El producto no se puede eliminar";
		break;

	case 'mostrar':
		$rspta = $articulo->mostrar($idarticulo);
		//Codificar el resultado utilizando json
		echo json_encode($rspta);
		break;

	case 'listar':
		$param2 = $_GET["param2"]; // valor categoria
		$param3 = $_GET["param3"]; // valor estado

		if ($param2 != '' && $param3 == '') {
			$rspta = $articulo->listarPorUsuarioParametro("a.idcategoria = '$param2'");
		} else if ($param2 == '' && $param3 != '') {
			if ($param3 == "1") {
				// Disponible
				$rspta = $articulo->listarPorUsuarioParametro("a.stock > a.stock_minimo");
			} else if ($param3 == "2") {
				// Agotándose
				$rspta = $articulo->listarPorUsuarioParametro("a.stock > 0 AND a.stock < a.stock_minimo");
			} else {
				// Agotado
				$rspta = $articulo->listarPorUsuarioParametro("a.stock = 0");
			}
		} else if ($param2 != '' && $param3 != '') {
			if ($param3 == "1") {
				// Disponible
				$rspta = $articulo->listarPorUsuarioParametro("a.idcategoria = '$param2' AND a.stock > a.stock_minimo");
			} else if ($param3 == "2") {
				// Agotándose
				$rspta = $articulo->listarPorUsuarioParametro("a.idcategoria = '$param2' AND a.stock > 0 AND a.stock < a.stock_minimo");
			} else {
				// Agotado
				$rspta = $articulo->listarPorUsuarioParametro("a.idcategoria = '$param2' AND a.stock = 0");
			}
		} else {
			$rspta = $articulo->listarPorUsuario();
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
					mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-warning" style="margin-right: 3px; height: 35px;" onclick="mostrar(' . $reg->idarticulo . ')"><i class="fa fa-pencil"></i></button>') .
					mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-danger "style="height: 35px;" onclick="eliminar(' . $reg->idarticulo . ')"><i class="fa fa-trash"></i></button>') .
					'</div>',
				"1" => '<a href="../files/articulos/' . $reg->imagen . '" class="galleria-lightbox" style="z-index: 10000 !important;">
									<img src="../files/articulos/' . $reg->imagen . '" height="50px" width="50px" class="img-fluid">
								</a>',
				"2" => $reg->nombre,
				"3" => $reg->categoria,
				"4" => $reg->codigo_producto,
				"5" => ($reg->stock > 0 && $reg->stock < $reg->stock_minimo) ? '<span style="color: #Ea9900; font-weight: bold">' . $reg->stock . '</span>' : (($reg->stock != '0') ? '<span>' . $reg->stock . '</span>' : '<span style="color: red; font-weight: bold">' . $reg->stock . '</span>'),
				"6" => $reg->stock_minimo,
				"7" => "S/. " . number_format($reg->precio_venta, 2, '.', ','),
				"8" => $reg->usuario,
				"9" => $cargo_detalle,
				"10" => ($reg->stock > 0 && $reg->stock < $reg->stock_minimo) ? '<span class="label bg-orange">agotandose</span>' : (($reg->stock != '0') ? '<span class="label bg-green">Disponible</span>' : '<span class="label bg-red">agotado</span>')
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

	case 'getLastCodigo':
		$result = $articulo->getLastCodigo();

		if ($result && mysqli_num_rows($result) > 0) {
			$row = mysqli_fetch_assoc($result);
			if ($row && !empty($row["last_codigo"])) {
				$last_codigo = $row["last_codigo"];
			} else {
				$last_codigo = 'PRO0000';
			}
		} else {
			$last_codigo = 'PRO0000';
		}
		echo $last_codigo;
		break;

		/* ======================= SELECTS ======================= */

	case 'listarTodosActivos':
		$rspta = $articulo->listarTodosActivos();

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

ob_end_flush();
