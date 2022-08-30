<?php
/**
 * Index Page (Demo)
 *
 * PHP Version 8.1
 *
 * @category Library
 * @package  GS1GTIN
 * @author   Jaime C. Rubin-de-Celis <james@sglms.com>
 * @license  MIT (https://sglms.com/license)
 * @link     https://sglms.com
 **/

use Sglms\Gs1Gtin\Gtin;
use Sglms\Gs1Gtin\Gs1;

require_once 'vendor/autoload.php';

$gtin = Gtin::create(45678);    // Item Reference
// 10000000456781
var_dump($gtin);
echo "<img src='" . $gtin->getBarcodeSource() . "' />";

$gtin = Gtin::create(45678, '0123');    // Item Reference + Client Prefix
// 10000000456781
var_dump($gtin);
echo "<img src='" . $gtin->getBarcodeSource() . "' />";

$gs1 = new Gs1('(01)1234(3102)123456(3302)134567(37)20(11)220801(17)221231');
echo "<img src='" . $gs1->getBarcodeSource(1) . "' />";

var_dump($gs1);
