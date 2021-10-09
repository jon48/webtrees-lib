<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Certificates
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2021, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Tests\Unit\Webtrees\Http\Middleware;

use Aura\Router\Route;
use Fig\Http\Message\StatusCodeInterface;
use Fisharebest\Webtrees\TestCase;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\User;
use Fisharebest\Webtrees\Contracts\UserInterface;
use Fisharebest\Webtrees\Http\Exceptions\HttpAccessDeniedException;
use MyArtJaub\Webtrees\Http\Middleware\AuthTreePreference;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class AuthTreePreferenceTest.
 *
 * @covers \MyArtJaub\Webtrees\Http\Middleware\AuthTreePreference
 */
class AuthTreePreferenceTest extends TestCase
{
    /**
     * @var AuthTreePreference
     */
    protected $authTreePreference;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->authTreePreference = new AuthTreePreference();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->authTreePreference);
    }

    public function testProcessWhenAllowed(): void
    {
        $handler = self::createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn(response('lorem ipsum'));

        $user = self::createMock(User::class);

        $tree = self::createMock(Tree::class);
        $tree->method('getPreference')->with('TEST_PERMISSION')->willReturn('2');
        $tree->method('getUserPreference')
            ->with($user, UserInterface::PREF_TREE_ROLE)
            ->willReturn(UserInterface::ROLE_MANAGER);

        $route = new Route();
        $route->extras([ 'permission_preference' =>  'TEST_PERMISSION']);

        $request    = self::createRequest()
            ->withAttribute('tree', $tree)
            ->withAttribute('user', $user)
            ->withAttribute('route', $route);

        $response = $this->authTreePreference->process($request, $handler);

        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        self::assertSame('lorem ipsum', (string) $response->getBody());
    }

    public function testProcessWhenNotAllowed(): void
    {
        $this->expectException(HttpAccessDeniedException::class);
        $this->expectExceptionMessage('You do not have permission to view this page.');

        $handler = self::createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn(response('lorem ipsum'));

        $user = self::createMock(User::class);

        $tree = self::createMock(Tree::class);
        $tree->method('getPreference')->with('TEST_PERMISSION')->willReturn('0');
        $tree->method('getUserPreference')
            ->with($user, UserInterface::PREF_TREE_ROLE)
            ->willReturn(UserInterface::ROLE_VISITOR);

        $route = new Route();
        $route->extras([ 'permission_preference' =>  'TEST_PERMISSION']);

        $request = self::createRequest()
            ->withAttribute('tree', $tree)
            ->withAttribute('user', $user)
            ->withAttribute('route', $route);

        $this->authTreePreference->process($request, $handler);
    }

    public function testProcessWhenNotLoggedIn(): void
    {
        $handler = self::createMock(RequestHandlerInterface::class);

        $tree = self::createMock(Tree::class);
        $request = self::createRequest()->withAttribute('tree', $tree);

        $response = $this->authTreePreference->process($request, $handler);

        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());
    }

    public function testProcessWhenNotLoggedInWithPost(): void
    {
        $this->expectException(HttpAccessDeniedException::class);
        $this->expectExceptionMessage('You do not have permission to view this page.');

        $handler = self::createMock(RequestHandlerInterface::class);

        $tree = self::createMock(Tree::class);
        $request    = self::createRequest()->withAttribute('tree', $tree)->withMethod('POST');

        $this->authTreePreference->process($request, $handler);
    }
}
