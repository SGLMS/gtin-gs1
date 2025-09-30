<?php

use Sglms\Gs1Gtin\Gs1;
use Sglms\Gs1Gtin\Gtin;
use Sglms\Gs1Gtin\Gtin8;
use Sglms\Gs1Gtin\Gtin12;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/Gtin.php';
error_reporting(E_ALL);

$gtin = Gtin::create(
    itemNumber: 987654,
    companyPrefix: '123456',
    type: 'GTIN-14',
    packagingLevel: 1
);

echo $gtin;
echo "<br/>";

// Display barcode
echo "SVG:";
echo "<br/>";
echo $gtin->barcode();
echo "<br/>";

// Save barcode
/* $gtin->saveBarcode('../resources/images/gtin'); */
/* echo "<img src='../resources/images/gtin.jpg' style=''/>"; */

// Save barcode with numbers
$gtin->saveWithNumber('../resources/images/gtin_numbers');
echo "JPG (with numbers):";
echo "<br/>";
echo "<img src='../resources/images/gtin_numbers.jpg' style=''/>";

echo "<hr>";


$gtin = Gtin12::create(
    itemNumber: 123,
    companyPrefix: '45678',
);
// GTIN / UPC-A: 456780001230

echo $gtin;
echo "<br/>";
echo "SVG:";
echo "<br/>";
echo $gtin->barcode();
$gtin->saveBarcode('../resources/images/gtin12');
$gtin->saveWithNumber('../resources/images/gtin12_numbers');
echo "<br/>";
echo "JPG (with numbers):";
echo "<br/>";
echo "<img src='../resources/images/gtin12_numbers.jpg' style='scale:.75;'/>";

echo "<hr>";

$gtin = Gtin8::create(
    itemNumber: 123,
    companyPrefix: '4567'
);
echo $gtin;
echo "<br/>";
echo "SVG:";
echo "<br/>";
echo $gtin->barcode();
$gtin->saveBarcode('../resources/images/gtin8');
echo "<img src='../resources/images/gtin8.jpg' />";

$gtin->saveWithNumber('../resources/images/gtin8_numbers');
echo "<br/>";
echo "JPG (with numbers):";
echo "<br/>";
echo "<img src='../resources/images/gtin8_numbers.jpg' style='scale:0.75'/>";

echo "<hr>";

$gs1 = Gs1::parse('(01)10012345678902(10)ABC123(3201)000500(3302)000700(17)250630(21)SN123456(37)10(11)230101');
var_dump($gs1);
echo $gs1;
echo $gs1->barcode(showNumbers:true);
$gs1->saveBarcode('../resources/images/gs1', ['01','37', '21','3102','3302'], 80);

echo "<hr>";

$gs1 = Gs1::create(
    gtin: '00012345678905',
    serial: 'ABC123',
    netWeightPounds: 1000,
    grossWeight:6000,
    pieces:10,
);
echo $gs1;
echo $gs1->barcode(
    codes: ['01','37', '21','3102','3302'],
    showNumbers:true
);
