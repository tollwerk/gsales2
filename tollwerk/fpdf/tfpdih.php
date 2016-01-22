<?php

if (!class_exists('TFPDI')) {
	require_once('tfpdi.php');
}

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2015 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy of
 *  this software and associated documentation files (the "Software"), to deal in
 *  the Software without restriction, including without limitation the rights to
 *  use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 *  the Software, and to permit persons to whom the Software is furnished to do so,
 *  subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 *  FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 *  COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 *  IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 *  CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 ***********************************************************************************/

/**
 * HTML enabled variant of FPDI
 */
require_once('class.string_tags.php');

if (!defined('PARAGRAPH_STRING')) {
	define('PARAGRAPH_STRING', '~~~');
}

/**
 * @package externe
 * @subpackage FPDF
 */
class TFPDIH extends TFPDI
{
	public $wt_Current_Tag;
	public $wt_Current_Nesting = 0;
	public $wt_List_Indent = 8;
	public $wt_FontInfo;//tags font info
	public $wt_DataInfo;//parsed string data info
	public $wt_DataExtraInfo;//data extra INFO
	public $wt_TempData; //some temporary info
	/**
	 * PDF configuration
	 *
	 * @var array
	 */
	public $pdfCfg;
	/**
	 * PDF document data
	 *
	 * @var array
	 */
	public $pdfData;
	/**
	 * Output header
	 *
	 * @var boolean
	 */
	public $booOutputHeader = true;
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
	 * Print a development grid into the pages
	 *
	 * @var bool
	 */
	public $showDevelopmentGrid = false;

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
	public function passGsalesData($arrData)
	{
		$this->pdfData = $arrData;
	}

	/**
	 * Include the page template (if enabled)
	 */
	public function IncludeTemplate() {
		if ($this->pdfCfg['use_stationery_pdf']) {
			$this->setSourceFile($this->pdfCfg['stationery_pdf_file']);
			$intTempplateId = $this->ImportPage(1);
			$this->useTemplate($intTempplateId, 0);
		}
	}

	public function PrintDevelopmentGrid() {
		if ($this->showDevelopmentGrid) {
			$this->SetXY(0, 0);
			$this->SetFont($this->pdfCfg['font'], '', 4);
			for ($i=0;$i<300;$i+=3) {
				$this->SetXY($i,3); $this->Cell(3,3,$i);
				$this->SetXY(3,$i); $this->Cell(3,3,$i);
			}
		}
	}

	/**
	 * Print a header line
	 *
	 * @param string $label Label key
	 * @param string $value Value
	 * @param int $offsetY Vertical offset
	 * @param int $lineHeight Line height
	 */
	public function HeaderLine($label, $value, &$offsetY, $lineHeight = 5)
	{
		$this->SetXY($this->pdfCfg['offsetX'] + $this->pdfCfg['column_unit_offset'], $offsetY);
		$this->SetFont($this->pdfCfg['font'], 'I', $this->pdfCfg['font_size_small']);
		$this->Cell($this->pdfCfg['column_price_width'], 0, $this->pdfText($this->pdfCfg[$label]), 0, 0, 'R');

		$this->SetXY($this->pdfCfg['offsetX'] + $this->pdfCfg['column_unit_offset'] + $this->pdfCfg['column_price_width'] + $this->pdfCfg['column_price_gap'],
			$offsetY);
		$this->SetFont($this->pdfCfg['font'], '', $this->pdfCfg['font_size_small']);
		$this->Cell($this->pdfCfg['column_price_width'], 0, $this->pdfText($value), 0, 0, 'R');
		$offsetY += $lineHeight;
	}

	/**
	 * Print the document info block
	 *
	 * @param int $ys Vertical offset
	 * @param int $lh Line height
	 */
	public function DocInfo($ys, $lh)
	{

		if ($this->PageNo() == 1) {

			// Kunden-Nr. Zeile (nur ausgeben wenn eine Nummer vorhanden ist )
			if (trim($this->pdfData['base']['customerno']) != '') {
				$this->HeaderLine('label_customerno', $this->pdfData['base']['customerno'], $ys, $lh);
			}

			// Job-Nr. Zeile (nur ausgeben wenn eine Nummer vorhanden ist )
			if (trim($this->pdfData['base']['custom1']) != '') {
				$this->HeaderLine('label_jobno', str_pad($this->pdfData['base']['custom1'], 3, '0', STR_PAD_LEFT),
					$ys, $lh);
			}

			// Lieferanten-Nr. Zeile (nur ausgeben wenn eine Nummer vorhanden ist )
			if (trim($this->pdfData['customerdata']['custom1']) != '') {
				$this->HeaderLine('label_supplierno', $this->pdfData['customerdata']['custom1'], $ys, $lh);
			}

			// Bestell-Nr. Zeile (nur ausgeben wenn eine Nummer vorhanden ist )
			if (trim($this->pdfData['base']['custom2']) != '') {
				$this->HeaderLine('label_orderno', $this->pdfData['base']['custom2'], $ys, $lh);
			}
		}

		// Dokumenttypabhängige Zeilen
		if ($this->pdfData['type'] == 'invoices') {

			// Rechnungs-Nr. Zeile
			$this->HeaderLine('label_invoiceno', $this->pdfData['base']['invoiceno'], $ys, $lh);

			if ($this->PageNo() == 1) {

				// Lieferdatum Zeile
				if ($this->pdfData['base']['deliverydate'] != '0000-00-00 00:00:00') {
					$this->HeaderLine('label_deliverydate',
						date('d.m.Y', strtotime($this->pdfData['base']['deliverydate'])), $ys, $lh);
				}
			}
		}

		// Angebots-Nr. Zeile
		if ($this->pdfData['type'] == 'offers') {
			$this->HeaderLine('label_offerno', $this->pdfData['base']['invoiceno'], $ys, $lh);
		}

		// Lieferschein-Nr. Zeile
		if ($this->pdfData['type'] == 'deliveries') {
			$this->HeaderLine('label_deliveryno', $this->pdfData['base']['invoiceno'], $ys, $lh);
		}

		// Auftrags-Nr. Zeile
		if ($this->pdfData['type'] == 'sales') {
			$this->HeaderLine('label_saleno', $this->pdfData['base']['invoiceno'], $ys, $lh);
		}

		// Gutschrifts-Nr. Zeile
		if ($this->pdfData['type'] == 'refunds') {
			$this->HeaderLine('label_refundno', $this->pdfData['base']['invoiceno'], $ys, $lh);
		}

		// Autor
		if ($this->PageNo() == 1) {
			$this->HeaderLine('label_author', $this->GetAuthorName($this->pdfData['base']['user_id']), $ys, $lh);

			// Seitenanzahl Zeile
			// TODO
//				$this->HeaderLine('label_page')
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
	public function GetAuthorName($author)
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
	 * Print the position table headlines
	 *
	 * @param int $intY Vertical offset
	 * @param bool $booShowDiscountCol
	 */
	function PositionTableHeadlines($intY, $booShowDiscountCol = true)
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
	public function GetFormatedStandardCurrency($value, $booDisplayMoreDecimals = false)
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
	public function GetFormatedForeignCurrency($value, $arrInvoiceBase, $booDisplayMoreDecimals = false)
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
	public function EuroReplace($string)
	{
		return str_replace('€', chr(128), $string);
	}

	/**
	 * Normalize text for output
	 *
	 * @param string $string Text
	 * @return string Normalized text
	 */
	public function PdfText($string)
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
			$retString = utf8_decode($this->EuroReplace($string), ENT_QUOTES);
		}

		$retString = $this->replaceVariables($retString);

		$markdown = $this->PrepareMarkdown($retString);
//			$withoutTags = strip_tags($markdown, '<b><strong><a><ul><ol><li>');
		$withoutTags = strip_tags($markdown, '<b><strong><a>');
		return html_entity_decode(trim($withoutTags));
	}

	/**
	 * Replace text variables
	 *
	 * @param string $str Text
	 * @return string Text with replaced variables
	 */
	public function replaceVariables($str) {
		return strtr($str, array(
			'{DY}' => date('Y', mktime(0, 0, 0, date('m') + 11)),
		));
	}

	/**
	 * Prepare and parse a MarkDown string
	 *
	 * @param string $str Markdown string
	 * @return string HTML
	 */
	public function PrepareMarkdown($str)
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
		$markdown = preg_replace(array(
			"%\R<ul>%",
			"%\R<ol>%",
			"%<ul>\R<li>%",
			"%<ol>\R<li>%",
			"%</li>\R</ul>%",
			"%</li>\R</ol>%"
		), array('<ul>', '<ol>', '<ul><li>', '<ol><li>', '</li></ul>', '</li></ol>'), $markdown);

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
	public function CopyPages($source, $from, $to)
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
	public function PrintParagraph($str, $width)
	{
		$endY = $this->GetY() + $this->pdfCfg['paragraphSpace'];

		#$postText = str_replace('&nbsp;',' ',$var_array['base']['vars_i_post_txt']); // bugfix für währung mit leerzeichen ausgeben ...

		$intRowCount = $this->WordWrap($this->PdfText($str), $width);
		if (($intRowCount * $this->pdfCfg['paragraphSpace']) + $endY > $this->pdfCfg['limitToY']) { // Checken ob der kommende Text noch auf die Seite passt
			$this->AddPage();
			$endY = $this->pdfCfg['restartAtY'];
		}

		$this->SetXY($this->pdfCfg['offsetX'], $endY);
		$this->SetFont($this->pdfCfg['font'], '', $this->pdfCfg['font_size']);
		$this->MultiCellTag($width, $this->pdfCfg['paragraphSpace'], $this->PdfText($str), 0, 'L');
//			$this->MultiCell($width, $this->pdfCfg['paragraphSpace'], $this->PdfText($str), 0, 'L');

	}

	/**
	 * Print a full width line
	 *
	 * @param int $y Vertical position
	 */
	public function PrintHorizontalLine($y)
	{
		$this->SetLineWidth(0.1);
		$this->Line($this->pdfCfg['offsetX'], $y, $this->pdfCfg['offsetX'] + $this->pdfCfg['fullwidth'], $y);
	}

	public function _wt_Reset_Datas()
	{
		$this->wt_Current_Tag = '';
		$this->wt_DataInfo = array();
		$this->wt_DataExtraInfo = array(
			'LAST_LINE_BR' => '',        //CURRENT LINE BREAK TYPE
			'CURRENT_LINE_BR' => '',    //LAST LINE BREAK TYPE
			'TAB_WIDTH' => 10            //The tab WIDTH IS IN mm
		);

		//if another measure unit is used ... calculate your OWN
		$this->wt_DataExtraInfo['TAB_WIDTH'] *= (72 / 25.4) / $this->k;
		/*
			$this->wt_FontInfo - do not reset, once read ... is OK!!!
		*/
	}

	/**
	 * Sets current tag to specified style
	 * @param        $tag - tag name
	 * $family - text font family
	 * $style - text style
	 * $size - text size
	 * $color - text color
	 * @return    nothing
	 */
	public function SetStyle2($tag, $family, $style, $size, $color)
	{

		if ($tag == 'ttags') {
			$this->Error('>> ttags << is reserved TAG Name.');
		}
		if ($tag == '') {
			$this->Error('Empty TAG Name.');
		}

		//use case insensitive tags
		$tag = trim(strtoupper($tag));
		$this->TagStyle[$tag]['family'] = trim($family);
		$this->TagStyle[$tag]['style'] = trim($style);
		$this->TagStyle[$tag]['size'] = trim($size);
		$this->TagStyle[$tag]['color'] = trim($color);
	}


	/**
	 * Sets current tag style as the current settings
	 *
	 * - if the tag name is not in the tag list then de 'DEFAULT' tag is saved.
	 * This includes a fist call of the public function SaveCurrentStyle()
	 *
	 * @param        $tag - tag name
	 * @return    nothing
	 */
	public function ApplyStyle($tag)
	{

		//use case insensitive tags
		$tag = trim(strtoupper($tag));

		if ($this->wt_Current_Tag == $tag) {
			return;
		}

		if (($tag == '') || (!isset($this->TagStyle[$tag]))) {
			$tag = 'DEFAULT';
		}

		$this->wt_Current_Tag = $tag;

		$style = &$this->TagStyle[$tag];

		if (isset($style)) {
			$this->SetFont($style['family'], $style['style'], $style['size']);
			//this is textcolor in FPDF format
			if (isset($style['textcolor_fpdf'])) {
				$this->TextColor = $style['textcolor_fpdf'];
				$this->ColorFlag = ($this->FillColor != $this->TextColor);
			} else {
				if ($style['color'] <> '') {//if we have a specified color
					$temp = explode(',', $style['color']);
					$this->SetTextColor($temp[0], $temp[1], $temp[2]);
				}
			}
		}
	}

	/**
	 * Save the current settings as a tag default style under the DEFAUTLT tag name
	 * @param        none
	 * @return    nothing
	 */
	public function SaveCurrentStyle()
	{
		//*
		$this->TagStyle['DEFAULT']['family'] = $this->FontFamily;
		$this->TagStyle['DEFAULT']['style'] = $this->FontStyle;
		$this->TagStyle['DEFAULT']['size'] = $this->FontSizePt;
		$this->TagStyle['DEFAULT']['textcolor_fpdf'] = $this->TextColor;
		$this->TagStyle['DEFAULT']['color'] = '';
		/**/
	}

	/**
	 * Extract a single line out of the data
	 *
	 * @param float $w Maximum width
	 * @return array Line data
	 */
	public function MakeLine($w)
	{

		$aDataInfo = &$this->wt_DataInfo;
		$aExtraInfo = &$this->wt_DataExtraInfo;

		// last line break >> current line break
		$aExtraInfo['LAST_LINE_BR'] = $aExtraInfo['CURRENT_LINE_BR'];
		$aExtraInfo['CURRENT_LINE_BR'] = '';

		// If the cell should take all available width
		if ($w == 0) {
			$w = $this->w - $this->rMargin - $this->x;
		}

		$wmax = ($w - 2 * $this->cMargin) * 1000;//max width

		$aLine = array(); // This will contain the result
		$return_result = false; // If break and return result
		$reset_spaces = false;

		$line_width = 0; // Line string width
		$total_chars = 0; // Total characters included in the result string
		$space_count = 0; // Number of spaces in the result string
		$fw = &$this->wt_FontInfo; // Font info array

		$last_sepch = ''; // Last separator character

		// Run through the remaining tag sections
		foreach ($aDataInfo as $key => $val) {
			$s = $val['text'];
			$tag = &$val['tag'];

			// Test if this is a paragraph
			$bParagraph = false;
			if (($s == "\t") && ($tag == 'pparg')) {
				$bParagraph = true;
				$s = "\t"; //place instead a TAB
			}

			// Get the total string length
			$s_length = strlen($s);

			$i = 0; // From where is the string remain
			$j = 0; // Until where is the string good to copy -- leave this == 1->> copy at least one character!!!
			$s_width = 0; // String width
			$last_sep = -1; // Last separator position
			$last_sepwidth = 0;
			$last_sepch_width = 0;
			$ante_last_sep = -1; // Ante last separator position
			$spaces = 0;

			// Parse the whole string
			while ($i < $s_length) {

				// Get a single character
				$c = $s[$i];

				// Is this an explicit line break?
				if ($c == "\n") {
					++$i; // Ignore/skip this character
					$aExtraInfo['CURRENT_LINE_BR'] = 'BREAK';
					$return_result = true;
					$reset_spaces = true;
					break;
				}

				// Is it a space character?
				if ($c == ' ') {
					++$space_count; // Increase the number of spaces
					++$spaces;
				}

				//	If it's not a tagged section or there are no font styles for this tag
				if (($tag == '') || !isset($fw[$tag])) {
					$this->ApplyStyle($tag);
					$fw[$tag]['w'] = $this->CurrentFont['cw']; //width
					$fw[$tag]['s'] = $this->FontSize; //size
				}

				// Determine the character width
				$char_width = $fw[$tag]['w'][$c] * $fw[$tag]['s'];

				// If it's a separator
				if (is_int(strpos(' ,.:;', $c))) {

					$ante_last_sep = $last_sep;
					$ante_last_sepch = $last_sepch;
					$ante_last_sepwidth = $last_sepwidth;

					$last_sep = $i; // Last separator position
					$last_sepch = $c; // Last separator char
					$last_sepch_width = $char_width; // Last separator char
					$last_sepwidth = $s_width;
				}

				// If it's a TAB character
				if ($c == "\t") {
					$c = $s[$i] = '';
					$char_width = $aExtraInfo['TAB_WIDTH'] * 1000;
				}

				// If it's a paragraph
				if ($bParagraph == true) {
					$c = $s[$i] = '';
					$char_width = $this->wt_TempData['LAST_TAB_REQSIZE'] * 1000 - $this->wt_TempData['LAST_TAB_SIZE'];
					if ($char_width < 0) {
						$char_width = 0;
					}
				}

				// Increase the line width
				$line_width += $char_width;

				// If the line needs to be wrapped
				if ($line_width > $wmax) {
					$aExtraInfo['CURRENT_LINE_BR'] = 'AUTO';

					if ($total_chars == 0) {
						/* This MEANS that the $w (width) is lower than a char width...
							Put $i and $j to 1 ... otherwise infinite while*/
						$i = 1;
						$j = 1;
						$return_result = true; //YES RETURN THE RESULT!!!
						break;
					}

					if ($last_sep <> -1) {
						//we have a separator in this tag!!!
						//untill now there one separator
						if (($last_sepch == $c) && ($last_sepch != ' ') && ($ante_last_sep <> -1)) {
							/*	this is the last character and it is a separator, if it is a space the leave it...
                                Have to jump back to the last separator... even a space
							*/
							$last_sep = $ante_last_sep;
							$last_sepch = $ante_last_sepch;
							$last_sepwidth = $ante_last_sepwidth;
						}

						if ($last_sepch == ' ') {
							$j = $last_sep;//just ignore the last space (it is at end of line)
							$i = $last_sep + 1;
							if ($spaces > 0) {
								$spaces--;
							}
							$s_width = $last_sepwidth;
						} else {
							$j = $last_sep + 1;
							$i = $last_sep + 1;
							$s_width = $last_sepwidth + $last_sepch_width;
						}

					} elseif (count($aLine) > 0) {
						//we have elements in the last tag!!!!
						if ($last_sepch == ' ') {//the last tag ends with a space, have to remove it

							$temp = &$aLine[count($aLine) - 1];

							if ($temp['text'][strlen($temp['text']) - 1] == ' ') {

								$temp['text'] = substr($temp['text'], 0, strlen($temp['text']) - 1);
								$temp['width'] -= $fw[$temp['tag']]['w'][' '] * $fw[$temp['tag']]['s'];
								$temp['spaces']--;

								//imediat return from this function
								break 2;
							} else {
								#die("should not be!!!");
							}//fi
						}//fi
					}//fi else

					$return_result = true;
					break;
				}//fi - Auto line break

				//increase the string width ONLY when it is added!!!!
				$s_width += $char_width;

				$i++;
				$j = $i;
				$total_chars++;
			}

			$str = substr($s, 0, $j);

			$sTmpStr = &$aDataInfo[$key]['text'];
			$sTmpStr = substr($sTmpStr, $i, strlen($sTmpStr));

			if (($sTmpStr == '') || ($sTmpStr === false))//empty
			{
				array_shift($aDataInfo);
			}

			if ($val['text'] == $str) {
			}

			if (!isset($val['href'])) {
				$val['href'] = '';
			}
			if (!isset($val['ypos'])) {
				$val['ypos'] = 0;
			}

			//we have a partial result
			array_push($aLine, array(
				'text' => $str,
				'tag' => $val['tag'],
				'href' => $val['href'],
				'width' => $s_width,
				'spaces' => $spaces,
				'ypos' => $val['ypos']
			));

			$this->wt_TempData['LAST_TAB_SIZE'] = $s_width;
			$this->wt_TempData['LAST_TAB_REQSIZE'] = (isset($val['size'])) ? $val['size'] : 0;

			if ($return_result) {
				break;
			}//break this for

		}//foreach

		// Check the first and last tag -> if first and last caracters are ' ' space remove them!!!'

		if ((count($aLine) > 0) && ($aExtraInfo['LAST_LINE_BR'] == 'AUTO')) {
			//first tag
			$temp = &$aLine[0];
			if ((strlen($temp['text']) > 0) && ($temp['text'][0] == ' ')) {
				$temp['text'] = substr($temp['text'], 1, strlen($temp['text']));
				$temp['width'] -= $fw[$temp['tag']]['w'][' '] * $fw[$temp['tag']]['s'];
				$temp['spaces']--;
			}

			//last tag
			$temp = &$aLine[count($aLine) - 1];
			if ((strlen($temp['text']) > 0) && ($temp['text'][strlen($temp['text']) - 1] == ' ')) {
				$temp['text'] = substr($temp['text'], 0, strlen($temp['text']) - 1);
				$temp['width'] -= $fw[$temp['tag']]['w'][' '] * $fw[$temp['tag']]['s'];
				$temp['spaces']--;
			}
		}

		if ($reset_spaces) {//this is used in case of a 'Explicit Line Break'
			//put all spaces to 0 so in case of 'J' align there is no space extension
			for ($k = 0; $k < count($aLine); $k++) {
				$aLine[$k]['spaces'] = 0;
			}
		}//fi

		return $aLine;
	}

	/**
	 * Draws a MultiCell with TAG recognition parameters
	 * @param        $w - with of the cell
	 * $h - height of the cell
	 * $pData - string or data to be printed
	 * $border - border
	 * $align    - align
	 * $fill - fill
	 * $pDataIsString - true if $pData is a string
	 * - false if $pData is an array containing lines formatted with $this->MakeLine($w) function
	 * (the false option is used in relation with StringToLines, to avoid double formatting of a string
	 *
	 * These paramaters are the same and have the same behavior as at Multicell function
	 * @return     nothing
	 */
	//public function MultiCellTag($w, $h, $pData, $border=0, $align='J', $fill=0, $pDataIsString = true){
	public function MultiCellTag($w, $h, $pData, $border = 0, $align = 'J', $fill = 0, $pDataIsString = true)
	{

		//save the current style settings, this will be the default in case of no style is specified
		$this->SaveCurrentStyle();
		$this->_wt_Reset_Datas();

		// If data is given as string: Split it into tag sections
		if ($pDataIsString === true) {
			$this->DivideByTags($pData);
		}

		// Borderd
		$b = $b1 = $b2 = $b3 = '';

		// Save the current X position, we will have to jump back!!!!
		$startX = $this->GetX();

		// If borders should be drawn
		if ($border) {
			if ($border == 1) {
				$border = 'LTRB';
				$b1 = 'LRT'; // without the bottom
				$b2 = 'LR'; // without the top and bottom
				$b3 = 'LRB'; // without the top
			} else {
				$b2 = '';
				if (is_int(strpos($border, 'L'))) {
					$b2 .= 'L';
				}
				if (is_int(strpos($border, 'R'))) {
					$b2 .= 'R';
				}
				$b1 = is_int(strpos($border, 'T')) ? $b2 . 'T' : $b2;
				$b3 = is_int(strpos($border, 'B')) ? $b2 . 'B' : $b2;
			}

			// Used if there is only one line
			$b = '';
			$b .= is_int(strpos($border, 'L')) ? 'L' : '';
			$b .= is_int(strpos($border, 'R')) ? 'R' : '';
			$b .= is_int(strpos($border, 'T')) ? 'T' : '';
			$b .= is_int(strpos($border, 'B')) ? 'B' : '';
		}

		// Set first and last line state
		$first_line = true;
		$last_line = false;
		if ($pDataIsString === true) {
			$last_line = !(count($this->wt_DataInfo) > 0);
		} else {
			$last_line = !(count($pData) > 0);
		}

		// While the last line hasn't been reached
		while (!$last_line) {

			// If the cell should be filled: Do it now
			if ($fill == 1) {
				$this->Cell($w, $h, '', 0, 0, '', 1);
				$this->SetX($startX); // Restore the X position
			}

			// If data was given as string
			if ($pDataIsString === true) {

				// Extract a single line
				$str_data = $this->MakeLine($w - $this->wt_Current_Nesting * $this->wt_List_Indent);

				// Test if all lines have been drawn
				$last_line = !(count($this->wt_DataInfo) > 0);

				// Else
			} else {

				// Extract a single line
				$str_data = array_shift($pData);

				// Test if all lines have been drawn
				$last_line = !(count($pData) > 0);
			}

			// Do not justify the last line
			if ($last_line && ($align == 'J')) {
				$align = 'L';
			}

			// Print the single line
			$this->PrintLine($w, $h, $str_data, $align);

			//see what border we draw:
			if ($first_line && $last_line) {
				//we have only 1 line
				$real_brd = $b;
			} elseif ($first_line) {
				$real_brd = $b1;
			} elseif ($last_line) {
				$real_brd = $b3;
			} else {
				$real_brd = $b2;
			}

			$first_line = false;

			// Draw the border and jump to the next line
			$this->SetX($startX); // Restore the X
			$this->Cell($w, $h, '', $real_brd, 2);

		}

		// Restore the default style
		$this->ApplyStyle('DEFAULT');

		$this->x = $this->lMargin;
	}


	/**
	 * This method divides the string into the tags and puts the result into wt_DataInfo variable.
	 * @param        $pStr - string to be printed
	 * @return     nothing
	 */

	public function DivideByTags($pStr, $return = false)
	{

		$pStr = str_replace("\t", '<ttags>\t</ttags>', $pStr);
		$pStr = str_replace(PARAGRAPH_STRING, '<pparg>\t</pparg>', $pStr);
		$pStr = str_replace("\r", '', $pStr);

		//initialize the String_TAGS class
		$sWork = new String_TAGS(6);

		//get the string divisions by tags
		$this->wt_DataInfo = $sWork->get_tags($pStr);

		if ($return) {
			return $this->wt_DataInfo;
		}
	}

	/**
	 * This method parses the current text and return an array that contains the text information for
	 * each line that will be drawed.
	 * @param        $w - with of the cell
	 * $pStr - String to be parsed
	 * @return     $aStrLines - array - contains parsed text information.
	 */
	public function StringToLines($w = 0, $pStr)
	{

		//save the current style settings, this will be the default in case of no style is specified
		$this->SaveCurrentStyle();
		$this->_wt_Reset_Datas();

		$this->DivideByTags($pStr);

		$last_line = !(count($this->wt_DataInfo) > 0);

		$aStrLines = array();

		while (!$last_line) {

			//make a line
			$str_data = $this->MakeLine($w);
			array_push($aStrLines, $str_data);

			//check for last line
			$last_line = !(count($this->wt_DataInfo) > 0);
		}//while(! $last_line){

		//APPLY THE DEFAULT STYLE
		$this->ApplyStyle('DEFAULT');

		return $aStrLines;
	}


	/**
	 * Draws a line returned from MakeLine function
	 * @param        $w - with of the cell
	 * $h - height of the cell
	 * $aTxt - array from MakeLine
	 * $align - text align
	 * @return     nothing
	 */
	public function PrintLine($w, $h, $aTxt, $align = 'J')
	{

		if ($w == 0) {
			$w = $this->w - $this->rMargin - $this->x;
		}

		$wmax = $w; //Maximum width

		$total_width = 0;    //the total width of all strings
		$total_spaces = 0;    //the total number of spaces

		$nr = count($aTxt);//number of elements

		for ($i = 0; $i < $nr; $i++) {
			$total_width += ($aTxt[$i]['width'] / 1000);
			$total_spaces += $aTxt[$i]['spaces'];
		}

		//default
		$w_first = $this->cMargin;

		switch ($align) {
			case 'J':
				if ($total_spaces > 0) {
					$extra_space = ($wmax - 2 * $this->cMargin - $total_width) / $total_spaces;
				} else {
					$extra_space = 0;
				}
				break;
			case 'L':
				break;
			case 'C':
				$w_first = ($wmax - $total_width) / 2;
				break;
			case 'R':
				$w_first = $wmax - $total_width - $this->cMargin;;
				break;
		}

		// Output the first Cell
		if ($w_first != 0) {
			$this->Cell($w_first, $h, '', 0, 0, 'L', 0);
		}

		$last_width = $wmax - $w_first;

		while (list($key, $val) = each($aTxt)) {

			$bYPosUsed = false;

			//apply current tag style
			$this->ApplyStyle($val['tag']);

			//If > 0 then we will move the current X Position
			$extra_X = 0;

			if ($val['ypos'] != 0) {
				$lastY = $this->y;
				$this->y = $lastY - $val['ypos'];
				$bYPosUsed = true;
			}

			//string width
			$width = $this->GetStringWidth($val['text']);
			$width = $val['width'] / 1000;

			if ($width == 0) {
				continue;
			}// No width jump over!!!

			if ($align == 'J') {
				if ($val['spaces'] < 1) {
					$temp_X = 0;
				} else {
					$temp_X = $extra_space;
				}

				$this->ws = $temp_X;

				$this->_out(sprintf('%.3f Tw', $temp_X * $this->k));

				$extra_X = $extra_space * $val['spaces'];//increase the extra_X Space

			} else {
				$this->ws = 0;
				$this->_out('0 Tw');
			}//fi

			//Output the Text/Links
			$this->Cell($width, $h, $val['text'], 0, 0, 'C', 0, $val['href']);

			$last_width -= $width;//last column width

			if ($extra_X != 0) {
				$this->SetX($this->GetX() + $extra_X);
				$last_width -= $extra_X;
			}//fi

			if ($bYPosUsed) {
				$this->y = $lastY;
			}

		}//while

		// Output the Last Cell
		if ($last_width != 0) {
			$this->Cell($last_width, $h, '', 0, 0, '', 0);
		}//fi
	}
}