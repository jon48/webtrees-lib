<?php

namespace MyArtJaub\Tests\Unit\Webtrees\Module\WelcomeBlock\Services;

use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\TestCase;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use MyArtJaub\Webtrees\Module\WelcomeBlock\WelcomeBlockModule;
use MyArtJaub\Webtrees\Module\WelcomeBlock\Services\MatomoStatsService;

/**
 * Class MatomoStatsServiceTest.
 *
 * @covers \MyArtJaub\Webtrees\Module\WelcomeBlock\Services\MatomoStatsService
 */
class MatomoStatsServiceTest extends TestCase
{
    protected MatomoStatsService $matomo_stats_service;
    protected MockHandler $http_handler;
    protected int $block_id;

    /** @var WelcomeBlockModule&\PHPUnit\Framework\MockObject\MockObject $welcome_block_module */
    protected WelcomeBlockModule $welcome_block_module;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->block_id = 42;
        $this->http_handler = new MockHandler();
        $this->matomo_stats_service = new MatomoStatsService();
        $this->matomo_stats_service->httpHandler(HandlerStack::create($this->http_handler));

        $this->welcome_block_module = self::createMock(WelcomeBlockModule::class);
        $this->welcome_block_module->setName('mod-welcomeblock');
        $this->welcome_block_module->method('matomoSettings')->willReturn([
            'matomo_enabled' => true,
            'matomo_url' => 'http://example.com/',
            'matomo_token'  => 'test-token',
            'matomo_siteid' => 3
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->matomo_stats_service);
        unset($this->http_handler);
        unset($this->block_id);
        unset($this->welcome_block_module);
    }

    protected function clearCache(): void
    {
        Registry::cache()->array()
            ->forget($this->welcome_block_module->name() . '-matomovisits-daily-' . $this->block_id);

        Registry::cache()->file()
            ->forget($this->welcome_block_module->name() . '-matomovisits-yearly-' . $this->block_id);
    }

    public function testHttpHandler(): void
    {
        self::assertTrue($this->matomo_stats_service->httpHandler()->hasHandler());
    }

    /**
     * Data provider for VisitsThisYear tests.
     * @return array<array<mixed>>
     */
    public static function httpResponsesYearly(): array
    {
        return [
            [[new Response(404)], null],
            [[new Response(200, [], 'Not a JSON response')], null],
            [[new Response(200, [], '{"value":15}'), new Response(200, [], 'Not a JSON response')], 15],
            [[new Response(200, [], '{"value":15}'), new Response(200, [], '{"value":3}')], 12],
        ];
    }

    /**
     * @dataProvider httpResponsesYearly
     *
     * @param array<Response> $responses
     * @param int|null $expected_visits
     */
    public function testVisitsThisYear(array $responses, int $expected_visits = null): void
    {
        $this->clearCache();
        $this->http_handler->reset();
        $this->http_handler->append(...$responses);

        self::assertSame(
            $expected_visits,
            $this->matomo_stats_service->visitsThisYear($this->welcome_block_module, $this->block_id)
        );
    }

    /**
     * Data provider for VisitsToday tests.
     * @return array<array<mixed>>
     */
    public static function httpResponsesToday(): array
    {
        return [
            [[new Response(404)], null],
            [[new Response(200, [], 'Not a JSON response')], null],
            [[new Response(200, [], '{"value":15}')], 15]
        ];
    }

    /**
     * @dataProvider httpResponsesToday
     *
     * @param array<Response> $responses
     * @param int $expected_visits
     */
    public function testVisitsToday(array $responses, int $expected_visits = null): void
    {
        $this->clearCache();
        $this->http_handler->reset();
        $this->http_handler->append(...$responses);

        self::assertSame(
            $expected_visits,
            $this->matomo_stats_service->visitsToday($this->welcome_block_module, $this->block_id)
        );
    }
}
