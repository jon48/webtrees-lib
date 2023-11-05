<?php

namespace MyArtJaub\Tests\Unit\Webtrees\Module\WelcomeBlock\Http\RequestHandlers;

use Fig\Http\Message\StatusCodeInterface;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Tests\Helpers\Webtrees\TestCase;
use MyArtJaub\Webtrees\Module\WelcomeBlock\WelcomeBlockModule;
use MyArtJaub\Webtrees\Module\WelcomeBlock\Http\RequestHandlers\MatomoStats;
use MyArtJaub\Webtrees\Module\WelcomeBlock\Services\MatomoStatsService;
use Exception;

/**
 * Class MatomoStatsTest.
 *
 * @covers \MyArtJaub\Webtrees\Module\WelcomeBlock\Http\RequestHandlers\MatomoStats
 */
class MatomoStatsTest extends TestCase
{
    protected static bool $uses_database = true;

    /** @var WelcomeBlockModule&\PHPUnit\Framework\MockObject\MockObject $welcome_block_module */
    protected WelcomeBlockModule $welcome_block_module;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->welcome_block_module = self::createPartialMock(WelcomeBlockModule::class, ['isMatomoEnabled']);
        $this->welcome_block_module->setName('mod-welcomeblock');
        $this->welcome_block_module->method('isMatomoEnabled')->willReturn(true);

        self::registerTestViewNamespace('mod-welcomeblock');
        self::useDefaultViewFor('::errors/unhandled-exception');
        self::useDefaultViewFor('mod-welcomeblock::matomo-stats');
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->welcome_block_module);
    }

    public function testNoModuleHandle(): void
    {
        $module_service = self::createMock(ModuleService::class);
        $module_service->method('findByInterface')->willReturn(collect());
        $matomo_service = self::createMock(MatomoStatsService::class);

        $request = self::createRequest();

        $actual_response = (new MatomoStats($module_service, $matomo_service))->handle($request);
        self::assertSame(StatusCodeInterface::STATUS_NOT_FOUND, $actual_response->getStatusCode());
    }

    public function testMatomoErrorHandle(): void
    {
        $module_service = self::createMock(ModuleService::class);
        $module_service->method('findByInterface')->willReturn(collect([$this->welcome_block_module]));

        $matomo_service = self::createMock(MatomoStatsService::class);

        $request = self::createRequest()->withAttribute('block_id', 42);
        $matomo_service->method('visitsToday')->willReturn(15);
        $matomo_service->method('visitsThisYear')->will(self::throwException(new Exception()));

        $actual_response = (new MatomoStats($module_service, $matomo_service))->handle($request);
        self::assertSame(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR, $actual_response->getStatusCode());
    }

    public function testSuccessfulHandle(): void
    {
        $module_service = self::createMock(ModuleService::class);
        $module_service->method('findByInterface')->willReturn(collect([$this->welcome_block_module]));

        $matomo_service = self::createMock(MatomoStatsService::class);
        $matomo_service->method('visitsToday')->willReturn(15);
        $matomo_service->method('visitsThisYear')->willReturn(3);

        $request = self::createRequest()->withAttribute('block_id', 42);

        $actual_response = (new MatomoStats($module_service, $matomo_service))->handle($request);
        self::assertSame(StatusCodeInterface::STATUS_OK, $actual_response->getStatusCode());
    }
}
