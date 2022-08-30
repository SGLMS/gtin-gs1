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

namespace Sglms\Gs1Gtin;

/**
 * GS1 Codes Enumerate
 * 
 * @category Library
 * @package  GS1GTINn
 * @author   Jaime C. Rubin-de-Celis <james@sglms.com>
 * @license  MIT (https://sglms.com/license)
 * @link     https://sglms.com
 **/
enum Gs1Code: string
{
    case GTIN           = '01';
    case Content        = '02';
    case BatchNumber    = '10';
    case ProductionDate = '11';
    case ExpirationDate = '17';
    case SerialNumber   = '21';
    case Units          = '37';
    case NetWeight      = '3102';
    case GrossWeight    = '3302';
}
