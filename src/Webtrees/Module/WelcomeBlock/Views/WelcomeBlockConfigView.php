<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage WelcomeBlock
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\WelcomeBlock\Views;

use \MyArtJaub\Webtrees\Mvc\View\AbstractView;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Functions\FunctionsEdit;

/**
 * View for WelcomeBlock@config
 */
class WelcomeBlockConfigView extends AbstractView {
        
	/**
	 * {@inhericDoc}
	 * @see \MyArtJaub\Webtrees\Mvc\View\AbstractView::renderContent()
	 */
    protected function renderContent() {     
        
        return '
            <tr>
                <td class="descriptionbox wrap width33">' .
                I18N::translate('Enable Piwik Statistics') .
                // Ideally, would like to add helpLink, but this does not work for modules...
                // FunctionsPrint::helpLink('piwik_enabled', $this->getName());
                '</td>
                <td class="optionbox">' .
                FunctionsEdit::editFieldYesNo('piwik_enabled', $this->data->get('piwik_enabled', '0')) .
                '</td>
            </tr>' .
            
            '<tr>
                <td class="descriptionbox wrap width33">' .
                I18N::translate('Piwik URL') .
                '</td>
                <td class="optionbox">
                    <input type="text" name="piwik_url" size="45" value="' . 
                    $this->data->get('piwik_url', '') . '" />
                </td>
            </tr>' .
            
            '<tr>
                <td class="descriptionbox wrap width33">' .
                    I18N::translate('Piwik Token') .
                '</td>
                <td class="optionbox">
                    <input type="text" name="piwik_token" size="45" value="' . 
                    $this->data->get('piwik_token', '') . '" />
                </td>
            </tr>' .
            
            '<tr>
                <td class="descriptionbox wrap width33">' .
                    I18N::translate('Piwik Site ID') .
                '</td>
                <td class="optionbox">
                    <input type="text" name="piwik_siteid" size="4" value="' . 
                    $this->data->get('piwik_siteid', '') . '" />
                </td>
            </tr>' ;
        
    }
    

}
 