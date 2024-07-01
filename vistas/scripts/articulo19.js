var tabla;

function actualizarCorrelativo() {
	$.post("../ajax/articulo.php?op=getLastCodigo", function (num) {
		console.log(num);
		const partes = num.match(/([a-zA-Z]+)(\d+)/) || ["", "", ""];

		const letras = partes[1];
		const numeros = partes[2];

		const siguienteCorrelativo = generarSiguienteCorrelativo(numeros);

		$("#cod_part_1").val(letras);
		$("#cod_part_2").val(siguienteCorrelativo);
	});
}

//Función que se ejecuta al inicio
function init() {
	mostrarform(false);
	listar();

	$("#formulario").on("submit", function (e) {
		guardaryeditar(e);
	})

	$("#imagenmuestra").hide();
	$('#mAlmacen').addClass("treeview active");
	$('#lArticulos').addClass("active");

	$.post("../ajax/articulo.php?op=listarTodosActivos", function (data) {
		// console.log(data)
		const obj = JSON.parse(data);
		console.log(obj);

		const selects = {
			"idcategoria": $("#idcategoria, #idcategoriaBuscar"),
		};

		for (const selectId in selects) {
			if (selects.hasOwnProperty(selectId)) {
				const select = selects[selectId];
				const atributo = selectId.replace('id', '');

				select.empty();
				select.html('<option value="">- Seleccione -</option>');
				
				if (obj.hasOwnProperty(atributo)) {
					obj[atributo].forEach(function (opcion) {
						select.append('<option value="' + opcion.id + '">' + opcion.titulo + '</option>');
					});
					select.selectpicker('refresh');
				}
			}
		}

		$('#idcategoria').closest('.form-group').find('input[type="text"]').attr('onkeydown', 'agregarCategoria(event)');
		$('#idcategoria').closest('.form-group').find('input[type="text"]').attr('maxlength', '40');
	});
}

function listarTodosActivos(selectId) {
	$.post("../ajax/articulo.php?op=listarTodosActivos", function (data) {
		const obj = JSON.parse(data);

		const select = $("#" + selectId);
		const atributo = selectId.replace('id', '');

		if (obj.hasOwnProperty(atributo)) {
			select.empty();
			select.html('<option value="">- Seleccione -</option>');
			obj[atributo].forEach(function (opcion) {
				select.append('<option value="' + opcion.id + '">' + opcion.titulo + '</option>');
			});
			select.selectpicker('refresh');
		}

		select.closest('.form-group').find('input[type="text"]').attr('onkeydown', 'agregar' + atributo.charAt(0).toUpperCase() + atributo.slice(1) + '(event)');
		select.closest('.form-group').find('input[type="text"]').attr('maxlength', '40');
		$("#" + selectId + ' option:last').prop("selected", true);
		select.selectpicker('refresh');
		select.selectpicker('toggle');
	});
}

function agregarCategoria(e) {
	let inputValue = $('#idcategoria').closest('.form-group').find('input[type="text"]');

	if (e.key === "Enter") {
		if ($('.no-results').is(':visible')) {
			e.preventDefault();
			$("#titulo2").val(inputValue.val());

			var formData = new FormData($("#formularioCategoria")[0]);

			$.ajax({
				url: "../ajax/categoria.php?op=guardaryeditar",
				type: "POST",
				data: formData,
				contentType: false,
				processData: false,

				success: function (datos) {
					datos = limpiarCadena(datos);
					if (!datos) {
						console.log("No se recibieron datos del servidor.");
						return;
					} else if (datos == "El nombre de la categoría que ha ingresado ya existe.") {
						bootbox.alert(datos);
						return;
					} else {
						// bootbox.alert(datos);
						listarTodosActivos("idcategoria");
						$("#idcategoria2").val("");
						$("#titulo2").val("");
						$("#descripcion2").val("");
					}
				}
			});
		}
	}
}

//Función limpiar
function limpiar() {
	$("#codigo_barra").val("");
	$("#cod_part_1").val("");
	$("#cod_part_2").val("");
	$("#nombre").val("");
	$("#descripcion").val("");
	$("#talla").val("");
	$("#color").val("");
	$("#stock").val("");
	$("#stock_minimo").val("");
	$("#imagenmuestra").attr("src", "");
	$("#imagenmuestra").hide();
	$("#imagenactual").val("");
	$("#imagen").val("");
	$("#precio_venta").val("");
	$("#print").hide();
	$("#idarticulo").val("");

	$("#idcategoria").val($("#idcategoria option:first").val());
	$("#idcategoria").selectpicker('refresh');

	// detenerEscaneo();
}

//Función mostrar formulario
function mostrarform(flag) {
	limpiar();
	actualizarCorrelativo();
	if (flag) {
		$(".listadoregistros").hide();
		$("#formularioregistros").show();
		$("#btnGuardar").prop("disabled", false);
		$("#btnagregar").hide();
		$("#btncomisiones").hide();
		$("#btnDetalles1").show();
		$("#btnDetalles2").hide();
		$("#frmDetalles").hide();
	}
	else {
		$(".listadoregistros").show();
		$("#formularioregistros").hide();
		$("#btnagregar").show();
		$("#btncomisiones").show();
		$("#btnDetalles1").show();
		$("#btnDetalles2").hide();
		$("#frmDetalles").hide();
	}
}

function frmDetalles(bool) {
	if (bool == true) { $("#frmDetalles").show(); $("#btnDetalles1").hide(); $("#btnDetalles2").show(); }
	if (bool == false) { $("#frmDetalles").hide(); $("#btnDetalles1").show(); $("#btnDetalles2").hide(); }
	// $('html, body').animate({ scrollTop: $(document).height() }, 10);
}

//Función cancelarform
function cancelarform() {
	limpiar();
	mostrarform(false);
}

//Función Listar
function listar() {
	let param2 = "";
	let param3 = "";

	tabla = $('#tbllistado').dataTable(
		{
			"lengthMenu": [5, 10, 25, 75, 100],
			"aProcessing": true,
			"aServerSide": true,
			dom: '<Bl<f>rtip>',
			buttons: [
				'copyHtml5',
				'excelHtml5',
				'csvHtml5',
				{
					'extend': 'pdfHtml5',
					'orientation': 'landscape',
					'exportOptions': {
						'columns': function (idx, data, node) {
							return idx > 1 ? true : false;
						}
					},
					'customize': function (doc) {
						doc.defaultStyle.fontSize = 7;
						doc.styles.tableHeader.fontSize = 7;
					},
				},
			],
			"ajax":
			{
				url: '../ajax/articulo.php?op=listar',
				type: "get",
				data: { param2: param2, param3: param3 },
				dataType: "json",
				error: function (e) {
					console.log(e.responseText);
				}
			},
			"language": {
				"lengthMenu": "Mostrar : _MENU_ registros",
				"buttons": {
					"copyTitle": "Tabla Copiada",
					"copySuccess": {
						_: '%d líneas copiadas',
						1: '1 línea copiada'
					}
				}
			},
			"bDestroy": true,
			"iDisplayLength": 5,//Paginación
			"order": [],
			"createdRow": function (row, data, dataIndex) {
				// $(row).find('td:eq(0), td:eq(1), td:eq(3), td:eq(4), td:eq(5), td:eq(6), td:eq(7), td:eq(8), td:eq(9), td:eq(10), td:eq(11, td:eq(12), td:eq(13), td:eq(14), td:eq(15)').addClass('nowrap-cell');
			}
		}).DataTable();
}

//Función para guardar o editar

function guardaryeditar(e) {
	e.preventDefault(); //No se activará la acción predeterminada del evento

	// var codigoBarra = $("#codigo_barra").val();

	// var formatoValido = /^[0-9]{1} [0-9]{2} [0-9]{4} [0-9]{1} [0-9]{4} [0-9]{1}$/.test(codigoBarra);

	// if (!formatoValido && codigoBarra != "") {
	// 	bootbox.alert("El formato del código de barra no es válido. El formato correcto es: X XX XXXX X XXXX X");
	// 	$("#btnGuardar").prop("disabled", false);
	// 	return;
	// }

	$("#btnGuardar").prop("disabled", true);

	formatearNumeroCorrelativo();

	var parteLetras = $("#cod_part_1").val();
	var parteNumeros = $("#cod_part_2").val();
	var codigoCompleto = parteLetras + parteNumeros;

	var formData = new FormData($("#formulario")[0]);
	formData.append("codigo_producto", codigoCompleto);

	let detalles = frmDetallesVisible() ? obtenerDetalles() : { talla: '', color: '' };

	for (let key in detalles) {
		formData.append(key, detalles[key]);
	}

	$.ajax({
		url: "../ajax/articulo.php?op=guardaryeditar",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,

		success: function (datos) {
			datos = limpiarCadena(datos);
			if (datos == "El código del producto que ha ingresado ya existe.") {
				bootbox.alert(datos);
				$("#btnGuardar").prop("disabled", false);
				return;
			}
			limpiar();
			bootbox.alert(datos);
			mostrarform(false);
			tabla.ajax.reload();
		}
	});
}

function obtenerDetalles() {
	let detalles = {
		talla: $("#talla").val(),
		color: $("#color").val(),
	};

	if (!detalles.talla) detalles.talla = '';
	if (!detalles.color) detalles.color = '';

	return detalles;
}

function frmDetallesVisible() {
	return $("#frmDetalles").is(":visible");
}

function mostrar(idarticulo) {
	mostrarform(true);
	frmDetalles(true);

	$(".caja1").show();
	$(".caja2").removeClass("col-lg-12 col-md-12 col-sm-12").addClass("col-lg-10 col-md-8 col-sm-12");
	$(".botones").removeClass("col-lg-12 col-md-12 col-sm-12").addClass("col-lg-10 col-md-8 col-sm-12");

	$.post("../ajax/articulo.php?op=mostrar", { idarticulo: idarticulo }, function (data, status) {
		data = JSON.parse(data);
		console.log(data);

		$("#idcategoria").val(data.idcategoria);
		$('#idcategoria').selectpicker('refresh');
		// $("#codigo_barra").val(data.codigo);

		const partes = data.codigo_producto.match(/([a-zA-Z]+)(\d+)/) || ["", "", ""];

		const letras = partes[1];
		const numeros = partes[2];

		$("#cod_part_1").val(letras);
		$("#cod_part_2").val(numeros);

		$("#nombre").val(data.nombre);
		$("#stock").val(data.stock);
		$("#stock_minimo").val(data.stock_minimo);
		$("#descripcion").val(data.descripcion);
		$("#talla").val(data.talla);
		$("#color").val(data.color);
		$("#imagenmuestra").show();
		$("#imagenmuestra").attr("src", "../files/articulos/" + data.imagen);
		$("#precio_venta").val(data.precio_venta);
		$("#imagenactual").val(data.imagen);
		$("#idarticulo").val(data.idarticulo);
	})
}

//Función para desactivar registros
function desactivar(idarticulo) {
	bootbox.confirm("¿Está seguro de desactivar el producto?", function (result) {
		if (result) {
			$.post("../ajax/articulo.php?op=desactivar", { idarticulo: idarticulo }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
			});
		}
	})
}

//Función para activar registros
function activar(idarticulo) {
	bootbox.confirm("¿Está seguro de activar el producto?", function (result) {
		if (result) {
			$.post("../ajax/articulo.php?op=activar", { idarticulo: idarticulo }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
			});
		}
	})
}

//Función para eliminar los registros
function eliminar(idarticulo) {
	bootbox.confirm("¿Estás seguro de eliminar el producto?", function (result) {
		if (result) {
			$.post("../ajax/articulo.php?op=eliminar", { idarticulo: idarticulo }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
			});
		}
	})
}

function resetear() {
	const selects = ["idcategoriaBuscar", "estadoBuscar"];

	for (const selectId of selects) {
		$("#" + selectId).val("");
		$("#" + selectId).selectpicker('refresh');
	}

	listar();
}

//Función buscar
function buscar() {
	let param2 = "";
	let param3 = "";

	// Obtener los selectores
	const selectCategoria = document.getElementById("idcategoriaBuscar");
	const selectEstado = document.getElementById("estadoBuscar");

	if (selectCategoria.value == "" && selectEstado.value == "") {
		bootbox.alert("Debe seleccionar al menos un campo para realizar la búsqueda.");
		return;
	}

	param2 = selectCategoria.value;
	param3 = selectEstado.value;

	tabla = $('#tbllistado').dataTable(
		{
			"lengthMenu": [5, 10, 25, 75, 100],
			"aProcessing": true,
			"aServerSide": true,
			dom: '<Bl<f>rtip>',
			buttons: [
				'copyHtml5',
				'excelHtml5',
				'csvHtml5',
				{
					'extend': 'pdfHtml5',
					'orientation': 'landscape',
					'exportOptions': {
						'columns': function (idx, data, node) {
							return idx > 1 ? true : false;
						}
					},
					'customize': function (doc) {
						doc.defaultStyle.fontSize = 7;
						doc.styles.tableHeader.fontSize = 7;
					},
				},
			],
			"ajax":
			{
				url: '../ajax/articulo.php?op=listar',
				data: { param2: param2, param3: param3 },
				type: "get",
				dataType: "json",
				error: function (e) {
					console.log(e.responseText);
				}
			},
			"language": {
				"lengthMenu": "Mostrar : _MENU_ registros",
				"buttons": {
					"copyTitle": "Tabla Copiada",
					"copySuccess": {
						_: '%d líneas copiadas',
						1: '1 línea copiada'
					}
				}
			},
			"bDestroy": true,
			"iDisplayLength": 5,//Paginación
			"order": [],
			"createdRow": function (row, data, dataIndex) {
				// $(row).find('td:eq(0), td:eq(1), td:eq(3), td:eq(4), td:eq(5), td:eq(6), td:eq(7), td:eq(8), td:eq(9), td:eq(10), td:eq(11), td:eq(12), td:eq(13), td:eq(14), td:eq(15)').addClass('nowrap-cell');
			}
		}).DataTable();
}

function convertirMayus() {
	var inputCodigo = document.getElementById("cod_part_1");
	inputCodigo.value = inputCodigo.value.toUpperCase();
}

init();