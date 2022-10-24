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

?>
<html>
    <body>
        <h1>GTIN-14</h1>
        <?php
        $gtin = Gtin::create(44444);    // Item Reference
        echo $gtin->number;
        echo "<img src='" . $gtin->getBarcodeSource() . "' />";
        echo "<hr/>";

        $gtin = Gtin::create(456789, 123);    // Item Reference + Client Prefix
        echo $gtin->number;
        echo "<img src='" . $gtin->getBarcodeSource() . "' />";
        echo "<hr/>";

        $gtin->saveWithNumber('resources/gtin');
        echo "<img src='resources/gtin.jpg' />";
        echo "<hr/>";
        ?>
        <h2>Validation</h2>

        Validate : <br/>

        <code>
        Gtin::validate(11230004567896, 'GTIN-14'); # TRUE
        </code>

        <?php
        var_dump(Gtin::validate(11230004567896));
        ?>

        <h1>GTIN-12 / UPC-A</h1>
        <?php
        $gtin12 = Gtin12::create(456789, 123);    // Item Reference + Client Prefix
        echo $gtin12->number;
        echo "<img src='" . $gtin12->getBarcodeSource(1, 64) . "' />";
        echo "<hr/>";

        $gtin12->saveWithNumber('resources/gtin12', 1, 64);
        echo "<img src='resources/gtin12.jpg' />";
        echo "<hr/>";

        $upca = UPCA::create(111, 222);
        $upca->saveWithNumber('resources/ufca', 1, 64);
        echo "<img src='resources/ufca.jpg' />";
        echo "<hr/>";

        ?>

        <h2>Validation</h2>

        Validate : <br/>

        <code>
        Gtin::validate(123004567895, 'GTIN-12'); # TRUE
        </code>

        <?php
        var_dump(Gtin::validate(123004567895, 'GTIN-12'));
        ?>

        <h1>GS1-128</h1>
        <?php
        $gs1 = new Gs1('(01)123456789(3102)123456(3302)134567(37)20(11)220801(17)221231');
        echo $gs1->gs1;
        echo "<img src='" . $gs1->getBarcodeSource(1) . "' />";
        echo "<hr/>";

        $gs1->saveBarcode('resources/gs1');
        echo "<img src='resources/gs1.jpg' />";
        echo "<hr/>";
        ?>
        <pre>
        <?php
        print_r($gs1);
        ?>
    </pre>
    </body>
</html>
