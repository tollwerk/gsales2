<?php

// Prevent template usage for standard routines
if ($var_array['type'] != 'dunning'){
	$this->refCore->setError('PDF Fehlgeschlagen! Das ausgewählte Template unterstützt nur die Generierung von Mahnungen!');
	return false;
}

// Prevent generation of dunning without invoice
if (!is_array($var_array['dunning']['invoicelist'])){
	$this->refCore->setError('Keine Rechnungen vorhanden. pdf Datei für Mahnung konnte nicht erstellt werden.');
	return false;
}

// Require autoloader & FPDF
require_once __DIR__ . '/tollwerk/vendor/autoload.php';
require_once __DIR__ . '/tollwerk/fpdf/tfpdih.php';

// Set the locale for localized date output
$locale = setlocale(LC_ALL, 0);
setlocale(LC_ALL, 'de_DE');

// Define the specific FPDF class
if (!class_exists('PDF_TEMPLATE_DEFAULT_DUNNING')) {
	/**
	 * Class PDF_TEMPLATE_DEFAULT_DUNNING
	 */
	class PDF_TEMPLATE_DEFAULT_DUNNING extends TFPDIH
	{
		/**
		 * Print the page header
		 */
		public function Header()
		{
			$this->IncludeTemplate();
			$this->PrintDevelopmentGrid();

			// Print recipient on the first page only
			if ($this->PageNo() == 1) {

				// Mini sender above recipient
				if ($this->pdfCfg['print_minisender']) {
					$this->SetXY($this->pdfCfg['offsetX'], 47);
					$this->SetFont($this->pdfCfg['font'], 'I', $this->pdfCfg['font_size_tiny']);
					$this->Cell(100, 0, $this->PdfText($this->pdfCfg['company_sender']));
				}

				// Recipient
				$this->SetXY($this->pdfCfg['offsetX'], $this->pdfCfg['startDocInfo'] - 1);
				$this->SetFont($this->pdfCfg['font'], '', $this->pdfCfg['font_size']);
				$this->MultiCell(100, 5, $this->PdfText($this->pdfData['base']['recipient_txt']), 0, 'L');


				// Document headline
				$documentHeadlines = [
					'invoices' => 'label_invoice',
					'offers' => 'label_offer',
					'refunds' => 'label_refund',
					'sales' => 'label_sale',
					'deliveries' => 'label_delivery',
				];
				$strHeadline = $this->pdfCfg[$documentHeadlines[$this->pdfData['type']]] . ' ' . $this->pdfData['base']['invoiceno'];
				if ($this->pdfData['base']['status_id'] == 2 && $this->pdfData['type'] != 'offers') {
					$strHeadline .= ' (' . $this->pdfCfg['label_canceled'] . ')';
				}
				$this->SetXY($this->pdfCfg['offsetX'], $this->pdfCfg['startAtY']);
				$this->SetFont($this->pdfCfg['font'], 'B', $this->pdfCfg['font_size_big']);
				$this->Cell(100, 0, $this->PdfText($strHeadline));

				// Date
				$this->SetFont($this->pdfCfg['font'], '', $this->pdfCfg['font_size']);
				$this->SetXY(0, $this->pdfCfg['startAtY']);
				$this->Cell($this->pdfCfg['offsetX'] + $this->pdfCfg['fullwidth'], 0,
					$this->PdfText($this->pdfCfg['label_city'] . ' ' . utf8_encode(trim(strftime('%e. %B %Y',
							strtotime($this->pdfData['base']['created']))))), 0, 0, 'R');

				$ys = $this->pdfCfg['startDocInfo'];
			} else {
				$ys = $this->pdfCfg['restartDocInfo'];
			}

			// Document info block
			if ($this->booOutputHeader) {
				$this->DocInfo($ys, $this->pdfCfg['docInfoLineHeight']);
			}

			// Set initial vertical offset for body
			$this->SetY($this->pdfCfg['startBodyAtY']);
		}
	}
}

$arrPDFConfig['use_stationery_pdf'] = $this->refCore->cfg->v('pdf_dunning_stationary');
$arrPDFConfig['stationery_pdf_file'] = $this->refCore->cfg->v('path_absolute').$this->refCore->cfg->v('pdf_dunning_stationary_file'); # bitte pdf Datei entsprechend hochladen!

// Include configuration
include __DIR__ . '/tollwerk/config/common.inc.php';

// Labels
$arrPDFConfig['label_1_step'] = $this->refCore->cfg->v('pdf_d_label_1_step');
$arrPDFConfig['label_2_step'] = $this->refCore->cfg->v('pdf_d_label_2_step');
$arrPDFConfig['label_3_step'] = $this->refCore->cfg->v('pdf_d_label_3_step');
$arrPDFConfig['label_date'] = $this->refCore->cfg->v('pdf_d_label_date');
$arrPDFConfig['label_customerno'] = $this->refCore->cfg->v('pdf_d_label_customerno');
$arrPDFConfig['label_page'] = $this->refCore->cfg->v('pdf_d_label_page');
$arrPDFConfig['label_invoiceno'] = $this->refCore->cfg->v('pdf_d_label_invoiceno');
$arrPDFConfig['label_invoicedate'] = $this->refCore->cfg->v('pdf_d_label_invoicedate');
$arrPDFConfig['label_invoicepart'] = $this->refCore->cfg->v('pdf_d_label_invoicepart');
$arrPDFConfig['label_invoicetopay'] = $this->refCore->cfg->v('pdf_d_label_invoicetopay');
$arrPDFConfig['label_subtotal'] = $this->refCore->cfg->v('pdf_d_label_subtotal');
$arrPDFConfig['label_total'] = $this->refCore->cfg->v('pdf_d_label_total');

if (!defined('FPDF_FONTPATH')) {
	define(FPDF_FONTPATH, dirname(__DIR__).'/fpdf/font/');
}

// Im Blanko keine Bilder, Briefpapier und Fußzeilenblöcke ausgeben
if ($booBlanko){
	$arrPDFConfig['use_stationery_pdf'] = false;
	$arrPDFConfig['use_picture'] = false;
	$arrPDFConfig['print_footer_blocks'] = false;
}


if (!class_exists('PDF_TEMPLATE_DEFAULT_DUNNING')){ // klasse nur beim ersten einbinden deklarieren
	
	class PDF_TEMPLATE_DEFAULT_DUNNING extends FPDI {

		public $pdfCfg;
		public $pdfData;
		public $booOutputHeader;
		
		function passConfiguration($arrConfig){
			$this->pdfCfg = $arrConfig;
		}
		
		function passGsalesData($arrData){
			$this->pdfData = $arrData;
		}
		
		function Header(){
			
			if($this->pdfCfg['use_stationery_pdf']){
				$this->setSourceFile($this->pdfCfg['stationery_pdf_file']);
				$intTempplateId = $this->ImportPage(1);
				$this->useTemplate($intTempplateId,0);
			}			
			
			/*
			// Development: XY Skala eindrucken
			$this->SetXY(0, 0);
			$this->SetFont($this->pdfCfg['font'], '', 4);
			for ($i=0;$i<300;$i+=3) {
				$this->SetXY($i,3); $this->Cell(3,3,$i);
				$this->SetXY(3,$i); $this->Cell(3,3,$i);
			}
			*/

			// Bild eindrucken
			
			if ($this->pdfCfg['use_picture']){
				$this->Image($this->pdfCfg['picture_file'], $this->pdfCfg['picture_posX'] , $this->pdfCfg['picture_posY'] , $this->pdfCfg['picture_width']);	
			}

			
			// Absender & Anschrift nur auf die erste Seite drucken
			
			if ($this->PageNo() == 1){
				
				// Mini-Absender über Empfänger
				if ($this->pdfCfg['print_minisender']){
					$this->SetXY($this->pdfCfg['offsetX'],50);
					$this->SetFont($this->pdfCfg['font'], '', $this->pdfCfg['font_size_tiny']);
					$this->Cell(100,0,$this->pdfText($this->pdfCfg['company_sender']));
				}
				
				// Empfängeranschrift
				$this->SetXY($this->pdfCfg['offsetX'],55);
				$this->SetFont($this->pdfCfg['font'], '', $this->pdfCfg['font_size']);
				$this->MultiCell(100,5, $this->pdfText($this->pdfData['base']['recipient_txt']),0,'L');
			
			}				
			
			// Headline
			//$this->SetXY($this->pdfCfg['offsetX'],90);
			$this->SetXY($this->pdfCfg['offsetX'],96);
			$this->SetFont($this->pdfCfg['font'], 'B', $this->pdfCfg['font_size_big']);
			$this->Cell(100,0,$this->pdfText($this->pdfCfg['label_'.$this->pdfData['dunning']['level'].'_step']));
			
			
			// Rechte Seite
			// Dokument-Daten auf Höhe der Empfängeranschrift beginnen
			
			$ys = 55;
			
			if ($this->booOutputHeader){
			
				// Datum Zeile
				
				$this->SetXY(140, $ys);
				$this->SetFont($this->pdfCfg['font'], 'B', $this->pdfCfg['font_size']);
				$this->Cell(25,0,$this->pdfText($this->pdfCfg['label_date']),0);

				$this->SetXY(175, $ys);
				$this->SetFont($this->pdfCfg['font'], '', $this->pdfCfg['font_size']);
				$this->Cell(20,0,$this->pdfText(date('d.m.Y')), 0, 0, 'R' );
				
				
				$ys +=5;
				
				
				if (trim($this->pdfData['base']['customerno']) != ''){
					
					// Kunden-Nr. Zeile
					
					$this->SetXY(140, $ys);
					$this->SetFont($this->pdfCfg['font'], 'B', $this->pdfCfg['font_size']);
					$this->Cell(25,0,$this->pdfText($this->pdfCfg['label_customerno']),0);
	
					$this->SetXY(175, $ys);
					$this->SetFont($this->pdfCfg['font'], '', $this->pdfCfg['font_size']);
					$this->Cell(20,0,$this->pdfText($this->pdfData['base']['customerno']), 0, 0, 'R' );
					
					$ys +=5;
					
				}
			
			}
				
		}
		
		function Footer(){
			
			if (false == $this->pdfCfg['print_footer_blocks']) return;
			
			// Fußzeilenblöcke ausgeben
			
			$arrFooter = $this->pdfCfg['arrFooter'];
			if (!is_array($arrFooter)) return false;
			
			// Footer überschreiben mit komplett manuellen Fußzeilenblöcken aus Konfiguration (gSales 1 Stil)

			if ($this->pdfCfg['footer_1'] != '' || $this->pdfCfg['footer_2'] != '' || $this->pdfCfg['footer_3'] != '' || $this->pdfCfg['footer_4'] != '' || $this->pdfCfg['footer_5'] != ''){
				unset($arrFooter);
				for ($i=1;$i<=5;$i++) $arrFooter[] = array('headline'=> $this->pdfCfg['footer_head_'.$i], 'txt'=>$this->pdfCfg['footer_'.$i]);
			}

			$intYOffset = 270;
			$intXStart = 15;
			$intXStop = 230; // Diesen Wert modifizieren um die horizontale Verteilung der Fußzeilenblöcke zu beeinflussen
			$intCalculatedSpace = $intXStop-$intXStart;
			$intCalculatedSpacePerBlock = floor($intCalculatedSpace/5);

			foreach ((array)$arrFooter as $key => $value){
				$this->SetXY($intXStart+($key*$intCalculatedSpacePerBlock), $intYOffset);
				$this->SetFont($this->pdfCfg['font'], 'B', 7);
				$this->Cell($intCalculatedSpacePerBlock,0,$this->pdfText($value['headline']));

				$this->SetXY($intXStart+($key*$intCalculatedSpacePerBlock), $intYOffset+2);
				$this->SetFont($this->pdfCfg['font'], '', 7);
				$this->MultiCell($intCalculatedSpacePerBlock,3,$this->pdfText($value['txt']),0,'L');
			}

			//reset font settings
			$this->SetFont($this->pdfCfg['font'], '', $this->pdfCfg['font_size']);

		}
		
		function posTableHeadlines($intY){
			$this->SetFont($this->pdfCfg['font'], 'B', $this->pdfCfg['font_size_small']);
			
			$this->setXY(22, $intY);
			$this->Cell(20,'',$this->pdfText($this->pdfCfg['label_invoiceno']),0,0);
			
			$this->setXY(60, $intY);
			$this->Cell(10,'',$this->pdfText($this->pdfCfg['label_invoicedate']),0,0);
			
			$this->setXY(140, $intY);
			$this->Cell(10,'',$this->pdfText($this->pdfCfg['label_invoicepart']),0,0,'R');
			
			$this->setXY(180, $intY);
			$this->Cell(20,'',$this->pdfText($this->pdfCfg['label_invoicetopay']),0,0,'R');
			
			$this->Ln(4);
			
		}
		
		function getFormatedStandardCurrency($value){
			if ($this->pdfCfg['currency_pre'] == true){
				return $this->pdfCfg['currency'].$this->pdfCfg['currency_space'].number_format($value,2,',','.');
			} else {
				return number_format($value,2,',','.').$this->pdfCfg['currency_space'].$this->pdfCfg['currency'];
			}
		}		
		
		function getFormatedForeignCurrency($value, $arrInvoiceBase, $booDisplayMoreDecimals=false){
			
			$strCurrencySymbol = $arrInvoiceBase['curr_symbol'];
			
			$booCurrencySpacing = false;
			if ($arrInvoiceBase['curr_spacing'] == 1) $booCurrencySpacing = true;
			
			$booCurrencyBeforeNumber = false;
			if ($arrInvoiceBase['curr_before_number'] == 1) $booCurrencyBeforeNumber = true;
			
			
			$intDisplayDecimals = 2;
			if ($booDisplayMoreDecimals) $intDisplayDecimals = $this->pdfCfg['displayDecimals'];
			return gsForeignCurrency($value, $strCurrencySymbol, true, $booCurrencySpacing, $booCurrencyBeforeNumber, $intDisplayDecimals);
			
		}		
		
		function euroReplace($string){
			return str_replace('€',chr(128),$string);
		}
		
		function pdfText($string){
			if (function_exists('iconv')){
				$retString = iconv('utf8', 'cp1252', html_entity_decode($string,ENT_QUOTES));
				if ($retString == false) $retString = iconv('utf-8', 'cp1252', html_entity_decode($string,ENT_QUOTES)); // auf manchen systemen korrekterweise utf-8 anstelle von utf8
				if ($retString) return $retString;
			}
			return utf8_decode(html_entity_decode($this->euroReplace($string),ENT_QUOTES));
		}			
		
		function WordWrap($text, $maxwidth){
			
		    $text = trim($text);
		    if ($text==='') return 0;
		    $space = $this->GetStringWidth(' ');
		    $lines = explode("\n", $text);
		    $text = '';
		    $count = 0;
		
		    foreach ($lines as $line){
		        $words = preg_split('/ +/', $line);
		        $width = 0;
		        foreach ($words as $word){
		            $wordwidth = $this->GetStringWidth($word);
		            if ($width + $wordwidth <= $maxwidth){
		                $width += $wordwidth + $space;
		                $text .= $word.' ';
		            } else {
		                $width = $wordwidth + $space;
		                $text = rtrim($text)."\n".$word.' ';
		                $count++;
		            }
		        }
		        $text = rtrim($text)."\n";
		        $count++;
		    }
		    $text = rtrim($text);
		    return $count;

		}
		
	}

}

// Create and configure FPDF instance
$pdf = new PDF_TEMPLATE_DEFAULT_DUNNING('P', 'mm', 'A4');
include __DIR__ . '/tollwerk/config/pdf.inc.php';

/**********************************************************************
 * Start output
 *********************************************************************/
$pdf->AddPage();
$pdf->SetXY($arrPDFConfig['offsetX'], $arrPDFConfig['startBodyAtY']);
$tmpY = $pdf->GetY();

/**********************************************************************
 * Salutation
 *********************************************************************/
if ($var_array['base']['customer_title'] === 'Frau') {
	$salutation = sprintf($arrPDFConfig['label_salutation_female'], $var_array['base']['customer_lastname']);
} elseif (!strncmp($var_array['base']['customer_title'], 'Herr', 4)) {
	$salutation = sprintf($arrPDFConfig['label_salutation_male'], $var_array['base']['customer_lastname']);
} else {
	$salutation = $arrPDFConfig['label_salutation'];
}
$pdf->SetXY($arrPDFConfig['offsetX'], $tmpY);
$pdf->SetFont($arrPDFConfig['font'], '', $arrPDFConfig['font_size']);
$pdf->Cell(150, '', $pdf->PdfText($salutation), 0);
$tmpY = $pdf->GetY() + $arrPDFConfig['paragraphSpace'];

/**********************************************************************
 * Introduction
 *********************************************************************/
if ($var_array['dunning']['var_txt_intro'] != '') {
	$pdf->SetXY($arrPDFConfig['offsetX'], $tmpY);
	$pdf->SetFont($arrPDFConfig['font'], '', $arrPDFConfig['font_size']);
	$pdf->MultiCell($arrPDFConfig['fullwidth'], 5, $pdf->PdfText($var_array['dunning']['var_txt_intro']), 0, 'L');
	$tmpY = $pdf->GetY() + $arrPDFConfig['paragraphSpace'];
}

/**********************************************************************
 * Pre-register discount
 *********************************************************************/
$booHasDiscountedItems = false;
foreach ($var_array['pos'] as $key => $value) {
	if ($value['discount'] > 0) {
		$booHasDiscountedItems = true;
		break;
	}
}
unset($key, $value);
$booShowDiscountCol = true;
if ($booHasDiscountedItems == false && $arrPDFConfig['hideDiscount']) {
	$booShowDiscountCol = false;
}

/**********************************************************************
 * Item table headers
 *********************************************************************/
$pdf->PositionTableHeadlines($tmpY + $arrPDFConfig['paragraphSpace'], $booShowDiscountCol);



$posCounter=0;
foreach ($var_array['dunning']['invoicelist'] as $key => $value){
	
	$tmpY = $pdf->GetY();

	// Wo soll die Zeile für die Position beginnen?
	// Muss berücksichtigt werden das die Zeile davor mehrzeilig war?
	
	if ($endeY > $tmpY) $tmpY = $endeY+4;
	else $tmpY += 4; 

	
	// überprüfen ob eine neue Seite benötigt wird.
	
	if ( (5+$tmpY) > $arrPDFConfig['limitToY'] ) {
		$posCounter=0;
		$pdf->AddPage();
		$pdf->posTableHeadlines(98);
		$tmpY=$arrPDFConfig['restartAtY'];
	}
	
	
	// Alternierenden Hintergrund eindrucken

	if ($posCounter%2 != 0 && $arrPDFConfig['alternateBg']){
		$pdf->SetFillColor(230,230,230);
		$pdf->Rect(20,$tmpY-3,183,6,'F');
	}
	
	
	// Rechnungsnummer
	
	$pdf->SetFont($arrPDFConfig['font'], '', $arrPDFConfig['font_size']);		
	$pdf->setXY(22, $tmpY);
	$pdf->Cell(20, 0, $pdf->pdfText($value['base']['invoiceno']), 0, 'L');
	
	
	// Rechnungsdatum
	
	$pdf->setXY(60, $tmpY-2.5); 
	$pdf->MultiCell(85, 5, date("d.m.Y",strtotime($value['base']['created'])), 0, 'L');
	$endeY = $pdf->GetY();

	
	// Teilzahlung
	
	if ($value['base']['partialpayment'] > 0){
		$pdf->setXY(140, $tmpY);
		$pdf->Cell(10, 0, $pdf->getFormatedStandardCurrency($value['base']['partialpayment']), 0, 0, 'R');
	}

	
	// Rechnungsbetrag
	
	$pdf->setXY(180, $tmpY);
	$pdf->Cell(20,'',$pdf->getFormatedStandardCurrency($value['base']['amount'] - $value['base']['partialpayment']),0,0,'R');
	
	
	
	$calcAmount += $value['base']['amount'] - $value['base']['partialpayment'];
	$posCounter++;
	
}
	
	
// Zwischensumme / Nettobetrag

if (($endeY+9) > $arrPDFConfig['limitToY']){ // Checken ob die nächste Zeile noch auf die Seite passt
	$pdf->AddPage();
	$endeY = $arrPDFConfig['restartAtY'];
} 

$tmpY = $endeY+3;
$pdf->SetLineWidth(0.6);
$pdf->Line(15, $tmpY, 203, $tmpY);
$tmpY = $endeY + 9;
$pdf->setXY(20, $tmpY);
$pdf->SetFont($arrPDFConfig['font'], 'B', $arrPDFConfig['font_size'] );
$pdf->Cell(20, 0, $pdf->pdfText($arrPDFConfig['label_subtotal']), 0, 0, 'L');
$pdf->setXY(180, $tmpY);
$pdf->Cell(20, 0, $pdf->getFormatedStandardCurrency($calcAmount), 0, 0, 'R');



// Mahngebühren

if (($endeY+5) > $arrPDFConfig['limitToY']){ // Checken ob die nächste Zeile noch auf die Seite passt
	$pdf->AddPage();
	$endeY = $arrPDFConfig['restartAtY'];
} 
	
$tmpY = $pdf->GetY() + 5;
$pdf->setXY(20, $tmpY);
$pdf->SetFont($arrPDFConfig['font'], '', $arrPDFConfig['font_size'] );
$pdf->Cell(20, 0, $pdf->pdfText($var_array['dunning']['kostentxt']), 0, 0, 'L');
$pdf->setXY(180, $tmpY);
$pdf->Cell(20,0,$pdf->getFormatedStandardCurrency($var_array['dunning']['kosten']),0,0,'R');


// Linie

$tmpY = $pdf->GetY()+2;
$pdf->SetLineWidth(0.25);
$pdf->Line(15,$tmpY+2.5,203,$tmpY+2.5);


// offener Gesamtbetrag

if (($endeY+7) > $arrPDFConfig['limitToY']){ // Checken ob die nächste Zeile noch auf die Seite passt
	$pdf->AddPage();
	$endeY = $arrPDFConfig['restartAtY'];
}
	
$tmpY = $pdf->GetY() + 7;
$pdf->setXY(20, $tmpY);
$pdf->SetFont($arrPDFConfig['font'], 'B', $arrPDFConfig['font_size'] );
$pdf->Cell(20,0,$pdf->pdfText($arrPDFConfig['label_total']),0,0,'L');
$pdf->setXY(180, $tmpY);
$pdf->Cell(20,0,number_format($calcAmount+$var_array['dunning']['kosten'],2,',','.').' '.$arrPDFConfig['currency'],0,0,'R');



// Doppellinie

$tmpY = $pdf->GetY();
$pdf->SetLineWidth(0.25);
$pdf->Line(15,$tmpY+2,203,$tmpY+2);
$pdf->Line(15,$tmpY+2.5,203,$tmpY+2.5);


// Abschlusstext

if ($var_array['dunning']['var_txt_outro'] != ''){

	$intRowCount = $pdf->WordWrap($pdf->pdfText($var_array['dunning']['var_txt_outro']), 170);

	if (($intRowCount*5)+$tmpY > $arrPDFConfig['limitToY']){ // Checken ob der kommende Text noch auf die Seite passt
		$pdf->AddPage(); 
		$tmpY=$arrPDFConfig['restartAtY'];
	}

	$tmpY = $pdf->GetY()+8;
	$pdf->SetFont($arrPDFConfig['font'], '', $arrPDFConfig['font_size'] );
	$pdf->SetXY($arrPDFConfig['offsetX'],$tmpY);
	$pdf->SetFont($pdf_config['font'], '', $arrPDFConfig['font_size'] );
	$pdf->MultiCell(170,5,$pdf->pdfText($var_array['dunning']['var_txt_outro']),0,'L');
	$tmpY = $pdf->GetY()+4;

}


/*
// Hoffentlich keine mehrseitigen Mahnungen :)
// Platzhalter für Gesamtzahlen ersetzen

$pdf->AliasNbPages('{nb}'); 
*/


// PDF Datei schreiben

if ($booBlanko) $pdf->Output($savetoblanko,'F');
else  $pdf->Output($saveto,'F');


// Reset für den nächsten Durchgang (blanko modus)

unset($tmpY, $endeY, $calcAmount, $posCounter);