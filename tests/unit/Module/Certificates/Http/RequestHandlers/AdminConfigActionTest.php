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

namespace MyArtJaub\Tests\Unit\Webtrees\Module\Certificates\Http\RequestHandlers;

use Fig\Http\Message\StatusCodeInterface;
use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\TestCase;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\Certificates\CertificatesModule;
use MyArtJaub\Webtrees\Module\Certificates\Http\RequestHandlers\AdminConfigAction;

/**
 * Class AdminConfigActionTest.
 *
 * @covers \MyArtJaub\Webtrees\Module\Certificates\Http\RequestHandlers\AdminConfigAction
 */
class AdminConfigActionTest extends TestCase
{
    public function testHandle(): void
    {
        $certificate_service = new CertificatesModule();
        $certificate_service->setName('mod-certificates');
        $certificate_service->boot();

        $module_service = $this->createMock(ModuleService::class);
        $module_service->method('findByInterface')
            ->with(CertificatesModule::class)
            ->willReturn(collect([$certificate_service]));

        $admin_config_action = new AdminConfigAction($module_service);

        $tree = self::createMock(Tree::class);

        $request = self::createRequest()
            ->withAttribute('tree', $tree)
            ->withParsedBody([
                'MAJ_CERTIF_WM_FONT_MAXSIZE' => '15',
                'MAJ_CERTIF_WM_FONT_COLOR' => '#15AE10'
            ]);

        $response = $admin_config_action->handle($request);
        $messages = FlashMessages::getMessages();

        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());
        self::assertCount(1, $messages);

        $first_message = $messages[0];
        self::assertTrue(property_exists($first_message, 'status'));
        self::assertSame('success', $first_message->status);
    }

    public function testHandleWithNoModule(): void
    {
        $module_service = $this->createMock(ModuleService::class);
        $module_service->method('findByInterface')
            ->with(CertificatesModule::class)
            ->willReturn(collect());

        $admin_config_action = new AdminConfigAction($module_service);

        $tree = self::createMock(Tree::class);

        $request = self::createRequest()
            ->withAttribute('tree', $tree);

        $response = $admin_config_action->handle($request);
        $messages = FlashMessages::getMessages();

        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());
        self::assertCount(1, $messages);

        $first_message = $messages[0];
        self::assertTrue(property_exists($first_message, 'status'));
        self::assertSame('danger', $first_message->status);
    }
}
