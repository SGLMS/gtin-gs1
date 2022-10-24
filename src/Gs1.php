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
class Gs1
{
    public string     $gs1;
    public array      $gs1Array;
    public int|string $gtin;
    public int        $units;
    public int|float  $netWeight;
    public int|float  $grossWeight;
    public int        $batch;
    public \DateTime  $productionDate;
    public \DateTime  $expirationDate;

    /**
     * Constructor
     *
     * @param string $gs1 Number
     *
     * @return void
     **/
    public function __construct(?string $gs1)
    {
        $this->gs1 = $gs1;
        $gs1Array = preg_split("/(\([0-9]*\))/", $gs1, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $this->gs1Array = $gs1Array;
        foreach ($gs1Array as $index => $item) {
            if (0 == $index % 2) {
                $key    = preg_replace("/[\(\)]/", "", $item);
                $code   = Gs1Code::tryFrom($key);
                if ($code) {
                    $array [$key]   = $gs1Array[$index + 1];
                    switch($code) :
                        case Gs1Code::GTIN:
                        case Gs1Code::Content:
                            $this->gtin = (Gtin::create((int) $gs1Array[$index + 1]))->number;
                            break;
                        case Gs1Code::NetWeight:
                            $this->netWeight = (int) $gs1Array[$index + 1] / 100;
                            break;
                        case Gs1Code::GrossWeight:
                            $this->grossWeight = (int) $gs1Array[$index + 1] / 100;
                            break;
                        case Gs1Code::Units:
                            $this->units = (int) $gs1Array[$index + 1];
                            break;
                        case Gs1Code::BatchNumber:
                            $this->batch = (int) $gs1Array[$index + 1];
                            break;
                        case Gs1Code::ProductionDate:
                            $this->productionDate = \DateTime::createFromFormat("ymd", $gs1Array[$index + 1]);
                            break;
                        case Gs1Code::ExpirationDate:
                            $this->expirationDate = \DateTime::createFromFormat("ymd", $gs1Array[$index + 1]);
                            break;
                    endswitch;
                }
            }
        }
        $this->update();
    }

    /**
     * Display GS1 Number
     *
     * @return string
     **/
    public function __toString()
    {
        return $this->gs1;
    }

    /**
     * Get (base64) barcode image source.
     *
     * @param int $sep    Separation or with of barcode
     * @param int $height Barcode Height
     *
     * @return string
     **/
    final public function getBarcodeSource(int $sep = 1, int $height = 36): string
    {
        $generator  = new BarcodeGeneratorPNG();
        return "data:image/png;base64," . base64_encode(
            $generator->getBarcode(
                $this->gs1,
                $generator::TYPE_CODE_128,
                $sep,
                $height
            )
        );
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

    /**
     * Update GS1 string to match calculated GTIN
     *
     * @return void
     **/
    final public function update(): void
    {
        $index = array_search('(01)', $this->gs1Array);
        $gtin  = (int) $this->gs1Array[$index + 1];
        if ($gtin !== $this->gtin) {
            $this->gs1Array[$index + 1] = (string) $this->gtin;
            $this->gs1 = implode($this->gs1Array);
        }
    }
}
