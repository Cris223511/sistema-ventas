<?php
//Activamos el almacenamiento en el buffer
ob_start();
session_start();

if (!isset($_SESSION["nombre"])) {
  header("Location: login.html");
} else {
  require 'header.php';
  if ($_SESSION['almacen'] == 1) {
?>
    <style>
      @media (max-width: 991px) {
        .caja1 {
          padding: 0 !important;
          margin: 0 !important;
        }

        .caja1 .contenedor {
          display: flex;
          flex-direction: column;
          justify-content: center;
          text-align: center;
          gap: 15px;
          margin-bottom: 20px;
        }

        .caja1 .contenedor img {
          width: 25% !important;
        }

        .contenedor_articulos {
          display: flex;
          flex-direction: column-reverse !important;
        }
      }

      @media (max-width: 767px) {
        .botones {
          width: 100% !important;
        }

        .table-responsive {
          margin: 0;
        }

        #label {
          display: none;
        }
      }

      tbody td:nth-child(12) {
        white-space: nowrap !important;
      }

      #camera video {
        width: 250px;
        height: auto;
        border-radius: 15px;
        margin-top: 10px;
      }

      #camera canvas.drawingBuffer {
        height: auto;
        position: absolute;
      }

      .contenedor_articulos .form-control:not(textarea),
      .contenedor_articulos .form-control button:not(textarea) {
        height: 45px;
        font-size: 16px;
        align-content: center;
      }
    </style>
    <div class="content-wrapper">
      <section class="content">
        <div class="row">
          <div class="col-md-12">
            <div class="box">
              <div class="box-header with-border">
                <h1 class="box-title">Productos
                  <?php if ($_SESSION["cargo"] == "admin" || $_SESSION["cargo"] == "vendedor") { ?>
                    <button class="btn btn-bcp" id="btnagregar" onclick="mostrarform(true)"><i class="fa fa-plus-circle"></i> Agregar</button>
                  <?php } ?>
                  <?php if ($_SESSION["cargo"] == "admin") { ?>
                    <a href="../reportes/rptarticulos.php" target="_blank">
                      <button class="btn btn-secondary" style="color: black !important;">
                        <i class="fa fa-clipboard"></i> Reporte
                      </button>
                    </a>
                  <?php } ?>
                  <a href="#" data-toggle="popover" data-placement="bottom" title="<strong>Productos</strong>" data-html="true" data-content="Módulo en donde se registran los productos para que sean utilizados en las ventas y proformas." style="color: #002a8e; font-size: 18px;">&nbsp;<i class="fa fa-question-circle"></i></a>
                </h1>
                <div class="box-tools pull-right"></div>
                <div class="panel-body table-responsive listadoregistros" style="overflow: visible; padding-left: 0px; padding-right: 0px; padding-bottom: 0px;">
                  <div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-6" style="padding: 5px; margin: 0px;">
                    <label>Buscar por categoría:</label>
                    <select id="idcategoriaBuscar" name="idcategoriaBuscar" class="form-control selectpicker" data-live-search="true" data-size="5">
                      <option value="">- Seleccione -</option>
                    </select>
                  </div>
                  <div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-6" style="padding: 5px; margin: 0px;">
                    <label>Buscar por estado:</label>
                    <select id="estadoBuscar" name="estadoBuscar" class="form-control selectpicker" data-live-search="true" data-size="5">
                      <option value="">- Seleccione -</option>
                      <option value="1">Disponible</option>
                      <option value="2">Agotándose</option>
                      <option value="3">Agotado</option>
                    </select>
                  </div>
                  <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12" style="padding: 5px; margin: 0px;">
                    <label id="labelCustom">ㅤ</label>
                    <div style="display: flex; gap: 10px;">
                      <button style="width: 100%;" class="btn btn-bcp" onclick="buscar()">Buscar</button>
                      <button style="height: 32px;" class="btn btn-success" onclick="resetear()"><i class="fa fa-repeat"></i></button>
                    </div>
                  </div>
                </div>
              </div>
              <div class="panel-body listadoregistros" style="background-color: #ecf0f5 !important; padding-left: 0 !important; padding-right: 0 !important; height: max-content;">
                <div class="table-responsive" style="padding: 8px !important; padding: 20px !important; background-color: white;">
                  <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover w-100" style="width: 100% !important">
                    <thead>
                      <th style="width: 1%;">Opciones</th>
                      <th>Imagen</th>
                      <th style="width: 20%; min-width: 260px;">Nombre</th>
                      <th>Categoría</th>
                      <th>C. producto</th>
                      <th>Stock normal</th>
                      <th>Stock mínimo</th>
                      <th>P. venta</th>
                      <th>Agregado por</th>
                      <th>Cargo</th>
                      <th>Estado</th>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                      <th>Opciones</th>
                      <th>Imagen</th>
                      <th>Nombre</th>
                      <th>Categoría</th>
                      <th>C. producto</th>
                      <th>Stock normal</th>
                      <th>Stock mínimo</th>
                      <th>P. venta</th>
                      <th>Agregado por</th>
                      <th>Cargo</th>
                      <th>Estado</th>
                    </tfoot>
                  </table>
                </div>
              </div>
              <div class="panel-body" id="formularioregistros" style="background-color: #ecf0f5 !important; padding-left: 0 !important; padding-right: 0 !important;">
                <form name="formulario" id="formulario" method="POST" enctype="multipart/form-data">
                  <div class="contenedor_articulos">
                    <div class="form-group col-lg-10 col-md-8 col-sm-12 caja2" style="background-color: white; border-top: 3px #002a8e solid !important; padding: 20px;">
                      <div class="form-group col-lg-4 col-md-6 col-sm-12" style="margin: 0; padding: 0;">
                        <div class="form-group col-lg-6 col-md-6 col-sm-6">
                          <label>Código(*):</label>
                          <input type="text" class="form-control" id="cod_part_1" maxlength="10" placeholder="PRO" onblur="convertirMayus()" required>
                        </div>
                        <div class="form-group col-lg-6 col-md-6 col-sm-6">
                          <label id="label">ㅤ</label>
                          <input type="text" class="form-control" id="cod_part_2" maxlength="10" placeholder="0001" oninput="onlyNumbersAndMaxLenght(this)" onblur="formatearNumeroCorrelativo()" required>
                        </div>
                      </div>
                      <div class="form-group col-lg-8 col-md-6 col-sm-12">
                        <label>Nombre(*):</label>
                        <input type="hidden" name="idarticulo" id="idarticulo">
                        <input type="text" class="form-control" name="nombre" id="nombre" maxlength="100" placeholder="Ingrese el nombre del producto." required>
                      </div>

                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label>Categoría(*):</label>
                        <select id="idcategoria" name="idcategoria" class="form-control selectpicker" data-live-search="true" data-size="5" required></select>
                      </div>
                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label>Precio venta(*):</label>
                        <input type="number" class="form-control" name="precio_venta" id="precio_venta" step="any" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="8" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" min="1" placeholder="Ingrese el precio de venta." required>
                      </div>

                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label>Stock(*):</label>
                        <input type="number" class="form-control" name="stock" id="stock" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" min="1" placeholder="Ingrese el stock." required>
                      </div>
                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label>Stock mínimo(*):</label>
                        <input type="number" class="form-control" name="stock_minimo" id="stock_minimo" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" min="1" placeholder="Ingrese el stock mínimo." required>
                      </div>
                      <div class="form-group col-lg-12 col-md-12 col-sm-12">
                        <label>Imagen:</label>
                        <input type="file" class="form-control" name="imagen" id="imagen" accept=".jpg,.jpeg,.png,.jfif,.bmp">
                        <input type="hidden" name="imagenactual" id="imagenactual">
                      </div>
                      <!-- <div class="form-group col-lg-6 col-md-12 col-sm-12">
                        <div>
                          <label>Código de barra(*):</label>
                          <input type="text" class="form-control" name="codigo" id="codigo_barra" maxlength="18" placeholder="Ingrese el código de barra.">
                        </div>
                        <div style="margin-top: 10px; display: flex; gap: 5px; flex-wrap: wrap;">
                          <button class="btn btn-info" type="button" onclick="generar()">Generar</button>
                          <button class="btn btn-warning" type="button" onclick="imprimir()">Imprimir</button>
                          <button class="btn btn-danger" type="button" onclick="borrar()">Borrar</button>
                          <button class="btn btn-success btn1" type="button" onclick="escanear()">Escanear</button>
                          <button class="btn btn-danger btn2" type="button" onclick="detenerEscaneo()">Detener</button>
                        </div>
                        <div id="print" style="overflow-y: hidden;">
                          <img id="barcode">
                        </div>
                      </div> -->
                      <div class="form-group col-lg-12 col-md-12" style="display: flex; justify-content: center;">
                        <button class="btn btn-success" type="button" id="btnDetalles1" onclick="frmDetalles(true)"><i class="fa fa-plus"></i> Más detalles</button>
                        <button class="btn btn-danger" type="button" id="btnDetalles2" onclick="frmDetalles(false)"><i class="fa fa-minus"></i> Cerrar</button>
                      </div>
                      <!-- form detalles -->
                      <div id="frmDetalles" class="col-lg-12 col-md-12" style="margin: 0 !important; padding: 0 !important;">
                        <div class="form-group col-lg-6 col-md-12">
                          <label>Talla:</label>
                          <input type="text" class="form-control" name="talla" id="talla" maxlength="15" placeholder="Ingrese la talla del producto." autocomplete="off">
                        </div>
                        <div class="form-group col-lg-6 col-md-12">
                          <label>Color:</label>
                          <input type="text" class="form-control" name="color" id="color" maxlength="30" placeholder="Ingrese el color del producto." autocomplete="off">
                        </div>
                        <div class="form-group col-lg-12 col-md-12 col-sm-12">
                          <label>Descripción:</label>
                          <textarea class="form-control" id="descripcion" name="descripcion" maxlength="1000" rows="5" placeholder="Ingrese la descripción del producto." autocomplete="off"></textarea>
                        </div>
                      </div>
                      <!-- end form detalles -->
                    </div>
                    <div class="form-group col-lg-2 col-md-4 col-sm-12 caja1" style="padding-right: 0 !important; padding-left: 20px;">
                      <div class="contenedor" style="background-color: white; border-top: 3px #002a8e solid !important; padding: 10px 20px 20px 20px; text-align: center;">
                        <label>Imagen de muestra:</label>
                        <div>
                          <img src="" width="100%" id="imagenmuestra" style="display: none;">
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="form-group col-lg-10 col-md-8 col-sm-12 botones" style="background-color: white !important; padding: 10px !important; float: left;">
                    <div style="float: left;">
                      <button class="btn btn-warning" onclick="cancelarform()" type="button"><i class="fa fa-arrow-circle-left"></i> Cancelar</button>
                      <button class="btn btn-bcp" type="submit" id="btnGuardar"><i class="fa fa-save"></i> Guardar</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Form categoría -->
    <form name="formularioCategoria" id="formularioCategoria" method="POST" style="display: none;">
      <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
        <label>Nombre(*):</label>
        <input type="hidden" name="idcategoria" id="idcategoria2">
        <input type="text" class="form-control" name="titulo" id="titulo2" maxlength="50" placeholder="Nombre" required>
      </div>
      <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
        <label>Descripción:</label>
        <input type="text" class="form-control" name="descripcion" id="descripcion2" maxlength="256" placeholder="Descripción">
      </div>
    </form>
    <!-- Fin form categoría -->
  <?php
  } else {
    require 'noacceso.php';
  }
  require 'footer.php';
  ?>
  <!-- <script type="text/javascript" src="../public/js/JsBarcode.all.min.js"></script>
  <script type="text/javascript" src="../public/js/jquery.PrintArea.js"></script> -->
  <script type="text/javascript" src="scripts/articulo19.js"></script>
<?php
}
ob_end_flush();
?>