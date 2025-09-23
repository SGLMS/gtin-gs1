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
class Gtin12 extends GtinAbstract
{
    /**
     * Constructor
     *
     * @param integer      $itemNumber
     * @param string|null  $companyPrefix
     */
    public function __construct(
        int $itemNumber,
        ?string $companyPrefix = null,
    ) {
        parent::__construct(
            itemNumber: $itemNumber,
            companyPrefix: $companyPrefix,
            type: 'GTIN-12',
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
        string $filename = "name",
        int $sep         = 2,
        int $height      = 36
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
            8,
            0,
            0,
            $bcHeight + 5,
            imagecolorallocate($barcode, 10, 10, 10),
            '../fonts/RobotoMono-SemiBold.ttf',
            (string) substr((string) $this->number, 0, 1)
        );
        imagettftext(
            $canvas,
            10,
            0,
            17,
            $bcHeight + 5,
            imagecolorallocate($barcode, 10, 10, 10),
            '../fonts/RobotoMono-SemiBold.ttf',
            substr((string) $this->number, 1, 5)
        );
        imagettftext(
            $canvas,
            10,
            0,
            66,
            $bcHeight + 5,
            imagecolorallocate($barcode, 10, 10, 10),
            '../fonts/RobotoMono-SemiBold.ttf',
            substr((string) $this->number, 6, 5)
        );
        imagettftext(
            $canvas,
            8,
            0,
            $bcWidth + 12,
            $bcHeight + 5,
            imagecolorallocate($barcode, 10, 10, 10),
            '../fonts/RobotoMono-SemiBold.ttf',
            (string) substr((string) $this->number, -1)
        );
        imagedestroy($barcode);
        imagejpeg($canvas, $filename . ".jpg", 100);
    }
}

class_alias("\Sglms\Gs1Gtin\Gtin12", "UPCA");
