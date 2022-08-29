<?php

/**
 * SGLMS GS1 / GTIN
 *
 * PHP Version 8.1
 *
 * @category Library
 * @package  GS1GTIN
 * @author   Jaime C. Rubin-de-Celis <james@sglms.com>
 * @license  MIT (https://sglms.com/licence)
 * @link     https://sglms.com
 **/

declare(strict_types=1);

namespace Sglms\Gtin;

use Picqer\Barcode\BarcodeGeneratorPNG;

/**
 * GS1 Codes Enumerate
 *
 * @category Library
 * @package  GS1GTINn
 * @author   Jaime C. Rubin-de-Celis <james@sglms.com>
 * @license  MIT (https://sglms.com/licence)
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
     * @param int $number Number
     *
     * @return void
     **/
    public function __construct(?int $number)
    {
        if (strlen((string) $number) > 14) {
            $number = (int) substr((string) $number, 0, 14);
        }
        if (strlen((string) $number) === 14) {
            $strArray              = str_split((string) $number);
            echo $this->baseNumber = (int) substr((string) $number, 0, strlen((string) $number) - 1);
            $this->indicator       = (int) substr((string) $number, 0, 1);
            $this->checkDigit      = (int) substr((string) $number, -1);
            $this->companyPrefix   = sprintf('%07d', substr((string) $this->baseNumber, 1, -5));
            $this->itemReference   = substr((string) $this->baseNumber, -5);
            if ($this->checkDigit !== $this->getCheckDigit()) {
                throw new \ErrorException(_("This appears to be a GTIN-14 number, but Check Digit could not be validated!"), 1000, 1);
            }
        } else {
            $this->indicator  = 1;
            $this->companyPrefix = sprintf('%07d', substr((string) $number, 0, -5));
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
     * Create a GTIN number (object) from a int or string
     *
     * @param int $number Number
     *
     * @return \Sglms\Gtin\Gtin
     **/
    final public static function create(int $number): \Sglms\Gtin\Gtin
    {
        $gtin             = new self($number);
        $gtin->checkDigit = $gtin->getCheckDigit();
        return $gtin;
    }
}
