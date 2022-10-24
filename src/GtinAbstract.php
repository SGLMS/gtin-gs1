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
 * Class: GtinInterface
 *
 * @category SGLMS_Library
 * @package  GS1GTIN
 * @author   Jaime C. Rubin-de-Celis <james@sglms.com>
 * @license  MIT (https://sglms.com/license)
 * @link     https://sglms.com
 **/
abstract class GtinAbstract
{
    protected string  $companyItemNumber;
    protected string  $companyPrefix;
    protected string  $itemReference;
    public int        $checkDigit;
    public int|string $number;

    /**
     * Display GTIN Number
     *
     * @return string
     **/
    public function __toString()
    {
        return (string) $this->number;
    }

    public function __construct(?int $itemNumber, ?string $companyPrefix = null, string $type = 'GTIN-12')
    {
        if (strlen((string) $companyPrefix . (string) $itemNumber) > self::getMaxDigits($type)) {
            if (self::validate((string) $companyPrefix . (string) $itemNumber, $type)) {
                $this->companyItemNumber = substr((string) $companyPrefix . (string) $itemNumber, 0, -1);
                $this->checkDigit        = (int) substr((string) $companyPrefix . (string) $itemNumber, -1);
            } else {
                throw new \ErrorException(_("Invalid Check Digit"));
            }
        } else {
            $this->buildCompanyItemNumber($itemNumber, $companyPrefix, $type);
            $this->checkDigit     = Gtin::calculateCheckDigit($this->companyItemNumber);
        }

        $this->number = (string) (
            (string) $this->companyItemNumber .
            (string) $this->checkDigit
        );
        return $this;
    }

    /**
     * Create a GTIN number (object) from a int or string
     *
     * @param int    $itemNumber    Number
     * @param string $companyPrefix Client Code or Id
     * @param string $type          [Ex. GTIN-12, etc.]
     *
     * @return \Sglms\Gtin\Gtin
     **/
    public static function create(
        int     $itemNumber,
        ?string $companyPrefix = null,
        string  $type          = 'GTIN-12'
    ): \Sglms\Gs1Gtin\GtinAbstract {
        $class = get_called_class();
        $gtin  = new $class($itemNumber, $companyPrefix, $type = 'GTIN-12');
        return $gtin;
    }

    /**
     * Calculate the Company Prefix + Item Reference Number
     *
     * @param mixed  $itemNumber    Item Reference Number
     * @param mixed  $companyPrefix Company Prefix
     * @param string $type          [Ex. GTIN-14, etc.]
     *
     * @return string
     */
    public function buildCompanyItemNumber(
        $itemNumber,
        $companyPrefix,
        $type = 'GTIN-14'
    ): string {
        $this->itemReference = (string) $itemNumber;
        $this->companyPrefix = (string) $companyPrefix;
        $maxDigits           = self::getMaxDigits($type);

        if ($maxDigits < strlen($this->companyPrefix . $this->itemReference)) {
            throw new \ErrorException(
                _("Company Prefix and Item Reference exceed digit allocation allowance."),
                1000,
                1
            );
        }
        $missingZeros = $maxDigits - strlen($this->companyPrefix . $this->itemReference);
        $this->itemReference = sprintf(
            '%0' . (strlen($this->itemReference) + $missingZeros). 'd',
            $this->itemReference
        );
        $this->companyItemNumber = $this->companyPrefix . $this->itemReference;
        return $this->companyItemNumber;
    }

    /**
     * Calculate Check Digit
     *
     * @param string $number Number
     *
     * @return int
     */
    public static function calculateCheckDigit(string $number): int
    {
        $base   = str_pad((string) $number, 15, '0', STR_PAD_LEFT);
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
    public function getBarcodeSource(int $sep = 2, int $height = 36): string
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
     * Get max number of digits (company prefix + item reference only)
     * Do not include check digit (or indicator digit for GTIN-14)
     *
     * @param ?string $type [Ex. GTIN-14, etc.]
     *
     * @return int
     */
    public static function getMaxDigits(
        ?string $type = 'GTIN-14'
    ): int {
        $type = strtoupper($type);
        return match ($type) {
            default   => 12,
            'GTIN-14' => 12,
            'EAN-14'  => 12,
            'ITF-14'  => 12,
            'GTIN-12' => 11,
            'UPC-A'   => 11,
            'GTIN-8'  => 7,
            'EAN-8'   => 7,
        };
    }

    /**
     * Save barcode image (JPEG).
     *
     * @param string $filename Separation or with of barcode
     * @param int    $sep      Separation or with of barcode
     * @param int    $height   Barcode Height
     *
     * @return string
     **/
    public function saveBarcode(
        string $filename = "name",
        int    $sep      = 2,
        int    $height   = 36
    ): void {
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
    public function saveWithNumber(
        string $filename,
        int    $sep    = 2,
        int    $height = 36
    ): void {
        $generator = $this->saveBarcode($filename, $sep, $height);
        $barcode   = imagecreatefromjpeg($filename . ".jpg");
        $bcWidth   = imagesx($barcode);
        $bcHeight  = imagesy($barcode);
        $canvas    = imagecreatetruecolor($bcWidth, $bcHeight + 20);
        $bgColor   = imagecolorallocate($canvas, 255, 255, 255);
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
            'resources/RobotoMono-SemiBold.ttf',
            (string) $this->number
        );
        imagejpeg($canvas, $filename . ".jpg", 100);
    }

    /**
     * Validate GTIN
     *
     * @param int|string $number Number to be validated
     * @param ?string    $type   [Ex. GTIN-12, etc.]
     *
     * @return bool
     */
    public static function validate(
        int|string $number,
        ?string    $type = 'GTIN-14'
    ): bool {
        if (strlen((string) $number) < self::getMaxDigits($type)) {
            throw new \ErrorException(_("Not enough digits!"), 1000, 1);
        } else {
            $checkDigit        = substr((string) $number, -1);
            $companyItemNumber = substr((string) $number, 0, -1);
            if ((int) $checkDigit === self::calculateCheckDigit((string) $companyItemNumber)) {
                return true;
            }
            return false;
        }
    }
}
