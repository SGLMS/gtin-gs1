# GS1 / GTIN

GS1-128 / GTIN (UPCA; EAN8; ITF14) creator and parser.  We use these for our projects, but you are welcome to use them.

In this version we have included Laravel Service Providers.

## Installation

```bash
composer require sglms/gtin-gs1
```

## Usage

### GTIN (GTIN-14)

Generate GTIN-14 (prefix + company number + item reference + check digit):

```php
use Sglms\Gs1Gtin\Gtin;

$gtin = Gtin::create(9876543210);
// GTIN: 10098765432105

$gtin = Gtin::create(
    itemNumber: 98765,
    companyPrefix: '0123'
);
// GTIN: 10123000987659

$gtin = Gtin::create(
    itemNumber: 987654,
    companyPrefix: '123',
    type: 'GTIN-14',
    packagingLevel: 2
);
// GTIN: 21230009876545

```
#### Display (on-the-fly)
```php
// Display barcode
echo "<img src='" . $gtin->getBarcodeSource() . "' />";
```

![barcode](resources/gtin.png "Generated barcode")

#### Save (with numbers) and display
```php
// Save barcode
echo $gtin->saveBarcode('path/barcode');
echo "<img src='path/barcode.jpg' />";
```

![barcode](resources/gtin.jpg "Generated barcode")

For now, only JPG images are supported, but will add other standards as needed (by our rojects).

#### Validate

```php
Gtin::validate(11230000456781); # TRUE
```



### GTIN-12 (UPC-A)

Generate GTIN-12 (company number + item reference + check digit):

```php
use Sglms\Gs1Gtin\Gtin12;

$gtin = Gtin::create(
    itemNumber: 1,
    companyPrefix: '614141',
    type: 'GTIN-12'
);
// GTIN: 614141000012
```

```php
// Save barcode
echo $gtin->saveBarcode('path/barcode');
echo "<img src='path/barcode.jpg' />";
```

![barcode](resources/gtin12.jpg "Generated barcode")

### GTIN-8 (EAN-8)

Generate GTIN-8 (company number + item reference + check digit):

```php
use Sglms\Gs1Gtin\Gtin8;


$gtin = Gtin::create(
    itemNumber: 0,
    companyPrefix: '506789',
    type: 'GTIN-8'
);
// GTIN-8/EAN8: 50678907
```

```php
// Save barcode
echo $gtin->saveBarcode('path/barcode');
echo "<img src='path/barcode.jpg' />";
```

![barcode](resources/gtin8.jpg "Generated barcode")

### GS1

```php
use Sglms\Gs1Gtin\Gs1;

$gs1 = Gs1::parse('(01)00012345678905(10)ABC123(3102)000500(3302)000700(17)250630(21)SN123456(37)10(11)230101');

// Sglms\Gs1Gtin\Gs1 {
//   +gs1: "(01)00012345678905(10)ABC123(3102)000500(3302)000700(17)250630(21)SN123456(37)10(11)230101"
//   +sscc: null
//   +gtin: "00012345678905"
//   +content: null
//   +netWeight: 5
//   +grossWeight: 7
//   +batch: "ABC123"
//   +serial: "SN123456"
//   +productionDate: "230101"
//   +expirationDate: "250630"
//   +pieces: 10
// }

$gs1 = Gs1::create(gtin: "00012345678904", batch: "ABC123");

// Sglms\Gs1Gtin\Gs1 {
//   +data: array:2 [▶]
//   +gs1: "(01)00012345678905(10)ABC123"
//   +sscc: null
//   +gtin: "00012345678905"
//   +content: ""
//   +netWeight: null
//   +grossWeight: null
//   +batch: "ABC123"
//   +serial: null
//   +productionDate: null
//   +expirationDate: null
//   +pieces: null
// }

echo "<img src='" . $gs1->getBarcodeSource() . "' />";
```

![barcode](resources/gs1.png "Generated barcode")

**Note**: GS1 works only with GTIN-14; per standards recommendations.

```php
$gs1->saveBarcode('path/gs1');
echo "<img src='path/gs1.jpg' />";
```

![barcode](resources/gs1.jpg "Generated barcode")


### Standards

GS1 Identifiers can be found [here](https://www.databar-barcode.info/application-identifiers/). We use only a few, they are enumerated in `src/Gs1Codes.php`, you can add your own as needed.

```php
enum Gs1Code: string
{
    case SSCC           = '00';
    case GTIN           = '01'; // n2+n14
    case Content        = '02'; // n2+n14
    case BatchNumber    = '10';
    case ProductionDate = '11'; // n2+n6
    case PaymentDate    = '12'; // n2+n6
    case BestBeforeDate = '15'; // n2+n6
    case ExpirationDate = '17'; // n2+n6
    case SerialNumber   = '21';
    case Units          = '37'; // n2+n..8
    case NetWeight      = '3102'; //n4+n6 (2 decimals)
    case GrossWeight    = '3302'; //n4+n6 (2 decimals)
}
```



# Credits
Bar code generator (images) : [picqer/php-barcode-generator](https://github.com/picqer/php-barcode-generator).