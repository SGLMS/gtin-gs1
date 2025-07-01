<?php

/**
 * SGLMS GS1 / GTIN
 *
 * PHP Version 8.1
 *
 * @category SGLMS_Library
 * @package  GS1GTIN
 * @author   Jaime C. Rubin-de-Celis <james@sglms.com>
 * @license  MIT (https://sglms.com/license)
 * @link     https://sglms.com
 **/

declare(strict_types=1);

namespace Sglms\Gs1Gtin;

use Picqer\Barcode\BarcodeGeneratorJPG;
use Picqer\Barcode\Renderers\JpgRenderer;
use Picqer\Barcode\Renderers\PngRenderer;
use Picqer\Barcode\Types\TypeCode128;

/**
 * GS1 Class
 *
 * @category SGLMS_Library
 * @package  GS1GTIN
 * @author   Jaime C. Rubin-de-Celis <james@sglms.com>
 * @license  MIT (https://sglms.com/license)
 * @link     https://sglms.com
 **/
class Gs1
{
    public array $data;
    public ?string $gs1;
    public ?string $sscc;          // 00
    public ?string $gtin;          // 01
    public ?string $content;       // 02
    public ?int $netWeight;     // 3102 / 3201
    public ?int $grossWeight;   // 3302
    public ?int $batch;         // 10
    public ?int $serial;        // 21
    public ?int $productionDate; // 11
    public ?int $expirationDate; // 17
    public ?int $pieces;        // 37

    /**
     * Constructor
     *
     * @param array $data GS1 Data
     *
     * @return void
     **/
    public function __construct(?array $data = [])
    {
        $this->data     = $data;
        $this->gtin     = (string) ($data[1] ?? null);
        $this->content  = $data[2] ?? null;
        $this->sscc     = $data[0] ?? null;
        $this->expirationDate   = $data[17] ?? null;
        $this->productionDate   = $data[11] ?? null;
        $this->batch    = $data[10] ?? null;
        $this->serial   = $data[21] ?? null;
        $this->pieces   = $data[37] ?? null;
        $this->netWeight    = isset($data[3102]) ? (int) round($data[3102] * 100) : null;
        $this->netWeight    = isset($data[3201]) ? $data [3201] / 0.45359237 : $this->netWeight;
        $this->grossWeight  = isset($data[3302]) ? (int) round($data[3302] * 100) : null;
    }

    /**
     * Get GS1 filtered by codes.
     *
     * @param array|null $codes
     *
     * @return void
     */
    public function get(
        ?array $codes = [1,21,17,3102]
    ) {
        $filter = array_filter(
            $this->data,
            fn ($v, $k) => in_array($k, $codes),
            ARRAY_FILTER_USE_BOTH
        );
        $array = array_map(
            function ($v, $k) {
                if ($k == 3102 || $k == 3302) {
                    $v = str_pad((string) $v, 6, "0", STR_PAD_LEFT);
                }
                return "($k)" . $v;
            },
            $filter,
            array_keys($filter)
        );
        return implode($array);
    }

    /**
     * Parse GS1 String
     *
     * @param string $string
     *
     * @return void
     */
    public static function parse(
        string $string
    ) {
        $gs1 = new self();
        preg_match("/^([\(]?01[\)]?)([0-9]{14})/", $string, $matches);
        if ($matches) {
            $gs1->gtin = (string) $matches[2];
            $gs1->data [1] = $gs1->gtin;
            $string = str_replace($matches[0], "", $string);
        }
        preg_match("/([\(]?3102[\)]?)([0-9]{6})/", $string, $weights);
        if ($weights) {
            $gs1->netWeight = (int) $weights[2];
            $gs1->data [3102] = $gs1->netWeight;
            $string = str_replace($weights[0], "", $string);
        }
        preg_match("/([\(]?3302[\)]?)([0-9]{6})/", $string, $matches);
        if ($matches) {
            $gs1->grossWeight = (int) $matches[2];
            $gs1->data [3302] = $gs1->grossWeight;
            $string = str_replace($matches[0], "", $string);
        }
        preg_match("/([\(]?3201[\)]?)([0-9]{6})/", $string, $weights);  // Net weight in pounds; 1 decimal
        if ($weights) {
            $gs1->netWeight = (int) ($weights[2] / 10 / 2.205);
            $gs1->data [3102] = $gs1->netWeight;
            $string = str_replace($weights[0], "", $string);
        }
        preg_match("/([\(]?11[\)]?)(\d{2}(?:0\d|1[0-2])(?:[0-2]\d|3[01]))/", $string, $matches);
        if ($matches) {
            $gs1->productionDate = (int) $matches[2];
            $gs1->data [11] = $gs1->productionDate;
            $string = str_replace($matches[0], "", $string);
        }
        preg_match("/([\(]?17[\)]?)(\d{2}(?:0\d|1[0-2])(?:[0-2]\d|3[01]))/", $string, $matches);
        if ($matches) {
            $gs1->expirationDate = (int) $matches[2];
            $gs1->data [17] = $gs1->expirationDate;
            $string = str_replace($matches[0], "", $string);
        }
        preg_match("/([\(]?10[\)]?)([0-9]{1,20})/", $string, $matches);
        if ($matches) {
            $gs1->batch = (int) $matches[2];
            $gs1->data [10] = $gs1->batch;
            $string = str_replace($matches[0], "", $string);
        }
        preg_match("/([\(]?21[\)]?)([0-9]{1,20})/", $string, $matches);
        if ($matches) {
            $gs1->serial = (int) $matches[2];
            $gs1->data [21] = $gs1->serial;
            ;
            $string = str_replace($matches[0], "", $string);
        }
        preg_match("/([\(]?37[\)]?)([0-9]{1,4})/", $string, $matches);
        if ($matches) {
            $gs1->pieces = (int) $matches[2];
            $gs1->data [37] = $gs1->pieces;
            ;
            $string = str_replace($matches[0], "", $string);
        }
        ksort($gs1->data);
        return $gs1;
    }

    /**
     * Display GS1 Number
     *
     * @return string
     **/
    public function __toString()
    {
        $s [] = '(01)' . $this->gtin;
        $s [] = $this->batch ? '(10)' . $this->batch : null;
        $s [] = $this->productionDate ? '(11)' . $this->productionDate : null;
        $s [] = $this->expirationDate ? '(17)' . $this->expirationDate : null;
        $s [] = $this->serial ? '(21)' . $this->serial : null;
        $s [] = $this->pieces ? '(37)' . $this->pieces : null;
        $s [] = $this->netWeight ? '(3102)' . str_pad((string) $this->netWeight, 6, "0", STR_PAD_LEFT) : null;
        $s [] = $this->grossWeight ? '(3302)' . str_pad((string) $this->grossWeight, 6, "0") : null;
        return implode($s);
    }

    /**
     * Get (base64) barcode image source.
     *
     * @param int $sep    Separation or width of barcode
     * @param int $height Barcode Height
     * @param array $codes [Default: 1, 10, 21, 3102]
     *
     * @return string
     **/
    final public function getBarcodeSource(
        int $height = 50,
        ?array $codes = [1,10,21,37,3102]
    ): string {
        $barcode = (new TypeCode128())->getBarcode((string) $this->get($codes));
        $renderer = new PngRenderer();
        $renderer->setBackgroundColor([255, 255, 255]);
        return "data:image/png;base64," . base64_encode(
            $renderer->render($barcode, $barcode->getWidth(), $height)
        );
    }

    final public function getBarcode(
        array $codes = [1, 21, 3201],
        $numbers = false,
        ?int $height = 50
    ) {
        $image = '<img src="'.$this->getBarcodeSource(height: $height, codes: $codes).'" style="margin: auto;"/>';
        if ($numbers) {
            $image = <<<EOT
                <span class='text-center'>{$image}
                <p >{$this->get($codes)}</p>
                <span>
            EOT;
        }
        return $image;
    }

    /**
     * Save barcode image (PNG).
     *
     * @param string $filename Separation or with of barcode
     * @param int    $sep      Separation or with of barcode
     * @param int    $height   Barcode Height
     *
     * @return string
     **/
    final public function saveBarcode(
        string $filename,
        array $codes = [1,10,11,17,21,37,3102],
        int $height = 50
    ): void {
        $barcode = (new TypeCode128())->getBarcode($this->get($codes));
        $renderer = new JpgRenderer();
        $renderer->setBackgroundColor([255, 255, 255]);
        $generator  = new BarcodeGeneratorJPG();
        \file_put_contents(
            $filename . ".jpg",
            $renderer->render(
                $barcode,
                $barcode->getWidth(),
                $height
            )
        );
        $barcode  = imagecreatefromjpeg($filename . ".jpg");
        $bcWidth  = imagesx($barcode);
        $bcHeight = imagesy($barcode);
        $canvas   = imagecreatetruecolor($bcWidth, $bcHeight + 20);
        $bgColor  = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $bcWidth, $bcHeight + 20, $bgColor);
        imagecopyresampled(
            $canvas,
            $barcode,
            0,
            0,
            0,
            0,
            $bcWidth,
            $bcHeight,
            $bcWidth,
            $bcHeight
        );
        imagedestroy($barcode);

        imagettftext(
            $canvas,
            11,
            0,
            (int) ($bcWidth * 0.025),
            $bcHeight + 16,
            imagecolorallocate($canvas, 10, 10, 10),
            'fonts/RobotoMono-SemiBold.ttf',
            (string) $this->get($codes)
        );
        imagejpeg($canvas, $filename . ".jpg", 100);
    }
}
