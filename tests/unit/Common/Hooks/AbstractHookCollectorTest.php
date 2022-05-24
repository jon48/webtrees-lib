<?php

namespace MyArtJaub\Tests\Unit\Webtrees\Common\Hooks;

use Fisharebest\Webtrees\TestCase;
use Fisharebest\Webtrees\Module\ModuleInterface;
use MyArtJaub\Webtrees\Common\Hooks\AbstractHookCollector;
use MyArtJaub\Webtrees\Contracts\Hooks\HookInterface;

/**
 * Class AbstractHookCollectorTest.
 *
 * @covers \MyArtJaub\Webtrees\Common\Hooks\AbstractHookCollector
 */
class AbstractHookCollectorTest extends TestCase
{
    /** @var AbstractHookCollector<HookInterface> $abstract_hook_collector */
    protected $abstract_hook_collector;

    /** @var ModuleInterface&\PHPUnit\Framework\MockObject\MockObject $module */
    protected $module;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->module = $this->createMock(ModuleInterface::class);
        $this->module->method('name')->willReturn('testModule');
        $this->abstract_hook_collector = $this->getMockBuilder(AbstractHookCollector::class)
            ->setConstructorArgs([$this->module])
            ->getMockForAbstractClass();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->abstract_hook_collector);
        unset($this->module);
    }

    public function testModule(): void
    {
        self::assertSame($this->module, $this->abstract_hook_collector->module());
    }

    public function testName(): void
    {
        self::assertStringStartsWith('testModule-mock_abstracthook_', $this->abstract_hook_collector->name());
    }

    public function testRegister(): void
    {
        $hook_1 = self::createMock(HookInterface::class);
        $hook_2 = self::createMock(HookInterface::class);
        $hook_3 = self::createMock(HookInterface::class);

        $this->abstract_hook_collector->register($hook_1, 42);
        $this->abstract_hook_collector->register($hook_2, 42);
        $this->abstract_hook_collector->register($hook_3, 17);

        $registered_hooks = $this->abstract_hook_collector->hooks();

        self::assertCount(2, $registered_hooks);
        self::assertSame($hook_3, $registered_hooks->get(0));
        self::assertSame($hook_1, $registered_hooks->get(1));
    }
}
