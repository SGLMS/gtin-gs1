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
class Gtin8 extends GtinAbstract
{

    /**
     * Create a GTIN number (object) from a int or string
     *
     * @param int    $itemNumber    Number
     * @param string $companyPrefix Client Code or Id
     * @param string $type          GTIN-12
     *
     * @return \Sglms\Gtin\Gtin
     **/
    public static function create(
        int     $itemNumber,
        ?string $companyPrefix  = null,
        string  $type = 'GTIN-8'
    ): \Sglms\Gs1Gtin\GtinAbstract {
        $gtin             = new self($itemNumber, $companyPrefix, $type);
        return $gtin;
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
        string $filename = "name",
        int $sep         = 1,
        int $height      = 56
    ): void {
        $generator = $this->saveBarcode($filename, $sep, $height);
        $barcode   = imagecreatefromjpeg($filename . ".jpg");
        $bcWidth   = imagesx($barcode);
        $bcHeight  = imagesy($barcode);

        $whiteBG   = imagecreatetruecolor(10, 10);
        $bgColor   = imagecolorallocate($whiteBG, 255, 255, 255);
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
            10,
            0,
            16,
            $bcHeight + 6,
            imagecolorallocate($barcode, 10, 10, 10),
            'resources/RobotoMono-SemiBold.ttf',
            substr((string) $this->number, 0, 4)
        );
        imagettftext(
            $canvas,
            10,
            0,
            53,
            $bcHeight + 6,
            imagecolorallocate($barcode, 10, 10, 10),
            'resources/RobotoMono-SemiBold.ttf',
            substr((string) $this->number, 4, 4)
        );
        imagedestroy($barcode);
        imagejpeg($canvas, $filename . ".jpg", 100);
    }
}

class_alias("\Sglms\Gs1Gtin\Gtin8", "EAN8");
