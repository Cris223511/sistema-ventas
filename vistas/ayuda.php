<?php
//Activamos el almacenamiento en el buffer
ob_start();
session_start();

if (!isset($_SESSION["nombre"])) {
  header("Location: login.html");
} else {
  require 'header.php';
?>
  <div class="content-wrapper">
    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="box">
            <div class="box-header with-border">
              <h1 class="box-title">Ayuda</h1>
              <div class="box-tools pull-right">
              </div>
            </div>
            <div class="panel-body">
              <h4><strong>Soporte: </strong></h4>
              <p>Si necesitas ayuda o más información, contáctate con nosotros llamando al <strong>+51 992 719 552</strong></p>
              <h4><strong>Empresa: </strong></h4>
              <p>Hakan Importa S.A.C.</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
  <?php
  require 'footer.php';
  ?>
<?php
}
ob_end_flush();
?>