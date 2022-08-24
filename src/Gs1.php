<?php

declare( strict_types = 1 );

namespace Sglms\Gtin;

class Gs1
{
    protected string $gs1;
    public int       $gtin;
    public int       $units;
    public int|float $netWeight;
    public int|float $grossWeight;

    public function __construct(?string $gs1)
    {
        $this->gs1 = $gs1;
        $gs1Array = preg_split("/(\([0-9]*\))/", $gs1, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        foreach ($gs1Array as $index => $item) {
            if (0 == $index % 2) {
                $key    = preg_replace("/[\(\)]/", "", $item);
                $code   = Gs1Code::tryFrom($key);
                if($code) {
                    $array [$key]   = $gs1Array[$index + 1];
                    switch($code) {
                        case Gs1Code::GTIN :
                        case Gs1Code::ITF :
                            $this->gtin = (int) $gs1Array[$index + 1]; break;
                        case Gs1Code::NetWeight :
                            $this->netWeight = (int) $gs1Array[$index + 1] / 100; break;
                        case Gs1Code::GrossWeight :
                            $this->grossWeight = (int) $gs1Array[$index + 1] / 100; break;
                        case Gs1Code::Units :
                            $this->units = (int) $gs1Array[$index + 1]; break;
                    }
                }
            }
        }
    }

    public function __toString()
    {
        return $this->gs1;
    }

}
