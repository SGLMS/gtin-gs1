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

use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\BarcodeGeneratorJPG;

/**
 * GS1 Class
 *
 * @category SGLMS_Library
 * @package  GS1GTIN
 * @author   Jaime C. Rubin-de-Celis <james@sglms.com>
 * @license  MIT (https://sglms.com/license)
 * @link     https://sglms.com
 **/
class Gs1Generator
{
    public array $data;
    public ?int $gs1;
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
        $this->gtin     = $data[1] ?? null;
        $this->content  = $data[2] ?? null;
        $this->sscc     = $data[0] ?? null;
        $this->expirationDate   = $data[10] ?? null;
        $this->productionDate   = $data[11] ?? null;
        $this->batch    = $data[17] ?? null;
        $this->serial   = $data[21] ?? null;
        $this->pieces   = $data[37] ?? null;
        $this->netWeight    = isset($data[3102]) ? (int) round($data[3102] * 100) : null;
        $this->netWeight    = isset($data[3201]) ? $data [3201] / 0.45359237 : $this->netWeight;
        $this->grossWeight  = isset($data[3302]) ? (int) round($data[3302] * 100) : null;
    }

    public function get(?array $array = [1,21,17,3102])
    {
        $filter = array_filter(
            $this->data,
            fn ($v, $k) => in_array($k, $array),
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

    public function generate()
    {

    }

    public static function parse(string $string)
    {
        $gs1 = new self();
        $gs1->gs1 = $string;
        $matches = collect();
        preg_match("/^([\(]?01[\)]?)([0-9]{14})/", $string, $matches);
        if ($matches) {
            $gs1->gtin = $matches[2];
            $string = str_replace($matches[0], "", $string);
        }
        preg_match("/([\(]?3102[\)]?)([0-9]{6})/", $string, $weights);
        if ($weights) {
            $gs1->netWeight = $weights[2] / 100;
            $string = str_replace($weights[0], "", $string);
        }
        preg_match("/([\(]?3302[\)]?)([0-9]{6})/", $string, $matches);
        if ($matches) {
            $gs1->grossWeight = $matches[2] / 100;
            $string = str_replace($matches[0], "", $string);
        }
        preg_match("/([\(]?3201[\)]?)([0-9]{6})/", $string, $weights);  // Net weight in pounds; 1 decimal
        if ($weights) {
            $gs1->netWeight = $weights[2] / 10 / 2.205;
            $string = str_replace($weights[0], "", $string);
        }
        preg_match("/([\(]?11[\)]?)(\d{2}(?:0\d|1[0-2])(?:[0-2]\d|3[01]))/", $string, $matches);
        if ($matches) {
            $gs1->productionDate = $matches[2];
            $string = str_replace($matches[0], "", $string);
        }
        preg_match("/([\(]?17[\)]?)(\d{2}(?:0\d|1[0-2])(?:[0-2]\d|3[01]))/", $string, $matches);
        if ($matches) {
            $gs1->expirationDate = $matches[2];
            $string = str_replace($matches[0], "", $string);
        }
        preg_match("/([\(]?10[\)]?)([0-9]{1,20})/", $string, $matches);
        if ($matches) {
            $gs1->batch = $matches[2];
            $string = str_replace($matches[0], "", $string);
        }
        preg_match("/([\(]?21[\)]?)([0-9]{1,20})/", $string, $matches);
        if ($matches) {
            $gs1->serial = $matches[2];
            $string = str_replace($matches[0], "", $string);
        }
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
        int $sep = 1,
        int $height = 36,
        ?array $codes = [1,10,21,37,3102]
    ): string {
        $generator  = new BarcodeGeneratorPNG();
        return "data:image/png;base64," . base64_encode(
            $generator->getBarcode(
                $this->get($codes),
                $generator::TYPE_CODE_128,
                $sep,
                $height
            )
        );
    }

    final public function getBarcode(
        array $codes = [1, 10, 21, 3201],
        $numbers = false,
        ?int $height = 48
    ) {
        $image = '<img src="'.$this->getBarcodeSource(height: $height, codes: $codes).'" style="margin: auto;"/>';
        if ($numbers) {
            $image = <<<EOT
                <span class='text-center'>{$image}
                <p style='text-align:center;'>{$this->get($codes)}</p>
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
    final public function saveBarcode(string $filename, int $sep = 1, int $height = 36): void
    {
        $generator  = new BarcodeGeneratorJPG();
        \file_put_contents(
            $filename . ".jpg",
            $generator->getBarcode(
                $this->gs1,
                $generator::TYPE_CODE_128,
                $sep,
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
            'resources/RobotoMono-SemiBold.ttf',
            (string) $this->gs1
        );
        imagejpeg($canvas, $filename . ".jpg", 100);
    }
}
