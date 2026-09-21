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
final class Gtin extends GtinAbstract
{
    public static function create(
        int|string $itemNumber,
        ?string $companyPrefix = null,
        ?string $type = 'GTIN-14',
        ?int $packagingLevel = 1,
    ): self {
        return new self(
            itemNumber: $itemNumber,
            companyPrefix: $companyPrefix,
            type: $type,
            packagingLevel: $packagingLevel,
        );
    }
}

class_alias("\Sglms\Gs1Gtin\Gtin", 'ITF14');
