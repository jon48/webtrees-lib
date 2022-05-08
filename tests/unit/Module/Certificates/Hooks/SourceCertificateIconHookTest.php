<?php

namespace MyArtJaub\Tests\Unit\Webtrees\Module\Certificates\Hooks;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Fact;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Source;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Services\UserService;
use MyArtJaub\Tests\Helpers\Webtrees\TestCase;
use MyArtJaub\Webtrees\Module\Certificates\CertificatesModule;
use MyArtJaub\Webtrees\Module\Certificates\Hooks\SourceCertificateIconHook;
use MyArtJaub\Webtrees\Module\Certificates\Services\UrlObfuscatorService;
use MyArtJaub\Webtrees\Module\Certificates\Elements\SourceCertificate;

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

        Registry::elementFactory()->registerTags([
            'INDI:SOUR:_ACT'    =>  new SourceCertificate(I18N::translate('Certificate'), $module)
        ]);

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

    public function testFactSourcePrependWithFact(): void
    {
        $tree = $this->createMock(Tree::class);
        $tree->method('getPreference')->with('MAJ_CERTIF_SHOW_CERT')->willReturn((string) Auth::PRIV_PRIVATE);

        $source = $this->createMock(Source::class);

        $fact_ok = $this->createMock(Fact::class);
        $fact_ok->method('target')->willReturn($source);
        $fact_ok->method('attribute')->with('_ACT')->willReturn('city/certificate.png');

        $fact_fail = $this->createMock(Fact::class);
        $fact_fail->method('target')->willReturn($source);
        $fact_fail->method('attribute')->with('_ACT')->willReturn('');

        self::useDefaultViewFor('mod-certificates::components/certificate-icon');

        self::assertNotEmpty($this->sci_hook->factSourcePrepend($tree, $fact_ok));
        self::assertEmpty($this->sci_hook->factSourcePrepend($tree, $fact_fail));
    }

    public function testFactSourcePrependWithArray(): void
    {
        $tree = $this->createMock(Tree::class);
        $tree->method('getPreference')->with('MAJ_CERTIF_SHOW_CERT')->willReturn((string) Auth::PRIV_PRIVATE);

        $array_ok = [[Registry::elementFactory()->make('INDI:SOUR:_ACT')], ['city/certificate.png']];
        $array_fail_empty_value = [[Registry::elementFactory()->make('INDI:SOUR:_ACT')], ['']];
        $array_fail_no_value = [[Registry::elementFactory()->make('INDI:SOUR:_ACT')], []];
        $array_fail_no_act = [[Registry::elementFactory()->make('INDI:SOUR:DATA')], ['city/certificate.png']];

        self::useDefaultViewFor('mod-certificates::components/certificate-icon');

        self::assertNotEmpty($this->sci_hook->factSourcePrepend($tree, $array_ok));
        self::assertEmpty($this->sci_hook->factSourcePrepend($tree, $array_fail_empty_value));
        self::assertEmpty($this->sci_hook->factSourcePrepend($tree, $array_fail_no_value));
        self::assertEmpty($this->sci_hook->factSourcePrepend($tree, $array_fail_no_act));
    }

    public function testFactSourceAppend(): void
    {
        $tree = $this->createMock(Tree::class);
        $tree->method('getPreference')->with('MAJ_CERTIF_SHOW_CERT')->willReturn((string) Auth::PRIV_PRIVATE);

        $array = [[Registry::elementFactory()->make('INDI:SOUR:_ACT')], ['city/certificate.png']];

        self::assertEmpty($this->sci_hook->factSourceAppend($tree, $array));
    }
}
