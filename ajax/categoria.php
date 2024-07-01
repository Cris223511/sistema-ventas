<?php
ob_start();
if (strlen(session_id()) < 1) {
	session_start(); //Validamos si existe o no la sesión
}

if (empty($_SESSION['idusuario']) && empty($_SESSION['cargo']) && $_GET["op"] !== 'guardaryeditar') {
	session_unset();
	session_destroy();
	header("Location: ../vistas/login.html");
	exit();
}

require_once "../modelos/Categoria.php";

$categorias = new Categoria();

// Variables de sesión a utilizar.
$idusuario = $_SESSION["idusuario"];
$cargo = $_SESSION["cargo"];

$idcategoria = isset($_POST["idcategoria"]) ? limpiarCadena($_POST["idcategoria"]) : "";
$titulo = isset($_POST["titulo"]) ? limpiarCadena($_POST["titulo"]) : "";
$descripcion = isset($_POST["descripcion"]) ? limpiarCadena($_POST["descripcion"]) : "";

switch ($_GET["op"]) {
	case 'guardaryeditar':
		if (empty($idcategoria)) {
			$nombreExiste = $categorias->verificarNombreExiste($titulo);
			if ($nombreExiste) {
				echo "El nombre de la categoría ya existe.";
			} else {
				$rspta = $categorias->agregar($idusuario, $titulo, $descripcion);
				echo $rspta ? "Categoria registrada" : "La categoría no se pudo registrar";
			}
		} else {
			$nombreExiste = $categorias->verificarNombreEditarExiste($titulo, $idcategoria);
			if ($nombreExiste) {
				echo "El nombre de la categoría ya existe.";
			} else {
				$rspta = $categorias->editar($idcategoria, $titulo, $descripcion);
				echo $rspta ? "Categoria actualizada" : "La categoría no se pudo actualizar";
			}
		}
		break;

	case 'desactivar':
		$rspta = $categorias->desactivar($idcategoria);
		echo $rspta ? "Categoria desactivada" : "La categoría no se pudo desactivar";
		break;

	case 'activar':
		$rspta = $categorias->activar($idcategoria);
		echo $rspta ? "Categoria activada" : "La categoría no se pudo activar";
		break;

	case 'eliminar':
		$rspta = $categorias->eliminar($idcategoria);
		echo $rspta ? "Categoria eliminado" : "La categoría no se pudo eliminar";
		break;

	case 'mostrar':
		$rspta = $categorias->mostrar($idcategoria);
		echo json_encode($rspta);
		break;

	case 'listar':

		$rspta = $categorias->listar();

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
			$reg->descripcion = (strlen($reg->descripcion) > 100) ? substr($reg->descripcion, 0, 100) . "..." : $reg->descripcion;

			$data[] = array(
				"0" => '<div style="display: flex; flex-wrap: nowrap; gap: 3px">' .
					mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-warning" style="margin-right: 3px; height: 35px;" onclick="mostrar(' . $reg->idcategoria . ')"><i class="fa fa-pencil"></i></button>') .
					(($reg->estado == 'activado') ?
						(mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-danger" style="margin-right: 3px; height: 35px;" onclick="desactivar(' . $reg->idcategoria . ')"><i class="fa fa-close"></i></button>')) : (mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-success" style="margin-right: 3px; width: 35px; height: 35px;" onclick="activar(' . $reg->idcategoria . ')"><i style="margin-left: -2px" class="fa fa-check"></i></button>'))) .
					mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-danger" style="height: 35px;" onclick="eliminar(' . $reg->idcategoria . ')"><i class="fa fa-trash"></i></button>') .
					'</div>',
				"1" => $reg->titulo,
				"2" => ($reg->descripcion == '') ? 'Sin registrar.' : $reg->descripcion,
				"3" => ucwords($reg->nombre),
				"4" => ucwords($cargo_detalle),
				"5" => $reg->fecha,
				"6" => ($reg->estado == 'activado') ? '<span class="label bg-green">Activado</span>' :
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

	case 'selectCategoria':
		$rspta = $categorias->listar();

		echo '<option value="">- Seleccione -</option>';
		while ($reg = $rspta->fetch_object()) {
			echo '<option value="' . $reg->idcategoria . '"> ' . $reg->titulo . '</option>';
		}
		break;
}

ob_end_flush();
