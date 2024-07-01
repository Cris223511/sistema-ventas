<?php
//Activamos el almacenamiento en el buffer
ob_start();
session_start();

if (!isset($_SESSION["nombre"])) {
  header("Location: login.html");
} else {
  require 'header.php';

  if ($_SESSION['ventas'] == 1) {
?>
    <style>
      .detallesProductosFinal thead,
      .detallesProductosFinal thead tr,
      .detallesProductosFinal thead th,
      .detallesProductosFinal tbody,
      .detallesProductosFinal tbody tr,
      .detallesProductosFinal tbody th {
        border: none !important;
        background-color: white;
        font-size: 16px;
        text-align: center;
      }

      .detallesProductosFinal thead {
        border-bottom: 1.5px black solid !important;
      }

      .detallesProductosFinal tbody,
      .detallesProductosFinal tfoot {
        border-top: 1.5px black solid !important;
      }

      .detallesProductosFinal tbody tr td,
      .detallesProductosFinal tfoot tr td {
        border: none !important;
      }

      .detallesPagosFinal thead,
      .detallesPagosFinal thead tr,
      .detallesPagosFinal thead th,
      .detallesPagosFinal tbody,
      .detallesPagosFinal tbody tr,
      .detallesPagosFinal tbody th {
        border: none !important;
        background-color: white;
        font-size: 16px;
        text-align: center;
      }

      .detallesPagosFinal thead {
        border-bottom: 1.5px black solid !important;
      }

      .detallesPagosFinal tbody,
      .detallesPagosFinal tfoot {
        border-top: 1.5px black solid !important;
      }

      .detallesPagosFinal tbody tr td,
      .detallesPagosFinal tfoot tr td {
        border: none !important;
      }
    </style>
    <div class="content-wrapper">
      <section class="content">
        <div class="row">
          <div class="col-md-12">
            <div class="box">
              <div class="box-header with-border">
                <h1 class="box-title">Clientes
                  <?php if ($_SESSION["cargo"] == "admin" || $_SESSION["cargo"] == "vendedor") { ?>
                    <button class="btn btn-bcp" id="btnagregar" onclick="mostrarform(true)">
                      <i class="fa fa-plus-circle"></i> Agregar
                    </button>
                  <?php } ?>
                  <?php if ($_SESSION["cargo"] == "admin") { ?>
                    <a href="../reportes/rptclientes.php" target="_blank">
                      <button class="btn btn-secondary" style="color: black !important;">
                        <i class="fa fa-clipboard"></i> Reporte
                      </button>
                    </a>
                  <?php } ?>
                  <a href="#" data-toggle="popover" data-placement="bottom" title="<strong>Clientes</strong>" data-html="true" data-content="Módulo para registrar los clientes para que sean utilizados en las ventas y proformas." style="color: #002a8e; font-size: 18px;">&nbsp;<i class="fa fa-question-circle"></i></a>
                </h1>
                <div class="box-tools pull-right">
                </div>
              </div>
              <div class="panel-body table-responsive" id="listadoregistros">
                <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover w-100" style="width: 100% !important;">
                  <thead>
                    <th style="width: 1%;">Opciones</th>
                    <th style="width: 20%; min-width: 220px;">Nombres</th>
                    <th>Tipo Doc.</th>
                    <th>Número Doc.</th>
                    <th style="width: 30%; min-width: 200px;">Dirección</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th style="width: 30%; min-width: 350px;">Descripción</th>
                    <th>Agregado por</th>
                    <th>Cargo</th>
                    <th>Fecha y hora</th>
                    <th>Estado</th>
                  </thead>
                  <tbody>

                  </tbody>
                  <tfoot>
                    <th>Opciones</th>
                    <th>Nombres</th>
                    <th>Tipo Doc.</th>
                    <th>Número Doc.</th>
                    <th>Dirección</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Descripción</th>
                    <th>Agregado por</th>
                    <th>Cargo</th>
                    <th>Fecha y hora</th>
                    <th>Estado</th>
                  </tfoot>
                </table>
              </div>

              <div class="panel-body" style="height: max-content;" id="formularioregistros">
                <form name="formulario" id="formulario" method="POST">
                  <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <label>Nombre(*):</label>
                    <input type="hidden" name="idcliente" id="idcliente">
                    <input type="text" class="form-control" name="nombre" id="nombre" maxlength="40" placeholder="Ingrese el nombre del cliente." autocomplete="off" required>
                  </div>
                  <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <label>Dirección:</label>
                    <input type="text" class="form-control" name="direccion" id="direccion" placeholder="Ingrese la dirección." maxlength="80">
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <label>Tipo Documento(*):</label>
                    <select class="form-control select-picker" name="tipo_documento" id="tipo_documento" onchange="changeValue(this);" required>
                      <option value="">- Seleccione -</option>
                      <option value="DNI">DNI</option>
                      <option value="RUC">RUC</option>
                      <option value="CEDULA">CEDULA</option>
                      <option value="CARNET DE EXTRANJERIA">CARNET DE EXTRANJERÍA</option>
                    </select>
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <label>Número(*):</label>
                    <input type="number" class="form-control" name="num_documento" id="num_documento" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="8" placeholder="Ingrese el N° de documento." required>
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <label>Teléfono:</label>
                    <input type="number" class="form-control" name="telefono" id="telefono" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="9" placeholder="Ingrese el teléfono.">
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <label>Email:</label>
                    <input type="email" class="form-control" name="email" id="email" maxlength="50" placeholder="Ingrese el correo electrónico.">
                  </div>
                  <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <label>Descripción:</label>
                    <textarea type="text" class="form-control" name="descripcion" id="descripcion" maxlength="1000" rows="4" placeholder="Ingrese una descripción."></textarea>
                  </div>
                  <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <button class="btn btn-warning" onclick="cancelarform()" type="button"><i class="fa fa-arrow-circle-left"></i> Cancelar</button>
                    <button class="btn btn-bcp" type="submit" id="btnGuardar"><i class="fa fa-save"></i> Guardar</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Modal Verificar -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog smallModal" style="width: 55%; max-height: 95vh; margin: 0 !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%); overflow-x: auto;">
        <div class="modal-content" style="background-color: #f2d150 !important;">
          <div class="modal-header" style="border-bottom: 2px solid #C68516 !important;">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <div style="text-align: center; display: flex; justify-content: center; flex-direction: column; gap: 5px;">
              <h4 class="modal-title infotitulo" style="margin: 0; padding: 0; font-weight: bold;">¿QUÉ HISTORIAL DEL CLIENTE DESEA VER?</h4>
            </div>
          </div>
          <div class="panel-body">
            <div class="col-lg-12 col-md-12 col-sm-12" style="padding: 0;">
              <div style="margin: 5px;">
                <button class="btn btn-secondary btn-ventas" style="width: 100%; text-align: center; font-weight: bold;" type="button" data-idcliente="" data-nombre="" data-tipo-documento="" data-num-documento="">VER HISTORIAL DE VENTAS</button>
              </div>
            </div>
            <!-- <div class="col-lg-6 col-md-6 col-sm-6" style="padding: 0;">
              <div style="margin: 5px;">
                <button class="btn btn-secondary btn-proformas" style="width: 100%; text-align: center; font-weight: bold;" type="button" data-idcliente="" data-nombre="" data-tipo-documento="" data-num-documento="">VER HISTORIAL DE COTIZACIONES</button>
              </div>
            </div> -->
          </div>
        </div>
      </div>
    </div>
    <!-- Fin Modal Verificar -->

    <!-- Modal 1 -->
    <div class="modal fade" id="myModal1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog smallModal" style="width: 65%; max-height: 95vh; margin: 0 !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%); overflow-x: auto;">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #f2d150 !important; border-bottom: 2px solid #C68516 !important;">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <div style="text-align: center; display: flex; justify-content: center; flex-direction: column; gap: 5px;">
              <h4 class="modal-title infotitulo" style="margin: 0; padding: 0; font-weight: bold;">HISTORIAL DE VENTAS DEL CLIENTE</h4>
              <h4 class="modal-title infotitulo cliente_detalles" style="margin: 0; padding: 0;"></h4>
            </div>
          </div>
          <div class="panel-body" style="background-color: #ecf0f5 !important; padding: 0 !important; height: max-content;">
            <div class="table-responsive" style="padding: 8px !important; padding: 20px !important; background-color: white;">
              <table id="tbldetalles" class="table table-striped table-bordered table-condensed table-hover w-100" style="width: 100% !important">
                <thead>
                  <th style="width: 1%;">Opciones</th>
                  <th>PDF</th>
                  <th>Fecha y hora</th>
                  <th>Documento</th>
                  <th>Número Ticket</th>
                  <th>Total Venta (S/.)</th>
                  <th>Agregado por</th>
                  <th>Estado</th>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                  <th>Opciones</th>
                  <th>PDF</th>
                  <th>Fecha y hora</th>
                  <th>Documento</th>
                  <th>Número Ticket</th>
                  <th>Total Venta (S/.)</th>
                  <th>Agregado por</th>
                  <th>Estado</th>
                </tfoot>
              </table>
            </div>
          </div>
          <div class="modal-footer form-group col-lg-12 col-md-12 col-sm-12 col-xs-12" style="background-color: #f2d150 !important; border-top: 2px solid #C68516 !important;">
            <button class="btn btn-warning" type="button" data-dismiss="modal"><i class="fa fa-arrow-circle-left"></i> Regresar</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin Modal 1 -->

    <!-- Modal 2 -->
    <div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog smallModal" style="width: 75%; max-height: 95vh; margin: 0 !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%); overflow-x: auto;">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #f2d150 !important; border-bottom: 2px solid #C68516 !important;">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <div style="text-align: center; display: flex; justify-content: center; flex-direction: column; gap: 5px;">
              <h4 class="modal-title infotitulo" style="margin: 0; padding: 0; font-weight: bold; text-align: start;">NOTA DE VENTA: <span id="nota_de_venta" style="font-weight: 600;"></span></h4>
              <h4 class="modal-title infotitulo" style="margin: 0; padding: 0; font-weight: bold; text-align: start;">CLIENTE: <span id="nombre_cliente" style="font-weight: 600;"></span></h4>
              <h4 class="modal-title infotitulo" style="margin: 0; padding: 0; font-weight: bold; text-align: start;">DIRECCIÓN CLIENTE: <span id="direccion_cliente" style="font-weight: 600;"></span></h4>
            </div>
          </div>
          <div class="panel-body">
            <div class="col-lg-12 col-md-12 col-sm-12 table-responsive" style="padding: 15px; padding-top: 0px; background-color: white; overflow: auto;">
              <table class="detallesProductosFinal table w-100" style="width: 100% !important; margin-bottom: 0px;">
                <thead style="border-bottom: 1.5px solid black !important;">
                  <th>DESCRIPCIÓN DEL PRODUCTO</th>
                  <th>CANTIDAD</th>
                  <th>PRECIO UNITARIO</th>
                  <th>DESCUENTO</th>
                  <th>SUBTOTAL</th>
                </thead>
                <tfoot>
                  <tr>
                    <td style="width: 44%; min-width: 180px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap; text-align: end !important; font-weight: bold;">SUBTOTAL</td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap; text-align: center !important; font-weight: bold;" id="subtotal_detalle"></td>
                  </tr>
                  <tr>
                    <td style="width: 44%; min-width: 180px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap; text-align: end !important; font-weight: bold;">IGV</td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap; text-align: center !important; font-weight: bold;" id="igv_detalle"></td>
                  </tr>
                  <tr>
                    <td style="width: 44%; min-width: 180px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap; text-align: end !important; font-weight: bold;">TOTAL</td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap; text-align: center !important; font-weight: bold;" id="total_detalle"></td>
                  </tr>
                </tfoot>
                <tbody>
                </tbody>
              </table>
            </div>

            <div class="col-lg-12 col-md-12 col-sm-12 table-responsive" style="padding: 15px; padding-top: 0px; background-color: white; overflow: auto;">
              <table class="detallesPagosFinal table w-100" style="width: 100% !important; margin-bottom: 0px;">
                <thead style="border-bottom: 1.5px solid black !important;">
                  <th>DESCRIPCIÓN DE PAGOS</th>
                  <th>MONTO</th>
                </thead>
                <tfoot>
                  <tr>
                    <td style="width: 80%; min-width: 180px; white-space: nowrap; text-align: end !important; font-weight: bold;">SUBTOTAL</td>
                    <td style="width: 20%; min-width: 40px; white-space: nowrap; text-align: center !important; font-weight: bold;" id="subtotal_pagos"></td>
                  </tr>
                  <tr>
                    <td style="width: 80%; min-width: 180px; white-space: nowrap; text-align: end !important; font-weight: bold;">VUELTO</td>
                    <td style="width: 20%; min-width: 40px; white-space: nowrap; text-align: center !important; font-weight: bold;" id="vueltos_pagos"></td>
                  </tr>
                  <tr>
                    <td style="width: 80%; min-width: 180px; white-space: nowrap; text-align: end !important; font-weight: bold;">TOTAL</td>
                    <td style="width: 20%; min-width: 40px; white-space: nowrap; text-align: center !important; font-weight: bold;" id="total_pagos"></td>
                  </tr>
                </tfoot>
                <tbody>
                </tbody>
              </table>
            </div>
            <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
              <label>Comentario interno:</label>
              <textarea type="text" class="form-control" id="comentario_interno_detalle" maxlength="1000" rows="4" autocomplete="off" disabled></textarea>
            </div>
            <div class="col-lg-12 col-md-12 col-sm-12" style="text-align: center;">
              <h4 style="font-weight: bold;">ATENDIDO POR: <span id="atendido_venta" style="font-weight: 600;"></span></h4>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin modal 2 -->

    <!-- Modal 3 -->
    <div class="modal fade" id="myModal3" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog smallModal" style="width: 65%; max-height: 95vh; margin: 0 !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%); overflow-x: auto;">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #f2d150 !important; border-bottom: 2px solid #C68516 !important;">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <div style="text-align: center; display: flex; justify-content: center; flex-direction: column; gap: 5px;">
              <h4 class="modal-title infotitulo" style="margin: 0; padding: 0; font-weight: bold;">HISTORIAL DE COTIZACIONES DEL CLIENTE</h4>
              <h4 class="modal-title infotitulo cliente_detalles" style="margin: 0; padding: 0;"></h4>
            </div>
          </div>
          <div class="panel-body" style="background-color: #ecf0f5 !important; padding: 0 !important; height: max-content;">
            <div class="table-responsive" style="padding: 8px !important; padding: 20px !important; background-color: white;">
              <table id="tbldetalles2" class="table table-striped table-bordered table-condensed table-hover w-100" style="width: 100% !important">
                <thead>
                  <th style="width: 1%;">Opciones</th>
                  <th>PDF</th>
                  <th>Fecha y hora</th>
                  <th>Documento</th>
                  <th>Número Ticket</th>
                  <th>Total Venta (S/.)</th>
                  <th>Agregado por</th>
                  <th>Estado</th>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                  <th>Opciones</th>
                  <th>PDF</th>
                  <th>Fecha y hora</th>
                  <th>Documento</th>
                  <th>Número Ticket</th>
                  <th>Total Venta (S/.)</th>
                  <th>Agregado por</th>
                  <th>Estado</th>
                </tfoot>
              </table>
            </div>
          </div>
          <div class="modal-footer form-group col-lg-12 col-md-12 col-sm-12 col-xs-12" style="background-color: #f2d150 !important; border-top: 2px solid #C68516 !important;">
            <button class="btn btn-warning" type="button" data-dismiss="modal"><i class="fa fa-arrow-circle-left"></i> Regresar</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin Modal 3 -->

    <!-- Modal 4 -->
    <div class="modal fade" id="myModal4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog smallModal" style="width: 75%; max-height: 95vh; margin: 0 !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%); overflow-x: auto;">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #f2d150 !important; border-bottom: 2px solid #C68516 !important;">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <div style="text-align: center; display: flex; justify-content: center; flex-direction: column; gap: 5px;">
              <h4 class="modal-title infotitulo" style="margin: 0; padding: 0; font-weight: bold; text-align: start;">COTIZACIÓN: <span id="nota_de_venta2" style="font-weight: 600;"></span></h4>
              <h4 class="modal-title infotitulo" style="margin: 0; padding: 0; font-weight: bold; text-align: start;">CLIENTE: <span id="nombre_cliente2" style="font-weight: 600;"></span></h4>
              <h4 class="modal-title infotitulo" style="margin: 0; padding: 0; font-weight: bold; text-align: start;">DIRECCIÓN CLIENTE: <span id="direccion_cliente2" style="font-weight: 600;"></span></h4>
            </div>
          </div>
          <div class="panel-body">
            <div class="col-lg-12 col-md-12 col-sm-12 table-responsive" style="padding: 15px; padding-top: 0px; background-color: white; overflow: auto;">
              <table class="detallesProductosFinal table w-100" style="width: 100% !important; margin-bottom: 0px;">
                <thead style="border-bottom: 1.5px solid black !important;">
                  <th>DESCRIPCIÓN DEL PRODUCTO</th>
                  <th>CANTIDAD</th>
                  <th>PRECIO UNITARIO</th>
                  <th>DESCUENTO</th>
                  <th>SUBTOTAL</th>
                </thead>
                <tfoot>
                  <tr>
                    <td style="width: 44%; min-width: 180px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap; text-align: end !important; font-weight: bold;">SUBTOTAL</td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap; text-align: center !important; font-weight: bold;" id="subtotal_detalle2"></td>
                  </tr>
                  <tr>
                    <td style="width: 44%; min-width: 180px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap; text-align: end !important; font-weight: bold;">IGV</td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap; text-align: center !important; font-weight: bold;" id="igv_detalle2"></td>
                  </tr>
                  <tr>
                    <td style="width: 44%; min-width: 180px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap; text-align: end !important; font-weight: bold;">TOTAL</td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap; text-align: center !important; font-weight: bold;" id="total_detalle2"></td>
                  </tr>
                </tfoot>
                <tbody>
                </tbody>
              </table>
            </div>

            <div class="col-lg-12 col-md-12 col-sm-12 table-responsive" style="padding: 15px; padding-top: 0px; background-color: white; overflow: auto;">
              <table class="table w-100 detallesPagosFinal" style="width: 100% !important; margin-bottom: 0px;">
                <thead style="border-bottom: 1.5px solid black !important;">
                  <th>DESCRIPCIÓN DE PAGOS</th>
                  <th>MONTO</th>
                </thead>
                <tfoot>
                  <tr>
                    <td style="width: 80%; min-width: 180px; white-space: nowrap; text-align: end !important; font-weight: bold;">SUBTOTAL</td>
                    <td style="width: 20%; min-width: 40px; white-space: nowrap; text-align: center !important; font-weight: bold;" id="subtotal_pagos2"></td>
                  </tr>
                  <tr>
                    <td style="width: 80%; min-width: 180px; white-space: nowrap; text-align: end !important; font-weight: bold;">VUELTO</td>
                    <td style="width: 20%; min-width: 40px; white-space: nowrap; text-align: center !important; font-weight: bold;" id="vueltos_pagos2"></td>
                  </tr>
                  <tr>
                    <td style="width: 80%; min-width: 180px; white-space: nowrap; text-align: end !important; font-weight: bold;">TOTAL</td>
                    <td style="width: 20%; min-width: 40px; white-space: nowrap; text-align: center !important; font-weight: bold;" id="total_pagos2"></td>
                  </tr>
                </tfoot>
                <tbody>
                </tbody>
              </table>
            </div>
            <div class="col-lg-12 col-md-12 col-sm-12" style="text-align: center;">
              <h4 style="font-weight: bold;">ATENDIDO POR: <span id="atendido_venta2" style="font-weight: 600;"></span></h4>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin modal 4 -->

    <!-- Modal 5 -->
    <div class="modal fade" id="myModal5" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog smallModal" style="width: 55%; max-height: 95vh; margin: 0 !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%); overflow-x: auto;">
        <div class="modal-content" style="background-color: #f2d150 !important;">
          <div class="modal-header" style="border-bottom: 2px solid #C68516 !important;">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <div style="text-align: center; display: flex; justify-content: center; flex-direction: column; gap: 5px;">
              <h4 class="modal-title infotitulo" style="margin: 0; padding: 0; font-weight: bold;">TICKET N° <span id="num_comprobante_final"></span></h4>
            </div>
          </div>
          <div class="panel-body">
            <div class="col-lg-6 col-md-6 col-sm-6" style="padding: 0;">
              <div style="margin: 5px;">
                <a target="_blank" style="color: #333;"><button class="btn btn-secondary" style="width: 100%; text-align: center; font-weight: bold;" type="button">GENERAR TICKET</button></a>
              </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-6" style="padding: 0;">
              <div style="margin: 5px;">
                <a target="_blank" style="color: #333;"><button class="btn btn-secondary" style="width: 100%; text-align: center; font-weight: bold;" type="button">GENERAR PDF-A4</button></a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin modal 5 -->

    <!-- Modal 6 -->
    <div class="modal fade" id="myModal6" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog smallModal" style="width: 55%; max-height: 95vh; margin: 0 !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%); overflow-x: auto;">
        <div class="modal-content" style="background-color: #f2d150 !important;">
          <div class="modal-header" style="border-bottom: 2px solid #C68516 !important;">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <div style="text-align: center; display: flex; justify-content: center; flex-direction: column; gap: 5px;">
              <h4 class="modal-title infotitulo" style="margin: 0; padding: 0; font-weight: bold;">TICKET N° <span id="num_comprobante_final2"></span></h4>
            </div>
          </div>
          <div class="panel-body">
            <div class="col-lg-6 col-md-6 col-sm-6" style="padding: 0;">
              <div style="margin: 5px;">
                <a target="_blank" style="color: #333;"><button class="btn btn-secondary" style="width: 100%; text-align: center; font-weight: bold;" type="button">GENERAR TICKET</button></a>
              </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-6" style="padding: 0;">
              <div style="margin: 5px;">
                <a target="_blank" style="color: #333;"><button class="btn btn-secondary" style="width: 100%; text-align: center; font-weight: bold;" type="button">GENERAR PDF-A4</button></a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin modal 6 -->
  <?php
  } else {
    require 'noacceso.php';
  }

  require 'footer.php';
  ?>
  <script type="text/javascript" src="scripts/clientes1.js"></script>
<?php
}
ob_end_flush();
?>