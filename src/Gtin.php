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
 * GS1 Codes Enumerate
 *
 * @category SGLMS_Library
 * @package  GS1GTIN
 * @author   Jaime C. Rubin-de-Celis <james@sglms.com>
 * @license  MIT (https://sglms.com/license)
 * @link     https://sglms.com
 **/
class Gtin
{
    protected int    $baseNumber;
    protected string $companyPrefix;
    protected string $itemReference;
    public int       $checkDigit;
    public int       $number;
    public int       $indicator = 1;

    /**
     * Constructor
     *
     * @param int    $number        Item Reference Number (See GS1 Standards)
     * @param string $companyPrefix Company Prefix or Id
     * @param int    $indicator     Indicator / Packaging Level (See GS1 Standards)
     *
     * @return void
     **/
    public function __construct(?int $number, ?string $companyPrefix = null, ?int $indicator = 1)
    {
        if (strlen((string) $number) > 14) {
            $number = (int) substr((string) $number, 0, 14);
        }
        if (strlen((string) $number) === 14) {
            $strArray              = str_split((string) $number);
            $this->baseNumber      = (int) substr((string) $number, 0, strlen((string) $number) - 1);
            $this->indicator       = (int) substr((string) $number, 0, 1);
            $this->checkDigit      = (int) substr((string) $number, -1);
            $this->companyPrefix   = sprintf('%07d', substr((string) $this->baseNumber, 1, -5));
            $this->itemReference   = substr((string) $this->baseNumber, -5);
            if ($this->checkDigit !== $this->getCheckDigit()) {
                throw new \ErrorException(_("This appears to be a GTIN-14 number, but Check Digit could not be validated!"), 1000, 1);
            }
        } else {
            $this->indicator  = $indicator ?? 1;
            $this->companyPrefix = $companyPrefix ?
                sprintf('%07d', $companyPrefix) :
                sprintf('%07d', substr((string) $number, 0, -5));
            $this->itemReference = sprintf('%05d', substr((string) $number, -5));
            $this->baseNumber = (int) ($this->indicator . $this->companyPrefix . $this->itemReference);
            $this->checkDigit = $this->getCheckDigit();
        }
        $this->number = (int) ((string) $this->baseNumber . (string) $this->checkDigit);
        return $this;
    }

    /**
     * Display GTIN Number
     *
     * @return string
     **/
    public function __toString()
    {
        return (string) $this->number;
    }

    /**
     * Calculate Check Digit
     *
     * @return int
     **/
    protected function getCheckDigit(): int
    {
        $base   = str_pad((string) $this->baseNumber, 15, '0', STR_PAD_LEFT);
        $sum    = 0;
        for ($i = 0; $i < 15; $i++) {
            $value = (int) $base[$i];
            $sum  += ((($i + 1) % 2) * 2 + 1) * $value;
        }
        $cd = 10 - ($sum % 10);
        return 10 == $cd ? 0 : $cd;
    }

    /**
     * Get (base64) barcode image source.
     *
     * @param int $sep    Separation or with of barcode
     * @param int $height Barcode Height
     *
     * @return string
     **/
    final public function getBarcodeSource(int $sep = 2, int $height = 36): string
    {
        $generator  = new BarcodeGeneratorPNG();
        return "data:image/png;base64," . base64_encode(
            $generator->getBarcode(
                $this->number,
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
    final public function saveBarcode(string $filename, int $sep = 2, int $height = 36): void
    {
        $generator  = new BarcodeGeneratorJPG();
        \file_put_contents(
            $filename . ".jpg",
            $generator->getBarcode(
                $this->number,
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
        imagecopyresampled($canvas, $barcode, 0, 0, 0, 0, $bcWidth, $bcHeight, $bcWidth, $bcHeight);
        imagedestroy($barcode);

        imagettftext(
            $canvas,
            11,
            0,
            (int) ($bcWidth * 0.25),
            $bcHeight + 16,
            imagecolorallocate($canvas, 10, 10, 10),
            'resources/Montserrat-SemiBold.ttf',
            (string) $this->number
        );
        imagejpeg($canvas, $filename . ".jpg", 100);
    }

    /**
     * Create a GTIN number (object) from a int or string
     *
     * @param int    $number         Number
     * @param string $companyPrefix  Client Code or Id
     * @param string $packagingLevel Packaging Level (Indicator according to GS1 Standards)
     *
     * @return \Sglms\Gtin\Gtin
     **/
    final public static function create(
        int $number,
        ?string $companyPrefix = null,
        int $packagingLevel = 1
    ): \Sglms\Gs1Gtin\Gtin {
        $gtin                   = new self($number, $companyPrefix, $packagingLevel);
        $gtin->checkDigit = $gtin->getCheckDigit();
        return $gtin;
    }
}
