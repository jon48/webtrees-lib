<?php

namespace MyArtJaub\Tests\Unit\Webtrees\Module\Certificates\Http\RequestHandlers;

use Fig\Http\Message\StatusCodeInterface;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\User;
use Fisharebest\Webtrees\Contracts\FilesystemFactoryInterface;
use Fisharebest\Webtrees\Contracts\UserInterface;
use Fisharebest\Webtrees\Http\Exceptions\HttpAccessDeniedException;
use Fisharebest\Webtrees\Http\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Services\ModuleService;
use Fisharebest\Webtrees\Services\TreeService;
use Fisharebest\Webtrees\Services\UserService;
use MyArtJaub\Tests\Helpers\Webtrees\TestCase;
use MyArtJaub\Webtrees\Module\Certificates\CertificatesModule;
use MyArtJaub\Webtrees\Module\Certificates\Http\RequestHandlers\AdminConfigPage;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class AdminConfigPageTest.
 *
 * @covers \MyArtJaub\Webtrees\Module\Certificates\Http\RequestHandlers\AdminConfigPage
 */
class AdminConfigPageTest extends TestCase
{
    /** @var ModuleService&MockObject $module_service */
    protected $module_service;

    /** @var User|MockObject $user */
    protected $user;

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\TestCase::setUp()
     */
    protected function setUp(): void
    {
        parent::setUp();

        $certificate_module = $this->createMock(CertificatesModule::class);
        $certificate_module->setName('mod-certificates');

        $this->module_service = $this->createMock(ModuleService::class);
        $this->module_service->method('findByInterface')
            ->with(CertificatesModule::class)
            ->willReturn(collect([$certificate_module]));

        $this->user = $this->createMock(User::class);
        $this->user->method('id')->willReturn(1);
        $this->user->method('getPreference')->with(UserInterface::PREF_IS_ADMINISTRATOR)->willReturn('1');

        $user_service = $this->createMock(UserService::class);
        $user_service->method('find')->willReturn($this->user);
        app()->instance(UserService::class, $user_service);

        $filesystem_factory = $this->createMock(FilesystemFactoryInterface::class);
        Registry::filesystem($filesystem_factory);
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\TestCase::tearDown()
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->module_service);

        app()->forgetInstance(UserService::class);
    }

    public function testHandle(): void
    {
        $tree = self::createMock(Tree::class);
        $tree->method('id')->willReturn(42);

        $tree_service = $this->createMock(TreeService::class);
        $tree_service->method('all')->willReturn(collect([$tree]));

        $admin_config_page = new AdminConfigPage($this->module_service, $tree_service);

        $request = self::createRequest()
            ->withAttribute('tree', $tree)
            ->withAttribute('user', $this->user);

        self::useDefaultViewFor('mod-certificates::admin/config');
        self::useDefaultViewFor('::layouts/administration');

        $response = $admin_config_page->handle($request);
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }

    public function testHandleWithNoTrees(): void
    {
        $tree = self::createMock(Tree::class);
        $tree->method('id')->willReturn(42);

        $tree_service = $this->createMock(TreeService::class);
        $tree_service->method('all')->willReturn(collect([]));

        $admin_config_page = new AdminConfigPage($this->module_service, $tree_service);

        $request = self::createRequest()
            ->withAttribute('tree', $tree)
            ->withAttribute('user', $this->user);

        self::expectException(HttpAccessDeniedException::class);
        $admin_config_page->handle($request);
    }

    public function testHandleWithOtherTree(): void
    {
        $tree = self::createMock(Tree::class);
        $tree->method('id')->willReturn(42);

        $other_tree = self::createMock(Tree::class);
        $other_tree->method('id')->willReturn(43);

        $tree_service = $this->createMock(TreeService::class);
        $tree_service->method('all')->willReturn(collect([$other_tree]));

        $admin_config_page = new AdminConfigPage($this->module_service, $tree_service);

        $request = self::createRequest()
            ->withAttribute('tree', $tree)
            ->withAttribute('user', $this->user);

        self::expectException(HttpAccessDeniedException::class);
        $admin_config_page->handle($request);
    }

    public function testHandleWithNoModule(): void
    {
        $module_service = $this->createMock(ModuleService::class);
        $module_service->method('findByInterface')
            ->with(CertificatesModule::class)
            ->willReturn(collect([]));

        $tree_service = $this->createMock(TreeService::class);

        $admin_config_page = new AdminConfigPage($module_service, $tree_service);

        $request = self::createRequest();

        self::expectException(HttpNotFoundException::class);
        $admin_config_page->handle($request);
    }
}
