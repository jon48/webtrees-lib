<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage AdminTasks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\AdminTasks;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Controller\AjaxController;
use Fisharebest\Webtrees\Controller\PageController;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\Html;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Log;
use Fisharebest\Webtrees\Module;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Theme;
use Fisharebest\Webtrees\Theme\AdministrationTheme;
use MyArtJaub\Webtrees\Controller\JsonController;
use MyArtJaub\Webtrees\Module\AdminTasks\Model\ConfigurableTaskInterface;
use MyArtJaub\Webtrees\Module\AdminTasks\Model\TaskProviderInterface;
use MyArtJaub\Webtrees\Mvc\Controller\MvcController;
use MyArtJaub\Webtrees\Mvc\View\ViewBag;
use MyArtJaub\Webtrees\Mvc\View\ViewFactory;
use MyArtJaub;

/**
 * Controller for Tasks
 */
class TaskController extends MvcController
{    
    /**
     * Tasks Provider
     * @var TaskProviderInterface $provider
     */
    protected $provider;    
    
    /**
     * Constructor for Admin Config controller
     * @param AbstractModule $module
     */
    public function __construct(AbstractModule $module) {
        parent::__construct($module);
        
        $this->provider = $this->module->getProvider();
    }    
    
    /**
     * Pages
     */        
	
	/**
	 * Task@trigger
	 */
	public function trigger() 
	{
		$controller = new AjaxController();
		$controller->pageHeader();
		
		$task_name = Filter::get('task');
		$token_submitted = Filter::get('force');
		$token = $this->module->getSetting('MAJ_AT_FORCE_EXEC_TOKEN');
		
		$tasks = $this->provider->getTasksToRun($token == $token_submitted, $task_name);
		
		foreach($tasks as $task) {
			$task->execute();		
		}
	}	
	
	/**
	 * Task@setStatus
	 */
    public function setStatus() {          
        $controller = new JsonController();
        
        $task_name = Filter::get('task');
        $task = $this->provider->getTask($task_name, false);
        
        $controller->restrictAccess(
            true // Filter::checkCsrf()   -- Cannot use CSRF on a GET request (modules can only work with GET requests)
            &&  Auth::isAdmin() 
            && $task
        );
        
        $status = Filter::getBool('status');
        $res = array('task' => $task->getName() , 'error' => null);
        try{
            $this->provider->setTaskStatus($task, $status);
            $res['status'] = $status;
			Log::addConfigurationLog('Module '.$this->module->getName().' : Admin Task "'.$task->getName().'" has been '. ($status ? 'enabled' : 'diabled') .'.');
        }
        catch (\Exception $ex) {
            $res['error'] = $ex->getMessage();
			Log::addErrorLog('Module '.$this->module->getName().' : Admin Task "'.$task->getName().'" could not be ' . ($status ? 'enabled' : 'diabled') .'. Error: '. $ex->getMessage());
        }
        
        $controller->pageHeader();
        if($res['error']) http_response_code(500);
        
        echo \Zend_Json::encode($res);
    }
	
	/**
	 * Task@edit
	 */
	public function edit() {
		global $WT_TREE;
        		
        $task_name = Filter::get('task');
        $task = $this->provider->getTask($task_name, false);
		
        Theme::theme(new AdministrationTheme)->init($WT_TREE);
        $controller = new PageController();        
        $controller
            ->restrictAccess(Auth::isAdmin() && $task)
			->setPageTitle(I18N::translate('Edit the administrative task'))
            ->addInlineJavascript('
                function toggleRemainingOccurrences() {
                    if($("input:radio[name=\'is_limited\']:checked").val() == 1) {
                        $("#nb_occurences").show();
                    }
                    else {
                        $("#nb_occurences").hide();
                    }
                }
        
                $("[name=\'is_limited\']").on("change", toggleRemainingOccurrences);
                toggleRemainingOccurrences();
            ')
        ;
        
        
        $data = new ViewBag();        
        $data->set('title', $controller->getPageTitle());
		$data->set('admin_config_url', 'module.php?mod=' . $this->module->getName() . '&mod_action=AdminConfig&ged=' . $WT_TREE->getNameUrl());
        $data->set('module_title', $this->module->getTitle());
		$data->set('save_url', 'module.php?mod=' . $this->module->getName() . '&mod_action=Task@save&ged=' . $WT_TREE->getNameUrl());
		$data->set('task', $task);
		    
        ViewFactory::make('TaskEdit', $this, $controller, $data)->render();	
	}	
	
	/**
	 * Task@save
	 */
	public function save() {		
        $tmp_contrl = new PageController();
				
        $tmp_contrl->restrictAccess(
            Auth::isAdmin() 
            && Filter::checkCsrf()
         );
        
		$task_name      = Filter::post('task');
        $frequency    	= Filter::postInteger('frequency');
        $is_limited  	= Filter::postInteger('is_limited', 0, 1);
        $nb_occur       = Filter::postInteger('nb_occur');
				
		$task = $this->provider->getTask($task_name, false);
        
        $success = false; 
        if($task) {
			$task->setFrequency($frequency);
			if($is_limited == 1) {
				$task->setRemainingOccurrences($nb_occur);
			}
			else {
				$task->setRemainingOccurrences(0);
			}
			
			$res = $task->save();
						
			if($res) {						
				if($task instanceof MyArtJaub\Webtrees\Module\AdminTasks\Model\ConfigurableTaskInterface) {
					$res = $task->saveConfig();
					
					if(!$res) {
						FlashMessages::addMessage(I18N::translate('An error occured while updating the specific settings of administrative task “%s”', $task->getTitle()), 'danger');
						Log::addConfigurationLog('Module '.$this->module->getName().' : AdminTask “'. $task->getName() .'” specific settings could not be updated. See error log.');
					}
				}
			
				if($res) {
					FlashMessages::addMessage(I18N::translate('The administrative task “%s” has been successfully updated', $task->getTitle()), 'success');
					Log::addConfigurationLog('Module '.$this->module->getName().' : AdminTask “'.$task->getName() .'” has been updated.');
					$success = true;
				}
			}
			else {
				FlashMessages::addMessage(I18N::translate('An error occured while updating the administrative task “%s”', $task->getTitle()), 'danger');
				Log::addConfigurationLog('Module '.$this->module->getName().' : AdminTask “'. $task->getName() .'” could not be updated. See error log.');
			}
			
        }
        
        $redirection_url = 'module.php?mod=' . $this->module->getName() . '&mod_action=AdminConfig';
        if(!$success) {
			$redirection_url = 'module.php?mod=' . $this->module->getName() . '&mod_action=Task@edit&task='. $task->getName();
        }        
        header('Location: ' . WT_BASE_URL . $redirection_url);
	}
     
}