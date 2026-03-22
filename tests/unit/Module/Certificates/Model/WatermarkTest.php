<?php

namespace MyArtJaub\Tests\Unit\Webtrees\Module\Certificates\Model;

use Fisharebest\Webtrees\TestCase;
use MyArtJaub\Webtrees\Module\Certificates\Model\Watermark;

/**
 * Class WatermarkTest.
 *
 * @covers \MyArtJaub\Webtrees\Module\Certificates\Model\Watermark
 */
class WatermarkTest extends TestCase
{
    protected Watermark $watermark;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->watermark = new Watermark('WatermarkTest', '#ff00f0', 42);
    }

    #[\Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->watermark);
    }

    public function testProperties(): void
    {
        self::assertSame('WatermarkTest', $this->watermark->text());
        self::assertSame('#ff00f0', $this->watermark->color());
        self::assertSame(42, $this->watermark->size());
    }

    public function testTextLengthEstimate(): void
    {
        self::assertSame(13 * 22, $this->watermark->textLengthEstimate());
    }

    public function testAdjustSize(): void
    {
        $this->watermark->adjustSize(100);
        self::assertSame(10, $this->watermark->size());

        $this->watermark->adjustSize(10);
        self::assertSame(2, $this->watermark->size());
    }
}
