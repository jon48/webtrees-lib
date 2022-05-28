<?php

namespace MyArtJaub\Tests\Unit\Webtrees\Common\GeoDispersion\Config;

use Fisharebest\Webtrees\TestCase;
use MyArtJaub\Webtrees\Common\GeoDispersion\Config\MapColorsConfig;
use Spatie\Color\Color;
use Spatie\Color\Hex;

/**
 * Class MapColorsConfigTest.
 *
 * @covers \MyArtJaub\Webtrees\Common\GeoDispersion\Config\MapColorsConfig
 */
class MapColorsConfigTest extends TestCase
{
    protected MapColorsConfig $mapColorsConfig;

    /** @var Color&\PHPUnit\Framework\MockObject\MockObject $default */
    protected Color $default;

    /** @var Color&\PHPUnit\Framework\MockObject\MockObject $stroke */
    protected Color $stroke;

    /** @var Color&\PHPUnit\Framework\MockObject\MockObject $max_value */
    protected Color $max_value;

    /** @var Color&\PHPUnit\Framework\MockObject\MockObject $hover */
    protected Color $hover;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->default = $this->createMock(Color::class);
        $this->default->method('toHex')->willReturn(Hex::fromString('#111111'));
        $this->stroke = $this->createMock(Color::class);
        $this->stroke->method('toHex')->willReturn(Hex::fromString('#222222'));
        $this->max_value = $this->createMock(Color::class);
        $this->max_value->method('toHex')->willReturn(Hex::fromString('#333333'));
        $this->hover = $this->createMock(Color::class);
        $this->hover->method('toHex')->willReturn(Hex::fromString('#444444'));
        $this->mapColorsConfig = new MapColorsConfig($this->default, $this->stroke, $this->max_value, $this->hover);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->mapColorsConfig);
        unset($this->default);
        unset($this->stroke);
        unset($this->max_value);
        unset($this->hover);
    }

    public function testColors(): void
    {
        self::assertSame($this->default, $this->mapColorsConfig->defaultColor());
        self::assertSame($this->stroke, $this->mapColorsConfig->strokeColor());
        self::assertSame($this->max_value, $this->mapColorsConfig->maxValueColor());
        self::assertSame($this->hover, $this->mapColorsConfig->hoverColor());
        self::assertSame('#ffffff', (string) $this->mapColorsConfig->minValueColor()->toHex());
    }

    public function testJsonSerialize(): void
    {
        self::assertSame([
            'default'   => '#111111',
            'stroke'    => '#222222',
            'maxvalue'  => '#333333',
            'hover'     => '#444444',
        ], $this->mapColorsConfig->jsonSerialize());
    }
}
