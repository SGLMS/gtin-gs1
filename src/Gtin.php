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
 * Class: Gtin
 *
 * @category SGLMS_Library
 * @package  GS1GTIN
 * @author   Jaime C. Rubin-de-Celis <james@sglms.com>
 * @license  MIT (https://sglms.com/license)
 * @link     https://sglms.com
 **/
class Gtin extends GtinAbstract
{
    public int        $indicatorDigit;  // Packing Level for GTIN-14

    /**
     * Constructor
     *
     * @param int    $itemNumber    Item Reference Number (See GS1 Standards)
     * @param string $companyPrefix Company Prefix or Id
     * @param int    $indicator     Indicator / Packaging Level (See GS1 Standards)
     *
     * @return void
     **/
    public function __construct(?int $itemNumber, ?string $companyPrefix = null, ?int $indicator = 0)
    {
        if (strlen((string) $companyPrefix . (string) $itemNumber) > self::getMaxDigits()) {
            if (self::validate((string) $companyPrefix . (string) $itemNumber)) {
                $this->companyItemNumber = substr((string) $companyPrefix . (string) $itemNumber, 0, -1);
                $this->checkDigit = (int) substr((string) $companyPrefix . (string) $itemNumber, -1);
                $this->indicatorDigit = 0;
            } else {
                throw new \ErrorException(_("Invalid Check Digit"));
            }
        } else {
            $this->indicatorDigit = $indicator ?: 1;
            $this->buildCompanyItemNumber($itemNumber, $companyPrefix);
            $this->checkDigit     = Gtin::calculateCheckDigit($this->indicatorDigit . $this->companyItemNumber);
        }

        $this->number = (string) (
            (string) $this->indicatorDigit .
            (string) $this->companyItemNumber .
            (string) $this->checkDigit
        );
        return $this;
    }

    /**
     * Create a GTIN number (object) from a int or string
     *
     * @param int    $itemNumber     Number
     * @param string $companyPrefix  Client Code or Id
     * @param string $type           [Ex. GTIN-14]
     * @param int    $packagingLevel Packaging Level (Indicator according to GS1 Standards)
     *
     * @return \Sglms\Gtin\Gtin
     **/
    public static function create(
        int     $itemNumber,
        ?string $companyPrefix  = null,
        string  $type = 'GTIN-14',
        int     $packagingLevel = 1
    ): \Sglms\Gs1Gtin\GtinAbstract {
        $gtin             = new self($itemNumber, $companyPrefix, $packagingLevel);
        return $gtin;
    }
}

class_alias("\Sglms\Gs1Gtin\Gtin", "ITF14");
