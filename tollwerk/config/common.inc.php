<?php

$arrPDFConfig['print_minisender']			= $this->refCore->cfg->v('pdf_invoice_printminisender');
$arrPDFConfig['font']						= 'GraublauSansProp';
$arrPDFConfig['font_prop']					= 'GraublauSansProp';
$arrPDFConfig['font_mono']					= 'GraublauSansMono';
$arrPDFConfig['font_size_tiny']				= 7; // Verwendung in Mini-Absender
$arrPDFConfig['font_size_small']			= 8;  // Verwendung in Überschriften der Positionstabelle & Fußzeilenblöcken
$arrPDFConfig['font_size_big']				= 12; // Verwendung in Dokumenten-Headline
$arrPDFConfig['font_size']					= 10; // Standardgröße für alles Weitere

// Währungseinstellungen
$arrPDFConfig['currency']					= $this->refCore->cfg->v('currency_symbol');
$arrPDFConfig['currency_space']				= '';
if ($this->refCore->cfg->v('currency_symbol_spacing') == true) {
	$arrPDFConfig['currency_space']			= ' ';
}

$arrPDFConfig['currency_pre']				= false;
if ($this->refCore->cfg->v('currency_symbol_before_number') == true) {
	$arrPDFConfig['currency_pre']			= true;
}

// € Symbol fix
$arrPDFConfig['currency']					= str_replace('€', chr(128), $arrPDFConfig['currency']);
$var_array['base']['curr_symbol']			= str_replace('€', chr(128), $var_array['base']['curr_symbol']);
$arrPDFConfig['displayDecimals']			= $this->refCore->cfg->v('decimalplaces_output');

// Sonstige Einstellungen
$arrPDFConfig['offsetX']					= 18; // linker Rand
$arrPDFConfig['limitToY']					= 277; // Wann soll ein Seitenumbruch geschehen?
$arrPDFConfig['startAtY']					= 110; // Ab welcher Position geht es nach einem Seitenumbruch auf der neuen Seite weiter?
$arrPDFConfig['startBodyAtY']				= 130; // Ab welcher Position geht es nach einem Seitenumbruch auf der neuen Seite weiter?
$arrPDFConfig['restartAtY']					= 55; // Ab welcher Position geht es nach einem Seitenumbruch auf der neuen Seite weiter?
$arrPDFConfig['startDocInfo']				= 55; // Beginn der Dokumentinfos auf der ersten Seite
$arrPDFConfig['restartDocInfo']				= 37; // Beginn der Dokumentinfos auf den Folgeseiten
$arrPDFConfig['docInfoLineHeight']			= 5; // Beginn der Dokumentinfos auf den Folgeseiten
$arrPDFConfig['paragraphSpace']				= 5; // Regulärer Absatzabstand
$arrPDFConfig['fullwidth']					= 174; // Volle Seitenbreite

$arrPDFConfig['showTax']					= $this->refCore->cfg->v('pdf_invoice_tax'); // MwSt. anzeigen?) (für Kleinunternehmner)
$arrPDFConfig['hideDiscount']				= $this->refCore->cfg->v('pdf_invoice_hide_discount'); // Rabatt-Spalte anzeigen wenn kein Rabatt gewährt wurde?
$arrPDFConfig['alternateBg']				= $this->refCore->cfg->v('pdf_alternate_bg'); // Alternierender Hintergrund
$arrPDFConfig['show_colpos']				= $this->refCore->cfg->v('pdf_invoice_position');

// Übertrag von Seite X
$arrPDFConfig['addPageCarryLine']			= $this->refCore->cfg->v('pdf_label_pagecarry_show');
$arrPDFConfig['label_pagecarry']			= $this->refCore->cfg->v('pdf_label_pagecarry');

// Absender (kann auch über die gSales Konfiguration beinflusst werden)
$arrPDFConfig['company_sender']				= $this->refCore->cfg->v('me_company') . ' » ' . $this->refCore->cfg->v('me_address') . ' » ' . $this->refCore->cfg->v('me_zip') . ' ' . $this->refCore->cfg->v('me_city');

// Labels
$arrPDFConfig['label_invoice']				= $this->refCore->cfg->v('pdf_i_label_invoice');
$arrPDFConfig['label_offer']				= $this->refCore->cfg->v('pdf_i_label_offer');
$arrPDFConfig['label_refund']				= $this->refCore->cfg->v('pdf_i_label_refund');
$arrPDFConfig['label_canceled']				= $this->refCore->cfg->v('pdf_i_label_canceled');
$arrPDFConfig['label_date']					= $this->refCore->cfg->v('pdf_i_label_date');
$arrPDFConfig['label_customerno']			= $this->refCore->cfg->v('pdf_i_label_customerno');
$arrPDFConfig['label_jobno']				= 'Projekt-Nr.';
$arrPDFConfig['label_supplierno']			= 'Unsere Lieferanten-Nr.';
$arrPDFConfig['label_orderno']				= 'Ihre Bestell-Nr. (PO)';
$arrPDFConfig['label_invoiceno']			= $this->refCore->cfg->v('pdf_i_label_invoiceno');
$arrPDFConfig['label_deliverydate']			= $this->refCore->cfg->v('pdf_i_label_deliverydate');
$arrPDFConfig['label_offerno']				= $this->refCore->cfg->v('pdf_i_label_offerno');
$arrPDFConfig['label_refundno']				= $this->refCore->cfg->v('pdf_i_label_refundno');
$arrPDFConfig['label_docno']				= 'Dokument-Nr.';
$arrPDFConfig['label_sale']					= $this->refCore->cfg->v('pdf_i_label_sale');
$arrPDFConfig['label_saleno']				= $this->refCore->cfg->v('pdf_i_label_saleno');
$arrPDFConfig['label_delivery']				= $this->refCore->cfg->v('pdf_i_label_delivery');
$arrPDFConfig['label_deliveryno']			= $this->refCore->cfg->v('pdf_i_label_deliveryno');
$arrPDFConfig['label_page']					= $this->refCore->cfg->v('pdf_i_label_page');
$arrPDFConfig['label_position']				= $this->refCore->cfg->v('pdf_i_label_position');
$arrPDFConfig['label_amount']				= $this->refCore->cfg->v('pdf_i_label_amount');
$arrPDFConfig['label_description']			= $this->refCore->cfg->v('pdf_i_label_description');
$arrPDFConfig['label_discount']				= $this->refCore->cfg->v('pdf_i_label_discount');
$arrPDFConfig['label_tax']					= $this->refCore->cfg->v('pdf_i_label_tax');
$arrPDFConfig['label_unitprice']			= $this->refCore->cfg->v('pdf_i_label_unitprice');
$arrPDFConfig['label_amountprice']			= $this->refCore->cfg->v('pdf_i_label_amountprice');
$arrPDFConfig['label_netamount']			= $this->refCore->cfg->v('pdf_i_label_netamount');
$arrPDFConfig['label_includeddiscount']		= $this->refCore->cfg->v('pdf_i_label_includeddiscount');
$arrPDFConfig['label_plustax']				= $this->refCore->cfg->v('pdf_i_label_plustax');
$arrPDFConfig['label_invoicetotal']			= $this->refCore->cfg->v('pdf_i_label_invoicetotal');
$arrPDFConfig['label_refundtotal']			= $this->refCore->cfg->v('pdf_i_label_refundtotal');
$arrPDFConfig['label_offertotal']			= $this->refCore->cfg->v('pdf_i_label_offertotal');
$arrPDFConfig['label_author']				= 'Autor';
$arrPDFConfig['label_city']					= 'Nürnberg, den';
$arrPDFConfig['label_pages']				= '%s von %s';
$arrPDFConfig['label_salutation_male']		= 'Sehr geehrter %s %s,';
$arrPDFConfig['label_salutation_female']	= 'Sehr geehrte %s %s,';
$arrPDFConfig['label_salutation']			= 'Sehr geehrte Damen und Herren,';
$arrPDFConfig['label_greeting']				= 'Mit besten Grüßen aus Nürnberg,';
$arrPDFConfig['label_ordersign_1']			= 'Ausdrücklich nicht Bestandteil des vorliegenden Angebots sind — soweit nicht explizit aufgeführt — generell Kosten, die von Seiten etwaiger Drittanbieter gefordert werden könnten, bspw.

• Lizenzgebühren für eingesetzte kostenpflichtige Schriftarten,
• Lizenzgebühren für zugekauftes Fotomaterial,
• Lizenzgebühren für integrierte kostenpflichtige Software,
• Gebühren für ggf. notwendige SSL-Zertifikate.

Wo zum Angebotszeitpunkt bereits abzusehen und bekannt, sind ungefähre Kostenübersichten in den Hinweisen dieses Angebots enthalten. Externe Kosten werden stets nur nach ausdrücklicher, schriftlicher Freigabe verursacht.

Bei Beauftragung übernehmen wir gerne die technische Umsetzung von Maßnahmen zur Einhaltung der [EU-Datenschutzgrundverordnung](https://de.wikipedia.org/wiki/Datenschutz-Grundverordnung) (**EU-DSGVO**) und beraten unverbindlich und nach bestem Wissen. Wir sind nicht in der Lage, Gewähr für die Vollständigkeit und Rechtmäßigkeit der umzusetzenden Maßnahmen zu übernehmen. Die entsprechenden Vorgaben sind vom Auftraggeber einzubringen und zu verantworten.';
$arrPDFConfig['label_ordersign_2']			= 'Über die Möglichkeit, Sie bei Ihrem Projekt unterstützen zu dürfen, würden wir uns außerordentlich freuen. Dieses Angebot dient gleichermaßen als Bestellformular. Bitte streichen Sie nicht relevante, als optional gekennzeichnete Positionen und senden Sie es uns unterzeichnet per E-Mail oder auf dem Postweg zurück, um den schnellstmöglichen Beginn der Arbeit an Ihrem Projekt zu unterstützen. Wir freuen uns über Ihren Auftrag!';
$arrPDFConfig['label_order']				= 'Bestellung';
$arrPDFConfig['label_orderconfirm']			= 'Hiermit bestellen wir gemäß des voranstehenden Angebots %s die mandatorischen sowie alle nicht gestrichenen, optionalen Positionen. Die im Angebot dargestellten Liefer- und Zahlungsbedingungen sowie die Allgemeinen Geschäftsbedingungen (AGB) der tollwerk GmbH wurden zur Kenntnis genommen und erhalten hiermit mein / unser Einverständnis.';
$arrPDFConfig['label_orderdate']			= 'Ort, Datum';
$arrPDFConfig['label_ordersignature']		= 'Stempel / Unterschrift';
$arrPDFConfig['label_cancelled_at']			= 'Storniert am %s';
$arrPDFConfig['label_cancelled_bc']			= ' mit der Begründung: %s';
$arrPDFConfig['label_total']				= 'Gesamtbetrag';

$arrPDFConfig['column_pos_width']			= 8;
$arrPDFConfig['column_amount_width']		= 12;
$arrPDFConfig['column_discount_offset']		= 110;
$arrPDFConfig['column_discount_width']		= 20;
$arrPDFConfig['column_unit_offset']			= 125;
$arrPDFConfig['column_price_width']			= 20;
$arrPDFConfig['column_price_gap']			= 10;

if (!defined('FPDF_FONTPATH')) {
	define(FPDF_FONTPATH, dirname(__DIR__).'/fpdf/font/');
}

// Im Blanko keine Bilder, Briefpapier und Fußzeilenblöcke ausgeben
if ($booBlanko) {
	$arrPDFConfig['use_stationery_pdf'] = false;
	$arrPDFConfig['use_picture'] = false;
	$arrPDFConfig['print_footer_blocks'] = false;
}