<?php

$pdf->AddFont('GraublauSans', '', 'GraublauSans-Regular.php');
$pdf->AddFont('GraublauSansProp', '', 'GraublauSans-Regular_prop.php');
$pdf->AddFont('GraublauSansMono', '', 'GraublauSans-Regular_mono.php');
$pdf->AddFont('GraublauSans', 'B', 'GraublauSans-Bold.php');
$pdf->AddFont('GraublauSansProp', 'B', 'GraublauSans-Bold_prop.php');
$pdf->AddFont('GraublauSansMono', 'B', 'GraublauSans-Bold_mono.php');
$pdf->AddFont('GraublauSans', 'I', 'GraublauSans-Light.php');
$pdf->AddFont('GraublauSansProp', 'I', 'GraublauSans-Light_prop.php');
$pdf->AddFont('GraublauSansMono', 'I', 'GraublauSans-Light_mono.php');

// Register styles for HTML formats
$pdf->SetStyle2('b', 'GraublauSansProp', 'B', '', '');
$pdf->SetStyle2('strong', 'GraublauSansProp', 'B', '', '');
$pdf->SetStyle2('a', 'GraublauSansProp', 'U', '', '0,148,170');

$pdf->SetAutoPageBreak(false);
$pdf->passConfiguration($arrPDFConfig);
$pdf->passGSalesData($var_array);