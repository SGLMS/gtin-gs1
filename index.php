<?php

use Sglms\Gtin\Gtin;
use Sglms\Gtin\Gs1;

require_once('vendor/autoload.php');

$gtin = Gtin::create(1234);

var_dump($gtin);

echo "<img src='" . $gtin->getBarcodeSource() . "' />";

$gs1 = new Gs1('(01)1234(3102)123456');

var_dump($gs1);
