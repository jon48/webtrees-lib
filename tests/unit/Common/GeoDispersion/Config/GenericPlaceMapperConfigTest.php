<?php

namespace MyArtJaub\Tests\Unit\Webtrees\Common\GeoDispersion\Config;

use Fisharebest\Webtrees\TestCase;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Module\ModuleInterface;
use MyArtJaub\Webtrees\Common\GeoDispersion\Config\GenericPlaceMapperConfig;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class GenericPlaceMapperConfigTest.
 *
 * @covers \MyArtJaub\Webtrees\Common\GeoDispersion\Config\GenericPlaceMapperConfig
 */
class GenericPlaceMapperConfigTest extends TestCase
{
    protected GenericPlaceMapperConfig $generic_place_mapper_config;

    /** @var array<string, string> $mapper_config */
    protected $mapper_config;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper_config = ['foo' => 'bar'];
        $this->generic_place_mapper_config = new GenericPlaceMapperConfig();
        $this->generic_place_mapper_config->setConfig($this->mapper_config);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->generic_place_mapper_config);
    }

    public function testConfig(): void
    {
        self::assertSame($this->mapper_config, $this->generic_place_mapper_config->config());
    }

    public function testGet(): void
    {
        self::assertSame($this->mapper_config['foo'], $this->generic_place_mapper_config->get('foo'));
    }

    public function testHas(): void
    {
        self::assertTrue($this->generic_place_mapper_config->has('foo'));
        self::assertFalse($this->generic_place_mapper_config->has('baz'));
    }

    public function testJsonSerialize(): void
    {
        self::assertSame([
            'class'     =>  GenericPlaceMapperConfig::class,
            'config'    =>  $this->mapper_config
        ], $this->generic_place_mapper_config->jsonSerialize());
    }

    public function testJsonSerializeConfig(): void
    {
        self::assertSame($this->mapper_config, $this->generic_place_mapper_config->jsonSerializeConfig());
    }

    public function testJsonDeserialize(): void
    {
        $this->generic_place_mapper_config->jsonDeserialize(3);
        self::assertSame($this->mapper_config, $this->generic_place_mapper_config->config());

        $new_config = ['foo' => 'baz'];
        $this->generic_place_mapper_config->jsonDeserialize($new_config);
        self::assertSame($new_config, $this->generic_place_mapper_config->config());

        $new_config = ['foo' => 'bax'];
        $this->generic_place_mapper_config->jsonDeserialize(json_encode($new_config));
        self::assertSame($new_config, $this->generic_place_mapper_config->config());
    }

    public function testConfigContent(): void
    {
        $module = self::createMock(ModuleInterface::class);
        $tree = self::createMock(Tree::class);
        self::assertSame('', $this->generic_place_mapper_config->configContent($module, $tree));
    }

    public function testWithConfigUpdate(): void
    {
        $request = self::createMock(ServerRequestInterface::class);
        self::assertSame(
            $this->generic_place_mapper_config,
            $this->generic_place_mapper_config->withConfigUpdate($request)
        );
    }
}
