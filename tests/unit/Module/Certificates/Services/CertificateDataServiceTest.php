<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Certificates
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2021-2023, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Tests\Unit\Webtrees\Module\Certificates\Services;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Cache;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Source;
use Fisharebest\Webtrees\TestCase;
use Fisharebest\Webtrees\Contracts\CacheFactoryInterface;
use Fisharebest\Webtrees\Contracts\UserInterface;
use Fisharebest\Webtrees\Services\GedcomImportService;
use Fisharebest\Webtrees\Services\TreeService;
use Fisharebest\Webtrees\Services\UserService;
use MyArtJaub\Webtrees\Module\Certificates\Model\Certificate;
use MyArtJaub\Webtrees\Module\Certificates\Services\CertificateDataService;
use Symfony\Component\Cache\Adapter\NullAdapter;

/**
 * Class CertificateDataServiceTest.
 *
 * @covers \MyArtJaub\Webtrees\Module\Certificates\Services\CertificateDataService
 */
class CertificateDataServiceTest extends TestCase
{
    protected static bool $uses_database = true;

    protected CertificateDataService $certificate_data_service;

    /**
     * @var Certificate&\PHPUnit\Framework\MockObject\MockObject $certificate
     */
    protected Certificate $certificate;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->certificate_data_service = new CertificateDataService();

        $this->certificate = self::createMock(Certificate::class);
        $this->certificate->method('gedcomPath')->willReturn('location1/image1.jpg');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->certificate_data_service);
    }

    public function testLinkedIndividuals(): void
    {
        self::assertCount(0, $this->certificate_data_service->linkedIndividuals($this->certificate));
    }

    public function testLinkedFamilies(): void
    {
        self::assertCount(0, $this->certificate_data_service->linkedFamilies($this->certificate));
    }

    public function testLinkedMedias(): void
    {
        self::assertCount(0, $this->certificate_data_service->linkedMedias($this->certificate));
    }

    public function testLinkedNotes(): void
    {
        self::assertCount(0, $this->certificate_data_service->linkedNotes($this->certificate));
    }

    public function testOneLinkedSourceNotNull(): void
    {
        $cache_factory = self::createMock(CacheFactoryInterface::class);
        $cache_factory->method('array')->willReturn(new Cache(new NullAdapter()));
        Registry::cache($cache_factory);

        $import_service = self::createMock(GedcomImportService::class);

        $tree_service = new TreeService($import_service);
        $tree = $tree_service->create('name', 'title');
        $this->certificate->method('tree')->willReturn($tree);

        $user_service = new UserService();

        $admin = $user_service->create('admin', 'admin', 'admin', '*');
        $admin->setPreference(UserInterface::PREF_IS_ADMINISTRATOR, '1');
        $admin->setPreference(UserInterface::PREF_AUTO_ACCEPT_EDITS, '1');

        Auth::login($admin);

        $source = $tree->createRecord("0 @@ SOUR\n1 TITL Test Source");
        $tree->createRecord("0 @@ NOTE Test note\n1 SOUR @" . $source->xref() . "@\n2 _ACT location1/image1.jpg");

        $linked_source = $this->certificate_data_service->oneLinkedSource($this->certificate);
        self::assertInstanceOf(Source::class, $linked_source); /** @var Source $linked_source */
        self::assertSame($source->xref(), $linked_source->xref());

        Auth::logout();
    }

    public function testOneLinkedSourceNull(): void
    {
        self::assertNull($this->certificate_data_service->oneLinkedSource($this->certificate));
    }
}
