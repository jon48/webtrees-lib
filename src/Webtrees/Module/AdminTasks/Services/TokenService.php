<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage AdminTasks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2021, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\AdminTasks\Services;

/**
 * Service for generating random tokens
 *
 */
class TokenService
{
    /**
     * Returns a random-ish generated token of a given size
     *
     * @param int $length Length of the token, default to 32
     * @return string Random token
     */
    public function generateRandomToken(int $length = 32): string
    {
        $chars = str_split('abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
        $len_chars = count($chars);
        $token = '';

        for ($i = 0; $i < $length; $i++) {
            $token .= $chars[mt_rand(0, $len_chars - 1)];
        }

        # Number of 32 char chunks
        $chunks = ceil(strlen($token) / 32);
        $md5token = '';

        # Run each chunk through md5
        for ($i = 1; $i <= $chunks; $i++) {
            $md5token .= md5(substr($token, $i * 32 - 32, 32));
        }

        # Trim the token to the required length
        return substr($md5token, 0, $length);
    }
}
