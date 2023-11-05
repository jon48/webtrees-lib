<?php

namespace MyArtJaub\Tests\Unit\Webtrees\Module\Certificates;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\TestCase;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\User;
use Fisharebest\Webtrees\Contracts\UserInterface;
use Fisharebest\Webtrees\Services\UserService;
use MyArtJaub\Webtrees\Module\Certificates\CertificatesModule;

/**
 * Class CertificatesModuleTest.
 *
 * @covers \MyArtJaub\Webtrees\Module\Certificates\CertificatesModule
 */
class CertificatesModuleTest extends TestCase
{
    protected CertificatesModule $certificates_module;

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\TestCase::setUp()
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->certificates_module = new CertificatesModule();
        $this->certificates_module->setName('mod-certificates');
        $this->certificates_module->boot();
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\TestCase::tearDown()
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->certificates_module);
    }

    public function testMetadata(): void
    {
        self::assertNotEmpty($this->certificates_module->title());
        self::assertNotEmpty($this->certificates_module->description());
        self::assertNotEmpty($this->certificates_module->customModuleVersion());
        self::assertNotEmpty($this->certificates_module->getConfigLink());
    }

    public function testHeadContent(): void
    {
        self::expectExceptionMessageMatches('/filemtime\(\): stat failed/');
        self::assertNotEmpty($this->certificates_module->headContent());
    }

    /**
     * @depends testMetadata
     */
    public function testListUrl(): void
    {
        $tree = $this->createMock(Tree::class);
        self::assertNotEmpty($this->certificates_module->listUrl($tree));
    }

    public function testListMenuClass(): void
    {
        self::assertSame('menu-maj-certificates', $this->certificates_module->listMenuClass());
    }

    public function testListIsEmpty(): void
    {
        $tree = $this->createMock(Tree::class);
        $tree->method('getPreference')->with('MAJ_CERTIF_SHOW_CERT')->willReturn((string) Auth::PRIV_USER);

        $user = $this->createMock(User::class);

        $user_service = $this->createMock(UserService::class);
        $user_service->method('find')->willReturn($user);
        app()->instance(UserService::class, $user_service);

        self::assertTrue($this->certificates_module->listIsEmpty($tree));

        $user = $this->createMock(User::class);
        $user->method('getPreference')->with(UserInterface::PREF_IS_ADMINISTRATOR)->willReturn('1');

        $user_service = $this->createMock(UserService::class);
        $user_service->method('find')->willReturn($user);
        app()->instance(UserService::class, $user_service);

        self::assertFalse($this->certificates_module->listIsEmpty($tree));

        app()->forgetInstance(UserService::class);
    }

    public function testListSubscribedHooks(): void
    {
        self::assertCount(1, $this->certificates_module->listSubscribedHooks());
    }
}
