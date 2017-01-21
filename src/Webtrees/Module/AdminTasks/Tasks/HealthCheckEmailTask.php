<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage AdminTasks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2012-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\AdminTasks\Tasks;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Database;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\Functions\Functions;
use Fisharebest\Webtrees\Functions\FunctionsEdit;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Log;
use Fisharebest\Webtrees\Mail;
use Fisharebest\Webtrees\Stats;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\User;
use MyArtJaub\Webtrees\Module\AdminTasks\Model\AbstractTask;
use MyArtJaub\Webtrees\Module\AdminTasks\Model\ConfigurableTaskInterface;

/**
 * Task to send an email summarising the healthcheck of the site
 */
class HealthCheckEmailTask extends AbstractTask implements ConfigurableTaskInterface {
    
    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\AdminTasks\Model\AbstractTask::getTitle()
     */
    public function getTitle() {
		return I18N::translate('Healthcheck Email');
	}
	
	/**
	 * {@inheritDoc}
	 * @see \MyArtJaub\Webtrees\Module\AdminTasks\Model\AbstractTask::getDefaultFrequency()
	 */
    public function getDefaultFrequency() {
		return 10080;  // = 1 week = 7 * 24 * 60 min
	}
    
	/**
	 * {@inheritDoc}
	 * @see \MyArtJaub\Webtrees\Module\AdminTasks\Model\AbstractTask::executeSteps()
	 */
    protected function executeSteps() {
	
		$res = false;		
		
		// Get the number of days to take into account, either last 7 days or since last check
		$interval_sincelast = 0;
		if($this->last_updated){
			$tmpInt = $this->last_updated->diff(new \DateTime('now'), true);
			$interval_sincelast = ( $tmpInt->days * 24  + $tmpInt->h ) * 60 + $tmpInt->i;
		}
		
		$interval = max($this->frequency, $interval_sincelast);
		$nbdays = ceil($interval / (24 * 60));
				
        // Check for updates
        $latest_version_txt = Functions::fetchLatestVersion();
        if (preg_match('/^[0-9.]+\|[0-9.]+\|/', $latest_version_txt)) {
        	list($latest_version, , $download_url) = explode('|', $latest_version_txt);
        } else {
        	// Cannot determine the latest version
        	list($latest_version, , $download_url) = explode('|', '||');
        }
		
		// Users statistics
		$warnusers = 0;
		$nverusers = 0;
		$applusers = 0;
		foreach(User::all() as $user) {
			if (((date("U") - (int)$user->getPreference('reg_timestamp')) > 604800) && !$user->getPreference('verified')) {
				$warnusers++;
			}
			if (!$user->getPreference('verified_by_admin') && $user->getPreference('verified')) {
				$nverusers++;
			}
			if (!$user->getPreference('verified')) {
				$applusers++;
			}
		}
		
		// Tree specifics checks
		$one_tree_done = false;
		foreach(Tree::getAll() as $tree){
			$isTreeEnabled = $tree->getPreference('MAJ_AT_'.$this->getName().'_ENABLED');
			if((is_null($isTreeEnabled) || $isTreeEnabled) && $webmaster = User::find($tree->getPreference('WEBMASTER_USER_ID'))){
				I18N::init($webmaster->getPreference('language'));
				
				$subject = I18N::translate('Health Check Report').' - '.I18N::translate('Tree %s', $tree->getTitle());
				$message = 
					I18N::translate('Health Check Report for the last %d days', $nbdays). Mail::EOL. Mail::EOL.
					I18N::translate('Tree %s', $tree->getTitle()).Mail::EOL.
					'=========================================='.Mail::EOL.Mail::EOL;
				
				// News
				$message_version = '';
				if($latest_version && version_compare(WT_VERSION, $latest_version)<0){
					$message_version = I18N::translate('News').Mail::EOL.
							'-------------'.Mail::EOL.
							I18N::translate('A new version of *webtrees* is available: %s. Upgrade as soon as possible.', $latest_version).Mail::EOL.
							I18N::translate('Download it here: %s.', $download_url).Mail::EOL.Mail::EOL;
				}
				$message .= $message_version;
				
				// Statistics users
				$message_users = I18N::translate('Users').Mail::EOL.
						'-------------'.Mail::EOL.
						WT_BASE_URL.'admin_users.php'.Mail::EOL.
						I18N::translate('Total number of users')."\t\t".User::count().Mail::EOL.
						I18N::translate('Not verified by the user')."\t\t".$applusers.Mail::EOL.
						I18N::translate('Not approved by an administrator')."\t".$nverusers.Mail::EOL.
						Mail::EOL;
				$message  .= $message_users;
								
				// Statistics tree:				
				$stats = new Stats($tree);
				$sql = 'SELECT ged_type AS type, COUNT(change_id) AS chgcount FROM wt_change'.
					' JOIN ('.
						' SELECT "indi" AS ged_type, i_id AS ged_id, i_file AS ged_file FROM `##individuals`'.
						' UNION SELECT "fam" AS ged_type, f_id AS ged_id, f_file AS ged_file FROM `##families`'.
						' UNION SELECT "sour" AS ged_type, s_id AS ged_id, s_file AS ged_file FROM `##sources`'.
						' UNION SELECT "media" AS ged_type, m_id AS ged_id, m_file AS ged_file FROM `##media`'.
						' UNION SELECT LOWER(o_type) AS ged_type, o_id AS ged_id, o_file AS ged_file FROM `##other`'.
					') AS gedrecords ON (xref = ged_id AND gedcom_id = ged_file)'.
					' WHERE change_time >= DATE_ADD( NOW(), INTERVAL - :nb_days DAY)'.
					' AND status = :status AND gedcom_id = :gedcom_id'.
					' GROUP BY ged_type';
				$changes = Database::prepare($sql)->execute(array(
					'status' => 'accepted', 
					'gedcom_id' => $tree->getTreeId(), 
					'nb_days' => $nbdays
				))->fetchAssoc();
								
				$message_gedcom = I18N::translate('Tree statistics').Mail::EOL.
					'-------------'.Mail::EOL.
					sprintf('%-25s', I18N::translate('Records'))."\t".sprintf('%15s', I18N::translate('Count'))."\t".sprintf('%15s', I18N::translate('Changes')).Mail::EOL.
					sprintf('%-25s', I18N::translate('Individuals'))."\t".sprintf('%15s', $stats->totalIndividuals())."\t".sprintf('%15s', (isset($changes['indi']) ? $changes['indi'] : 0)).Mail::EOL.
					sprintf('%-25s', I18N::translate('Families'))."\t".sprintf('%15s', $stats->totalFamilies())."\t".sprintf('%15s', (isset($changes['fam']) ? $changes['fam'] : 0)).Mail::EOL.
					sprintf('%-25s', I18N::translate('Sources'))."\t".sprintf('%15s', $stats->totalSources())."\t".sprintf('%15s', (isset($changes['sour']) ? $changes['sour'] : 0)).Mail::EOL.
					sprintf('%-25s', I18N::translate('Repositories'))."\t".sprintf('%15s', $stats->totalRepositories())."\t".sprintf('%15s', (isset($changes['repo']) ? $changes['repo'] : 0)).Mail::EOL.
					sprintf('%-25s', I18N::translate('Media objects'))."\t".sprintf('%15s', $stats->totalMedia())."\t".sprintf('%15s', (isset($changes['media']) ? $changes['media'] : 0)).Mail::EOL.
					sprintf('%-25s', I18N::translate('Notes'))."\t".sprintf('%15s', $stats->totalNotes())."\t".sprintf('%15s', (isset($changes['note']) ? $changes['note'] : 0)).Mail::EOL.
					Mail::EOL;				
				$message .= $message_gedcom;
								
				//Errors
				$sql = 'SELECT SQL_CACHE log_message, gedcom_id, COUNT(log_id) as nblogs, MAX(log_time) as lastoccurred'.
							' FROM `##log`'.
							' WHERE log_type = :log_type AND (gedcom_id = :gedcom_id OR ISNULL(gedcom_id))'.
							' AND log_time >= DATE_ADD( NOW(), INTERVAL - :nb_days DAY)'.
							' GROUP BY log_message, gedcom_id'.
							' ORDER BY lastoccurred DESC';
				$errors=Database::prepare($sql)->execute(array(
					'log_type' => Log::TYPE_ERROR, 
					'gedcom_id' => $tree->getTreeId(), 
					'nb_days' => $nbdays
				))->fetchAll();	
				$nb_errors = 0;		
				$tmp_message = '';
				$nb_char_count_title = strlen(I18N::translate('Count'));
				$nb_char_type = max(strlen(I18N::translate('Type')), strlen(I18N::translate('Site')), strlen(I18N::translate('Tree')));
				foreach ($errors as $error) {
					$tmp_message .= sprintf('%'.$nb_char_count_title.'d', $error->nblogs)."\t";
					$tmp_message .= sprintf('%'.$nb_char_type.'s', is_null($error->gedcom_id) ? I18N::translate('Site') : I18N::translate('Tree'));
					$tmp_message .= "\t".sprintf('%20s', $error->lastoccurred)."\t";
					$tmp_message .= str_replace("\n", "\n\t\t\t\t\t\t", $error->log_message).Mail::EOL;
					$nb_errors += $error->nblogs;
				}
				if($nb_errors > 0){
					$message .= I18N::translate('Errors [%d]', $nb_errors).Mail::EOL.
						'-------------'.Mail::EOL.
						WT_BASE_URL.'admin_site_logs.php'.Mail::EOL.
						I18N::translate('Count')."\t".
						sprintf('%-'.$nb_char_type.'s', I18N::translate('Type'))."\t".
						sprintf('%-20s', I18N::translate('Last occurrence'))."\t".
						I18N::translate('Error').Mail::EOL.
						str_repeat('-', $nb_char_count_title)."\t".str_repeat('-', $nb_char_type)."\t".str_repeat('-', 20)."\t".str_repeat('-', strlen(I18N::translate('Error'))).Mail::EOL.
						$tmp_message.Mail::EOL;
				}
				else{
					$message .= I18N::translate('No errors', $nb_errors).Mail::EOL.Mail::EOL;
				}
				
				$tmpres = true;
				if($webmaster->getPreference('contactmethod') !== 'messaging' 
						&& $webmaster->getPreference('contactmethod') !== 'none') {
					$tmpres = Mail::systemMessage($tree, $webmaster, $subject, $message);
				}		
				$res = $tmpres && (!$one_tree_done || $one_tree_done && $res);
				$one_tree_done = true;
			}
		}
		
		return $res;
	
	
	}
        
	/**
	 * {@inheritDoc}
	 * @see \MyArtJaub\Webtrees\Module\AdminTasks\Model\ConfigurableTaskInterface::htmlConfigForm()
	 */
	public function htmlConfigForm() {	
		$html = '
			<div class="form-group">
    			<label class="control-label col-sm-3"> '.
    				I18N::translate('Enable healthcheck emails for') .
    			'</label>
    			<div class="col-sm-9">';

		foreach(Tree::getAll() as $tree){
			if(Auth::isManager($tree)){	
			    $html .= '<div class="form-group row">
			        <span class="col-sm-3 control-label">' .
			             $tree->getTitle() .
					'</span>
					 <div class="col-sm-2">';
				$html .= FunctionsEdit::editFieldYesNo('HEALTHCHECK_ENABLED_' . $tree->getTreeId(), $tree->getPreference('MAJ_AT_'.$this->getName().'_ENABLED', 1), 'class="radio-inline"');
				$html .= '</div></div>';
			}
		}
		
		$html .= '	<p class="small text-muted">'.
    					I18N::translate('Enable the health check emails for each of the selected trees.') .
    				'</p>
    			</div>
    		</div>';
			
		return $html;
	}
		
	/**
	 * {@inheritDoc}
	 * @see \MyArtJaub\Webtrees\Module\AdminTasks\Model\ConfigurableTaskInterface::saveConfig()
	 */
	public function saveConfig() {
		try {
			foreach(Tree::getAll() as $tree){		
				if(Auth::isManager($tree)){
					$tree_enabled = Filter::postInteger('HEALTHCHECK_ENABLED_' . $tree->getTreeId(), 0, 1);
					$tree->setPreference('MAJ_AT_'.$this->getName().'_ENABLED', $tree_enabled);
				}
			}
			return true;
		}
		catch (\Exception $ex) {
			Log::addErrorLog(sprintf('Error while updating the Admin Task "%s". Exception: %s', $this->getName(), $ex->getMessage()));
			return false;
		}
	}
}
 