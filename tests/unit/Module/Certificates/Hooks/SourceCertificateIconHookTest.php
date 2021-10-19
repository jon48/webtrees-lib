<?php

namespace MyArtJaub\Tests\Unit\Webtrees\Module\Certificates\Hooks;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Services\UserService;
use MyArtJaub\Tests\Helpers\Webtrees\TestCase;
use MyArtJaub\Webtrees\Module\Certificates\CertificatesModule;
use MyArtJaub\Webtrees\Module\Certificates\Hooks\SourceCertificateIconHook;
use MyArtJaub\Webtrees\Module\Certificates\Services\UrlObfuscatorService;

/**
 * Class SourceCertificateIconHookTest.
 *
 * @covers \MyArtJaub\Webtrees\Module\Certificates\Hooks\SourceCertificateIconHook
 */
class SourceCertificateIconHookTest extends TestCase
{
    protected SourceCertificateIconHook $sci_hook;

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\TestCase::setUp()
     */
    protected function setUp(): void
    {
        parent::setUp();

        $module = $this->createMock(CertificatesModule::class);
        $module->setName('mod-certificates');

        $user_service = $this->createMock(UserService::class);
        $user_service->method('find')->willReturn(null);
        app()->instance(UserService::class, $user_service);

        $url_obfuscator_service = $this->createMock(UrlObfuscatorService::class);
        $this->sci_hook = new SourceCertificateIconHook($module, $url_obfuscator_service);
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\TestCase::tearDown()
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->sci_hook);

        app()->forgetInstance(UserService::class);
    }

    public function testModule(): void
    {
        self::assertSame('mod-certificates', $this->sci_hook->module()->name());
    }

    public function testFactSourcePrepend(): void
    {
        $tree = $this->createMock(Tree::class);
        $tree->method('getPreference')->with('MAJ_CERTIF_SHOW_CERT')->willReturn((string) Auth::PRIV_PRIVATE);

        self::useDefaultViewFor('mod-certificates::components/certificate-icon');

        self::assertNotEmpty($this->sci_hook->factSourcePrepend($tree, '3 _ACT city/certificate.png', 3));
        self::assertEmpty($this->sci_hook->factSourcePrepend($tree, '1 BIRT Y', 3));
    }

    public function testFactSourceAppend(): void
    {
        $tree = $this->createMock(Tree::class);
        $tree->method('getPreference')->with('MAJ_CERTIF_SHOW_CERT')->willReturn((string) Auth::PRIV_PRIVATE);

        self::assertEmpty($this->sci_hook->factSourceAppend($tree, '3 _ACT city/certificate.png', 3));
    }
}
