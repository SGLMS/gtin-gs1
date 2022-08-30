<?php

/**
 * SGLMS GS1 / GTIN
 *
 * PHP Version 8.1
 *
 * @category Library
 * @package  GS1GTIN
 * @author   Jaime C. Rubin-de-Celis <james@sglms.com>
 * @license  MIT (https://sglms.com/license)
 * @link     https://sglms.com
 **/

declare(strict_types=1);

namespace Sglms\Gs1Gtin;

use Picqer\Barcode\BarcodeGeneratorPNG;

/**
 * GS1 Class
 *
 * @category Library
 * @package  GS1GTIN
 * @author   Jaime C. Rubin-de-Celis <james@sglms.com>
 * @license  MIT (https://sglms.com/license)
 * @link     https://sglms.com
 **/
class Gs1
{
    protected string $gs1;
    public int       $gtin;
    public int       $units;
    public int|float $netWeight;
    public int|float $grossWeight;
    public int       $batch;
    public \DateTime $productionDate;
    public \DateTime $expirationDate;

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
        foreach ($gs1Array as $index => $item) {
            if (0 == $index % 2) {
                $key    = preg_replace("/[\(\)]/", "", $item);
                $code   = Gs1Code::tryFrom($key);
                if ($code) {
                    $array [$key]   = $gs1Array[$index + 1];
                    switch($code) {
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
                    }
                }
            }
        }
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
    final public function getBarcodeSource(int $sep = 2, int $height = 36): string
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
}
