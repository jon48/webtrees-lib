<?php

namespace MyArtJaub\Tests\Unit\Webtrees\Common\GeoDispersion\Config;

use Fisharebest\Webtrees\TestCase;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Module\ModuleInterface;
use MyArtJaub\Webtrees\Common\GeoDispersion\Config\NullPlaceMapperConfig;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class NullPlaceMapperConfigTest.
 *
 * @covers \MyArtJaub\Webtrees\Common\GeoDispersion\Config\NullPlaceMapperConfig
 */
class NullPlaceMapperConfigTest extends TestCase
{
    protected NullPlaceMapperConfig $null_place_mapper_config;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->null_place_mapper_config = new NullPlaceMapperConfig();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->null_place_mapper_config);
    }

    public function testGet(): void
    {
        self::assertSame('bar', $this->null_place_mapper_config->get('foo', 'bar'));
    }

    public function testHas(): void
    {
        self::assertFalse($this->null_place_mapper_config->has('foo'));
    }

    public function testJsonDeserialize(): void
    {
        self::assertSame($this->null_place_mapper_config, $this->null_place_mapper_config->jsonDeserialize([]));
    }

    public function testJsonSerialize(): void
    {
        self::assertSame([], $this->null_place_mapper_config->jsonSerialize());
    }

    public function testConfigContent(): void
    {
        $module = self::createMock(ModuleInterface::class);
        $tree = self::createMock(Tree::class);
        self::assertSame('', $this->null_place_mapper_config->configContent($module, $tree));
    }

    public function testWithConfigUpdate(): void
    {
        $request = self::createMock(ServerRequestInterface::class);
        self::assertSame($this->null_place_mapper_config, $this->null_place_mapper_config->withConfigUpdate($request));
    }
}
