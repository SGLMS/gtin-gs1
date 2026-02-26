<?php

/**
 * SGLMS GS1 / GTIN
 *
 * PHP Version 8.1
 *
 * @category SGLMS_Library
 *
 * @author   Jaime C. Rubin-de-Celis <james@sglms.com>
 * @license  MIT (https://sglms.com/license)
 *
 * @link     https://sglms.com
 **/

declare(strict_types=1);

namespace Sglms\Gs1Gtin;

/**
 * Class: Gtin
 *
 * @category SGLMS_Library
 *
 * @author   Jaime C. Rubin-de-Celis <james@sglms.com>
 * @license  MIT (https://sglms.com/license)
 *
 * @link     https://sglms.com
 **/
class Gtin extends GtinAbstract {}

class_alias("\Sglms\Gs1Gtin\Gtin", 'ITF14');
