<?php
//Activamos el almacenamiento en el buffer
ob_start();
if (strlen(session_id()) < 1)
  session_start();

if (!isset($_SESSION["nombre"])) {
  echo 'Debe ingresar al sistema correctamente para visualizar el reporte';
} else {
  if ($_SESSION['ventas'] == 1) {

    require('PDF_MC_Table.php');

    $pdf = new PDF_MC_Table();

    $pdf->AddPage('L');

    $y_axis_initial = 25;

    $pdf->SetFont('Arial', 'B', 12);

    $pdf->Cell(40, 6, '', 0, 0, 'C');
    $pdf->Cell(190, 6, 'LISTA DE VENTAS', 1, 0, 'C');
    $pdf->Ln(10);

    $pdf->SetFillColor(232, 232, 232);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(37, 6, 'Fecha y hora', 1, 0, 'C', 1);
    $pdf->Cell(55, 6, 'Usuario', 1, 0, 'C', 1);
    $pdf->Cell(95, 6, 'Cliente', 1, 0, 'C', 1);
    $pdf->Cell(33, 6, 'Documento', 1, 0, 'C', 1);
    $pdf->Cell(25, 6, utf8_decode('Número'), 1, 0, 'C', 1);
    $pdf->Cell(32, 6, 'Total', 1, 0, 'C', 1);

    $pdf->Ln(10);

    require_once "../modelos/Venta.php";
    $venta = new Venta();

    $idusuario = $_SESSION["idusuario"];
    $cargo = $_SESSION["cargo"];

    $rspta = $venta->listar();

    $pdf->SetWidths(array(37, 55, 95, 33, 25, 32));

    while ($reg = $rspta->fetch_object()) {
      $fecha = $reg->fecha;
      $usuario = $reg->usuario . " " . $reg->apellido;
      $cliente = $reg->cliente;
      $tipo_comprobante = $reg->tipo_comprobante;
      $num_comprobante = $reg->num_comprobante;
      $total_venta = $reg->total_venta;

      $pdf->SetFont('Arial', '', 10);
      $pdf->Row(array($fecha, utf8_decode($usuario), utf8_decode($cliente), $tipo_comprobante, $num_comprobante, $total_venta));
    }

    $pdf->Output();
?>
<?php
  } else {
    echo 'No tiene permiso para visualizar el reporte';
  }
}
ob_end_flush();
?>