<?php

namespace MyArtJaub\Tests\Unit\Webtrees\Common\GeoDispersion\GeoAnalysis;

use Fisharebest\Webtrees\Place;
use Fisharebest\Webtrees\TestCase;
use Fisharebest\Webtrees\Tree;
use MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisPlace;

/**
 * Class GeoAnalysisPlaceTest.
 *
 * @covers \MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisPlace
 */
class GeoAnalysisPlaceTest extends TestCase
{
    protected GeoAnalysisPlace $geoanalysis_place;
    protected int $depth;
    protected bool $strict_depth;

    /** @var Tree&\PHPUnit\Framework\MockObject\MockObject $tree */
    protected Tree $tree;

    /** @var Place&\PHPUnit\Framework\MockObject\MockObject $place */
    protected Place $place;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->tree = $this->createMock(Tree::class);
        $this->place = $this->createMock(Place::class);
        $this->depth = 42;
        $this->strict_depth = true;
        $this->geoanalysis_place = new GeoAnalysisPlace($this->tree, $this->place, $this->depth, $this->strict_depth);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->geoanalysis_place);
        unset($this->tree);
        unset($this->place);
        unset($this->depth);
        unset($this->strict_depth);
    }

    /**
     * Data provider for GeoAnalysisPlace tests
     * @return array<array<mixed>>
     */
    public static function placeData(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            ['', 2, false, '', false, false, true],
            ['place1', 2, false, 'place1', true, false, false],
            ['place1', 2, true, '##INVALID##', true, true, true],
            ['place1, place2', 2, true, 'place1, place2', true, false, false],
            ['place1, place2', 2, false, 'place1, place2', true, false, false],
            ['place1, place2, place3', 2, false, 'place2, place3', true, false, false],
            ['place1, place2, place3', 2, true, 'place2, place3', true, false, false],
        ];
        // phpcs:enable
    }

    public function testNullPlace(): void
    {
        $geoanalysis_place = new GeoAnalysisPlace($this->tree, null, 2);
        self::assertSame('', $geoanalysis_place->key());
        self::assertSame('', $geoanalysis_place->place()->gedcomName());
        self::assertFalse($geoanalysis_place->isKnown());
        self::assertTrue($geoanalysis_place->isUnknown());
        self::assertFalse($geoanalysis_place->isInvalid());
        self::assertTrue($geoanalysis_place->isExcluded());
    }

    /**
     *
     * @dataProvider placeData
     */
    public function testGeoAnalysisPlace(
        string $gedcom_name,
        int $depth,
        bool $strict,
        string $place_key,
        bool $is_known,
        bool $is_invalid,
        bool $is_excluded
    ): void {
        $place = new Place($gedcom_name, $this->tree);
        $geoanalysis_place = new GeoAnalysisPlace($this->tree, $place, $depth, $strict);

        self::assertSame($place_key, $geoanalysis_place->key());
        self::assertSame($place_key, $geoanalysis_place->place()->gedcomName());
        self::assertSame($is_known, $geoanalysis_place->isKnown());
        self::assertSame(!$is_known, $geoanalysis_place->isUnknown());
        self::assertSame($is_invalid, $geoanalysis_place->isInvalid());
        self::assertSame($is_excluded, $geoanalysis_place->isExcluded());
    }

    public function testExclude(): void
    {
        $place = new Place('place1, place2', $this->tree);
        $geoanalysis_place = new GeoAnalysisPlace($this->tree, $place, 2, false);

        self::assertFalse($geoanalysis_place->isExcluded());

        $geoanalysis_place->exclude();
        self::assertTrue($geoanalysis_place->isExcluded());

        $geoanalysis_place->include();
        self::assertFalse($geoanalysis_place->isExcluded());
    }
}
