<?php

namespace MyArtJaub\Tests\Unit\Webtrees\Common\GeoDispersion\GeoAnalysis;

use Fisharebest\Webtrees\TestCase;
use MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisPlace;
use MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisResultItem;

/**
 * Class GeoAnalysisResultItemTest.
 *
 * @covers \MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisResultItem
 */
class GeoAnalysisResultItemTest extends TestCase
{
    protected GeoAnalysisResultItem $geoanalysis_result_item;
    protected int $count;

    /** @var GeoAnalysisPlace&\PHPUnit\Framework\MockObject\MockObject $place */
    protected $place;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->place = $this->createMock(GeoAnalysisPlace::class);
        $this->place->method('key')->willReturn('place_1');
        $this->count = 42;
        $this->geoanalysis_result_item = new GeoAnalysisResultItem($this->place, $this->count);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->geoanalysis_result_item);
        unset($this->place);
        unset($this->count);
    }

    public function testKey(): void
    {
        self::assertSame($this->place->key(), $this->geoanalysis_result_item->key());
    }

    public function testPlace(): void
    {
        self::assertSame($this->place, $this->geoanalysis_result_item->place());
    }

    public function testCount(): void
    {
        self::assertSame($this->count, $this->geoanalysis_result_item->count());
    }

    public function testIncrement(): void
    {
        $this->geoanalysis_result_item->increment();
        self::assertSame($this->count + 1, $this->geoanalysis_result_item->count());
    }

    public function testClone(): void
    {
        $geoanalysis_result_item_clone = clone $this->geoanalysis_result_item;
        self::assertInstanceOf(get_class($geoanalysis_result_item_clone), $geoanalysis_result_item_clone);
        self::assertNotSame($this->geoanalysis_result_item, $geoanalysis_result_item_clone);
        self::assertSame($this->place->key(), $this->geoanalysis_result_item->key());
        self::assertSame($this->place, $this->geoanalysis_result_item->place());
        self::assertSame($this->count, $this->geoanalysis_result_item->count());
    }
}
