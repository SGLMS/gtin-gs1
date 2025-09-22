<?php

use \Sglms\Gs1Gtin\Gtin;
use \Sglms\Gs1Gtin\Gtin8;
use \Sglms\Gs1Gtin\Gtin12;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/Gtin.php';


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
    itemNumber: 1,
    companyPrefix: '614141',
    type: 'GTIN-12'
);
// GTIN: 614141000012

echo $gtin;
echo $gtin->barcode();
$gtin->saveBarcode('../resources/gtin12');
$gtin->saveWithNumber('../resources/gtin12_numbers');
echo "<img src='../resources/gtin12_numbers.jpg' style='border:1px solid gray;'/>";

echo "<hr>";

$gtin = Gtin8::create(
    itemNumber: 0,
    companyPrefix: '506789',
    type: 'GTIN-8'
);
echo $gtin;
echo $gtin->barcode();
$gtin->saveBarcode('../resources/gtin8');
echo "<img src='../resources/gtin8.jpg' />";

$gtin->saveWithNumber('../resources/gtin8_numbers');
echo "<img src='../resources/gtin8_numbers.jpg' style='border:1px solid gray;'/>";