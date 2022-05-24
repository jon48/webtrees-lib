<?php

namespace MyArtJaub\Tests\Unit\Webtrees\Common\GeoDispersion\GeoAnalysis;

use Fisharebest\Webtrees\Place;
use Fisharebest\Webtrees\TestCase;
use Fisharebest\Webtrees\Tree;
use MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisPlace;
use MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisResults;

/**
 * Class GeoAnalysisResultsTest.
 *
 * @covers \MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisResults
 */
class GeoAnalysisResultsTest extends TestCase
{
    protected GeoAnalysisResults $geoanalysis_results;
    protected Tree $tree;
    protected GeoAnalysisPlace $place;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->tree = self::createMock(Tree::class);
        $this->place = new GeoAnalysisPlace($this->tree, new Place('place1', $this->tree), 2);
        $this->geoanalysis_results = new GeoAnalysisResults();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->geoanalysis_results);
        unset($this->tree);
    }

    public function testGlobal(): void
    {
        self::assertSame(0, $this->geoanalysis_results->global()->countKnown());
    }

    public function testDetailed(): void
    {
        self::assertCount(0, $this->geoanalysis_results->detailed());
    }

    public function testSortedDetailed(): void
    {
        $this->geoanalysis_results->addPlaceInCategory('test_z', 3, $this->place);
        $this->geoanalysis_results->addPlaceInCategory('test_a', 3, $this->place);

        self::assertCount(2, $this->geoanalysis_results->detailed());
        self::assertSame('test_z', $this->geoanalysis_results->detailed()->first()->description());

        self::assertCount(2, $this->geoanalysis_results->sortedDetailed());
        self::assertSame('test_a', $this->geoanalysis_results->sortedDetailed()->first()->description());
    }

    public function testAddPlace(): void
    {
        $this->geoanalysis_results->addPlace($this->place);
        self::assertSame(1, $this->geoanalysis_results->global()->countKnown());
        self::assertSame($this->place, $this->geoanalysis_results->global()->knownPlaces()->first()->place());
    }

    public function testAddCategory(): void
    {
        $this->geoanalysis_results->addCategory('test', 3);
        self::assertCount(1, $this->geoanalysis_results->detailed());
    }

    public function testAddPlaceInCreatedCategory(): void
    {
        $this->geoanalysis_results->addPlaceInCreatedCategory('test', $this->place);
        self::assertCount(0, $this->geoanalysis_results->detailed());

        $this->geoanalysis_results->addCategory('test', 3);
        $this->geoanalysis_results->addPlaceInCreatedCategory('test', $this->place);
        self::assertCount(1, $this->geoanalysis_results->detailed());
        self::assertSame(1, $this->geoanalysis_results->detailed()->get('test')->countKnown());
    }

    public function testAddPlaceInCategory(): void
    {
        $this->geoanalysis_results->addPlaceInCategory('test', 3, $this->place);
        self::assertCount(1, $this->geoanalysis_results->detailed());
        self::assertSame(1, $this->geoanalysis_results->detailed()->get('test')->countKnown());
    }
}
