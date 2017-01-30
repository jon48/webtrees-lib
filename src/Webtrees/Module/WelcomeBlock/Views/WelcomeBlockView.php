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
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Site;

/**
 * View for WelcomeBlock@index
 */
class WelcomeBlockView extends AbstractView {
        
	/**
	 * {@inhericDoc}
	 * @see \MyArtJaub\Webtrees\Mvc\View\AbstractView::renderContent()
	 */
    protected function renderContent() {        
        /** @var \Fisharebest\Webtrees\Individual $indi */
        $indi = $this->data->get('indi');
        
        /** @var \Fisharebest\Webtrees\Tree $tree */
        $tree = $this->data->get('tree');
                
        //Welcome section - gedcom title, date, statistics - based on gedcom_block        
        $content = 
            '<table>
                <tr>
                    <td>
                        <a href="pedigree.php?rootid=' . $indi->getXref() . '&amp;ged=' . $tree->getNameUrl() . '">
                            <i class="icon-pedigree"></i><br>' . I18N::translate('Default chart') . '
                        </a>
                    </td>
                    <td>
                        <a href="individual.php?pid=' . $indi->getXref() . '&amp;ged=' . $tree->getNameUrl() . '">
                            <i class="icon-indis"></i><br>' . I18N::translate('Default individual') . '
                        </a>
                    </td>';
        
        if (Site::getPreference('USE_REGISTRATION_MODULE') && !Auth::check()) {
            $content .= '
                    <td>
                        <a href="' . WT_LOGIN_URL . '?action=register">
                            <i class="icon-user_add"></i><br>'.I18N::translate('Request a new user account').'
                        </a>
                    </td>';
        }
        $content .= '
                </tr>
            </table>';
        
        // Piwik Statistics        
        if ($this->data->get('piwik_enabled', false)){
            $content .= '
                <div class="center">
                    <div id="piwik_stats">
                        <i class="icon-loading-small"></i>&nbsp;' . I18N::translate('Retrieving Piwik statistics...') . '
                    </div>
                </div>';
        }
        
        $content .=  '<hr />';
        
        // Login section - based on login_block
        if (Auth::check()) {
            $content .= '
            <div class="center">
                <form method="post" action="logout.php" name="logoutform" onsubmit="return true;">
                    <br>
                    <a href="edituser.php" class="name2">' . I18N::translate('You are signed in as %s.', '<a href="edituser.php" class="name2">' . Auth::user()->getRealNameHtml() . '</a>') . '</a>
                    <br><br>
                    <input type="submit" value="'.I18N::translate('sign out').'">
                    <br><br>
                </form>
            </div>';
        } else {
            $content .= '
            <div id="maj-login-box">
                <form id="maj-login-form" name="maj-login-form" method="post" action="'.WT_LOGIN_URL.'">
					<input type="hidden" name="action" value="login">
				    <div>
					    <label for="maj-username">'. I18N::translate('Username').
							    '<input type="text" id="maj-username" name="username" class="formField">
						</label>
					</div>
					<div>
						<label for="maj-password">'. I18N::translate('Password').
								'<input type="password" id="maj-password" name="password" class="formField">
						</label>
					</div>
					<div>
						<input type="submit" value="'. I18N::translate('sign in'). '">
					</div>
					<div>
						<a href="#" id="maj-passwd_click">'. I18N::translate('Request a new password').'</a>
					</div>';
            if (Site::getPreference('USE_REGISTRATION_MODULE')) {
                $content.= '
                    <div>
                        <a href="'.WT_LOGIN_URL.'?action=register">'. I18N::translate('Request a new user account').'</a>
                    </div>';
            }
            $content.= '
                </form>'; // close "login-form"
             
            // hidden New Password block
            $content.= '
                <div id="maj-new_passwd">
                    <form id="maj-new_passwd_form" name="new_passwd_form" action="'.WT_LOGIN_URL.'" method="post">
                        <input type="hidden" name="time" value="">
                        <input type="hidden" name="action" value="requestpw">
                        <h4>'. I18N::translate('Lost password request').'</h4>
                        <div>
                            <label for="maj-new_passwd_username">'. I18N::translate('Username or email address') . '
                                <input type="text" id="maj-new_passwd_username" name="new_passwd_username" value="">
                            </label>
        				</div>
        				<div>
                            <input type="submit" value="'. I18N::translate('Continue'). '">
                        </div>
                    </form>
                </div>
            </div>';//"login-box"
        }
        
        return $content;
    }
    

}
 