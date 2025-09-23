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
use Picqer\Barcode\Renderers\HtmlRenderer;
use Picqer\Barcode\Renderers\JpgRenderer;
use Picqer\Barcode\Renderers\PngRenderer;
use Picqer\Barcode\Renderers\SvgRenderer;
use Picqer\Barcode\Types\TypeCode128;

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
    public string     $type = 'GTIN-14';
    public int        $packagingLevel = 1;  // Packing Level for GTIN-14


    /**
     * Constructor
     *
     * @param integer      $itemNumber
     * @param string|null  $companyPrefix
     * @param string|null  $type           [Ex. GTIN-14]
     * @param integer|null $packagingLevel Packaging Level (Indicator according to GS1 Standards). Default: 1
     */
    public function __construct(
        int $itemNumber,
        ?string $companyPrefix = null,
        ?string $type = 'GTIN-14',
        ?int $packagingLevel = 1
    ) {
        $this->type = strtoupper($type);
        $this->packagingLevel = $packagingLevel ?: 1;
        if (strlen(
            (string) $companyPrefix . (string) $itemNumber
        ) > self::getMaxDigits($this->type)
        ) {
            if (self::validate(
                (string) $companyPrefix
                . (string) $itemNumber,
                $this->type
            )
            ) {
                $this->companyItemNumber = substr((string) $companyPrefix . (string) $itemNumber, 0, -1);
                $this->checkDigit        = (int) substr((string) $companyPrefix . (string) $itemNumber, -1);
            } else {
                throw new \ErrorException(_("Invalid Check Digit"));
            }
        } else {
            $this->buildCompanyItemNumber($itemNumber, $companyPrefix, $this->type);
            $this->checkDigit     = Gtin::calculateCheckDigit($this->companyItemNumber);
        }

        $this->number = (string) (
            ($this->type == 'GTIN-14' ? (string) $packagingLevel : '') .
            (string) $this->companyItemNumber .
            (string) $this->checkDigit
        );
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
        ?string $companyPrefix = null,
        ?string $type          = 'GTIN-14',
        ?int    $packagingLevel = 1
    ): \Sglms\Gs1Gtin\GtinAbstract {
        $class = get_called_class();
        $gtin  = new $class(
            $itemNumber,
            $companyPrefix,
            $type,
            $packagingLevel
        );
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

    public function getBarcode()
    {
        return $barcode = (new TypeCode128())->getBarcode($this->number);
    }

    public function renderBarcode(?string $format = 'svg', ?int $height = 50)
    {
        $barcode = $this->getBarcode();
        switch ($format) {
            case 'png':
                $renderer = new PngRenderer();
                $renderer->setBackgroundColor([255, 255, 255]);
                break;

            case 'jpg':
                $renderer = new JpgRenderer();
                $renderer->setBackgroundColor([255, 255, 255]);
                break;

            case 'html':
                $renderer = new HtmlRenderer();
                break;

            default:
                $renderer = new SvgRenderer();
                $renderer->setBackgroundColor([255, 255, 255]);
                break;
        }
        return $renderer->render($barcode, $barcode->getWidth(), $height);
    }

    public function barcode(?int $height = 50): string
    {
        $barcode = (new TypeCode128())->getBarcode($this->number);
        $renderer = new SvgRenderer();
        $renderer->setForegroundColor([50, 50, 50]); // Give a color red for the bars, default is black. Give it as 3 times 0-255 values for red, green and blue.
        $renderer->setBackgroundColor([250, 250, 250]); // Give a color blue for the background, default is transparent. Give it as 3 times 0-255 values for red, green and blue.
        $renderer->setSvgType($renderer::TYPE_SVG_INLINE);
        return $renderer->render($barcode, $barcode->getWidth(), $height);
    }

    /**
     * Get (base64) barcode image source.
     *
     * @param int $sep    Separation or with of barcode
     * @param int $height Barcode Height
     *
     * @return string
     **/
    public function getBarcodeSource(?int $height = 50): string
    {
        $barcode = (new TypeCode128())->getBarcode($this->number);
        $renderer = new PngRenderer();
        $renderer = new SvgRenderer();
        $renderer->setForegroundColor([50, 50, 50]); // Give a color red for the bars, default is black. Give it as 3 times 0-255 values for red, green and blue.
        $renderer->setBackgroundColor([250, 250, 250]); // Give a color blue for the background, default is transparent. Give it as 3 times 0-255 values for red, green and blue.
        $renderer->setSvgType($renderer::TYPE_SVG_INLINE);
        return $renderer->render($barcode, $barcode->getWidth(), $height);
        return "data:image/png;base64," . base64_encode(
            $renderer->render($barcode, $barcode->getWidth(), $height)
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
        $barcode = (new TypeCode128())->getBarcode($this->number);
        $renderer = new JpgRenderer();
        $renderer->setBackgroundColor([255, 255, 255]);
        $generator  = new BarcodeGeneratorJPG();
        \file_put_contents(
            $filename . ".jpg",
            $renderer->render(
                $barcode,
                $barcode->getWidth(),
                50
            ),
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
            10,
            0,
            (int) ($bcWidth * 0.0),
            $bcHeight + 16,
            imagecolorallocate($canvas, 10, 10, 10),
            '../fonts/RobotoMono-SemiBold.ttf',
            (string) $this->number
        );
        imagejpeg($canvas, $filename . ".jpg", 100);
    }

    /**
     * Undocumented function
     *
     * @param integer|string $number Number to be validated
     * @param string|null    $type   TIN format. Default: GTIN-14
     *
     * @return boolean
     */
    public static function validate(
        int|string $number,
        ?string    $type = 'GTIN-14'
    ): bool {
        try {
            $type = strtoupper($type);
            if (strlen((string) $number) < self::getMaxDigits($type)) {
                throw new \ErrorException(__("Not enough digits!"), 1000);
            } else {
                $checkDigit        = substr((string) $number, -1);
                // die(var_dump($number, $checkDigit));
                $companyItemNumber = substr((string) $number, 0, -1);
                if ((int) $checkDigit === self::calculateCheckDigit((string) $companyItemNumber)) {
                    return true;
                }
                return false;
            }
        } catch (\Throwable $th) {
            $type = 'GTIN-14';
            return false;
        }
    }
}
