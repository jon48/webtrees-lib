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

namespace MyArtJaub\Webtrees\Module\Certificates\Services;

use Psr\Http\Message\ServerRequestInterface;
use InvalidArgumentException;

/**
 * Service for obfuscating/deobfuscating strings for use in URLs.
 * Even though it is using encryption mechanism, the service is not designed to provide strong security.
 */
class UrlObfuscatorService
{
    /**
     * @var string|null $encryption_key
     */
    private $encryption_key;


    /**
     * Return (and generate) the key to be used for the encryption step
     *
     * @return string Encryption key
     */
    protected function encryptionKey(): string
    {
        if ($this->encryption_key === null) {
            /** @var ServerRequestInterface $request **/
            $request = app(ServerRequestInterface::class);
            $server_name = $request->getServerParams()['SERVER_NAME'] ?? '';
            $server_software = $request->getServerParams()['SERVER_SOFTWARE'] ?? '';
            $this->encryption_key = str_pad(md5(
                $server_name !== '' && $server_software !== '' ?
                $server_name . $server_software :
                'STANDARDKEYIFNOSERVER'
            ), SODIUM_CRYPTO_SECRETBOX_KEYBYTES, "1234567890ABCDEF");
        }
        return $this->encryption_key;
    }

    /**
     * Obfuscate a clear text, with a combination of encryption and base64 encoding.
     * The return string is URL-safe.
     *
     * @param string $cleartext Text to obfuscate
     * @param string $key
     * @param string $nonce
     * @return string
     */
    public function obfuscate(string $cleartext, string $key = '', string $nonce = ''): string
    {
        if ($nonce === '') {
            $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        }
        if ($key === '') {
            $key = $this->encryptionKey();
        }

        if (strlen($nonce) !== SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) {
            throw new InvalidArgumentException('The nonce needs to be SODIUM_CRYPTO_SECRETBOX_NONCEBYTES long');
        }

        if (strlen($key) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
            throw new InvalidArgumentException('The key needs to be SODIUM_CRYPTO_SECRETBOX_KEYBYTES long');
        }

        $encryted = sodium_crypto_secretbox($cleartext, $nonce, $key);
        return strtr(base64_encode($nonce . $encryted), '+/=', '._-');
    }

    /**
     * Deobfuscate a string from an URL to a clear text.
     *
     * @param string $obfuscated Text to deobfuscate
     * @param string $key
     * @throws InvalidArgumentException
     * @return string
     */
    public function deobfuscate(string $obfuscated, string $key = ''): string
    {
        $obfuscated = strtr($obfuscated, '._-', '+/=');
        if ($key === '') {
            $key = $this->encryptionKey();
        }

        if (strlen($key) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
            throw new InvalidArgumentException('The key needs to be SODIUM_CRYPTO_SECRETBOX_KEYBYTES long');
        }

        $encrypted = base64_decode($obfuscated, true);
        if ($encrypted === false) {
            throw new InvalidArgumentException('The encrypted value is not in correct base64 format.');
        }

        if (mb_strlen($encrypted, '8bit') < (SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES)) {
            throw new InvalidArgumentException('The encrypted value does not contain enough characters for the key.');
        }

        $nonce = mb_substr($encrypted, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
        $ciphertext = mb_substr($encrypted, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');

        $decrypted = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);

        if ($decrypted === false) {
            throw new InvalidArgumentException('The message has been tampered with in transit.');
        }

        //sodium_memzero($ciphertext);    // sodium_compat cannot handle it, only through libsodium

        /** @var string $decrypted - Psalm detect as string|true otherwise */
        return $decrypted;
    }

    /**
     * Try to deobfuscate a string from an URL to a clear text, returning whether the operation is a success.
     *
     * @param string $obfuscated Text to deobfuscate
     * @param string $key
     * @return bool
     */
    public function tryDeobfuscate(string &$obfuscated, string $key = ''): bool
    {
        try {
            $obfuscated = $this->deobfuscate($obfuscated, $key);
            return true;
        } catch (InvalidArgumentException $ex) {
        }
        return false;
    }
}
