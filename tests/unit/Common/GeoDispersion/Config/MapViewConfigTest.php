<?php

namespace MyArtJaub\Tests\Unit\Webtrees\Common\GeoDispersion\Config;

use Fisharebest\Webtrees\TestCase;
use MyArtJaub\Webtrees\Common\GeoDispersion\Config\MapViewConfig;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperConfigInterface;

/**
 * Class MapViewConfigTest.
 *
 * @covers \MyArtJaub\Webtrees\Common\GeoDispersion\Config\MapViewConfig
 */
class MapViewConfigTest extends TestCase
{
    protected MapViewConfig $map_view_config;
    protected string $map_mapping_property;

    /** @var PlaceMapperConfigInterface&\PHPUnit\Framework\MockObject\MockObject $mapper_config */
    protected PlaceMapperConfigInterface $mapper_config;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->map_mapping_property = 'test_mapping';
        $this->mapper_config = $this->createMock(PlaceMapperConfigInterface::class);
        $this->map_view_config = new MapViewConfig($this->map_mapping_property, $this->mapper_config);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->map_view_config);
        unset($this->map_mapping_property);
        unset($this->mapper_config);
    }

    public function testMapMappingProperty(): void
    {
        self::assertSame($this->map_mapping_property, $this->map_view_config->mapMappingProperty());
    }

    public function testMapperConfig(): void
    {
        self::assertSame($this->mapper_config, $this->map_view_config->mapperConfig());
    }

    public function testWith(): void
    {
        $map_view_config_with = $this->map_view_config->with('replace_mapping', $this->mapper_config);
        self::assertInstanceOf(get_class($this->map_view_config), $map_view_config_with);
        self::assertNotSame($this->map_view_config, $map_view_config_with);
        self::assertSame('replace_mapping', $map_view_config_with->mapMappingProperty());
        self::assertSame($this->mapper_config, $map_view_config_with->mapperConfig());
    }
}
