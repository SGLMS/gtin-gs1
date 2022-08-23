<?php

declare(strict_types=1);

namespace Sglms\Gtin;

class Gtin
{
    protected int $baseNumber;
    public int    $checkDigit;

    public function __construct(?int $number)
    {
        if (strlen((string) $number) < 14) {
            $this->baseNumber = $number;
            $this->checkDigit = $this->getCheckDigit();
        } else {
            $strArray   = str_split((string) $number);
            $this->checkDigit   = (int) array_pop($strArray);
            $this->baseNumber   = (int) substr((string) $number, 0, strlen((string) $number) - 1);
            if($this->checkDigit !== $this->getCheckDigit()) {
                throw new \ErrorException(_("This appers to be a GTIN-14 number, but Check Digit does not match standards"), 1000, 1);
            }
        }
    }

    public function getCheckDigit(): int
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

    public static function create(int $number)
    {
        $gtin             = new self($number);
        $gtin->checkDigit = $gtin->getCheckDigit();
        return $gtin;
    }

    public function __toString()
    {
        return $this->baseNumber . $this->getCheckDigit();
    }
}
