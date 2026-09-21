<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Sglms\Gs1Gtin\Gs1;

class Gs1Test extends TestCase
{
    public function test_it_can_be_constructed_from_data_array(): void
    {
        $gs1 = new Gs1([
            '01' => '10012345678902',
            '21' => 'ABC123',
        ]);

        $this->assertSame('10012345678902', $gs1->gtin);
        $this->assertSame('ABC123', $gs1->serial);
        $this->assertSame('(01)10012345678902(21)ABC123', (string) $gs1);
    }

    public function test_it_can_parse_a_full_gs1_string(): void
    {
        $gs1 = Gs1::parse('(01)10012345678902(10)ABC123(3201)000500(3302)000700(17)250630(21)SN123456(37)10(11)230101');

        $this->assertSame('10012345678902', $gs1->gtin);
        $this->assertSame('ABC123', $gs1->batch);
        $this->assertSame('SN123456', $gs1->serial);
        $this->assertSame('230101', $gs1->productionDate);
        $this->assertSame('250630', $gs1->expirationDate);
        $this->assertSame(10, $gs1->pieces);
    }

    public function test_get_returns_expected_value(): void
    {
        $gs1 = new Gs1(['01' => '10012345678902']);

        $this->assertSame('(01)10012345678902', $gs1->get(['01']));
    }

    public function test_barcode_returns_svg_string(): void
    {
        $gs1 = new Gs1([
            '01' => '10012345678902',
            '21' => 'ABC123',
        ]);

        $result = $gs1->barcode(['01', '21']);

        $this->assertIsString($result);
        $this->assertStringContainsString('<svg', $result);
    }

    public function test_save_barcode_creates_file(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'gs1_test_');
        unlink($file);

        $gs1 = new Gs1([
            '01' => '10012345678902',
            '21' => 'ABC123',
        ]);

        $gs1->saveBarcode($file);

        $this->assertFileExists($file.'.jpg');
        $this->assertGreaterThan(0, filesize($file.'.jpg'));

        unlink($file.'.jpg');
    }
}
