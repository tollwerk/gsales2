<?php

/**
 * 
 * Default gSales 2 PDF Dunning Template
 * Author: gSales Development Team - Gedankengut GbR Manuel Häuser & Gökhan Sirin
 * 
 * http://www.gsales.de
 * http://www.gedankengut.de
 * 
 * Copyright 2010 - Gedankengut GbR
 * 
 * Damit gSales Updates nicht die eigenen Änderungen überschreiben, sollte diese Datei kopiert (tpl.meinName.php) und dann auf die eigenen Bedürfnisse angepasst werden!
 * Die Weitergabe von modifizierten Rechnungstemplates an andere gSales Benutzer über unser Supportforum (http://forum.gsales.de) ist gestattet.
 * 
 */


// PDF Datei als Briefpapier hinterlegen? (empfohlen)

	/*
		Um das optimale Ergebnis zu erzielen lege Texte und wenn möglich dein Firmenlogo in der Vorlage als Vektoren an.
		Dadurch erhälst du bei Zoomen eine gleichbleibende Qualität und die Dateigröße deiner Rechnungen bleibt schön klein was beim Versand per E-Mail
		ein wichtiger Faktor ist.
	*/

$arrPDFConfig['use_stationery_pdf'] = $this->refCore->cfg->v('pdf_dunning_stationary');
$arrPDFConfig['stationery_pdf_file'] = $this->refCore->cfg->v('path_absolute').$this->refCore->cfg->v('pdf_dunning_stationary_file'); # bitte pdf Datei entsprechend hochladen!


// Mini Absender
$arrPDFConfig['print_minisender'] = $this->refCore->cfg->v('pdf_dunning_printminisender');


// Firmenlogo einbinden

	/*
		Achtung! Möglich sind folgende Dateiformate
		- JPG Bilder (Graustufenbilder, Truecolor 24 Bit, CMYK 32 Bit)
		- PNG Bilder (Graustufenbilder 8 Bit & 256 Graustufen, Farbpaletten, Truecolor 24 Bit)
	*/

$arrPDFConfig['use_picture'] = $this->refCore->cfg->v('pdf_dunning_picture');
$arrPDFConfig['picture_file'] = $this->refCore->cfg->v('path_absolute').$this->refCore->cfg->v('pdf_dunning_picture_file'); # gSales Logo als Beispiel, bitte eigene Verwenden damit diese bei Updates nicht versehentlich überschrieben wird
$arrPDFConfig['picture_width'] = $this->refCore->cfg->v('pdf_dunning_picture_width');
$arrPDFConfig['picture_posX'] = $this->refCore->cfg->v('pdf_dunning_picture_posx');
$arrPDFConfig['picture_posY'] = $this->refCore->cfg->v('pdf_dunning_picture_posy');	


// Fußzeilenblöcke

$arrPDFConfig['print_footer_blocks'] = $this->refCore->cfg->v('pdf_dunning_printfooter');


// Fußzeilenblöcke Content (verwendet den Konfigurationsreiter "Meine Daten")

if ($this->refCore->cfg->v('me_ustidnr') != '' && $this->refCore->cfg->v('me_taxno')){
	$arrTaxFooter = array('headline'=>'Steuernummern', 'txt'=>'USt-IdNr. '.$this->refCore->cfg->v('me_ustidnr')."\nSt.-Nr. ".$this->refCore->cfg->v('me_taxno'));	
} else {
	if ($this->refCore->cfg->v('me_ustidnr') != '') $arrTaxFooter = array('headline'=>'USt-IdNr.', 'txt'=>$this->refCore->cfg->v('me_ustidnr'));		
	else $arrTaxFooter = array('headline'=>'Steuernummer', 'txt'=>$this->refCore->cfg->v('me_taxno'));		
}

$arrContactFooter = array('headline'=>'Anschrift', 		'txt'=>$this->refCore->cfg->v('me_company')."\n".$this->refCore->cfg->v('me_address')."\n".$this->refCore->cfg->v('me_countrycode').$this->refCore->cfg->v('me_zip').' '.$this->refCore->cfg->v('me_city'));	
if ($this->refCore->cfg->v('me_owner') != '' || $this->refCore->cfg->v('me_manager') != '') $arrContactFooter['txt'] .= "\n";
if ($this->refCore->cfg->v('me_owner') != '') $arrContactFooter['txt'] .= "\n".'Inh. '.$this->refCore->cfg->v('me_owner');
if ($this->refCore->cfg->v('me_manager') != '') $arrContactFooter['txt'] .= "\n".'GF '.$this->refCore->cfg->v('me_manager');

$arrBankFooter = array('headline'=>'Bankverbindung', 'txt'=>'');
if ($this->refCore->cfg->v('me_bank') != '') $arrBankFooter['txt'] .= $this->refCore->cfg->v('me_bank')."\n";
if ($this->refCore->cfg->v('me_bankaccount') != '') $arrBankFooter['txt'] .= 'Konto '.$this->refCore->cfg->v('me_bankaccount')."\n";
if ($this->refCore->cfg->v('me_bankcode') != '') $arrBankFooter['txt'] .= 'BLZ '.$this->refCore->cfg->v('me_bankcode')."\n";
if ($this->refCore->cfg->v('me_bankiban') != '') $arrBankFooter['txt'] .= 'IBAN '.$this->refCore->cfg->v('me_bankiban')."\n";
if ($this->refCore->cfg->v('me_bankbic') != '') $arrBankFooter['txt'] .= 'BIC '.$this->refCore->cfg->v('me_bankbic')."\n";

// footer im gSales 1 stil = textfelder aus konfiguration

$arrPDFConfig['footer_head_1'] = $this->refCore->cfg->v('pdf_footer_headline_1');
$arrPDFConfig['footer_1'] = $this->refCore->cfg->v('pdf_footer_1');
$arrPDFConfig['footer_head_2'] = $this->refCore->cfg->v('pdf_footer_headline_2');
$arrPDFConfig['footer_2'] = $this->refCore->cfg->v('pdf_footer_2');
$arrPDFConfig['footer_head_3'] = $this->refCore->cfg->v('pdf_footer_headline_3');
$arrPDFConfig['footer_3'] = $this->refCore->cfg->v('pdf_footer_3');
$arrPDFConfig['footer_head_4'] = $this->refCore->cfg->v('pdf_footer_headline_4');
$arrPDFConfig['footer_4'] = $this->refCore->cfg->v('pdf_footer_4');

// footer aus "meine daten"

$arrPDFConfig['arrFooter'] = array(
	$arrContactFooter,
	array('headline'=>'Kontakt', 		'txt'=>"Tel. ".$this->refCore->cfg->v('me_phone')."\nFax ".$this->refCore->cfg->v('me_fax')."\n".$this->refCore->cfg->v('me_mail')."\n".$this->refCore->cfg->v('me_web')),
	$arrTaxFooter,
	$arrBankFooter
);


// Schrift definieren

$arrPDFConfig['font'] = 'Arial';
$arrPDFConfig['font_size_tiny'] = 6; // Verwendung in Mini-Absender
$arrPDFConfig['font_size_small'] = 8;  // Verwendung in Überschriften der Positionstabelle & Fußzeilenblöcken
$arrPDFConfig['font_size_big'] = 14; // Verwendung in Dokumenten-Headline
$arrPDFConfig['font_size'] = 10; // Standardgröße für alles weitere


// Währungseinstellungen

$arrPDFConfig['currency'] = $this->refCore->cfg->v('currency_symbol');

$arrPDFConfig['currency_space'] = '';
if ($this->refCore->cfg->v('currency_symbol_spacing') == true) $arrPDFConfig['currency_space'] = ' ';

$arrPDFConfig['currency_pre'] = false;
if ($this->refCore->cfg->v('currency_symbol_before_number') == true) $arrPDFConfig['currency_pre'] = true;

if (trim($arrPDFConfig['currency'] == '€')) $arrPDFConfig['currency'] = chr(128); // € Symbol fix


// Sonstige Einstellungen

$arrPDFConfig['offsetX'] = 20; // linker Rand
$arrPDFConfig['limitToY'] = 265; // Wann soll ein Seitenumbruch geschehen?
//$arrPDFConfig['restartAtY'] = 105; // Ab welcher Position geht es nach einem Seitenumbruch auf der neuen Seite weiter?
$arrPDFConfig['restartAtY'] = 78; // Ab welcher Position geht es nach einem Seitenumbruch auf der neuen Seite weiter?
$arrPDFConfig['alternateBg'] = $this->refCore->cfg->v('pdf_alternate_bg'); // Alternierender Hintergrund

// Absender (kann auch über die gSales Konfiguration beinflusst werden)

$arrPDFConfig['company_sender'] = $this->refCore->cfg->v('me_company').' - '.$this->refCore->cfg->v('me_address').' - '.trim($this->refCore->cfg->v('me_countrycode').' '.$this->refCore->cfg->v('me_zip')).' '.$this->refCore->cfg->v('me_city');


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





















// Ab hier: Entwicklermodus ;)




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
	

	
	
	
// Fremdnutzung des Templates verhindern!

if ($var_array['type'] != 'dunning'){
	$this->refCore->setError('PDF Fehlgeschlagen! Das ausgewählte Template unterstützt nur die Generierung von Mahnungen!');
	return false;
}
	

$pdf = new PDF_TEMPLATE_DEFAULT_DUNNING('P', 'mm', 'A4'); 
$pdf->SetAutoPageBreak(true);
$pdf->booOutputHeader = true;


$pdf->passConfiguration($arrPDFConfig);
$pdf->passGSalesData($var_array);

$pdf->AddPage();

$pdf->SetXY($arrPDFConfig['offsetX'],100);
$tmpY = $pdf->GetY();


// Einleitungstext

if ($var_array['dunning']['var_txt_intro'] != ''){

	$pdf->SetXY($arrPDFConfig['offsetX'],$tmpY);
	$pdf->SetFont($arrPDFConfig['font'], '', $arrPDFConfig['font_size'] );
	$pdf->MultiCell(170, 5, $pdf->pdfText($var_array['dunning']['var_txt_intro']),0,'L');
	
	$tmpY = $pdf->GetY()+3;
}

// Positionstabelle (Headlines)

$pdf->posTableHeadlines($tmpY+5);


// Mahnung ohne Rechnungen => Aussteigen
if (!is_array($var_array['dunning']['invoicelist'])){
	$this->refCore->setError('Keine Rechnungen vorhanden. pdf Datei für Mahnung konnte nicht erstellt werden.');
	return false;		
}

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