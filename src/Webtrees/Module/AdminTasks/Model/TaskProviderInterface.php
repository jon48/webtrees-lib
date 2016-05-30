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
namespace MyArtJaub\Webtrees\Module\AdminTasks\Model;

use MyArtJaub\Webtrees\Module\AdminTasks\Model\AbstractTask;

/**
 * Interface for classes implementing data access to Admin Tasks
 */
interface TaskProviderInterface {
        
	
	/**
	 * Get an Admin Task by its name.
	 * The function can only search for only enabled tasks, or all.
	 *
	 * @param string $task_name Admin Task name
	 * @param bool $only_enabled Search for only enabled Admin Taks
	 * @return AbstractTask|null
	 */
	public function getTask($task_name, $only_enabled = true);
	
	/**
	 * Set the status of a specific admin task.
	 * The status can be enabled (true), or disabled (false).
	 *
	 * @param AbstractTask $ga
	 * @param bool $status
	 */
	public function setTaskStatus(AbstractTask $task, $status);
		
    /**
     * Update an Admin Task in the database.
     * 
     * @param AbstractTask $task Task to update
     * @return bool
     */
    function updateTask(AbstractTask $task);    
    
    /**
     * Delete the task from the database, in a transactional manner.
     *
     * @param string $task_name Task to delete
     */
    public function deleteTask($task_name);
    

    /**
     * Returns the number of Admin Tasks (active and inactive).
     *
     * @return int
     */
    public function getTasksCount();
    
    /**
     * Return the list of Admin Tasks matching specified criterias.
     *
     * @param string $search Search criteria in analysis description
     * @param array $order_by Columns to order by
     * @param int $start Offset to start with (for pagination)
     * @param int|null $limit Max number of items to return (for pagination)
     * @return \MyArtJaub\Webtrees\Module\GeoDispersion\Model\GeoAnalysis[]
     */
    function getFilteredTasksList($search = null, $order_by = null, $start = 0, $limit = null);
    
    /**
     * Returns the list of tasks that are currently meant to run.
     * Tasks to run can be forced, or can be limited to only one.
     * 
     * @param string|null $force Force the enabled tasks to run.
     * @param string|null $task_name Name of the specific task to run
     */
	function getTasksToRun($force = false, $task_name = null);
		
	/**
	 * Gets and inserts in the DB the tasks for which a file exists in the tasks folder.
	 *
	 * @return array List of tasks in the folder:
	 */
	public function getInstalledTasks();
}
 