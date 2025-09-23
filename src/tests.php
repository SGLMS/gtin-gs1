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
    companyPrefix: '123',
    type: 'GTIN-14',
    packagingLevel: 1
);

echo $gtin;

// Display barcode
echo $gtin->barcode();

// Save barcode
$gtin->saveBarcode('../resources/gtin');
$gtin->saveWithNumber('../resources/gtin_numbers');
echo "<img src='../resources/gtin_numbers.jpg' style='border:1px solid gray;'/>";

echo "<hr>";


$gtin = Gtin12::create(
    itemNumber: 123,
    companyPrefix: '45678',
);
// GTIN: 614141000012

echo $gtin;
echo $gtin->barcode();
$gtin->saveBarcode('../resources/gtin12');
$gtin->saveWithNumber('../resources/gtin12_numbers');
echo "<img src='../resources/gtin12_numbers.jpg' style='border:1px solid gray;'/>";

echo "<hr>";

$gtin = Gtin8::create(
    itemNumber: 123,
    companyPrefix: '4567'
);
echo $gtin;
echo $gtin->barcode();
$gtin->saveBarcode('../resources/gtin8');
echo "<img src='../resources/gtin8.jpg' />";

$gtin->saveWithNumber('../resources/gtin8_numbers');
echo "<img src='../resources/gtin8_numbers.jpg' style='border:1px solid gray;'/>";

echo "<hr>";

$gs1 = Gs1::parse('(01)00012345678905(10)ABC123(3201)000500(3302)000700(17)250630(21)SN123456(37)10(11)230101');
var_dump($gs1);
echo $gs1;
echo $gs1->barcode(showNumbers:true);

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
