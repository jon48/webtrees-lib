<?php

namespace MyArtJaub\Tests\Unit\Webtrees\Common\GeoDispersion\GeoAnalysis;

use Fisharebest\Webtrees\Place;
use Fisharebest\Webtrees\TestCase;
use Fisharebest\Webtrees\Tree;
use Illuminate\Support\Collection;
use MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisPlace;
use MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisResult;
use MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisResultItem;

/**
 * Class GeoAnalysisResultTest.
 *
 * @covers \MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisResult
 */
class GeoAnalysisResultTest extends TestCase
{
    protected GeoAnalysisResult $geoanalysis_result;
    protected string $description;
    protected int $order;
    protected int $places_count;
    protected int $unknown;
    protected Tree $tree;

    protected GeoAnalysisPlace $place_1;
    protected GeoAnalysisPlace $place_2;
    protected GeoAnalysisPlace $place_3;
    protected GeoAnalysisPlace $place_4;

    /** @var Collection<array-key, GeoAnalysisResultItem> $result_items */
    protected Collection $result_items;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->description = 'test_description';
        $this->order = 17;
        $this->tree = self::createMock(Tree::class);
        $this->place_1 =  new GeoAnalysisPlace($this->tree, new Place('place1', $this->tree), 2);
        $this->place_2 =  new GeoAnalysisPlace($this->tree, new Place('place1, place2', $this->tree), 2);
        $this->place_3 =  new GeoAnalysisPlace($this->tree, new Place('place1, place3', $this->tree), 2);
        $this->place_4 =  new GeoAnalysisPlace($this->tree, new Place('place1, place4', $this->tree), 2);

        $this->result_items = collect([
            'place1' => new GeoAnalysisResultItem($this->place_1, 7),
            'place1, place3' => new GeoAnalysisResultItem($this->place_3, 3),
            'place1, place2' => new GeoAnalysisResultItem($this->place_2, 3),
        ]);
        $this->places_count = 7 + 3 + 3;

        $this->unknown = 42;

        $this->geoanalysis_result =
            new GeoAnalysisResult($this->description, $this->order, $this->result_items, $this->unknown);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->geoanalysis_result);
        unset($this->description);
        unset($this->order);
        unset($this->result_items);
        unset($this->unknown);
        unset($this->tree);
        unset($this->place_1);
        unset($this->place_2);
        unset($this->place_3);
        unset($this->place_4);
    }

    public function testDescription(): void
    {
        self::assertSame($this->description, $this->geoanalysis_result->description());
    }

    public function testOrder(): void
    {
        self::assertSame($this->order, $this->geoanalysis_result->order());
    }

    public function testAddPlace(): void
    {
        $this->geoanalysis_result->addPlace(new GeoAnalysisPlace($this->tree, null, 2));
        self::assertSame($this->places_count, $this->geoanalysis_result->countKnown());

        $this->geoanalysis_result->addPlace($this->place_4);
        self::assertSame($this->places_count + 1, $this->geoanalysis_result->countKnown());
    }

    public function testExclude(): void
    {
        $this->geoanalysis_result->exclude($this->place_4);
        self::assertSame(0, $this->geoanalysis_result->countExcluded());

        $this->geoanalysis_result->exclude($this->place_1);
        self::assertSame(7, $this->geoanalysis_result->countExcluded());
    }

    public function testAddUnknown(): void
    {
        $this->geoanalysis_result->addUnknown();
        self::assertSame($this->unknown + 1, $this->geoanalysis_result->countUnknown());
    }

    public function testCounts(): void
    {
        self::assertSame($this->places_count, $this->geoanalysis_result->countKnown());
        self::assertSame($this->places_count, $this->geoanalysis_result->countFound());
        self::assertSame(0, $this->geoanalysis_result->countExcluded());
        self::assertSame($this->unknown, $this->geoanalysis_result->countUnknown());
        self::assertSame(7, $this->geoanalysis_result->maxCount());
    }

    public function testKnownPlaces(): void
    {
        self::assertSameSize($this->result_items, $this->geoanalysis_result->knownPlaces());
        self::assertSameSize($this->result_items, $this->geoanalysis_result->knownPlaces(true));
    }

    public function testSortedKnownPlaces(): void
    {
        $sorted_places = $this->geoanalysis_result->sortedKnownPlaces();
        self::assertSameSize($this->result_items, $sorted_places);
        self::assertSame($this->place_1, $sorted_places->first()?->place());
        self::assertSame($this->place_3, $sorted_places->last()?->place());
    }

    public function testExcludedPlaces(): void
    {
        self::assertCount(0, $this->geoanalysis_result->excludedPlaces());
    }

    public function testCopy(): void
    {
        $geo_analysis_result_copy = $this->geoanalysis_result->copy();
        self::assertInstanceOf(get_class($this->geoanalysis_result), $geo_analysis_result_copy);
        self::assertNotSame($this->geoanalysis_result, $geo_analysis_result_copy);
        self::assertSame($this->geoanalysis_result->countKnown(), $geo_analysis_result_copy->countKnown());
    }

    public function testMerge(): void
    {
        $geo_analysis_result_copy = $this->geoanalysis_result->copy();
        $geo_analysis_result_copy->addPlace($this->place_4);
        $this->geoanalysis_result->merge($geo_analysis_result_copy);

        self::assertSame($this->places_count + 1, $this->geoanalysis_result->countKnown());
        self::assertSame($this->unknown, $this->geoanalysis_result->countUnknown());
    }
}
