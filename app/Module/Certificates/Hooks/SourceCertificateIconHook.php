<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Certificates
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2022, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\Certificates\Hooks;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Fact;
use Fisharebest\Webtrees\Source;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Module\ModuleInterface;
use MyArtJaub\Webtrees\Contracts\Hooks\FactSourceTextExtenderInterface;
use MyArtJaub\Webtrees\Module\Certificates\CertificatesModule;
use MyArtJaub\Webtrees\Module\Certificates\Elements\SourceCertificate;
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
    public function factSourcePrepend(Tree $tree, $fact): string
    {
        $permission_level = $tree->getPreference('MAJ_CERTIF_SHOW_CERT');
        if (is_numeric($permission_level) && Auth::accessLevel($tree) <= (int) $permission_level) {
            $path = $this->extractPath($fact);

            if ($path !== '') {
                $certificate = new Certificate($tree, $path);
                return view($this->module->name() . '::components/certificate-icon', [
                    'module_name'               =>  $this->module->name(),
                    'certificate'               =>  $certificate,
                    'url_obfuscator_service'    =>  $this->url_obfuscator_service,
                    'js_script_url'             =>  $this->module->assetUrl('js/certificates.min.js')
                ]);
            }
        }
        return '';
    }

    /**
     * Extract path from the provided fact objet.
     *
     * @param \Fisharebest\Webtrees\Fact|array<array<\Fisharebest\Webtrees\Contracts\ElementInterface|string>> $fact
     * @return string
     * @psalm-suppress RedundantConditionGivenDocblockType
     */
    private function extractPath($fact): string
    {
        if ($fact instanceof Fact && $fact->target() instanceof Source) {
            return $fact->attribute('_ACT');
        } elseif (
            is_array($fact) && count($fact) == 2
            && null !== ($source_elements = $fact[0]) && is_array($source_elements) // @phpstan-ignore-line
            && null !== ($source_values = $fact[1]) && is_array($source_values) // @phpstan-ignore-line
        ) {
            foreach ($source_elements as $key => $element) {
                if (
                    $element instanceof SourceCertificate
                    && isset($source_values[$key]) && is_string($source_values[$key])
                ) {
                    return $element->canonical($source_values[$key]);
                }
            }
        }
        return '';
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\FactSourceTextExtenderInterface::factSourceAppend()
     */
    public function factSourceAppend(Tree $tree, $fact): string
    {
        return '';
    }
}
