<?php

// Prevent template usage for dunning routines
if (!in_array($var_array['type'], ['invoices', 'offers', 'refunds', 'sales', 'deliveries'])) {
    $this->refCore->setError('PDF Fehlgeschlagen! Das ausgewählte Template unterstützt nur die Generierung von Angeboten, Rechnungen, Gutschriften, Auftragsbestätigungen und Lieferscheine!');
    return false;
}

// Cancel if document has no positions
if (false == is_array($var_array['pos'])) {
    $this->refCore->setError('Keine Positionen vorhanden. pdf Datei konnte nicht erstellt werden.');
    return false;
}

// Require autoloader & FPDF
require_once __DIR__.'/tollwerk/vendor/autoload.php';
require_once __DIR__.'/tollwerk/fpdf/tfpdih.php';

// Set the locale for localized date output
$locale = setlocale(LC_ALL, 0);
setlocale(LC_ALL, 'de_DE');

// Define the specific FPDF class
if (!class_exists('PDF_TEMPLATE_DEFAULT')) {
    /**
     * Class PDF_TEMPLATE_DEFAULT
     */
    class PDF_TEMPLATE_DEFAULT extends TFPDIH
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
                $strHeadline = (trim($this->pdfData['base']['custom3']) ?: $this->pdfCfg[$documentHeadlines[$this->pdfData['type']]]).' '.$this->pdfData['base']['invoiceno'];
                if ($this->pdfData['base']['status_id'] == 2 && $this->pdfData['type'] != 'offers') {
                    $strHeadline .= ' ('.$this->pdfCfg['label_canceled'].')';
                }
                $this->SetXY($this->pdfCfg['offsetX'], $this->pdfCfg['startAtY']);
                $this->SetFont($this->pdfCfg['font'], 'B', $this->pdfCfg['font_size_big']);
                $this->Cell(100, 0, $this->PdfText($strHeadline));

                // Date
                $this->SetFont($this->pdfCfg['font'], '', $this->pdfCfg['font_size']);
                $this->SetXY(0, $this->pdfCfg['startAtY']);
                $this->Cell($this->pdfCfg['offsetX'] + $this->pdfCfg['fullwidth'], 0,
                    $this->PdfText($this->pdfCfg['label_city'].' '.utf8_encode(trim(strftime('%e. %B %Y',
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

// Specific configuration
$arrPDFConfig['use_stationery_pdf'] = $this->refCore->cfg->v('pdf_invoice_stationary');
$arrPDFConfig['stationery_pdf_file'] = $this->refCore->cfg->v('path_absolute').$this->refCore->cfg->v('pdf_invoice_stationary_file');

// Include configuration
include __DIR__.'/tollwerk/config/common.inc.php';

// Create and configure FPDF instance
$pdf = new PDF_TEMPLATE_DEFAULT('P', 'mm', 'A4');
include __DIR__.'/tollwerk/config/pdf.inc.php';

/**********************************************************************
 * Start output
 *********************************************************************/
$pdf->AddPage();
$pdf->SetXY($arrPDFConfig['offsetX'], $arrPDFConfig['startBodyAtY']);
$tmpY = $pdf->GetY();

/**********************************************************************
 * Cancellation hint (invoices only)
 *********************************************************************/
if ($var_array['base']['status_id'] == 2 && $var_array['type'] == 'invoices') {
    $pdf->SetFont($arrPDFConfig['font'], 'B', $arrPDFConfig['font_size']);
    $txtStorno = sprintf($arrPDFConfig['label_cancelled_at'],
        date('d.m.Y', strtotime($var_array['base']['status_date'])));
    if ($var_array['base']['storno_txt'] != '') {
        $txtStorno .= sprintf($arrPDFConfig['label_cancelled_bc'], $var_array['base']['storno_txt']);
    }
    $pdf->MultiCell($arrPDFConfig['fullwidth'], 5, $pdf->PdfText($txtStorno), 0, 'L');
    $tmpY = $pdf->GetY() + $arrPDFConfig['paragraphSpace'];
}

/**********************************************************************
 * Salutation
 *********************************************************************/
if (!strncmp($var_array['base']['customer_title'], 'Frau', 4)) {
    $salutation = sprintf($arrPDFConfig['label_salutation_female'], $var_array['base']['customer_title'], $var_array['base']['customer_lastname']);
} elseif (!strncmp($var_array['base']['customer_title'], 'Herr', 4)) {
    $salutation = sprintf($arrPDFConfig['label_salutation_male'], $var_array['base']['customer_title'], $var_array['base']['customer_lastname']);
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
if ($var_array['base']['vars_i_pre_txt'] != '') {
    $pdf->SetXY($arrPDFConfig['offsetX'], $tmpY);
    $pdf->SetFont($arrPDFConfig['font'], '', $arrPDFConfig['font_size']);
    $pdf->MultiCellTag($arrPDFConfig['fullwidth'], 5, $pdf->PdfText($var_array['base']['vars_i_pre_txt']), 0, 'L');
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

/**********************************************************************
 * Item table
 *********************************************************************/
$posCounter = 0;
$intTotalPosCounter = 0;
$endY = 0;
$pageCarrLineTotal = 0;
$lastLineWasHeadline = false;

foreach ($var_array['pos'] as $key => $value) {
    $tmpY = $pdf->GetY();

    // Find the item line offset
    if ($endY > $tmpY) {
        $tmpY = $endY + 4;
    } else {
        $tmpY += 4;
    }

    // Pre-calculate line consumption
    $intPosTxtWidth = 80; // TODO
    if (($value['headline'] == 1) || ($var_array['type'] == 'deliveries')) {
        $intPosTxtWidth = 160;
    }
    $lines = $pdf->WordWrap($pdf->PdfText($value['vars_pos_txt']), $intPosTxtWidth);
    if ($value['headline'] == 1) {
        ++$lines;
    }

    // Test for page breaks
    if (($posCounter != 0) && (
            ((($lines * 4) + $tmpY) > $arrPDFConfig['limitToY']) ||
            (($value['headline'] == 1) && !$lastLineWasHeadline))
    ) {
        $posCounter = 0;
        $pdf->AddPage();
        $tmpY = $arrPDFConfig['restartAtY'];
        $pdf->PositionTableHeadlines($tmpY + 5, $booShowDiscountCol);
        $tmpY = $pdf->GetY() + 2;

        // Carry-over
        if ($arrPDFConfig['addPageCarryLine'] && $var_array['type'] != 'deliveries') {
            $pdf->SetFont($arrPDFConfig['font'], 'I', $arrPDFConfig['font_size_small']);
            $pdf->setXY($arrPDFConfig['offsetX'] + $arrPDFConfig['column_pos_width'] + $arrPDFConfig['column_amount_width'],
                $tmpY);
            $pdf->Cell($arrPDFConfig['column_price_width'], 0,
                $pdf->PdfText(str_ireplace('{p}', ($pdf->PageNo() - 1), $arrPDFConfig['label_pagecarry'])), 0, 0, 'L');
            $pdf->setXY($arrPDFConfig['offsetX'] + $arrPDFConfig['column_unit_offset'] + $arrPDFConfig['column_price_width'] + $arrPDFConfig['column_price_gap'],
                $tmpY);

            // Currency
            $out = ($var_array['base']['curr_id'] == 0) ? $pdf->GetFormatedStandardCurrency($pageCarrLineTotal) : $pdf->GetFormatedForeignCurrency($pageCarrLineTotal,
                $var_array['base']);
            $pdf->Cell($arrPDFConfig['column_price_width'], 0, $out, 0, 0, 'R');
            $tmpY += 8;
        } else {
            $tmpY += 2;
        }
    }

    // If it's a headline only
    if ($value['headline'] == 1) {
        $pdf->SetFont($arrPDFConfig['font'], 'B', $arrPDFConfig['font_size']);

        if ($posCounter == 0) {
            $pdf->setXY($arrPDFConfig['offsetX'] + $arrPDFConfig['column_pos_width'] + $arrPDFConfig['column_amount_width'],
                $tmpY - 3);
        } else {
            $pdf->setXY($arrPDFConfig['offsetX'] + $arrPDFConfig['column_pos_width'] + $arrPDFConfig['column_amount_width'],
                $tmpY + 2);
        }

        $pdf->MultiCell($arrPDFConfig['fullwidth'], 5, $pdf->PdfText($value['vars_pos_txt']), 0, 'L');
        $endY = $pdf->GetY();
        $pdf->SetFont($arrPDFConfig['font'], '', $arrPDFConfig['font_size']);

        // Else: Regular item
    } else {
        ++$intTotalPosCounter;

        if (false == isset($value['optional'])) {
            $value['optional'] = '';
        }

        // Position number
        if ($arrPDFConfig['show_colpos']) {
            $pdf->SetFont($arrPDFConfig['font'], '', $arrPDFConfig['font_size']);
            $pdf->setXY($arrPDFConfig['offsetX'], $tmpY);
            $pdf->Cell($arrPDFConfig['column_pos_width'] - 1, 0, $pdf->PdfText($intTotalPosCounter), 0, 0, 'L');
        }

        // Amount & unit
        $pdf->SetFont($arrPDFConfig['font'], '', $arrPDFConfig['font_size']);
        $pdf->setXY($arrPDFConfig['offsetX'] + $arrPDFConfig['column_pos_width'], $tmpY);
        $pdf->Cell($arrPDFConfig['column_amount_width'], 0,
            $pdf->PdfText(gsFloat($value['quantity']).' '.$value['unit']), 0, 0, 'C');

        // If it's not a delivery note
        if ($var_array['type'] != 'deliveries') {

            // Discount
            if ($value['discount'] > 0) { // TODO
                $pdf->setXY($arrPDFConfig['offsetX'] + $arrPDFConfig['column_discount_offset'], $tmpY);
                $pdf->Cell($arrPDFConfig['column_discount_width'], 0, $pdf->PdfText(gsFloat($value['discount']).'%'),
                    0, 0, 'R');
            }

            // Unit price
            $pdf->SetFont($arrPDFConfig['font_mono'], '', $arrPDFConfig['font_size']);
            $pdf->setXY($arrPDFConfig['offsetX'] + $arrPDFConfig['column_unit_offset'], $tmpY);
            $out = ($var_array['base']['curr_id'] == 0) ? $pdf->GetFormatedStandardCurrency($value['price'],
                true) : $pdf->GetFormatedForeignCurrency($value['curr_price'], $var_array['base'], true);
            if ($value['optional'] == 1) {
                $out = '('.$out.')';
            }
            $pdf->Cell($arrPDFConfig['column_price_width'], '', $out, 0, 0, 'R');

            // Total
            $pdf->SetFont($arrPDFConfig['font_mono'], '', $arrPDFConfig['font_size']);
            $pdf->setXY($arrPDFConfig['offsetX'] + $arrPDFConfig['column_unit_offset'] + $arrPDFConfig['column_price_width'] + $arrPDFConfig['column_price_gap'],
                $tmpY);
            $out = ($var_array['base']['curr_id'] == 0) ? $pdf->GetFormatedStandardCurrency($value['tprice']) : $pdf->GetFormatedForeignCurrency($value['rounded_curr_tprice'],
                $var_array['base']);
            if ($value['optional'] == 1) {
                $out = '('.$out.')';
            }
            $pdf->Cell($arrPDFConfig['column_price_width'], '', $out, 0, 0, 'R');
        }

        // Item text (increase width for delivery notes)
        $intStrWidth = $arrPDFConfig['column_unit_offset'] - $arrPDFConfig['column_pos_width'] - $arrPDFConfig['column_amount_width'];
        if ($var_array['type'] == 'deliveries') {
//			$intStrWidth = $arrPDFConfig['column_unit_offset'];
        }
        $pdf->SetFont($arrPDFConfig['font'], '', $arrPDFConfig['font_size']);
        $pdf->setXY($arrPDFConfig['offsetX'] + $arrPDFConfig['column_pos_width'] + $arrPDFConfig['column_amount_width'],
            $tmpY - 2.5);
        $pdf->MultiCellTag($intStrWidth, 5, $pdf->PdfText($value['vars_pos_txt']), 0, 'L');
        $endY = $pdf->GetY();

        // Calculate carry-over total
        if ($value['optional'] != 1) {
            if ($var_array['base']['curr_id'] == 0) {
                $pageCarrLineTotal += $value['rounded_tprice'];
            } else {
                $pageCarrLineTotal += $value['rounded_curr_tprice'];
            }
        }
    }

    $lastLineWasHeadline = ($value['headline'] == 1);

    ++$posCounter;
}

/**********************************************************************
 * Subtotal, Discount, Tax, Total
 *********************************************************************/
if ($var_array['type'] != 'deliveries') {

    // Subtotal
    if (($endY + 35) > $arrPDFConfig['limitToY']) {
        $pdf->AddPage();
        $endY = $arrPDFConfig['restartAtY'];
    }
    $tmpY = $endY + 3;
    $pdf->PrintHorizontalLine($tmpY);
    $tmpY = $endY + 9;
    $pdf->SetFont($arrPDFConfig['font'], '', $arrPDFConfig['font_size']);
    $pdf->setXY($arrPDFConfig['offsetX'] + $arrPDFConfig['column_unit_offset'], $tmpY);
    $pdf->Cell($arrPDFConfig['column_price_width'], 0, $pdf->PdfText($arrPDFConfig['label_netamount']), 0, 0, 'R');
    $pdf->SetFont($arrPDFConfig['font_mono'], '', $arrPDFConfig['font_size']);
    $pdf->setXY($arrPDFConfig['offsetX'] + $arrPDFConfig['column_unit_offset'] + $arrPDFConfig['column_price_width'] + $arrPDFConfig['column_price_gap'],
        $tmpY);
    $out = ($var_array['base']['curr_id'] == 0) ? $pdf->GetFormatedStandardCurrency($var_array['summ']['rounded_net']) : $pdf->GetFormatedForeignCurrency($var_array['summ']['rounded_curr_net'],
        $var_array['base']);
    $pdf->Cell($arrPDFConfig['column_price_width'], 0, $out, 0, 0, 'R');


    // Discount
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
//		$pdf->Cell(20, 0, $pdf->PdfText($arrPDFConfig['label_includeddiscount']), 0, 0, 'L');
//		$pdf->setXY(180, $endY);
//		if ($var_array['base']['curr_id'] == 0) {
//			$out = $pdf->GetFormatedStandardCurrency($var_array['summ']['rounded_discount']);
//		} // Standardwährung
//		else {
//			$out = $pdf->GetFormatedForeignCurrency($var_array['summ']['rounded_curr_discount'], $var_array['base']);
//		}    // Fremdwährung
//		$pdf->Cell(20, 0, $out, 0, 0, 'R');
//	}

    // Tax
    if ($var_array['summ']['tax'] > 0 || $arrPDFConfig['showTax']) {
        $endY = $pdf->GetY() + 7;
        if (($endY) > $arrPDFConfig['limitToY']) {
            $pdf->AddPage();
            $endY = $arrPDFConfig['restartAtY'];
        }

        // Split tax rates
        $txtTax = '';
        if (is_array($var_array['summ']['taxes'])) {
            if (count($var_array['summ']['taxes']) > 1) {
                $txtTax = ' (beinhaltet ';
                $booFirstRun = true;
                foreach ($var_array['summ']['taxes'] as $keyTax => $valueTax) {
                    if (false == $booFirstRun) {
                        $txtTax .= '; ';
                    }
                    $txtTax .= ($var_array['base']['curr_id'] == 0) ? $pdf->GetFormatedStandardCurrency($valueTax['rounded_std']).' aus '.gsFloat($keyTax).'%' : $pdf->GetFormatedForeignCurrency($valueTax['rounded_curr'],
                            $var_array['base']).' aus '.gsFloat($keyTax).'%';
                    $booFirstRun = false;
                }
                $txtTax .= ')';
            }
        }
        $pdf->SetFont($arrPDFConfig['font'], '', $arrPDFConfig['font_size']);
        $pdf->setXY($arrPDFConfig['offsetX'] + $arrPDFConfig['column_unit_offset'], $endY);
        $pdf->Cell($arrPDFConfig['column_price_width'], 0, $pdf->PdfText($arrPDFConfig['label_plustax']), 0, 0, 'R');
        $pdf->SetFont($arrPDFConfig['font_mono'], '', $arrPDFConfig['font_size']);
        $pdf->setXY($arrPDFConfig['offsetX'] + $arrPDFConfig['column_unit_offset'] + $arrPDFConfig['column_price_width'] + $arrPDFConfig['column_price_gap'],
            $endY);
        $out = ($var_array['base']['curr_id'] == 0) ? $pdf->GetFormatedStandardCurrency($var_array['summ']['tax']) : $pdf->GetFormatedForeignCurrency($var_array['summ']['rounded_curr_tax'],
            $var_array['base']);
        $pdf->Cell($arrPDFConfig['column_price_width'], 0, $out, 0, 0, 'R');
    }

    // Total
    $endY = $pdf->GetY() + 7;
    if (($endY) > $arrPDFConfig['limitToY']) { // Checken ob die nächste Zeile noch auf die Seite passt
        $pdf->AddPage();
        $endY = $arrPDFConfig['restartAtY'];
    }
    $pdf->SetFont($arrPDFConfig['font'], 'B', $arrPDFConfig['font_size']);
    $pdf->setXY($arrPDFConfig['offsetX'] + $arrPDFConfig['column_unit_offset'], $endY);
    $strTotal = $arrPDFConfig['label_total'];
    if ($var_array['type'] == 'refunds') {
        $strTotal = $arrPDFConfig['label_refundtotal'];
    }
    if ($var_array['type'] == 'invoices') {
        $strTotal = $arrPDFConfig['label_invoicetotal'];
    }
    if ($var_array['type'] == 'offers') {
        $strTotal = $arrPDFConfig['label_offertotal'];
    }
    $pdf->Cell($arrPDFConfig['column_price_width'], 0, $pdf->PdfText($strTotal), 0, 0, 'R');
    $pdf->SetFont($arrPDFConfig['font_mono'], 'B', $arrPDFConfig['font_size']);
    $pdf->setXY($arrPDFConfig['offsetX'] + $arrPDFConfig['column_unit_offset'] + $arrPDFConfig['column_price_width'] + $arrPDFConfig['column_price_gap'],
        $endY);
    $out = ($var_array['base']['curr_id'] == 0) ? $pdf->GetFormatedStandardCurrency($var_array['summ']['gross']) : $pdf->GetFormatedForeignCurrency($var_array['summ']['rounded_curr_gross'],
        $var_array['base']);
    $pdf->Cell($arrPDFConfig['column_price_width'], 0, $out, 0, 0, 'R');
}

/**********************************************************************
 * Final note
 *********************************************************************/
$pdf->Ln($arrPDFConfig['paragraphSpace']);
if ($var_array['base']['vars_i_post_txt'] != '') {
    $pdf->PrintParagraph($var_array['base']['vars_i_post_txt'], $arrPDFConfig['fullwidth']);
}

/**********************************************************************
 * Type specific additions
 *********************************************************************/
switch ($var_array['type']) {

    // Offers
    case 'offers':

        // Offer conditions
        $pdf->PrintParagraph($arrPDFConfig['label_ordersign_1'], $arrPDFConfig['fullwidth']);
        $pdf->PrintParagraph($arrPDFConfig['label_ordersign_2'], $arrPDFConfig['fullwidth']);
        $pdf->PrintParagraph($arrPDFConfig['label_greeting'], $arrPDFConfig['fullwidth']);
        $pdf->PrintParagraph($pdf->GetAuthorName($var_array['base']['user_id']), $arrPDFConfig['fullwidth']);

        // Order clause
        $pdf->AddPage();
        $pdf->SetXY($arrPDFConfig['offsetX'], $arrPDFConfig['restartAtY']);
        $pdf->SetFont($arrPDFConfig['font'], 'B', $arrPDFConfig['font_size_big']);
        $pdf->Cell(100, 0, $pdf->PdfText($arrPDFConfig['label_order']));
        $pdf->PrintParagraph(sprintf($arrPDFConfig['label_orderconfirm'], $var_array['base']['invoiceno']),
            $arrPDFConfig['fullwidth']);
        $pdf->Ln(5 * $arrPDFConfig['paragraphSpace']);
        $pdf->PrintHorizontalLine($pdf->GetY());
        $tmpY = $pdf->GetY() + $arrPDFConfig['paragraphSpace'];
        $pdf->SetXY($arrPDFConfig['offsetX'], $tmpY);
        $pdf->Cell($arrPDFConfig['fullwidth'] / 2, 0, $pdf->PdfText($arrPDFConfig['label_orderdate']));
        $pdf->Cell(100, 0, $pdf->PdfText($arrPDFConfig['label_ordersignature']));

        // Terms & conditions
        $pdf->CopyPages($arrPDFConfig['stationery_pdf_file'], $booBlanko ? 6 : 2, $booBlanko ? 9 : 5);
        break;

    // Default
    default:
        $pdf->PrintParagraph($arrPDFConfig['label_greeting'], $arrPDFConfig['fullwidth']);
        $pdf->PrintParagraph($pdf->GetAuthorName($var_array['base']['user_id']), $arrPDFConfig['fullwidth']);
        break;
}

/**********************************************************************
 * Page numbers
 *********************************************************************/
$pdf->SetAutoPageBreak(false);
$intLastPage = $pdf->PageNo();
for ($i = 1; $i <= $intLastPage; $i++) {
    $pdf->page = $i;
    $pdf->HeaderLine('label_page', $pdf->PdfText(sprintf($arrPDFConfig['label_pages'], $i, $intLastPage)),
        ($i > 1) ? $pdf->followingPageCountYPos : $pdf->firstPageCountYPos, $arrPDFConfig['docInfoLineHeight']);
    $pdf->page = $intLastPage;
}

/**********************************************************************
 * Write out PDF file
 *********************************************************************/
if ($booBlanko) {
    $pdf->Output($savetoblanko, 'F');
} else {
    $pdf->Output($saveto, 'F');
}

/**********************************************************************
 * Reset & start over
 *********************************************************************/
unset($tmpY, $endY, $pageCarrLineTotal, $intLastPage, $txtTax, $booFirstRun, $intTotalPosCounter);
setlocale(LC_ALL, $locale);