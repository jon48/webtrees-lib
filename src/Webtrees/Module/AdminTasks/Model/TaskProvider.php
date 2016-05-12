<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage AdminTasks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\AdminTasks\Model;

use Fisharebest\Webtrees\Database;
use Fisharebest\Webtrees\Log;
use MyArtJaub\Webtrees\Module\AdminTasks\Model\AbstractTask;
use MyArtJaub\Webtrees\Module\AdminTasks\Model\TaskProviderInterface;

/**
 * Provide AdminTasks data access
 */
class TaskProvider implements TaskProviderInterface {
    
    /**
     * Root path of thr folder containing the tasks
     * @var string $root_path
     */
    protected $root_path;
	    
    /**
     * Constructor for the Task provider
     * @param string $root_path
     */
    public function __construct($root_path) {
        $this->root_path = $root_path;
		$this->all_tasks = null;
    }
	
    /**
     * Load a task object from a file.
     * 
     * @param string $task_name Name of the task to load.
     */
	protected function loadTask($task_name) {
		try {
			if (file_exists($this->root_path . $task_name .'.php')) {
				$task = include $this->root_path . $task_name .'.php';
				if($task instanceof AbstractTask) {
				    $task->setProvider($this);
					return $task;
				}
			}
		}
		catch(\Exception $ex) { }
		
		return null;
	}
	
    /**
     * Creates and returns a Task object from a data row.
     * The row data is expected to be an array with the indexes:
     *  - majat_name: task name
     *  - majat_status: task status
     *  - majat_last_run: last run time
     *  - majat_last_result: last run result
     *  - majat_frequency: run frequency
     *  - majat_nb_occur: remaining running occurrences
     *  - majat_running: is task running
     *
     * @param array $row
     * @return AbstractTask|null
     */
    protected function loadTaskFromRow($row) {
        $task = $this->loadTask($row['majat_name']);
        
		if($task) {
			$task->setParameters(
				$row['majat_status'] == 'enabled',
				new \DateTime($row['majat_last_run']), 
				$row['majat_last_result'] == 1,
				$row['majat_frequency'],
				$row['majat_nb_occur'],
				$row['majat_running'] == 1
				);
        
			return $task;
		}
		else {
		    $this->deleteTask($row['majat_name']);
		}
		return null;
    }
    
    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\AdminTasks\Model\TaskProviderInterface::getTask()
     */
    public function getTask($task_name, $only_enabled = true) {
        $args = array (
            'task_name' => $task_name
        );
    
        $sql = 'SELECT majat_name, majat_status, majat_last_run, majat_last_result, majat_frequency, majat_nb_occur, majat_running' .
            ' FROM `##maj_admintasks`' .
            ' WHERE majat_name = :task_name';
        if($only_enabled) {
            $sql .= ' AND majat_status = :status';
            $args['status'] = 'enabled';
        }
    
        $task_array = Database::prepare($sql)->execute($args)->fetchOneRow(\PDO::FETCH_ASSOC);
    
        if($task_array) {
            return $this->loadTaskFromRow($task_array);
        }
    
        return null;
    }
    
	/**
	 * {@inheritDoc}
	 * @see \MyArtJaub\Webtrees\Module\AdminTasks\Model\TaskProviderInterface::setTaskStatus()
	 */
    public function setTaskStatus(AbstractTask $task, $status) {
        Database::prepare(
            'UPDATE `##maj_admintasks`'.
            ' SET majat_status = :status'.
            ' WHERE majat_name = :name'
        )->execute(array(
                'name' => $task->getName(),
				'status' => $status ? 'enabled' : 'disabled'
        ));
    }
	
   /**
    * {@inheritDoc}
    * @see \MyArtJaub\Webtrees\Module\AdminTasks\Model\TaskProviderInterface::updateTask()
    */
    public function updateTask(AbstractTask $task) {        
        try{
            Database::prepare(
                'UPDATE `##maj_admintasks`'.
                ' SET majat_status = :status,'.
                ' majat_last_run = :last_run,'.
                ' majat_last_result = :last_result,'.
                ' majat_frequency = :frequency,'.
                ' majat_nb_occur = :nb_occurrences,'.
                ' majat_running = :is_running'.
                ' WHERE majat_name = :name'
                )->execute(array(
                    'name' => $task->getName(),
                    'status' => $task->isEnabled() ? 'enabled' : 'disabled',
                    'last_run' => $task->getLastUpdated()->format('Y-m-d H:i:s'),
                    'last_result' =>  $task->isLastRunSuccess() ? 1 : 0,
                    'frequency' => $task->getFrequency(),
                    'nb_occurrences' => $task->getRemainingOccurrences(),
                    'is_running' => $task->isRunning() ? 1 : 0
                ));
            return true;
        }
        catch (\Exception $ex) {
            Log::addErrorLog(sprintf('Error while updating the Admin Task %s. Exception: %s', $task->getName(), $ex->getMessage()));
            return false;
        }        
    }
	
	/**
	 * {@inheritDoc}
	 * @see \MyArtJaub\Webtrees\Module\AdminTasks\Model\TaskProviderInterface::getTasksCount()
	 */
    public function getTasksCount() {
        return Database::prepare(
            'SELECT COUNT(majat_name)' .
            ' FROM `##maj_admintasks`'
            )->execute()->fetchOne();
    }
	
    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\AdminTasks\Model\TaskProviderInterface::getTasksToRun()
     */
	public function getTasksToRun($force = false, $task_name = null) 
	{
		$res = array();
		
		$sql = 
			'SELECT majat_name, majat_status, majat_last_run, majat_last_result, majat_frequency, majat_nb_occur, majat_running
			FROM `##maj_admintasks`
			WHERE majat_status = :status
			AND (majat_running = :is_running OR DATE_ADD(majat_last_run, INTERVAL :time_out SECOND) <= NOW())';
		
		$args = array(
			'status' => 'enabled',
			'is_running' => 0,
			'time_out' => AbstractTask::TASK_TIME_OUT		
		);
		
		if(!$force) {
			$sql .= ' AND (DATE_ADD(majat_last_run, INTERVAL majat_frequency MINUTE) <= NOW() OR majat_last_result = 0)';
		}
		
		if($task_name) {
			$sql .= ' AND majat_name = :task_name';
			$args['task_name'] = $task_name;
		}
		
		$data = Database::prepare($sql)->execute($args)->fetchAll(\PDO::FETCH_ASSOC);
		
		foreach($data as $task_row) {
            $task = $this->loadTaskFromRow($task_row);
			if($task)
			{
				$res[] = $task;
			} 
        }
		
		return $res;	
	}
	
	/**
	 * {@inheritDoc}
	 * @see \MyArtJaub\Webtrees\Module\AdminTasks\Model\TaskProviderInterface::getFilteredTasksList()
	 */
    public function getFilteredTasksList($search = null, $order_by = null, $start = 0, $limit = null){
        $res = array();
            
        $sql = 'SELECT majat_name, majat_status, majat_last_run, majat_last_result, majat_frequency, majat_nb_occur, majat_running' .
            ' FROM `##maj_admintasks`';
        
        $args = array();
                
        if ($order_by) {
            $sql .= ' ORDER BY ';
            $i = 0;
            foreach ($order_by as $key => $value) {
                if ($i > 0) {
                    $sql .= ',';
                }
                
                switch ($value['dir']) {
                    case 'asc':
                        $sql .= $value['column'] . ' ASC ';
                        break;
                    case 'desc':
                        $sql .= $value['column'] . ' DESC ';
                        break;
                }
                $i++;
            }
        } else {
            $sql .= ' ORDER BY majat_name ASC';
        }
        
        if ($limit) {
            $sql .= ' LIMIT :limit OFFSET :offset';
            $args['limit']  = $limit;
            $args['offset'] = $start;
        }
            
        $data = Database::prepare($sql)->execute($args)->fetchAll(\PDO::FETCH_ASSOC);

        foreach($data as $ga) {
            $task = $this->loadTaskFromRow($ga);
			if($task && (empty($search) || ($search && strpos($task->getTitle(), $search) !== false)))
			{
				$res[] = $task;
			}
        }
		
        return $res;
    }
	
	
    /**
     * {inhericDoc}
     * @see \MyArtJaub\Webtrees\Module\AdminTasks\Model\TaskProviderInterface::getInstalledTasks()
     */
	public function getInstalledTasks() {
		$tasks=array();
		$dir=opendir($this->root_path);
		while (($file=readdir($dir))!==false){ 
			try {
			    if($file == '.' || $file == '..') continue;
				$task = include $this->root_path . $file;
				if($task ) {
				    $task->setProvider($this);
					Database::prepare(
						'INSERT IGNORE INTO `##maj_admintasks`'.
						' (majat_name, majat_status, majat_frequency)'.
						' VALUES (:task_name, :status, :frequency)'
					)->execute(array(
						'task_name' => $task->getName(), 
						'status' => 'disabled',
						'frequency' => $task->getDefaultFrequency()
					));
					
					$tasks[] = $task;
				}
				else {
					throw new \Exception;
				}
			}
			catch (\Exception $ex) {
				Log::addErrorLog('An error occured while trying to load the task in file ' . $file . '. Exception: ' . $ex->getMessage());
			}
		}
		return $tasks;
	}	
    	
	
	/**
	 * {inhericDoc}
	 * @see \MyArtJaub\Webtrees\Module\AdminTasks\Model\TaskProviderInterface::deleteTask()
	 */
	public function deleteTask($task_name){
		try{
			Database::beginTransaction();
			
			Database::prepare('DELETE FROM  `##maj_admintasks` WHERE majat_name= :task_name')
				->execute(array('task_name' => $task_name));
			Database::prepare('DELETE FROM  `##gedcom_setting` WHERE setting_name LIKE :setting_name')
				->execute(array('setting_name' => 'MAJ_AT_' . $task_name .'%'));
				
			Database::commit();
			
			Log::addConfigurationLog('Admin Task '.$task_name.' has been deleted from disk - deleting it from DB');
			
			return true;
		}
		catch(\Exception $ex) {
			Database::rollback();
		
			Log::addErrorLog('An error occurred while deleting Admin Task '.$task_name.'. Exception: '. $ex->getMessage());
			return false;
		}
	}
	
    
}
 