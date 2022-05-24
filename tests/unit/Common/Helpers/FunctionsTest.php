<?php

namespace MyArtJaub\Tests\Unit\Webtrees\Common\Helpers;

use Fisharebest\Webtrees\TestCase;
use MyArtJaub\Webtrees\Contracts\Hooks\HookInterface;
use MyArtJaub\Webtrees\Contracts\Hooks\HookServiceInterface;
use MyArtJaub\Webtrees\Contracts\Hooks\HookCollectorInterface;

/**
 * Class FunctionsTest.
 */
class FunctionsTest extends TestCase
{
    /**
     * @covers ::hook
     */
    public function testInvalidHook(): void
    {
        $hook_interface = self::createMock(HookInterface::class);
        self::assertSame('default', hook(get_class($hook_interface), fn(): string => 'test', 'default'));
    }

    /**
     * @covers ::hook
     */
    public function testHook(): void
    {
        $hook_interface = self::createMock(HookInterface::class);
        $hook_class = get_class($hook_interface);
        app()->instance($hook_class, $hook_interface);

        $hook_collector = self::createMock(HookCollectorInterface::class);

        $hook_service = self::createMock(HookServiceInterface::class);
        $hook_service->method('use')->with(self::equalTo($hook_class))->willReturn($hook_collector);
        app()->instance(HookServiceInterface::class, $hook_service);

        self::assertSame('test', hook($hook_class, fn(): string => 'test', 'default'));
    }

    /**
     * @covers ::columnIndex
     */
    public function testColumnIndex(): void
    {
        $new_column_indexes = collect([5, 12]);

        self::assertSame(3, columnIndex(3, $new_column_indexes));
        self::assertSame(6, columnIndex(5, $new_column_indexes));
        self::assertSame(8, columnIndex(7, $new_column_indexes));
        self::assertSame(14, columnIndex(12, $new_column_indexes));
        self::assertSame(17, columnIndex(15, $new_column_indexes));
    }
}
