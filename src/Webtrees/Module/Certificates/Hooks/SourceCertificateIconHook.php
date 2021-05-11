<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Certificates
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2021, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\Certificates\Hooks;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Module\ModuleInterface;
use MyArtJaub\Webtrees\Contracts\Hooks\FactSourceTextExtenderInterface;
use MyArtJaub\Webtrees\Module\Certificates\CertificatesModule;
use MyArtJaub\Webtrees\Module\Certificates\Model\Certificate;
use MyArtJaub\Webtrees\Module\Certificates\Services\UrlObfuscatorService;

/**
 * Hook for displaying the certificate link icon next to the source citation title.
 */
class SourceCertificateIconHook implements FactSourceTextExtenderInterface
{
    private CertificatesModule $module;
    private UrlObfuscatorService $url_obfuscator_service;

    /**
     * Constructor for SourceCertificateIconHook
     *
     * @param CertificatesModule $module
     * @param UrlObfuscatorService $url_obfuscator_service
     */
    public function __construct(CertificatesModule $module, UrlObfuscatorService $url_obfuscator_service)
    {
        $this->module = $module;
        $this->url_obfuscator_service = $url_obfuscator_service;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\HookInterface::module()
     */
    public function module(): ModuleInterface
    {
        return $this->module;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\FactSourceTextExtenderInterface::factSourcePrepend()
     */
    public function factSourcePrepend(Tree $tree, string $source_record, int $level): string
    {
        $permission_level = $tree->getPreference('MAJ_CERTIF_SHOW_CERT');
        if (
            is_numeric($permission_level) && Auth::accessLevel($tree) <= (int) $permission_level &&
            preg_match('/^' . $level . ' _ACT (.*)$/m', $source_record, $match) === 1
        ) {
            return view($this->module->name() . '::components/certificate-icon', [
                'module_name'               =>  $this->module->name(),
                'certificate'               =>  new Certificate($tree, $match[1]),
                'url_obfuscator_service'    =>  $this->url_obfuscator_service,
                'js_script_url'             =>  $this->module->assetUrl('js/certificates.min.js')
            ]);
        }
        return '';
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\FactSourceTextExtenderInterface::factSourceAppend()
     */
    public function factSourceAppend(Tree $tree, string $source_record, int $level): string
    {
        return '';
    }
}
