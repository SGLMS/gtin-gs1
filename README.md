# GS1 / GTIN

Simple classes to handle GS1-128 / GTIN-14 numbers and barcodes.

We use these for our own projects, but you are welcome to use them (or contribute here).

## Usage

### GTIN

Generate GTIN-14 (prefix + company number + item number + check digit):

```php
use Sglms\Gs1Gtin\Gtin;

$gtin = Gtin::create(45678);    // Item Reference only!
// GTIN: 10000000456781

$gtin = Gtin::create(45678, '0123');    // Item Reference + Client Prefix
// GTIN: 10000123456781
```

```php
// Display barcode
echo "<img src='" . $gtin->getBarcodeSource() . "' />";
```

![barcode](resources/gtin.png "Generated barcode")

```php
// Save barcode
echo $gtin->saveBarcode('barcode');
echo "<img src='barcode.jpg' />";
```

![barcode](resources/gtin.jpg "Generated barcode")

### GS1

```php
use Sglms\Gs1Gtin\Gs1;

$gs1 = new Gs1('(01)1234(3102)123456(3302)134567(37)20(11)220801(17)221231');
// object(Sglms\Gtin\Gs1)[3]
// protected string 'gs1' => string '(01)1234(3102)123456(3302)134567(37)20(11)220801' (length=48)
// public int 'gtin' => int 10000000012345
// public int 'units' => int 20
// public int|float 'netWeight' => float 1234.56
// public int|float 'grossWeight' => float 1345.67
// ...

echo "<img src='" . $gs1->getBarcodeSource() . "' />";
```

GS1-128: (01)1234(3102)123456(3302)134567(37)20(11)220801(17)221231

![barcode](resources/gs1.png "Generated barcode")

```php
$gs1->saveBarcode('gs1');
echo "<img src='gs1.jpg' />";
```

![barcode](resources/gs1.jpg "Generated barcode")

GS1 Identifiers can be found [here](https://www.databar-barcode.info/application-identifiers/). We use only a few, they are enumerated in `src/Gs1Codes.php`, you can add your own as needed.

```php
enum Gs1Code: string
{
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

