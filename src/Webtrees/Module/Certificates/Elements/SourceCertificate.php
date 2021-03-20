<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Certificates
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2021, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\Certificates\Elements;

use Fisharebest\Webtrees\Elements\AbstractElement;

/**
 * Gedcom element for certificate associated to a source.
 * Structure:
 *  n   SOUR @XREF@
 *  n+1 _ACT certificate_file_path
 */
class SourceCertificate extends AbstractElement
{
    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Elements\AbstractElement::canonical()
     */
    public function canonical($value): string
    {
        return strtr($value, '\\', '/');
    }
}
