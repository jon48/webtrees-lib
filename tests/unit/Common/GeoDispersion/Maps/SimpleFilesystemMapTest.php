<?php

namespace MyArtJaub\Tests\Unit\Webtrees\Common\GeoDispersion\Maps;

use Fisharebest\Webtrees\TestCase;
use League\Flysystem\FilesystemReader;
use MyArtJaub\Webtrees\Common\GeoDispersion\Maps\SimpleFilesystemMap;

/**
 * Class SimpleFilesystemMapTest.
 *
 * @covers \MyArtJaub\Webtrees\Common\GeoDispersion\Maps\SimpleFilesystemMap
 */
class SimpleFilesystemMapTest extends TestCase
{
    protected SimpleFilesystemMap $simple_filesystem_map;

    protected string $id;
    protected string $title;
    protected string $path;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->id = '42';
        $this->title = 'Test Map';
        $this->path = '/path/to/map';

        $feature = json_encode([
            'type' => 'FeatureCollection',
            'features' => [[
                'type' => 'Feature',
                'geometry' => ['type' => 'Point', 'coordinates' => [3.5, 44.5]],
                'properties' => ['name' => 'Mende']
            ]]
        ]);

        $filesystem = $this->createMock(FilesystemReader::class);
        $filesystem->method('read')->with(self::equalTo($this->path))->willReturn($feature);
        $this->simple_filesystem_map = new SimpleFilesystemMap($this->id, $this->title, $filesystem, $this->path);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->simple_filesystem_map);
        unset($this->id);
        unset($this->title);
        unset($this->path);
    }

    public function testProperties(): void
    {
        self::assertSame($this->id, $this->simple_filesystem_map->id());
        self::assertSame($this->title, $this->simple_filesystem_map->title());
    }

    public function testFeatures(): void
    {
        self::assertCount(1, $this->simple_filesystem_map->features());
    }

    public function testInvalidFeatures(): void
    {
        $filesystem = $this->createMock(FilesystemReader::class);
        $filesystem->method('read')->with(self::equalTo($this->path))->willReturn('{invalid: true}');
        $invalid_map = new SimpleFilesystemMap($this->id, $this->title, $filesystem, $this->path);

        self::assertCount(0, $invalid_map->features());
    }
}
