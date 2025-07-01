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
use Sglms\Gs1Gtin\Gtin8;
use Sglms\Gs1Gtin\Gtin12;
use Sglms\Gs1Gtin\Gs1;

require_once 'vendor/autoload.php';

?>
<html>
    <body>
        <?php
        ?>

        <div style="display: flex; flex-direction:column;">
            <h1>GTIN-14</h1>
            <?php
            $gtin = Gtin::create(
                itemNumber: 45678,
                companyPrefix: 1234,
                packagingLevel: 1,
                type: 'GTIN-14'  
            );
            echo <<<'EOT'
            <pre>
            $gtin = Gtin::create(
                itemNumber: 45678,
                companyPrefix: 123456,
                packagingLevel: 1,
                type: 'GTIN-14'  
            );
            </pre>
            EOT;
            echo "<br/>GTIN-14 : " . $gtin->number;
            echo "<br/>SVG: " . $gtin->renderBarcode(format:  'svg');
            echo "<br/>HTML: " .$gtin->renderBarcode(format:  'html');
            echo "<br/>PNG : <img src='data:image/png;base64," . base64_encode($gtin->renderBarcode('png')) . "' style='max-width:200px;' />";
            echo "<br/>JPG : <img src='data:image/jpg;base64," . base64_encode($gtin->renderBarcode('jpg')) . "' style='max-width:200px;' />";
            echo "<hr/>";
            
            $gtin = Gtin::validate(
                number: 11230000456781,
                type: 'GTIN-14'  
            );  // TRUE
            echo <<<'EOT'
            <pre>
            $gtin = Gtin::validate(
                number: 11230000456781,
                type: 'GTIN-14'  
            );  // TRUE
            </pre>
            EOT;
            var_dump($gtin);

            echo "<h1>GTIN-12 / UPC-A</h1> ";
            $upca = Gtin12::create(
                itemNumber: 45678,
                companyPrefix: 1234,
            );
            echo <<<'EOT'
            <pre>
            $upca = Gtin12::create(
                itemNumber: 45678,
                companyPrefix: 1234,
            );
            </pre>
            EOT;
            echo "UPC-A :" . $upca->number;
            echo "<br/>SVG: " . $upca->renderBarcode(format:  'svg');
            echo "<br/>HTML: " .$upca->renderBarcode(format:  'html');
            
            echo <<<'EOT'
            <pre>
            $upca->saveWithNumber('filaneme');  //JPG
            </pre>
            EOT;
            $upca->saveWithNumber('resources/upca');
            echo "<img src='resources/upca.jpg' style='width:140' />";
            echo "<hr/>";
        
            // GTIN-8
            echo "<h1>GTIN-8</h1> ";
            $gtin8 = Gtin8::create(
                itemNumber: 890,
                companyPrefix: 123,
            );
            echo <<<'EOT'
            <pre>
            $gtin8 = Gtin8::create(
                itemNumber: 890,
                companyPrefix: 123,
            );
            </pre>
            EOT;
            echo "GTIN-8 :" . $gtin8->number;
            echo "<br/>SVG: " . $gtin8->renderBarcode(format:  'svg');
            echo "<br/>HTML: " .$gtin8->renderBarcode(format:  'html');
            
            echo <<<'EOT'
            <pre>
            $gtin8->saveWithNumber('filaneme');  //JPG
            </pre>
            EOT;
            $gtin8->saveWithNumber('resources/gtin');
            echo "<img src='resources/gtin.jpg' style='width:140' />";
            echo "<hr/>";
        
        ?>

        <h1>GS1-128</h1>
        <h2>GS1 Generator</h2>
        <div style="display:flex; flex-direction: column; align-items: start;">
        <?php
            $gs1 = new Gs1([
                1  => 22334455667788,
                10 => 123456,
                11 => 250601,
                17 => 270601,
                37 => 20,
                3102 => 123456,
                3302 => 134567,
                21 => 1234567890123,
            ]);
            echo <<<'EOT'
            <pre>
            $gs1 = new Gs1([
                1  => 22334455667788,
                10 => 123456,
                11 => 250601,
                17 => 270601,
                37 => 20,
                3102 => 123456,
                3302 => 134567,
                21 => 1234567890123,
            ]);
            </pre>
            EOT;
            echo "<br/>GS1 : " . $gs1;
            echo "<br/>PNG : " . $gs1->getBarcode(codes: [1,10,11,17,21,37,3102], height: 50, numbers: true);
            echo "<br/><img src='" . $gs1->getBarcodeSource(height:50, codes: [1,10,11,17,21,37,3102]) . "' />";
            
            echo "<h2>GS1 Parser</h2>";
            $gs1 = Gs1::parse('(01)22334455667788(10)34567(3102)123456(3302)134567(37)100(11)220801(17)221231(21)123456789');
            echo <<<'EOT'
            <pre>
            $gs1 = Gs1::parse('(01)22334455667788(10)34567(3102)123456(3302)134567(37)100(11)220801(17)221231(21)123456789');
            </pre>
            EOT;
            echo "GS1 :" . $gs1;
            
            echo "<br/><img src='" . $gs1->getBarcodeSource(height:50, codes: [1,10,11,17,21,37,3102]) . "' />";
            echo "<hr/>";
            
            echo "<pre>GS1 Image Generator</pre>";
            echo <<<'EOT'
            <pre>
            $gs1->getBarcode(height:50, codes: [1,10,11,17,21,37,3102])
            $gs1->saveBarcode('resources/gs1');     // JPG
            </pre>
            EOT;
            $gs1->saveBarcode('resources/gs1');
            
            echo "<img src='resources/gs1.jpg' style='width: 500px;' />";
            echo "<hr/>";
        ?>
        <pre>
            <?php
        print_r($gs1);
        ?>
        </div>
    </pre>
    </body>
</html>
