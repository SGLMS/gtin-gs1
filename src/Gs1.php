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
    public array   $data;
    public ?string $gs1;
    public ?string $sscc;           // 00
    public ?string $gtin;           // 01
    public ?string $content;        // 02
    public ?string $netWeight;      // 3102
    public ?string $grossWeight;    // 3302
    public ?string $batch;          // 10
    public ?string $serial;         // 21
    public ?string $productionDate; // 11
    public ?string $expirationDate; // 17
    public ?int    $pieces;         // 37

    /**
     * Constructor
     *
     * @param array $data GS1 Data
     *
     * @return void
     **/
    public function __construct(?array $data = [])
    {
        $this->data         = $data;
        $this->gtin         = (string) ($data['01'] ?? null);
        $this->content      = $data['02'] ?? null;
        $this->sscc         = $data['00'] ?? null;
        $this->expirationDate   = $data['17'] ?? null;
        $this->productionDate   = $data['11'] ?? null;
        $this->batch        = $data['10'] ?? null;
        $this->serial       = $data['21'] ?? null;
        $this->pieces       = $data['37'] ?? null;
        $this->netWeight    = isset($data['3102']) ? str_pad((string) $data['3102'], 6, "0", STR_PAD_LEFT) : null;
        $this->grossWeight  = isset($data['3302']) ? str_pad((string) $data['3302'], 6, "0", STR_PAD_LEFT) : null;
        if (isset($data['3201']) && $this->netWeight == null) {
            $this->netWeight = str_pad((string) round((float) $data ['3201'] * 10 * 0.45359237, 0), 6, "0", STR_PAD_LEFT);
            $this->data['3102'] = $this->netWeight;
        }
    }

    /**
     * Display GS1 String
     *
     * @return string
     **/
    public function __toString()
    {
        $s [] = $this->gtin ? '(01)' . $this->gtin : null;
        $s [] = $this->sscc ? '(00)' . $this->sscc : null;
        $s [] = $this->content ? '(02)' . $this->content : null;
        $s [] = $this->batch ? '(10)' . $this->batch : null;
        $s [] = $this->netWeight ? '(3102)' . str_pad((string) $this->netWeight, 6, "0", STR_PAD_LEFT) : null;
        $s [] = $this->grossWeight ? '(3302)' . str_pad((string) $this->grossWeight, 6, "0") : null;
        $s [] = $this->productionDate ? '(11)' . $this->productionDate : null;
        $s [] = $this->expirationDate ? '(17)' . $this->expirationDate : null;
        $s [] = $this->serial ? '(21)' . $this->serial : null;
        $s [] = $this->pieces ? '(37)' . $this->pieces : null;
        return implode($s);
    }

    /**
     * Create new GS1 instance, using codes as array keys.
     *
     * @param array $data GS1 Array ['Gs1Code' => value]
     *
     * @return Gs1
     */
    public static function createFromArray(
        $data = []
    ): Gs1 {
        return new self($data);
    }

    /**
     * Create new GS1 instance verbosely.
     *
     * @param integer $gtin
     * @param integer|null $sscc
     * @param string|null $content
     * @param string|null $netWeight
     * @param string|null $grossWeight
     * @param string|null $batch
     * @param string|null $serial
     * @param string|null $productionDate
     * @param string|null $expirationDate
     * @param string|null $pieces
     *
     * @return Gs1
     */
    public static function create(
        string $gtin,
        ?string $sscc = null,
        ?string $content = null,
        ?string $netWeight = null,
        ?string $grossWeight = null,
        ?string $batch = null,
        ?string $serial = null,
        ?string $productionDate = null,
        ?string $expirationDate = null,
        ?string $pieces = null,
        ?string $netWeightPounds = null,
    ): Gs1 {
        $content = !$content && !Gtin::validate($gtin) ? $gtin : null;
        $gtin    = Gtin::validate($gtin) ? $gtin : null;
        $gs1 = new self(array_filter([
            '00' => $sscc,
            '01' => $gtin,
            '02' => $content,
            '3102' => $netWeight ? str_pad((string) $netWeight, 6, "0", STR_PAD_LEFT) : null,
            '3201' => $netWeightPounds ? str_pad((string) $netWeightPounds, 6, "0", STR_PAD_LEFT) : null,
            '3302' => $grossWeight ? str_pad((string) $grossWeight, 6, "0", STR_PAD_LEFT) : null,
            '10' => $batch,
            '21' => $serial,
            '11' => $productionDate,
            '17' => $expirationDate,
            '37' =>  (int) $pieces,
        ]));
        $gs1->gs1 = (string) $gs1;
        return $gs1;
    }

    /**
     * Get GS1 filtered by codes.
     *
     * @param array|null $codes
     *
     * @return void
     */
    public function get(
        ?array $codes = ['01','21','17','3102']
    ) {
        $array = [];
        foreach (Gs1Code::filter($codes) as $code) {
            if (isset($this->data[$code->value])) {
                // dump($code, $this->data[$code->value]);
                $array [] = "({$code->value})" . $this->data[$code->value] ?? null;
            }
        }
        return implode($array);
    }

    /**
     * Parse GS1 String
     *
     * Recognizes both (01) and 01 formats.
     *
     * @param string $string
     *
     * @return void
     */
    public static function parse(
        string $string
    ) {
        $gs1      = new self();
        $gs1->gs1 = $string;
        $matches  = collect();
        preg_match("/^([\(]?00[\)]?)([0-9]{14,20})/", $string, $matches);
        if ($matches) {
            $gs1->sscc = $matches[2];
            $gs1->data['00'] = $gs1->sscc;
        }
        preg_match("/^([\(]?01[\)]?)([0-9]{14})/", $string, $matches);
        if ($matches) {
            $gtin = $matches[2];
            if (Gtin::validate($gtin)) {
                $gs1->gtin = $gtin;
                $gs1->data['01'] = $gs1->gtin;
            } else {
                $gs1->content = $gtin;
                $gs1->data['02'] = $gs1->content;
            }
            $string = preg_replace("/^([\(]?01[\)]?)([0-9]{14})/", '', $string);
        }
        preg_match("/^([\(]?02[\)]?)([0-9]{6,14})/", $string, $matches);
        if ($matches) {
            $gs1->content = $matches[2];
            $gs1->data['02'] = $gs1->content;
            $string = preg_replace("/^([\(]?02[\)]?)([0-9]{6,14})/", '', $string);
        }
        preg_match("/([\(]?3102[\)]?)([0-9]{6})/", $string, $weights);
        if ($weights) {
            $gs1->netWeight = str_pad((string) round((int) $weights[2], 0), 6, "0", STR_PAD_LEFT);
            $gs1->data['3102'] = $gs1->netWeight;
            $string = preg_replace("/([\(]?3102[\)]?)([0-9]{6})/", '', $string);
        }
        preg_match("/([\(]?3302[\)]?)([0-9]{6})/", $string, $weights);
        if ($weights) {
            $gs1->grossWeight = str_pad((string) round((int) $weights[2], 0), 6, "0", STR_PAD_LEFT);
            $gs1->data['3302'] = $gs1->grossWeight;
            $string = preg_replace("/([\(]?3302[\)]?)([0-9]{6})/", '', $string);
        }
        preg_match("/([\(]?3201[\)]?)([0-9]{6})/", $string, $weights);  // Net weight in pounds; 1 decimal
        if ($weights) {
            $gs1->netWeight = str_pad((string) round($weights[2] / 10 / 2.205 * 100, 0), 6, "0", STR_PAD_LEFT);
            $gs1->data['3102'] = $gs1->netWeight;
            $string = preg_replace("/([\(]?3201[\)]?)([0-9]{6})/", '', $string);
        }
        preg_match("/([\(]?11[\)]?)(\d{2}(?:0\d|1[0-2])(?:[0-2]\d|3[01]))/", $string, $matches);
        if ($matches) {
            $gs1->productionDate = $matches[2];
            $gs1->data['11'] = $gs1->productionDate;
            $string = preg_replace("/([\(]?11[\)]?)(\d{2}(?:0\d|1[0-2])(?:[0-2]\d|3[01]))/", '', $string);
        }
        preg_match("/([\(]?17[\)]?)(\d{2}(?:0\d|1[0-2])(?:[0-2]\d|3[01]))/", $string, $matches);
        if ($matches) {
            $gs1->expirationDate = $matches[2];
            $gs1->data['17'] = $gs1->expirationDate;
            $string = preg_replace("/([\(]?17[\)]?)(\d{2}(?:0\d|1[0-2])(?:[0-2]\d|3[01]))/", '', $string);
        }
        preg_match("/([\(]?21[\)]?)([0-9SN]{1,20})/", $string, $matches);
        if ($matches) {
            $gs1->serial = $matches[2];
            $gs1->data['21'] = $gs1->serial;
        }
        preg_match("/([\(]?37[\)]?)([0-9]{1,4})/", $string, $matches);
        if ($matches) {
            $gs1->pieces = (int) $matches[2];
            $gs1->data['37'] = $gs1->pieces;
        }
        preg_match("/([\(]?10[\)]?)([0-9A-Z]{1,20})/", $string, $matches);
        if ($matches) {
            $gs1->batch = $matches[2];
            $gs1->data['10'] = $gs1->batch;
        }
        return $gs1;
    }

    /**
     * Get Barcode (SVG).
     *
     * @param array|null   $codes       GS1 Codes to include [Default: 01,10,21,37,3102]
     * @param boolean|null $inline      Inline SVG
     * @param integer|null $width       Barcode Width
     * @param integer|null $height      Barcode Height
     * @param boolean|null $showNumbers Include numbers below barcode
     *
     * @return void
     */
    final public function barcode(
        ?array $codes = ['01','10','21','37','3102'],
        ?bool $inline = false,
        ?int $width = null,
        ?int $height = 50,
        ?bool $showNumbers = false
    ) {
        // var_dump($this);
        $barcode = (new TypeCode128())->getBarcode((string) $this->get($codes));
        $renderer = new SvgRenderer();
        $renderer->setBackgroundColor([255, 255, 255]);
        $renderer->setSvgType($inline ? $renderer::TYPE_SVG_INLINE : $renderer::TYPE_SVG_STANDALONE);
        $width = $width ?: $barcode->getWidth();
        $svgImage = $renderer->render($barcode, $width, $height);
        return  $showNumbers ?
            "<div class='text-center flex flex-col items-center'>
                <span class='text-center'>{$svgImage}</span>
                <span class='font-mono text-xs font-bold'>{$this->get($codes)}</span>
            </div>"
            : $svgImage;
    }

    /**
     * Save barcode image (JPG).
     *
     * @param string   $filename Separation or with of barcode
     * @param int|null $sep      Separation or with of barcode
     * @param int|null $height   Barcode Height
     *
     * @return string
     **/
    final public function saveBarcode(
        string $filename,
        ?array $codes = ['01','21','37','3102'],
        ?int $height = 50
    ): void {
        $barcode = (new TypeCode128())->getBarcode($this->get($codes));
        $renderer = new JpgRenderer();
        $renderer->setBackgroundColor([255, 255, 255]);
        $generator  = new BarcodeGeneratorJPG();
        \file_put_contents(
            $filename . ".jpg",
            $renderer->render(
                $barcode,
                $barcode->getWidth(),
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
            'fonts/RobotoMono-SemiBold.ttf',
            (string) $this->get($codes)
        );
        imagejpeg($canvas, $filename . ".jpg", 100);
    }
}
