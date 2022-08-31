<?php
/**
 * Index Page (Demo)
 *
 * PHP Version 8.1
 *
 * @category SGLMS_Library
 * @package  GS1GTIN
 * @author   Jaime C. Rubin-de-Celis <james@sglms.com>
 * @license  MIT (https://sglms.com/license)
 * @link     https://sglms.com
 **/

use Sglms\Gs1Gtin\Gtin;
use Sglms\Gs1Gtin\Gs1;

require_once 'vendor/autoload.php';

$gtin = Gtin::create(45678);    // Item Reference
/* var_dump($gtin); */
echo $gtin->number;
echo "<img src='" . $gtin->getBarcodeSource() . "' />";

$gtin = Gtin::create(45678, '0123');    // Item Reference + Client Prefix
/* var_dump($gtin); */
echo $gtin->number;
echo "<img src='" . $gtin->getBarcodeSource() . "' />";

$gtin->saveBarcode('resources/gtin');
echo "<img src='resources/gtin.jpg' />";

$gs1 = new Gs1('(01)1234(3102)123456(3302)134567(37)20(11)220801(17)221231');
/* var_dump($gs1); */
echo $gs1->gs1;
echo "<img src='" . $gs1->getBarcodeSource(1) . "' />";

$gs1->saveBarcode('resources/gs1');
echo "<img src='resources/gs1.jpg' />";
print_r($gs1);
