<?php

$locale = setlocale(LC_ALL, 0);
setlocale(LC_ALL, 'de_DE');

require_once __DIR__ . '/tollwerk/vendor/autoload.php';
include __DIR__ . '/tollwerk/config/common.inc.php';

if (!class_exists('PDF_TEMPLATE_DEFAULT_INVOICE')) {
	require_once __DIR__ . '/tollwerk/fpdf/tfpdih.php';

	class PDF_TEMPLATE_DEFAULT_INVOICE extends TFPDIH
	{

		public $pdfCfg;
		public $pdfData;
		public $booOutputHeader;
		/**
		 * Vertical offset of the page number on the first page
		 *
		 * @var int
		 */
		public $firstPageCountYPos;
		/**
		 * Vertical offset of the page number on the following pages
		 *
		 * @var int
		 */
		public $followingPageCountYPos;

		/**
		 * Pass in the PDF configuration
		 *
		 * @param array $arrConfig PDF configuration
		 */
		public function passConfiguration($arrConfig)
		{
			$this->pdfCfg = $arrConfig;
		}

		/**
		 * Pass in the PDF data
		 *
		 * @param array $arrData PDF data
		 */
		function passGsalesData($arrData)
		{
			$this->pdfData = $arrData;
		}

		/**
		 * Print a header line
		 *
		 * @param string $label Label key
		 * @param string $value Value
		 * @param int $offsetY Vertical offset
		 * @param int $lineHeight Line height
		 */
		public function headerLine($label, $value, &$offsetY, $lineHeight = 5)
		{
			$this->SetXY($this->pdfCfg['offsetX'] + $this->pdfCfg['column_unit_offset'], $offsetY);
			$this->SetFont($this->pdfCfg['font'], 'I', $this->pdfCfg['font_size_small']);
			$this->Cell($this->pdfCfg['column_price_width'], 0, $this->pdfText($this->pdfCfg[$label]), 0, 0, 'R');

			$this->SetXY($this->pdfCfg['offsetX'] + $this->pdfCfg['column_unit_offset'] + $this->pdfCfg['column_price_width'] + $this->pdfCfg['column_price_gap'],
				$offsetY);
			$this->SetFont($this->pdfCfg['font'], '', $this->pdfCfg['font_size_small']);
			$this->Cell($this->pdfCfg['column_price_width'], 0, $this->pdfText($value), 0, 0, 'R');
//			$this->Cell(28, 0, $this->pdfText($value), 0);

			$offsetY += $lineHeight;
		}

		/**
		 * Print the page header
		 */
		public function Header()
		{

			if ($this->pdfCfg['use_stationery_pdf']) {
				$this->setSourceFile($this->pdfCfg['stationery_pdf_file']);
				$intTempplateId = $this->ImportPage(1);
				$this->useTemplate($intTempplateId, 0);
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

			// Absender & Anschrift nur auf die erste Seite drucken

			if ($this->PageNo() == 1) {

				// Mini-Absender über Empfänger
				if ($this->pdfCfg['print_minisender']) {
					$this->SetXY($this->pdfCfg['offsetX'], 47);
					$this->SetFont($this->pdfCfg['font'], 'I', $this->pdfCfg['font_size_tiny']);
					$this->Cell(100, 0, $this->pdfText($this->pdfCfg['company_sender']));
				}

				// Empfängeranschrift
				$this->SetXY($this->pdfCfg['offsetX'], $this->pdfCfg['startDocInfo'] - 1);
				$this->SetFont($this->pdfCfg['font'], '', $this->pdfCfg['font_size']);
				$this->MultiCell(100, 5, $this->pdfText($this->pdfData['base']['recipient_txt']), 0, 'L');


				// Dokument-Headline
				if ($this->pdfData['type'] == 'invoices') {
					$strHeadline = $this->pdfCfg['label_invoice'];
				}
				if ($this->pdfData['type'] == 'offers') {
					$strHeadline = $this->pdfCfg['label_offer'];
				}
				if ($this->pdfData['type'] == 'refunds') {
					$strHeadline = $this->pdfCfg['label_refund'];
				}
				if ($this->pdfData['type'] == 'sales') {
					$strHeadline = $this->pdfCfg['label_sale'];
				}
				if ($this->pdfData['type'] == 'deliveries') {
					$strHeadline = $this->pdfCfg['label_delivery'];
				}

				$strHeadline .= ' ' . $this->pdfData['base']['invoiceno'];

				if ($this->pdfData['base']['status_id'] == 2 && $this->pdfData['type'] != 'offers') {
					$strHeadline .= ' (' . $this->pdfCfg['label_canceled'] . ')';
				}
				$this->SetXY($this->pdfCfg['offsetX'], $this->pdfCfg['startAtY']);
				$this->SetFont($this->pdfCfg['font'], 'B', $this->pdfCfg['font_size_big']);
				$this->Cell(100, 0, $this->pdfText($strHeadline));

				// Datum
				$this->SetFont($this->pdfCfg['font'], '', $this->pdfCfg['font_size']);
				$this->SetXY(0, $this->pdfCfg['startAtY']);
				$this->Cell($this->pdfCfg['offsetX'] + $this->pdfCfg['column_unit_offset'] + $this->pdfCfg['column_price_width'] * 2 + $this->pdfCfg['column_price_gap'],
					0, $this->pdfText($this->pdfCfg['label_city'] . ' ' . trim(strftime('%e. %B %Y',
							strtotime($this->pdfData['base']['created'])))), 0, 0, 'R');

				$ys = $this->pdfCfg['startDocInfo'];

			} else {
				$ys = $this->pdfCfg['restartDocInfo'];
			}

			// Dokumentinfos
			if ($this->booOutputHeader) {
				$this->DocInfo($ys, $this->pdfCfg['docInfoLineHeight']);
			}

			$this->SetY($this->pdfCfg['startBodyAtY']);
		}

		/**
		 * Dokumentinfo rechts oben ausgeben
		 *
		 * @param int $ys Vertikaler Offset
		 * @param int $lh Zeilenhöhe
		 */
		public function DocInfo($ys, $lh)
		{

			if ($this->PageNo() == 1) {

				// Kunden-Nr. Zeile (nur ausgeben wenn eine Nummer vorhanden ist )
				if (trim($this->pdfData['base']['customerno']) != '') {
					$this->headerLine('label_customerno', $this->pdfData['base']['customerno'], $ys, $lh);
				}

				// Job-Nr. Zeile (nur ausgeben wenn eine Nummer vorhanden ist )
				if (trim($this->pdfData['base']['custom1']) != '') {
					$this->headerLine('label_jobno', str_pad($this->pdfData['base']['custom1'], 3, '0', STR_PAD_LEFT),
						$ys, $lh);
				}

				// Lieferanten-Nr. Zeile (nur ausgeben wenn eine Nummer vorhanden ist )
				if (trim($this->pdfData['customerdata']['custom1']) != '') {
					$this->headerLine('label_supplierno', $this->pdfData['customerdata']['custom1'], $ys, $lh);
				}

				// Bestell-Nr. Zeile (nur ausgeben wenn eine Nummer vorhanden ist )
				if (trim($this->pdfData['base']['custom2']) != '') {
					$this->headerLine('label_orderno', $this->pdfData['base']['custom2'], $ys, $lh);
				}
			}

			// Dokumenttypabhängige Zeilen
			if ($this->pdfData['type'] == 'invoices') {

				// Rechnungs-Nr. Zeile
				$this->headerLine('label_invoiceno', $this->pdfData['base']['invoiceno'], $ys, $lh);

				if ($this->PageNo() == 1) {

					// Lieferdatum Zeile
					if ($this->pdfData['base']['deliverydate'] != '0000-00-00 00:00:00') {
						$this->headerLine('label_deliverydate',
							date('d.m.Y', strtotime($this->pdfData['base']['deliverydate'])), $ys, $lh);
					}
				}
			}

			// Angebots-Nr. Zeile
			if ($this->pdfData['type'] == 'offers') {
				$this->headerLine('label_offerno', $this->pdfData['base']['invoiceno'], $ys, $lh);
			}

			// Lieferschein-Nr. Zeile
			if ($this->pdfData['type'] == 'deliveries') {
				$this->headerLine('label_deliveryno', $this->pdfData['base']['invoiceno'], $ys, $lh);
			}

			// Auftrags-Nr. Zeile
			if ($this->pdfData['type'] == 'sales') {
				$this->headerLine('label_saleno', $this->pdfData['base']['invoiceno'], $ys, $lh);
			}

			// Gutschrifts-Nr. Zeile
			if ($this->pdfData['type'] == 'refunds') {
				$this->headerLine('label_refundno', $this->pdfData['base']['invoiceno'], $ys, $lh);
			}

			// Autor
			if ($this->PageNo() == 1) {
				$this->headerLine('label_author', $this->getAuthorName($this->pdfData['base']['user_id']), $ys, $lh);

				// Seitenanzahl Zeile
				// TODO
//				$this->headerLine('label_page')
//
//				$this->SetXY(140, $ys);
//				$this->SetFont($this->pdfCfg['font'], 'B', $this->pdfCfg['font_size']);
//				$this->Cell(25, 0, $this->pdfText($this->pdfCfg['label_page']), 0);

				// Variable definieren für Ersetzung nach der Erstellung der Seiten

				$this->firstPageCountYPos = $ys;

				// Else: Following pages
			} else {
				$this->followingPageCountYPos = $ys;
			}
		}

		/**
		 * Query an author name from the database
		 *
		 * @param int $author Author ID
		 * @return string Author name
		 */
		public function getAuthorName($author)
		{
			$dbParams = $GLOBALS['db'][0];
			$dbConnection = mysqli_connect($dbParams['host'], $dbParams['user'], $dbParams['password'],
				$dbParams['database']);
			$authorResult = mysqli_query($dbConnection, 'SELECT fullname FROM user WHERE id = ' . $author . ' LIMIT 1');
			if ($authorResult && mysqli_num_rows($authorResult)) {
				$authorRecord = mysqli_fetch_assoc($authorResult);
				$author = $authorRecord['fullname'];
			}
			return $author;
		}

		/**
		 * Print the page footer
		 *
		 * @return bool|void
		 */
		public function Footer()
		{
			if (false == $this->pdfCfg['print_footer_blocks']) {
				return;
			}

			// Fußzeilenblöcke ausgeben

			$arrFooter = $this->pdfCfg['arrFooter'];
			if (!is_array($arrFooter)) {
				return false;
			}

			// Footer überschreiben mit komplett manuellen Fußzeilenblöcken aus Konfiguration (gSales 1 Stil)

			if ($this->pdfCfg['footer_1'] != '' || $this->pdfCfg['footer_2'] != '' || $this->pdfCfg['footer_3'] != '' || $this->pdfCfg['footer_4'] != '') {
				unset($arrFooter);
				for ($i = 1; $i <= 4; $i++) {
					$arrFooter[] = array(
						'headline' => $this->pdfCfg['footer_head_' . $i],
						'txt' => $this->pdfCfg['footer_' . $i]
					);
				}
			}

			$intYOffset = 270;
			$intXStart = 15;
			$intXStop = 230; // Diesen Wert modifizieren um die horizontale Verteilung der Fußzeilenblöcke zu beeinflussen
			$intCalculatedSpace = $intXStop - $intXStart;
			$intCalculatedSpacePerBlock = floor($intCalculatedSpace / 5);

			foreach ((array)$arrFooter as $key => $value) {
				$this->SetXY($intXStart + ($key * $intCalculatedSpacePerBlock), $intYOffset);
				$this->SetFont($this->pdfCfg['font'], 'B', 7);
				$this->Cell($intCalculatedSpacePerBlock, 0, $this->pdfText($value['headline']));

				$this->SetXY($intXStart + ($key * $intCalculatedSpacePerBlock), $intYOffset + 2);
				$this->SetFont($this->pdfCfg['font'], '', 7);
				$this->MultiCell($intCalculatedSpacePerBlock, 3, $this->pdfText($value['txt']), 0, 'L');
			}

			//reset font settings
			$this->SetFont($this->pdfCfg['font'], '', $this->pdfCfg['font_size']);

		}

		/**
		 * Print the position table headlines
		 *
		 * @param int $intY Vertical offset
		 * @param bool $booShowDiscountCol
		 */
		function posTableHeadlines($intY, $booShowDiscountCol = true)
		{
			$this->SetFont($this->pdfCfg['font'], 'I', $this->pdfCfg['font_size_small']);

			// Position column
			if ($this->pdfCfg['show_colpos']) {
				$this->setXY($this->pdfCfg['offsetX'], $intY);
				$this->Cell($this->pdfCfg['column_pos_width'] - 1, '', $this->pdfText($this->pdfCfg['label_position']),
					0, 0);
			}

			$this->setXY($this->pdfCfg['offsetX'] + $this->pdfCfg['column_pos_width'], $intY);
			$this->Cell($this->pdfCfg['column_amount_width'] - 1, '', $this->pdfText($this->pdfCfg['label_amount']), 0,
				0, 'C');

			$this->setXY($this->pdfCfg['offsetX'] + $this->pdfCfg['column_pos_width'] + $this->pdfCfg['column_amount_width'],
				$intY);
			$this->Cell(10, '', $this->pdfText($this->pdfCfg['label_description']), 0, 0);

			// Bei lieferscheinen keinen rabatt, steuer, preise eindrucken
			if ($this->pdfData['type'] != 'deliveries') {

				// Wenn ein Rabatt gezeigt werden soll
				if ($booShowDiscountCol) {
					$this->setXY($this->pdfCfg['offsetX'] + $this->pdfCfg['column_discount_offset'], $intY);
					$this->Cell($this->pdfCfg['column_discount_width'], '',
						$this->pdfText($this->pdfCfg['label_discount']), 0, 0, 'R');
				}

				// Überprüfen ob die MwSt. Spalte trotz deaktivierung angezeigt werden muss da Positionen mit MwSt. vorhanden sind
				if (false == $this->pdfCfg['showTax']) {
					$booTaxedPos = false;
					foreach ((array)$this->pdfData['pos'] as $key => $value) {
						if ($value['tax'] > 0) {
							$booTaxedPos = true;
						}
					}
				}

				$booTaxedPos = false;

				if ($this->pdfCfg['showTax'] || $booTaxedPos == true) {
					$this->setXY($this->pdfCfg['offsetX'] + $this->pdfCfg['column_discount_offset'], $intY);
					$this->Cell($this->pdfCfg['column_discount_width'], '', $this->pdfText($this->pdfCfg['label_tax']),
						0, 0, 'R');
				}

				$this->setXY($this->pdfCfg['offsetX'] + $this->pdfCfg['column_unit_offset'], $intY);
				$this->Cell($this->pdfCfg['column_price_width'], '', $this->pdfText($this->pdfCfg['label_unitprice']),
					0, 0, 'R');

				$this->setXY($this->pdfCfg['offsetX'] + $this->pdfCfg['column_unit_offset'] + $this->pdfCfg['column_price_width'] + $this->pdfCfg['column_price_gap'],
					$intY);
				$this->Cell($this->pdfCfg['column_price_width'], '', $this->pdfText($this->pdfCfg['label_amountprice']),
					0, 0, 'R');
			}

			$this->Ln(4);
			$tmpY = $this->GetY();
			$this->printHorizontalLine($tmpY);
			$this->Ln(2);
		}

		/**
		 * Format a standard currency value
		 *
		 * @param float $value Value
		 * @param bool $booDisplayMoreDecimals Display more decimals
		 * @return string Formatted value
		 */
		public function getFormatedStandardCurrency($value, $booDisplayMoreDecimals = false)
		{

			$intDisplayDecimals = 2;
			if ($booDisplayMoreDecimals) {
				$intDisplayDecimals = $this->pdfCfg['displayDecimals'];
			}

			if ($this->pdfCfg['currency_pre'] == true) {
				return $this->pdfCfg['currency'] . $this->pdfCfg['currency_space'] . gsDecimalTrim(number_format($value,
					$intDisplayDecimals, ',', '.'), ',');
			} else {
				return gsDecimalTrim(number_format($value, $intDisplayDecimals, ',', '.'),
					',') . $this->pdfCfg['currency_space'] . $this->pdfCfg['currency'];
			}
		}

		/**
		 * Format a foreign currency value
		 *
		 * @param float $value Value
		 * @param array $arrInvoiceBase Invoice data
		 * @param bool $booDisplayMoreDecimals Display more decimals
		 * @return mixed Formatted value
		 */
		public function getFormatedForeignCurrency($value, $arrInvoiceBase, $booDisplayMoreDecimals = false)
		{

			$strCurrencySymbol = $arrInvoiceBase['curr_symbol'];

			$booCurrencySpacing = false;
			if ($arrInvoiceBase['curr_spacing'] == 1) {
				$booCurrencySpacing = true;
			}

			$booCurrencyBeforeNumber = false;
			if ($arrInvoiceBase['curr_before_number'] == 1) {
				$booCurrencyBeforeNumber = true;
			}


			$intDisplayDecimals = 2;
			if ($booDisplayMoreDecimals) {
				$intDisplayDecimals = $this->pdfCfg['displayDecimals'];
			}
			return gsForeignCurrency($value, $strCurrencySymbol, true, $booCurrencySpacing, $booCurrencyBeforeNumber,
				$intDisplayDecimals);

		}

		/**
		 * Replace the Euro symbol
		 *
		 * @param string $string String
		 * @return string String with replaced Euro symbols
		 */
		public function euroReplace($string)
		{
			return str_replace('€', chr(128), $string);
		}

		/**
		 * Normalize text for output
		 *
		 * @param string $string Text
		 * @return string Normalized text
		 */
		function pdfText($string)
		{
			$string = str_replace('&nbsp;', ' ', $string); // html_entity decode funktioniert nicht ... wieso?
			$retString = false;

			if (function_exists('iconv')) {
				$retString = iconv('utf8', 'cp1252', html_entity_decode($string, ENT_QUOTES));
				if ($retString == false) {
					$retString = iconv('utf-8', 'cp1252', html_entity_decode($string, ENT_QUOTES));
				} // auf manchen systemen korrekterweise utf-8 anstelle von utf8
			}

			if ($retString === false) {
				$retString = utf8_decode(html_entity_decode($this->euroReplace($string), ENT_QUOTES));
			}

			$markdown = $this->prepareMarkdown($retString);
//			$withoutTags = strip_tags($markdown, '<b><strong><a><ul><ol><li>');
			$withoutTags = strip_tags($markdown, '<b><strong><a>');
			return trim($withoutTags);
		}

		/**
		 * Prepare and parse a MarkDown string
		 *
		 * @param string $str Markdown string
		 * @return string HTML
		 */
		public function prepareMarkdown($str)
		{
			$markdown = preg_replace("%\R%", "\r\n", trim($str));
			$listMode = false;
			$emptyLine = false;
			$markdownList = array();
			foreach (explode("\r\n", $markdown) as $line) {

				if ($listMode) {
					if (!preg_match("%^\s*\*\s%", $line)) {
						$listMode = false;

						if (!$emptyLine) {
							$markdownList[] = '';
						}
					}

				} elseif (preg_match("%^\s*\*\s%", $line)) {

					if (!$emptyLine) {
						$markdownList[] = '';
					}

					$listMode = true;
				}
				$markdownList[] = $line;
				$emptyLine = !strlen(trim($line));
			}

			$markdown = implode("\r\n", $markdownList);
			$markdown = \Michelf\Markdown::defaultTransform($markdown);
			$markdown = preg_replace(array("%\R<ul>%", "%\R<ol>%", "%<ul>\R<li>%", "%<ol>\R<li>%", "%</li>\R</ul>%", "%</li>\R</ol>%"), array('<ul>', '<ol>', '<ul><li>', '<ol><li>', '</li></ul>', '</li></ol>'), $markdown);

			return $markdown;
		}

		/**
		 * Wrap words and calculate the number of lines
		 *
		 * @param string $text Text
		 * @param int $maxwidth Maximum number of chars
		 * @return int Number of lines
		 */
		function WordWrap($text, $maxwidth)
		{

			$text = trim($text);
			if ($text === '') {
				return 0;
			}
			$space = $this->GetStringWidth(' ');
			$lines = explode("\n", $text);
			$text = '';
			$count = 0;

			foreach ($lines as $line) {
				$words = preg_split('/ +/', $line);
				$width = 0;
				foreach ($words as $word) {
					$wordwidth = $this->GetStringWidth($word);
					if ($width + $wordwidth + $space <= $maxwidth) {
						$width += $wordwidth + $space;
						$text .= $word . ' ';
					} else {
						$width = $wordwidth + $space;
						$text = rtrim($text) . "\n" . $word . ' ';
						$count++;
					}
				}
				$text = rtrim($text) . "\n";
				$count++;
			}
			$text = rtrim($text);
			return $count;
		}

		/**
		 * Copy pages from the source template to the current document
		 *
		 * @param string $source Source file
		 * @param int $from Start page
		 * @param int $to End page
		 */
		public function copyPages($source, $from, $to)
		{
			$this->setSourceFile($source);
			for ($page = $from; $page <= $to; ++$page) {
				$this->AddPage();
				$this->useTemplate($this->importPage($page), 0);
			}
		}

		/**
		 * Print a paragraph
		 *
		 * @param string $str Paragraph
		 * @param int $width Width
		 */
		public function printParagraph($str, $width)
		{
			$endY = $this->GetY() + $this->pdfCfg['paragraphSpace'];

			#$postText = str_replace('&nbsp;',' ',$var_array['base']['vars_i_post_txt']); // bugfix für währung mit leerzeichen ausgeben ...

			$intRowCount = $this->WordWrap($this->pdfText($str), $width);
			if (($intRowCount * $this->pdfCfg['paragraphSpace']) + $endY > $this->pdfCfg['limitToY']) { // Checken ob der kommende Text noch auf die Seite passt
				$this->AddPage();
				$endY = $this->pdfCfg['restartAtY'];
			}

			$this->SetXY($this->pdfCfg['offsetX'], $endY);
			$this->SetFont($this->pdfCfg['font'], '', $this->pdfCfg['font_size']);
			$this->MultiCellTag($width, $this->pdfCfg['paragraphSpace'], $this->pdfText($str), 0, 'L');
//			$this->MultiCell($width, $this->pdfCfg['paragraphSpace'], $this->pdfText($str), 0, 'L');

		}

		/**
		 * Print a full width line
		 *
		 * @param int $y Vertical position
		 */
		public function printHorizontalLine($y)
		{
			$this->SetLineWidth(0.1);
			$this->Line($this->pdfCfg['offsetX'], $y, $this->pdfCfg['offsetX'] + $this->pdfCfg['fullwidth'], $y);
		}
	}
}

// Fremdnutzung des Templates (durch Mahnwesen) verhindern!
if ($var_array['type'] != 'invoices' && $var_array['type'] != 'offers' && $var_array['type'] != 'refunds' && $var_array['type'] != 'sales' && $var_array['type'] != 'deliveries') {
	$this->refCore->setError('PDF Fehlgeschlagen! Das ausgewählte Template unterstützt nur die Generierung von Angeboten, Rechnungen, Gutschriften, Auftragsbestätigungen und Lieferscheine!');
	return false;
}

$pdf = new PDF_TEMPLATE_DEFAULT_INVOICE('P', 'mm', 'A4');
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
$pdf->booOutputHeader = true;
$pdf->passConfiguration($arrPDFConfig);
$pdf->passGSalesData($var_array);

$pdf->AddPage();

$pdf->SetXY($arrPDFConfig['offsetX'], $arrPDFConfig['startBodyAtY']);
$tmpY = $pdf->GetY();

// Storno Hinweis (nur für Rechnungen)
if ($var_array['base']['status_id'] == 2 && $var_array['type'] == 'invoices') {
	$pdf->SetFont($arrPDFConfig['font'], 'B', $arrPDFConfig['font_size']);
	$txtStorno = 'Storniert am ' . date('d.m.Y', strtotime($var_array['base']['status_date']));
	if ($var_array['base']['storno_txt'] != '') {
		$txtStorno .= ' mit der Begründung: ' . $var_array['base']['storno_txt'];
	}
	$pdf->MultiCell($arrPDFConfig['fullwidth'], 5, $pdf->pdfText($txtStorno), 0, 'L');
	$tmpY = $pdf->GetY() + $arrPDFConfig['paragraphSpace'];
}

// Anrede
if ($var_array['base']['customer_title'] === 'Frau') {
	$salutation = sprintf($arrPDFConfig['label_salutation_female'], $var_array['base']['customer_lastname']);
} elseif (!strncmp($var_array['base']['customer_title'], 'Herr', 4)) {
	$salutation = sprintf($arrPDFConfig['label_salutation_male'], $var_array['base']['customer_lastname']);
} else {
	$salutation = $arrPDFConfig['label_salutation'];
}
$pdf->SetXY($arrPDFConfig['offsetX'], $tmpY);
$pdf->SetFont($arrPDFConfig['font'], '', $arrPDFConfig['font_size']);
$pdf->Cell(150, '', $pdf->pdfText($salutation), 0);
$tmpY = $pdf->GetY() + $arrPDFConfig['paragraphSpace'];

// Einleitungstext
if ($var_array['base']['vars_i_pre_txt'] != '') {
	$pdf->SetXY($arrPDFConfig['offsetX'], $tmpY);
	$pdf->SetFont($arrPDFConfig['font'], '', $arrPDFConfig['font_size']);
	$pdf->MultiCell($arrPDFConfig['fullwidth'], 5, $pdf->pdfText($var_array['base']['vars_i_pre_txt']), 0, 'L');
	$tmpY = $pdf->GetY() + $arrPDFConfig['paragraphSpace'];
}

// Dokument ohne Positionen macht sich nicht so gut. => Aussteigen
if (false == is_array($var_array['pos'])) {
	$this->refCore->setError('Keine Positionen vorhanden. pdf Datei konnte nicht erstellt werden.');
	return false;
}

// Positionen nach Rabatt durchsuchen
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

// Positionstabelle (Headlines)

$pdf->posTableHeadlines($tmpY + $arrPDFConfig['paragraphSpace'], $booShowDiscountCol);

$posCounter = 0;
$colorCounter = 0;
$intTotalPosCounter = 0;
$endY = 0;
$pageCarrLineTotal = 0;

// Durchlaufen aller Positionen
foreach ($var_array['pos'] as $key => $value) {

	$tmpY = $pdf->GetY();

	// Wo soll die Zeile für die Position beginnen?
	// Muss berücksichtigt werden das die Zeile davor mehrzeilig war?

	if ($endY > $tmpY) {
		$tmpY = $endY + 4;
	} else {
		$tmpY += 4;
	}

	// Überprüfen wieviele Zeilen die nächste Position in Anspruch nehmen wird
	$intPosTxtWidth = 80;
	if ($value['headline'] == 1 || $var_array['type'] == 'deliveries') {
		$intPosTxtWidth = 160;
	}
	$zeilen = $pdf->WordWrap($pdf->pdfText($value['vars_pos_txt']), $intPosTxtWidth);
	if ($value['headline'] == 1) {
		$zeilen++;
	} // nächste einzeilige position muss bei headline auch noch mit draufpassen können

	// Ab der zweiten Position pro Seite (sonst Schleife) überprüfen ob eine neue Seite benötigt wird.
	if (($zeilen * 5) + $tmpY > $arrPDFConfig['limitToY'] && $posCounter != 0) {
		$posCounter = 0;
		$pdf->AddPage();
		$tmpY = $arrPDFConfig['restartAtY'];
		$pdf->posTableHeadlines($tmpY + 5, $booShowDiscountCol);
		$tmpY = $pdf->GetY() + 2;

		// "Übertrag von Seite x" Zeile ausgeben

		if ($arrPDFConfig['addPageCarryLine'] && $var_array['type'] != 'deliveries') {
			$pdf->SetFont($arrPDFConfig['font'], 'I', $arrPDFConfig['font_size_small']);
			$pdf->setXY($arrPDFConfig['offsetX'] + $arrPDFConfig['column_pos_width'] + $arrPDFConfig['column_amount_width'],
				$tmpY);
			$pdf->Cell($arrPDFConfig['column_price_width'], 0,
				$pdf->pdfText(str_ireplace('{p}', ($pdf->PageNo() - 1), $arrPDFConfig['label_pagecarry'])), 0, 0, 'L');
			$pdf->setXY($arrPDFConfig['offsetX'] + $arrPDFConfig['column_unit_offset'] + $arrPDFConfig['column_price_width'] + $arrPDFConfig['column_price_gap'],
				$tmpY);

			// Standardwährung
			if ($var_array['base']['curr_id'] == 0) {
				$out = $pdf->getFormatedStandardCurrency($pageCarrLineTotal);

				// Fremdwährung
			} else {
				$out = $pdf->getFormatedForeignCurrency($pageCarrLineTotal, $var_array['base']);
			}
			$pdf->Cell($arrPDFConfig['column_price_width'], 0, $out, 0, 0, 'R');
			$tmpY += 8;
		} else {
			$tmpY += 2;
		}

	}

	// Wenn es sich um eine reine Überschriftenzeile handelt
	if ($value['headline'] == 1) {

		$pdf->SetFont($arrPDFConfig['font'], 'B', $arrPDFConfig['font_size']);

		if ($posCounter == 0) {
			$pdf->setXY($arrPDFConfig['offsetX'] + $arrPDFConfig['column_pos_width'] + $arrPDFConfig['column_amount_width'],
				$tmpY - 3);
		} else {
			$pdf->setXY($arrPDFConfig['offsetX'] + $arrPDFConfig['column_pos_width'] + $arrPDFConfig['column_amount_width'],
				$tmpY + 2);
		}

		$pdf->MultiCell($arrPDFConfig['fullwidth'], 5, $pdf->pdfText($value['vars_pos_txt']), 0, 'L');

		$endY = $pdf->GetY();
		$pdf->SetFont($arrPDFConfig['font'], '', $arrPDFConfig['font_size']);

		$colorCounter = 1;

	} else {
		$intTotalPosCounter++;

		if (false == isset($value['optional'])) {
			$value['optional'] = '';
		}

		// Alternierenden Hintergrund eindrucken
		if ($colorCounter % 2 != 0 && $arrPDFConfig['alternateBg']) {
			$pdf->SetFillColor(230, 230, 230);
			$pdf->Rect(15, $tmpY - 3, 188, ($zeilen * 5) + 1, 'F');
		}

		// Pos.
		if ($arrPDFConfig['show_colpos']) {
			$pdf->SetFont($arrPDFConfig['font'], '', $arrPDFConfig['font_size']);
			$pdf->setXY($arrPDFConfig['offsetX'], $tmpY);
			$pdf->Cell($arrPDFConfig['column_pos_width'] - 1, 0, $pdf->pdfText($intTotalPosCounter), 0, 0, 'L');
		}

		// Menge & Einheit
		$pdf->SetFont($arrPDFConfig['font'], '', $arrPDFConfig['font_size']);
		$pdf->setXY($arrPDFConfig['offsetX'] + $arrPDFConfig['column_pos_width'], $tmpY);
		$pdf->Cell($arrPDFConfig['column_amount_width'], 0,
			$pdf->pdfText(gsFloat($value['quantity']) . ' ' . $value['unit']), 0, 0, 'C');

		if ($var_array['type'] != 'deliveries') {

			// Rabatt %
			if ($value['discount'] > 0) {
				$pdf->setXY($arrPDFConfig['offsetX'] + $arrPDFConfig['column_discount_offset'], $tmpY);
				$pdf->Cell($arrPDFConfig['column_discount_width'], 0, $pdf->pdfText(gsFloat($value['discount']) . '%'),
					0, 0, 'R');
			}

			// Einzelpreis (falls benötigt)
			$pdf->SetFont($arrPDFConfig['font_mono'], '', $arrPDFConfig['font_size']);
			$pdf->setXY($arrPDFConfig['offsetX'] + $arrPDFConfig['column_unit_offset'], $tmpY);
			if ($var_array['base']['curr_id'] == 0) {
				$out = $pdf->getFormatedStandardCurrency($value['price'], true);
			} // Standardwährung
			else {
				$out = $pdf->getFormatedForeignCurrency($value['curr_price'], $var_array['base'], true);
			} // Fremdwährung
			if ($value['optional'] == 1) {
				$out = '(' . $out . ')';
			}
			$pdf->Cell($arrPDFConfig['column_price_width'], '', $out, 0, 0, 'R');


			// Gesamtpreis
			$pdf->SetFont($arrPDFConfig['font_mono'], '', $arrPDFConfig['font_size']);
			$pdf->setXY($arrPDFConfig['offsetX'] + $arrPDFConfig['column_unit_offset'] + $arrPDFConfig['column_price_width'] + $arrPDFConfig['column_price_gap'],
				$tmpY);
			if ($var_array['base']['curr_id'] == 0) {
				$out = $pdf->getFormatedStandardCurrency($value['tprice']);
			} // Standardwährung
			else {
				$out = $pdf->getFormatedForeignCurrency($value['rounded_curr_tprice'], $var_array['base']);
			} // Fremdwährung
			if ($value['optional'] == 1) {
				$out = '(' . $out . ')';
			}
			$pdf->Cell($arrPDFConfig['column_price_width'], '', $out, 0, 0, 'R');
		}

		// Positionstext (bei Lieferscheinen von 80 auf 160 Breite erhöhen)
		$intStrWidth = $arrPDFConfig['column_unit_offset'] - $arrPDFConfig['column_pos_width'] - $arrPDFConfig['column_amount_width'];
		if ($var_array['type'] == 'deliveries') {
//			$intStrWidth = $arrPDFConfig['column_unit_offset'];
		}

		$pdf->SetFont($arrPDFConfig['font'], '', $arrPDFConfig['font_size']);
		$pdf->setXY($arrPDFConfig['offsetX'] + $arrPDFConfig['column_pos_width'] + $arrPDFConfig['column_amount_width'], $tmpY - 2.5);
//		$pdf->MultiCell($intStrWidth, 5, $pdf->pdfText($value['vars_pos_txt']), 0, 'L');
		$pdf->MultiCellTag($intStrWidth, 5, $pdf->pdfText($value['vars_pos_txt']), 0, 'L');
		$endY = $pdf->GetY();

		// Rechnung für Übertrag von Seite x
		if ($value['optional'] != 1) {
			if ($var_array['base']['curr_id'] == 0) {
				$pageCarrLineTotal += $value['rounded_tprice'];
			} else {
				$pageCarrLineTotal += $value['rounded_curr_tprice'];
			}
		}
	}

	$posCounter++;
	$colorCounter++;
}

// Zwischensumme, Rabatt, Steuer, Gesamtbetrag, etc. bei Lieferscheinen nicht eindrucken

if ($var_array['type'] != 'deliveries') {

	// Zwischensumme / Nettobetrag
	if (($endY + 35) > $arrPDFConfig['limitToY']) { // Checken ob die nächsten Zeilen noch auf die Seite passen
		$pdf->AddPage();
		$endY = $arrPDFConfig['restartAtY'];
	}

	$tmpY = $endY + 3;
	$pdf->printHorizontalLine($tmpY);

	$tmpY = $endY + 9;
	$pdf->SetFont($arrPDFConfig['font'], '', $arrPDFConfig['font_size']);
	$pdf->setXY($arrPDFConfig['offsetX'] + $arrPDFConfig['column_unit_offset'], $tmpY);
	$pdf->Cell($arrPDFConfig['column_price_width'], 0, $pdf->pdfText($arrPDFConfig['label_netamount']), 0, 0, 'R');

	$pdf->SetFont($arrPDFConfig['font_mono'], '', $arrPDFConfig['font_size']);
	$pdf->setXY($arrPDFConfig['offsetX'] + $arrPDFConfig['column_unit_offset'] + $arrPDFConfig['column_price_width'] + $arrPDFConfig['column_price_gap'],
		$tmpY);

//	$pdf->posTableHeadlines()

	// Standardwährung
	if ($var_array['base']['curr_id'] == 0) {
		$out = $pdf->getFormatedStandardCurrency($var_array['summ']['rounded_net']);

		// Fremdwährung
	} else {
		$out = $pdf->getFormatedForeignCurrency($var_array['summ']['rounded_curr_net'], $var_array['base']);
	}
	$pdf->Cell($arrPDFConfig['column_price_width'], 0, $out, 0, 0, 'R');


//	if ($var_array['summ']['rounded_discount'] > 0) {
//
//		$endY = $pdf->GetY() + 5;
//
//		// enth. Rabatt
//		if (($endY) > $arrPDFConfig['limitToY']) { // Checken ob die nächste Zeile noch auf die Seite passt
//			$pdf->AddPage();
//			$endY = $arrPDFConfig['restartAtY'];
//		}
//
//		$pdf->setXY(15, $endY);
//		$pdf->SetFont($arrPDFConfig['font'], '', $arrPDFConfig['font_size']);
//		$pdf->Cell(20, 0, $pdf->pdfText($arrPDFConfig['label_includeddiscount']), 0, 0, 'L');
//		$pdf->setXY(180, $endY);
//		if ($var_array['base']['curr_id'] == 0) {
//			$out = $pdf->getFormatedStandardCurrency($var_array['summ']['rounded_discount']);
//		} // Standardwährung
//		else {
//			$out = $pdf->getFormatedForeignCurrency($var_array['summ']['rounded_curr_discount'], $var_array['base']);
//		}    // Fremdwährung
//		$pdf->Cell(20, 0, $out, 0, 0, 'R');
//
//	}


	// Zzgl. MwSt.
	if ($var_array['summ']['tax'] > 0 || $arrPDFConfig['showTax']) {
		$endY = $pdf->GetY() + 7;

		if (($endY) > $arrPDFConfig['limitToY']) { // Checken ob die nächste Zeile noch auf die Seite passt
			$pdf->AddPage();
			$endY = $arrPDFConfig['restartAtY'];
		}

		// Mehrwertsteuer aufschlüsseln
		$txtTax = '';
		if (is_array($var_array['summ']['taxes'])) {
			if (count($var_array['summ']['taxes']) > 1) {
				$txtTax = ' (beinhaltet ';
				$booFirstRun = true;
				foreach ($var_array['summ']['taxes'] as $keyTax => $valueTax) {
					if (false == $booFirstRun) {
						$txtTax .= '; ';
					}
					if ($var_array['base']['curr_id'] == 0) {
						$txtTax .= $pdf->getFormatedStandardCurrency($valueTax['rounded_std']) . ' aus ' . gsFloat($keyTax) . '%';
					} else {
						$txtTax .= $pdf->getFormatedForeignCurrency($valueTax['rounded_curr'],
								$var_array['base']) . ' aus ' . gsFloat($keyTax) . '%';
					}
					$booFirstRun = false;
				}
				$txtTax .= ')';
			}
		}
		$pdf->SetFont($arrPDFConfig['font'], '', $arrPDFConfig['font_size']);
		$pdf->setXY($arrPDFConfig['offsetX'] + $arrPDFConfig['column_unit_offset'], $endY);
		$pdf->Cell($arrPDFConfig['column_price_width'], 0, $pdf->pdfText($arrPDFConfig['label_plustax']), 0, 0, 'R');

		$pdf->SetFont($arrPDFConfig['font_mono'], '', $arrPDFConfig['font_size']);
		$pdf->setXY($arrPDFConfig['offsetX'] + $arrPDFConfig['column_unit_offset'] + $arrPDFConfig['column_price_width'] + $arrPDFConfig['column_price_gap'],
			$endY);

		// Standardwährung
		if ($var_array['base']['curr_id'] == 0) {
			$out = $pdf->getFormatedStandardCurrency($var_array['summ']['tax']);

			// Fremdwährung
		} else {
			$out = $pdf->getFormatedForeignCurrency($var_array['summ']['rounded_curr_tax'], $var_array['base']);
		}
		$pdf->Cell($arrPDFConfig['column_price_width'], 0, $out, 0, 0, 'R');
	}

	// Gesamtbetrag
	$endY = $pdf->GetY() + 7;
	if (($endY) > $arrPDFConfig['limitToY']) { // Checken ob die nächste Zeile noch auf die Seite passt
		$pdf->AddPage();
		$endY = $arrPDFConfig['restartAtY'];
	}

	$pdf->SetFont($arrPDFConfig['font'], 'B', $arrPDFConfig['font_size']);
	$pdf->setXY($arrPDFConfig['offsetX'] + $arrPDFConfig['column_unit_offset'], $endY);
	$strTotal = 'Gesamtbetrag';
	if ($var_array['type'] == 'refunds') {
		$strTotal = $arrPDFConfig['label_refundtotal'];
	}
	if ($var_array['type'] == 'invoices') {
		$strTotal = $arrPDFConfig['label_invoicetotal'];
	}
	if ($var_array['type'] == 'offers') {
		$strTotal = $arrPDFConfig['label_offertotal'];
	}
	$pdf->Cell($arrPDFConfig['column_price_width'], 0, $pdf->pdfText($strTotal), 0, 0, 'R');

	$pdf->SetFont($arrPDFConfig['font_mono'], 'B', $arrPDFConfig['font_size']);
	$pdf->setXY($arrPDFConfig['offsetX'] + $arrPDFConfig['column_unit_offset'] + $arrPDFConfig['column_price_width'] + $arrPDFConfig['column_price_gap'],
		$endY);

	// Standardwährung
	if ($var_array['base']['curr_id'] == 0) {
		$out = $pdf->getFormatedStandardCurrency($var_array['summ']['gross']);

		// Fremdwährung
	} else {
		$out = $pdf->getFormatedForeignCurrency($var_array['summ']['rounded_curr_gross'], $var_array['base']);
	}
	$pdf->Cell($arrPDFConfig['column_price_width'], 0, $out, 0, 0, 'R');
}

$pdf->Ln($arrPDFConfig['paragraphSpace']);

// Rechnungsabschlusstext
if ($var_array['base']['vars_i_post_txt'] != '') {
	$pdf->printParagraph($var_array['base']['vars_i_post_txt'], $arrPDFConfig['fullwidth']);
}

// Typabhängige Ergänzungen
switch ($var_array['type']) {

	// Angebote: AGB hinzufügen
	case 'offers':
		$pdf->printParagraph($arrPDFConfig['label_ordersign_1'], $arrPDFConfig['fullwidth']);
		$pdf->printParagraph($arrPDFConfig['label_ordersign_2'], $arrPDFConfig['fullwidth']);
		$pdf->printParagraph($arrPDFConfig['label_greeting'], $arrPDFConfig['fullwidth']);
		$pdf->printParagraph($pdf->getAuthorName($var_array['base']['user_id']), $arrPDFConfig['fullwidth']);

		// Bestellklausel
		$pdf->AddPage();
		$pdf->SetXY($arrPDFConfig['offsetX'], $arrPDFConfig['restartAtY']);
		$pdf->SetFont($arrPDFConfig['font'], 'B', $arrPDFConfig['font_size_big']);
		$pdf->Cell(100, 0, $pdf->pdfText($arrPDFConfig['label_order']));
		$pdf->printParagraph(sprintf($arrPDFConfig['label_orderconfirm'], $var_array['base']['invoiceno']),
			$arrPDFConfig['fullwidth']);
		$pdf->Ln(5 * $arrPDFConfig['paragraphSpace']);
		$pdf->printHorizontalLine($pdf->GetY());
		$tmpY = $pdf->GetY() + $arrPDFConfig['paragraphSpace'];
		$pdf->SetXY($arrPDFConfig['offsetX'], $tmpY);
		$pdf->Cell($arrPDFConfig['fullwidth'] / 2, 0, $pdf->pdfText($arrPDFConfig['label_orderdate']));
		$pdf->Cell(100, 0, $pdf->pdfText($arrPDFConfig['label_ordersignature']));

		$pdf->copyPages($arrPDFConfig['stationery_pdf_file'], $booBlanko ? 4 : 2, $booBlanko ? 5 : 3);
		break;

	// Sonstige Dokumente
	default:
		$pdf->printParagraph($arrPDFConfig['label_greeting'], $arrPDFConfig['fullwidth']);
		$pdf->printParagraph($pdf->getAuthorName($var_array['base']['user_id']), $arrPDFConfig['fullwidth']);
		break;
}


// Platzhalter für Gesamtzahlen ersetzen (werden nun manuell ersetzt - dadurch rechtsbündig)

// bis rev.812, fpdf funktion
# $pdf->AliasNbPages('{nb}');

// ab rev813, manuell durchlaufen
$pdf->SetAutoPageBreak(false);
$intLastPage = $pdf->PageNo();

for ($i = 1; $i <= $intLastPage; $i++) {
	$pdf->page = $i;
	$pdf->headerLine('label_page', $pdf->pdfText(sprintf($arrPDFConfig['label_pages'], $i, $intLastPage)),
		($i > 1) ? $pdf->followingPageCountYPos : $pdf->firstPageCountYPos, $arrPDFConfig['docInfoLineHeight']);
	$pdf->page = $intLastPage;
}

// PDF Datei schreiben

if ($booBlanko) {
	$pdf->Output($savetoblanko, 'F');
} else {
	$pdf->Output($saveto, 'F');
}

// Reset für den nächsten Durchgang (blanko modus)
unset($tmpY, $endY, $pageCarrLineTotal, $intLastPage, $txtTax, $booFirstRun, $intTotalPosCounter);

setlocale(LC_ALL, $locale);