<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Certificates
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2021-2022, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Http\Middleware;

use Fig\Http\Message\RequestMethodInterface;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\User;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Http\Exceptions\HttpAccessDeniedException;
use Fisharebest\Webtrees\Http\RequestHandlers\LoginPage;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware to restrict access based on a Tree preference.
 */
class AuthTreePreference implements MiddlewareInterface
{
    /**
     * {@inheritDoc}
     * @see \Psr\Http\Server\MiddlewareInterface::process()
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $tree = Validator::attributes($request)->tree();
        $route = Validator::attributes($request)->route();
        $user = Validator::attributes($request)->user();

        $permission_preference = $route->extras['permission_preference'] ?? '';
        $permission_level = $permission_preference === '' ? '' : $tree->getPreference($permission_preference);

        // Permissions are configured
        if (is_numeric($permission_level)) {
            // Logged in with the correct role?
            if (Auth::accessLevel($tree, $user) <= (int) $permission_level) {
                    return $handler->handle($request);
            }

            // Logged in, but without the correct role?
            if ($user instanceof User) {
                throw new HttpAccessDeniedException();
            }
        }

        // Permissions no configured, or not logged in
        if ($request->getMethod() === RequestMethodInterface::METHOD_POST) {
            throw new HttpAccessDeniedException();
        }

        return redirect(route(LoginPage::class, ['tree' => $tree->name(), 'url' => (string) $request->getUri()]));
    }
}
