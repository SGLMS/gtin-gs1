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
 * Class: Gtin12
 *
 * @category SGLMS_Library
 * @package  GS1GTIN
 * @author   Jaime C. Rubin-de-Celis <james@sglms.com>
 * @license  MIT (https://sglms.com/license)
 * @link     https://sglms.com
 **/
class Gtin12 extends Gtin
{
    /**
     * Constructor
     *
     * @param int    $number        Item Reference Number (See GS1 Standards)
     * @param string $companyPrefix Company Prefix or Id
     * @param int    $indicator     Indicator / Packaging Level (See GS1 Standards)
     *
     * @return void
     **/
    public function __construct(?int $number, ?string $companyPrefix = null, ?int $indicator = 0)
    {
        if (strlen((string) $number) > 12) {
            $number = (int) substr((string) $number, 0, 12);
        }
        if (strlen((string) $number) === 12) {
            $strArray              = str_split((string) $number);
            $this->baseNumber      = (int) substr((string) $number, 0, strlen((string) $number) - 1);
            $this->indicator       = (int) substr((string) $number, 0, 1);
            $this->checkDigit      = (int) substr((string) $number, -1);
            $this->companyPrefix   = sprintf('%07d', substr((string) $this->baseNumber, 1, -5));
            $this->itemReference   = substr((string) $this->baseNumber, -5);
            if ($this->checkDigit !== $this->getCheckDigit()) {
                throw new \ErrorException(_("This appears to be a GTIN-12 number, it has the right length, but Check Digit could not be validated!"), 1000, 1);
            }
        } else {
            $companyPrefixLength = strlen((string) $companyPrefix);
            $this->companyPrefix = $companyPrefix;
            $itemReferenceLength = 11 - $companyPrefixLength;
            $this->itemReference = sprintf(
                '%0' . $itemReferenceLength . 'd',
                (string) $number
            );
            $this->baseNumber    = (int) ($this->companyPrefix . $this->itemReference);
            $this->checkDigit    = $this->getCheckDigit();
        }
        $this->number = (int) ((string) $this->baseNumber . (string) $this->checkDigit);
        return $this;
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
    public function saveBarcode(string $filename, int $sep = 2, int $height = 36): void
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

        $whiteBG = imagecreatetruecolor(10, 10);
        $bgColor = imagecolorallocate($whiteBG, 255, 255, 255);
        imagefilledrectangle(
            $whiteBG,
            0,
            0,
            10,
            10,
            $bgColor
        );
        imagecopyresampled(
            $barcode,
            $whiteBG,
            5,
            $bcHeight - 8,
            0,
            0,
            (int) ($bcWidth / 2  - 6),
            10,
            10,
            10
        );

        imagecopyresampled(
            $barcode,
            $whiteBG,
            (int) ($bcWidth / 2  + 5),
            $bcHeight - 8,
            0,
            0,
            (int) ($bcWidth / 2  - 9),
            10,
            10,
            10
        );

        // Canvas for new (final) image
        $canvas   = imagecreatetruecolor($bcWidth + 20, $bcHeight + 16);
        $bgColor  = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $bcWidth + 20, $bcHeight + 16, $bgColor);
        imagecopyresampled(
            $canvas,
            $barcode,
            10,
            0,
            0,
            0,
            $bcWidth,
            $bcHeight,
            $bcWidth,
            $bcHeight
        );

        imagettftext(
            $canvas,
            8,
            0,
            0,
            $bcHeight + 8,
            imagecolorallocate($barcode, 10, 10, 10),
            'resources/RobotoMono-SemiBold.ttf',
            (string) substr((string) $this->number, 0, 1)
        );
        imagettftext(
            $canvas,
            10,
            0,
            17,
            $bcHeight + 8,
            imagecolorallocate($barcode, 10, 10, 10),
            'resources/RobotoMono-SemiBold.ttf',
            substr((string) $this->number, 1, 5)
        );
        imagettftext(
            $canvas,
            10,
            0,
            66,
            $bcHeight + 8,
            imagecolorallocate($barcode, 10, 10, 10),
            'resources/RobotoMono-SemiBold.ttf',
            substr((string) $this->number, 6, 5)
        );
        imagettftext(
            $canvas,
            8,
            0,
            $bcWidth + 12,
            $bcHeight + 8,
            imagecolorallocate($barcode, 10, 10, 10),
            'resources/RobotoMono-SemiBold.ttf',
            (string) substr((string) $this->number, -1)
        );
        imagedestroy($barcode);
        imagejpeg($canvas, $filename . ".jpg", 100);
    }

    /**
     * Create a GTIN12 number (object) from a int or string
     *
     * @param int    $number         Number
     * @param string $companyPrefix  Client Code or Id
     * @param string $packagingLevel Packaging Level (Indicator according to GS1 Standards)
     *
     * @return \Sglms\Gtin\Gtin
     **/
    final public static function create(
        int     $number,
        ?string $companyPrefix  = "1",
        int     $packagingLevel = null
    ): \Sglms\Gs1Gtin\Gtin {
        $gtin             = new self($number, $companyPrefix, $packagingLevel);
        $gtin->checkDigit = $gtin->getCheckDigit();
        return $gtin;
    }
}

class_alias("\Sglms\Gs1Gtin\Gtin12", "UPCA");
