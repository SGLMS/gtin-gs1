<?php

namespace Sglms\Gtin;

enum Gs1Code: string
{
    case GTIN           = '01';
    case ITF            = '02';
    case NetWeight      = '3102';
    case GrossWeight    = '3302';
    case Units          = '37';
    case ProductionDate = '11';
    case ExpirationDate = '17';
    case BatchNumber    = '10';
}
