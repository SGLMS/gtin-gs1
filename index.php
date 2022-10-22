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
use Sglms\Gs1Gtin\Gtin12;
use Sglms\Gs1Gtin\Gs1;

require_once 'vendor/autoload.php';

$gtin = Gtin::create(45678);    // Item Reference
echo $gtin->number;
echo "<img src='" . $gtin->getBarcodeSource() . "' />";
echo "<hr/>";

$gtin = Gtin::create(45678, '0123');    // Item Reference + Client Prefix
/* var_dump($gtin); */
echo $gtin->number;
echo "<img src='" . $gtin->getBarcodeSource() . "' />";
echo "<hr/>";

$gtin->saveBarcode('resources/gtin');
echo "<img src='resources/gtin.jpg' />";
echo "<hr/>";

$gtin12 = Gtin12::create(1, 614141);    // Item Reference + Client Prefix
echo $gtin12->number;
echo "<img src='" . $gtin12->getBarcodeSource(1, 64) . "' />";
echo "<hr/>";

$gtin12->saveBarcode('resources/gtin12', 1, 64);
echo "<img src='resources/gtin12.jpg' />";
echo "<hr/>";

$upca = UPCA::create(614141019199);    // GTIN-12 Valid Number
echo $upca->number;
echo "<hr/>";
$upca->saveBarcode('resources/upca', 1, 64);
echo "<img src='resources/upca.jpg' />";
echo "<hr/>";

$gs1 = new Gs1('(01)1234(3102)123456(3302)134567(37)20(11)220801(17)221231');
echo $gs1->gs1;
echo "<img src='" . $gs1->getBarcodeSource(1) . "' />";
echo "<hr/>";

$gs1->saveBarcode('resources/gs1');
echo "<img src='resources/gs1.jpg' />";
echo "<hr/>";
print_r($gs1);
