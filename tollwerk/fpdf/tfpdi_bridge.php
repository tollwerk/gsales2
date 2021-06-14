<?php
/**
 * This file is part of FPDI
 *
 * @package   FPDI
 * @copyright Copyright (c) 2015 Setasign - Jan Slabon (http://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 * @version   1.6.1
 */

if (!class_exists('tFPDF')) {
    require_once('tfpdf.php');
}

class tfpdi_bridge extends tFPDF
{
    // empty body
}