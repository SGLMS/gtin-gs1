<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Sglms\Gs1Gtin\Gs1;

class Gs1Test extends TestCase
{
    private Gs1 $gs1;

    protected function setUp(): void
    {
        parent::setUp();
        // Adjust constructor arguments if needed
        $this->gs1 = new Gs1([
            '01' => '10012345678902',
            '21' => '9876',
        ]);
    }

    public function test_it_can_be_cast_to_string(): void
    {
        $this->assertIsString((string) $this->gs1);
    }

    public function test_get_returns_expected_value(): void
    {
        // Assuming get() retrieves a property by key
        $this->assertEquals('(01)10012345678902', $this->gs1->get(['01']));
    }

    public function test_barcode_returns_image_data(): void
    {
        $result = $this->gs1->barcode(['01']);
        $this->assertNotEmpty($result);
        $this->assertIsString($result); // Might be binary string or SVG/XML
    }

    public function test_save_barcode_creates_file(): void
    {
        $file =  './test_barcode';

        $this->gs1->saveBarcode($file);

        $this->assertFileExists($file.'.jpg');
        $this->assertGreaterThan(0, filesize($file.'.jpg'));

        unlink($file.'.jpg'); // cleanup
    }
}
