<?php

namespace MyArtJaub\Tests\Unit\Webtrees\Module\WelcomeBlock;

use Aura\Router\Map;
use Aura\Router\Route;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Module\LoginBlockModule;
use Fisharebest\Webtrees\Module\ModuleBlockInterface;
use MyArtJaub\Tests\Helpers\Webtrees\TestCase;
use MyArtJaub\Webtrees\Module\WelcomeBlock\WelcomeBlockModule;
use MyArtJaub\Webtrees\Module\WelcomeBlock\Http\RequestHandlers\MatomoStats;

/**
 * Class WelcomeBlockModuleTest.
 *
 * @covers \MyArtJaub\Webtrees\Module\WelcomeBlock\WelcomeBlockModule
 */
class WelcomeBlockModuleTest extends TestCase
{
    protected static bool $uses_database = true;

    protected const MOD_NAME = 'mod-welcomeblock';

    protected int $block_id = 42;

    /** @var WelcomeBlockModule&\PHPUnit\Framework\MockObject\MockObject $welcome_block_module */
    protected WelcomeBlockModule $welcome_block_module;

    /** @var Tree&\PHPUnit\Framework\MockObject\MockObject $tree */
    protected Tree $tree;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->welcome_block_module = self::getMockBuilder(WelcomeBlockModule::class)
            ->onlyMethods(['assetUrl'])
            ->getMock(); //new WelcomeBlockModule();
        $this->welcome_block_module->setName(self::MOD_NAME);
        $this->welcome_block_module->method('assetUrl')->willReturn('path-to-assets');

        $this->tree = self::createMock(Tree::class);
        $this->block_id = 42;

        self::useDefaultViewFor(self::MOD_NAME . '::block-embed');
        self::useDefaultViewFor(self::MOD_NAME . '::config');
        self::useDefaultViewFor('::modules/block-template');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->welcome_block_module);
        unset($this->block_id);
        unset($this->tree);
    }

    public function testMetadata(): void
    {
        $this->welcome_block_module->boot();

        self::assertNotEmpty($this->welcome_block_module->title());
        self::assertNotEmpty($this->welcome_block_module->description());
        self::assertNotEmpty($this->welcome_block_module->customModuleVersion());
        self::assertTrue($this->welcome_block_module->isTreeBlock());
        self::assertFalse($this->welcome_block_module->isUserBlock());
    }

    public function testLoadRoutes(): void
    {
        $router = new Map(new Route());

        $this->welcome_block_module->loadRoutes($router);
        self::assertCount(1, $router->getRoutes());
        self::assertSame(MatomoStats::class, $router->getRoute(MatomoStats::class)->name);
    }

    public function testGetBlock(): void
    {
        $wt_welcome_block = self::createMock(ModuleBlockInterface::class);
        $wt_welcome_block->method('getBlock')->with($this->tree, $this->block_id)->willReturn('welcome_block');
        app()->instance(\Fisharebest\Webtrees\Module\WelcomeBlockModule::class, $wt_welcome_block);

        $wt_login_block = self::createMock(LoginBlockModule::class);
        $wt_login_block->method('getBlock')->with($this->tree, $this->block_id)->willReturn('login_block');
        app()->instance(\Fisharebest\Webtrees\Module\LoginBlockModule::class, $wt_login_block);

        foreach (['', ModuleBlockInterface::CONTEXT_TREE_PAGE, ModuleBlockInterface::CONTEXT_EMBED] as $context) {
            self::assertSame(
                self::DEFAULT_VIEW_TEXT,
                $this->welcome_block_module->getBlock($this->tree, $this->block_id, $context)
            );
        }
    }

    public function testEditBlockConfiguration(): void
    {
        self::assertSame(
            self::DEFAULT_VIEW_TEXT,
            $this->welcome_block_module->editBlockConfiguration($this->tree, $this->block_id)
        );
    }

    /**
     * Data provider for SaveBlockConfiguration tests
     * @return array<array<mixed>>
     */
    public static function blockConfigurations(): array
    {
        return [
            ['false', '', '1', '', false, '', 0,''],
            ['yes', 'invalid-url', '1', 'token', true, '', 0, ''],
            ['yes', 'http://example.com', 'invalid', 'token', true, '', 0, ''],
            ['yes', 'http://example.com', '-1', 'token', true, '', 0, ''],
            ['yes', 'http://example.com', '3', 'token', true, 'http://example.com', 3, 'token'],
            ['yes', 'http://example.com', '3', ' token ', true, 'http://example.com', 3, 'token']
        ];
    }

    /**
     * @dataProvider blockConfigurations
     *
     * @param string $enabled
     * @param string $url
     * @param string $site_id
     * @param string $token
     * @param bool $expected_enabled
     * @param string $expected_url
     * @param int $expected_site_id
     * @param string $expected_token
     */
    public function testSaveBlockConfiguration(
        string $enabled,
        string $url,
        string $site_id,
        string $token,
        bool $expected_enabled,
        string $expected_url,
        int $expected_site_id,
        string $expected_token
    ): void {
        Registry::cache()->array()->forget('block-setting-' . $this->block_id);

        $request = self::createRequest()->withParsedBody(
            ['matomo_enabled' => $enabled, 'matomo_url' => $url, 'matomo_siteid' => $site_id, 'matomo_token' => $token]
        );
        $this->welcome_block_module->saveBlockConfiguration($request, $this->block_id);

        self::assertSame($expected_enabled, $this->welcome_block_module->isMatomoEnabled($this->block_id));
        //var_dump($url . ' - ' . filter_var($url, FILTER_VALIDATE_URL));
        self::assertEqualsCanonicalizing([
            'matomo_enabled' => $expected_enabled,
            'matomo_url' => $expected_url,
            'matomo_token'  => $expected_token,
            'matomo_siteid' => $expected_site_id
        ], $this->welcome_block_module->matomoSettings($this->block_id));
    }
}
