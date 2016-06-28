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
namespace MyArtJaub\Webtrees\Module\AdminTasks;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Controller\AjaxController;
use Fisharebest\Webtrees\Controller\PageController;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Log;
use Fisharebest\Webtrees\Module;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Theme;
use Fisharebest\Webtrees\Theme\AdministrationTheme;
use MyArtJaub\Webtrees\Controller\JsonController;
use MyArtJaub\Webtrees\Functions\Functions;
use MyArtJaub\Webtrees\Module\AdminTasks\Model\AbstractTask;
use MyArtJaub\Webtrees\Module\AdminTasks\Model\TaskProviderInterface;
use MyArtJaub\Webtrees\Mvc\Controller\MvcController;
use MyArtJaub\Webtrees\Mvc\View\ViewBag;
use MyArtJaub\Webtrees\Mvc\View\ViewFactory;
use Rhumsaa\Uuid\Uuid;

/**
 * Controller for AdminTasks AdminConfig
 */
class AdminConfigController extends MvcController
{    
    /**
     * Tasks Provider
     * @var TaskProviderInterface $provider
     */
    protected $provider;    
    
    /**
     * Constructor for Admin Config controller
     * @param \Fisharebest\Webtrees\Module\AbstractModule $module
     */
    public function __construct(AbstractModule $module) {
        parent::__construct($module);
        
        $this->provider = $this->module->getProvider();
    }    
    
    /**
     * Pages
     */
        
    /**
     * AdminConfig@index
     */
    public function index() {
        global $WT_TREE;
        
        Theme::theme(new AdministrationTheme)->init($WT_TREE);
        $controller = new PageController();
        $controller
            ->restrictAccess(Auth::isAdmin())
            ->setPageTitle($this->module->getTitle());
			
		$token = $this->module->getSetting('MAJ_AT_FORCE_EXEC_TOKEN');
		if(is_null($token)) {
			$token = Functions::generateRandomToken();
			$this->module->setSetting('PAT_FORCE_EXEC_TOKEN', $token);
		}
        
        $data = new ViewBag();
        $data->set('title', $controller->getPageTitle());
        
        $table_id = 'table-admintasks-' . Uuid::uuid4();
        $data->set('table_id', $table_id);
		
		$data->set('trigger_url_root', WT_BASE_URL.'module.php?mod='.$this->module->getName().'&mod_action=Task@trigger');
		$token = $this->module->getSetting('MAJ_AT_FORCE_EXEC_TOKEN');
		if(is_null($token)) {
			$token = Functions::generateRandomToken();
			$this->module->setSetting('MAJ_AT_FORCE_EXEC_TOKEN', $token);
		}
		$data->set('trigger_token', $token);
		
		$this->provider->getInstalledTasks();
		
		$controller
            ->addExternalJavascript(WT_JQUERY_DATATABLES_JS_URL)
            ->addExternalJavascript(WT_DATATABLES_BOOTSTRAP_JS_URL)
            ->addInlineJavascript('
                //Datatable initialisation
				jQuery.fn.dataTableExt.oSort["unicode-asc"  ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
				jQuery.fn.dataTableExt.oSort["unicode-desc" ]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
				jQuery.fn.dataTableExt.oSort["num-html-asc" ]=function(a,b) {a=parseFloat(a.replace(/<[^<]*>/, "")); b=parseFloat(b.replace(/<[^<]*>/, "")); return (a<b) ? -1 : (a>b ? 1 : 0);};
				jQuery.fn.dataTableExt.oSort["num-html-desc"]=function(a,b) {a=parseFloat(a.replace(/<[^<]*>/, "")); b=parseFloat(b.replace(/<[^<]*>/, "")); return (a>b) ? -1 : (a<b ? 1 : 0);};
	
				var adminTasksTable = jQuery("#'.$table_id.'").DataTable({
					'.I18N::datatablesI18N().',			
					sorting: [[3, "asc"]],
					pageLength: 10,
                    processing: true,
                    serverSide : true,
					ajax : {
						url : "module.php?mod='.$this->module->getName().'&mod_action=AdminConfig@jsonTasksList&ged='. $WT_TREE->getNameUrl().'",
                        type : "POST"
					},
                    columns: [
						/* 0 Edit		 	*/ { sortable: false, className: "text-center"},
                        /* 1 task_name      */ { visible: false },
						/* 2 Enabled 		*/ { sortable: false, className: "text-center"  },
						/* 3 Task Title		*/ null,
						/* 4 Last Run		*/ null,
						/* 5 Last status 	*/ { className: "text-center" },
						/* 6 Frequency      */ { sortable: false, className: "text-center" },
						/* 7 Nb Occcurrences*/ { sortable: false, className: "text-center" },
						/* 8 Is Running    	*/ { sortable: false, className: "text-center" },					
						/* 9 Run task		*/ { sortable: false, className: "text-center" }
					],
				});
                
                ')
                ->addInlineJavascript('					
					function generate_force_token() {
						jQuery("#bt_genforcetoken").attr("disabled", "disabled");
						jQuery("#bt_tokentext").empty().html("<i class=\"fa fa-spinner fa-pulse fa-fw\"></i>");
						jQuery("#token_url").load(
							"module.php?mod='.$this->module->getName().'&mod_action=AdminConfig@generateToken",
							function() {
								jQuery("#bt_genforcetoken").removeAttr("disabled");
								jQuery("#bt_tokentext").empty().html("'.I18N::translate('Regenerate token').'");
                                adminTasksTable.ajax.reload();
							}
						);
					}
				
                    function set_admintask_status(task, status) {
                		jQuery.ajax({
                            url: "module.php", 
                            type: "GET",
                            data: {
                			    mod: "' . $this->module->getName() .'",
                                mod_action:  "Task@setStatus",
                			    task: task,
                                status: status
                            },
                            error: function(result, stat, error) {
                                var err = typeof result.responseJSON === "undefined" ? error : result.responseJSON.error;
                                alert("' . I18N::translate('An error occured while editing this task:') . '" + err);
                            },
                            complete: function(result, stat) {
                                adminTasksTable.ajax.reload(null, false);
                            }                            
                		});
                    } 
                    
                    function run_admintask(taskname) {
                        jQuery("#bt_runtask_" + taskname).attr("disabled", "disabled");
				        jQuery("#bt_runtasktext_" + taskname).empty().html("<i class=\"fa fa-cog fa-spin fa-fw\"></i><span class=\"sr-only\">'.I18N::translate('Running').'</span>");
				        jQuery("#bt_runtasktext_" + taskname).load(
					       "module.php?mod='.$this->module->getName().'&mod_action=Task@trigger&force='.$token.'&task=" + taskname,
        					function() {
        						jQuery("#bt_runtasktext_" + taskname).empty().html("<i class=\"fa fa-check\"></i>'.I18N::translate('Done').'");
        						adminTasksTable.ajax.reload();
        					}
				        );
                    
                    } 
                ');
        
        ViewFactory::make('AdminConfig', $this, $controller, $data)->render();
    }
    
    /**
     * AdminConfig@jsonTasksList
     */
    public function jsonTasksList() {
        global $WT_TREE;
    
        $controller = new JsonController();
        $controller
            ->restrictAccess(Auth::isAdmin());
    
        // Generate an AJAX/JSON response for datatables to load a block of rows
        $search = Filter::postArray('search');
        if($search) $search = $search['value'];
        $start  = Filter::postInteger('start');
        $length = Filter::postInteger('length');
        $order  = Filter::postArray('order');
    
		$order_by_name = false;
        foreach($order as $key => &$value) {
            switch($value['column']) {
                case 3:
					$order_by_name = true;
                    unset($order[$key]);
                    break;
                case 4;
					$value['column'] = 'majat_last_run';
					break;
				case 4;
					$value['column'] = 'majat_last_result';
					break;
                default:
                    unset($order[$key]);
            }
        }
    
        $list = $this->provider->getFilteredTasksList($search, $order, $start, $length);
		if($order_by_name) {
			usort($list, function(AbstractTask $a, AbstractTask $b) { return I18N::strcasecmp($a->getTitle(), $b->getTitle()); });
		}
        $recordsFiltered = count($list);
        $recordsTotal = $this->provider->getTasksCount();
    
        $data = array();
        foreach($list as $task) {    
            $datum = array();
			
            $datum[0] = '
                <div class="btn-group">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-pencil"></i><span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                       <li>
                            <a href="#" onclick="return set_admintask_status(\''. $task->getName().'\', '.($task->isEnabled() ? 'false' : 'true').');">
                                <i class="fa fa-fw '.($task->isEnabled() ? 'fa-times' : 'fa-check').'"></i> ' . ($task->isEnabled() ? I18N::translate('Disable') : I18N::translate('Enable')) . '
                            </a>
                       </li>
                        <li>
                            <a href="module.php?mod='.$this->module->getName().'&mod_action=Task@edit&task='. $task->getName().'">
                                <i class="fa fa-fw fa-pencil"></i> ' . I18N::translate('Edit') . '
                            </a>
                       </li>
                    </ul>
                </div>';
            $datum[1] = $task->getName();
            $datum[2] = $task->isEnabled() ? 
				'<i class="fa fa-check"></i><span class="sr-only">'.I18N::translate('Enabled').'</span>' : 
				'<i class="fa fa-times"></i><span class="sr-only">'.I18N::translate('Disabled').'</span>';
            $datum[3] = $task->getTitle();
            $date_format = str_replace('%', '', I18N::dateFormat()) . ' H:i:s';
			$datum[4] = $task->getLastUpdated()->format($date_format);
            $datum[5] = $task->isLastRunSuccess() ? 
				'<i class="fa fa-check"></i><span class="sr-only">'.I18N::translate('Yes').'</span>' : 
				'<i class="fa fa-times"></i><span class="sr-only">'.I18N::translate('No').'</span>';
            $dtF = new \DateTime('@0');
            $dtT = new \DateTime('@' . ($task->getFrequency() * 60));            
            $datum[6] = $dtF->diff($dtT)->format(I18N::translate('%a d %h h %i m'));
			$datum[7] = $task->getRemainingOccurrences() > 0 ? I18N::number($task->getRemainingOccurrences()) : I18N::translate('Unlimited');
			$datum[8] = $task->isRunning() ? 
				'<i class="fa fa-cog fa-spin fa-fw"></i><span class="sr-only">'.I18N::translate('Running').'</span>' : 
				'<i class="fa fa-times"></i><span class="sr-only">'.I18N::translate('Not running').'</span>';
			if($task->isEnabled() && !$task->isRunning()) {
			    $datum[9] = '
    			    <button id="bt_runtask_'. $task->getName() .'" class="btn btn-primary" href="#" onclick="return run_admintask(\''. $task->getName() .'\')">
    			         <div id="bt_runtasktext_'. $task->getName() .'"><i class="fa fa-cog fa-fw" ></i>' . I18N::translate('Run') . '</div>
    			    </button>';
			}
			else {
			    $datum[9] = '';
			}			    
						
            $data[] = $datum;
        }
    
        $controller->pageHeader();
    
        echo \Zend_Json::encode(array(
            'draw'            => Filter::getInteger('draw'),
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data
        ));
    
    }
		
	/**
	 * AdminConfig@generateToken
     *
	 * Ajax call to generate a new token. Display the token, if generated.
	 * Tokens call only be generated by a site administrator.
	 *
	 */
	public function generateToken()
	{
		$controller = new AjaxController();
		$controller->restrictAccess(Auth::isAdmin());
		
		$token = Functions::generateRandomToken();		
		$this->module->setSetting('MAJ_AT_FORCE_EXEC_TOKEN', $token);
		Log::addConfigurationLog($this->module->getTitle().' : New token generated.');
		
		$controller->pageHeader();
		echo $token;
	}
	
	
    
    
}