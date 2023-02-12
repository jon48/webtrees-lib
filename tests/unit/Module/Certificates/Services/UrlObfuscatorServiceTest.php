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

use Fisharebest\Webtrees\TestCase;
use MyArtJaub\Webtrees\Module\Certificates\Services\UrlObfuscatorService;
use Psr\Http\Message\ServerRequestInterface;
use InvalidArgumentException;

/**
 * Class UrlObfuscatorServiceTest.
 *
 * @covers \MyArtJaub\Webtrees\Module\Certificates\Services\UrlObfuscatorService
 */
class UrlObfuscatorServiceTest extends TestCase
{
    protected UrlObfuscatorService $url_obfuscator_service;
    protected string $valid_key;
    protected string $valid_nonce;

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\TestCase::setUp()
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->url_obfuscator_service = new UrlObfuscatorService();
        $this->valid_key = str_repeat('ABCD', 8); // SODIUM_CRYPTO_SECRETBOX_KEYBYTES = 32
        $this->valid_nonce = str_repeat('1234', 6); // SODIUM_CRYPTO_SECRETBOX_NONCEBYTES = 24
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\TestCase::tearDown()
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->url_obfuscator_service);
    }

    // phpcs:disable Generic.Files.LineLength.TooLong
    /**
     * Data provider for valid data
     *
     * @return string[][]
     */
    public static function obfuscationValidData(): array
    {
        return [
            ['Test Text To Obfuscate', 'MTIzNDEyMzQxMjM0MTIzNDEyMzQxMjM0ttJ9rj2l7P9v3ANNk10Ko3a0M_n8vye0LIhi4ankTsEiqPMUD7o-'],
            ['42', 'MTIzNDEyMzQxMjM0MTIzNDEyMzQxMjM0a1GDQnWXSdvVz79OPz3mKRbj'],
            ['', 'MTIzNDEyMzQxMjM0MTIzNDEyMzQxMjM0i_fshWXMDPjiZT0tdbAflg--']
        ];
    }

    /**
     * Data provider for invalid data
     *
     * @return string[][]
     */
    public static function obfuscationInvalidData(): array
    {
        return [
            ['Invalid&', 'The encrypted value is not in correct base64 format.'],
            ['MTIzNDEyMzQxMjM0MTIzNDEyMzQxMjM0', 'The encrypted value does not contain enough characters for the key.'],
            ['MTIzNDEyMzQxMjM0MTIzNDEyMzQxMjM0QUJDREVGR0hJSktMTU5PUA==', 'The message has been tampered with in transit.']
        ];
    }
    // phpcs:enable

    /**
     * @dataProvider obfuscationValidData
     */
    public function testObfuscateWithValidData(string $plain, string $obfuscated): void
    {
        self::assertSame(
            $obfuscated,
            $this->url_obfuscator_service->obfuscate($plain, $this->valid_key, $this->valid_nonce)
        );
    }

    public function testObfuscateWithInvalidKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->url_obfuscator_service->obfuscate('test', 'INVALID_KEY', $this->valid_nonce);
    }

    public function testObfuscateWithInvalidNonce(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->url_obfuscator_service->obfuscate('test', $this->valid_key, 'INVALID_NONCE');
    }

    public function testObfuscateWithoutKey(): void
    {
        self::assertStringStartsWith(
            'MTIzNDEyMzQxMjM0',
            $this->url_obfuscator_service->obfuscate('Test', '', $this->valid_nonce)
        );
    }

    public function testObfuscateWithoutKeyOrNonce(): void
    {
        self::assertNotEmpty($this->url_obfuscator_service->obfuscate('Test'));
    }

    public function testObfuscateWithServerParams(): void
    {
        $request = self::createMock(ServerRequestInterface::class);
        $request->method('getServerParams')->willReturn([
            'SERVER_NAME' => 'MYSERVER',
            'SERVER_SOFTWARE' => 'phpunit'
        ]);
        app()->instance(ServerRequestInterface::class, $request);

        self::assertSame(
            'MTIzNDEyMzQxMjM0MTIzNDEyMzQxMjM0iLeJXGFGf2WiOGytf4SAlcWMvds-',
            $this->url_obfuscator_service->obfuscate('Test', '', $this->valid_nonce)
        );
    }

    /**
     * @dataProvider obfuscationValidData
     */
    public function testDeobfuscateWithValidData(string $plain, string $obfuscated): void
    {
        self::assertSame($plain, $this->url_obfuscator_service->deobfuscate($obfuscated, $this->valid_key));
    }

    /**
     * @dataProvider obfuscationInvalidData
     */
    public function testDeobfuscateWithInvalidData(string $obfuscated, string $error): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($error);
        $this->url_obfuscator_service->deobfuscate($obfuscated, $this->valid_key);
    }

    public function testDeobfuscateWithInvalidKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The key needs to be SODIUM_CRYPTO_SECRETBOX_KEYBYTES long');
        $this->url_obfuscator_service->deobfuscate('test', 'INVALID_KEY');
    }

    public function testDeobfuscateWithoutKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The message has been tampered with in transit.');
        $this->url_obfuscator_service->deobfuscate('MTIzNDEyMzQxMjM0MTIzNDEyMzQxMjM0QUJDREVGR0hJSktMTU5PUA==');
    }

    /**
     * @dataProvider obfuscationValidData
     */
    public function testTryDeobfuscateWithValidData(string $plain, string $obfuscated): void
    {
        self::assertTrue($this->url_obfuscator_service->tryDeobfuscate($obfuscated, $this->valid_key));
        self::assertSame($plain, $obfuscated);
    }

    /**
     * @dataProvider obfuscationInvalidData
     */
    public function testTryDeobfuscateWithInvalidData(string $obfuscated): void
    {
        self::assertFalse($this->url_obfuscator_service->tryDeobfuscate($obfuscated, $this->valid_key));
    }
}
