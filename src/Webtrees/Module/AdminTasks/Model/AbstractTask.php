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

use Fisharebest\Webtrees\Log;
use MyArtJaub\Webtrees\Module\AdminTasks\Model\TaskProviderInterface;

/**
 * Abstract Class for Admin Tasks
 * 
 * @abstract
 */
abstract class AbstractTask {
    
    /**
     * Time out for runnign tasks, in seconds. Default 5 min
     * @var int TASK_TIME_OUT
     */
    const TASK_TIME_OUT = 300;
              
    /**
     * Task provider
     * @var TaskProviderInterface $provider
     */
	protected $provider;
			
	/**
	 * Task name
	 * @var string $name
	 */
    protected $name;
    
    /**
     * Status of the task
     * @var bool $is_enabled
     */
    protected $is_enabled;
    
    /**
     * Last updated date
     * @var \DateTime $last_updated
     */
    protected $last_updated;
    
    /**
     * Last run result
     * @var bool $last_result
     */
    protected $last_result;
    
    /**
     * Task run frequency
     * @var int $frequency
     */
    protected $frequency;
    
    /**
     * Task remaining runs
     * @var int $nb_occurrences
     */
    protected $nb_occurrences;
    
    /**
     * Current running status of the task
     * @var bool $is_running
     */
    protected $is_running;
    
    /**
     * Constructor for the Admin task class
	 *
	 * @param string $file Filename containing the task object
	 * @param TaskProviderInterface $provider Provider for tasks
     */
    public function __construct($file, TaskProviderInterface $provider = null){
        $this->name = trim(basename($file, '.php'));
		$this->provider = $provider;
    }
    
    /**
     * Get the provider.
     *
     * @return TaskProviderInterface 
     */
    public function getProvider(){
        return $this->provider;
    }
    
    /**
     * Set the provider.
     *
     * @param TaskProviderInterface $provider
     * @return self Enable method-chaining
     */
    public function setProvider(TaskProviderInterface $provider){
        $this->provider = $provider;
        return $this;
    }
    
    /**
     * Set parameters of the Task
     *
     * @param bool $is_enabled Status of the task
     * @param \DateTime $lastupdated Time of the last task run
     * @param bool $last_result Result of the last run, true for success, false for failure
     * @param int $frequency Frequency of execution in minutes
     * @param int $nb_occur Number of remaining occurrences, 0 for tasks not limited
     * @param bool $is_running Indicates if the task is currently running
     */
    public function setParameters($is_enabled, \DateTime $last_updated, $last_result, $frequency, $nb_occur, $is_running){
        $this->is_enabled = $is_enabled;
        $this->last_updated = $last_updated;
        $this->last_result = $last_result;
        $this->frequency = $frequency;
        $this->nb_occurrences = $nb_occur;
        $this->is_running = $is_running;
    }
    
    /**
     * Get the name of the task
     *
     * @return string
     */
    public function getName(){
        return $this->name;
    }
    
    
    /**
     * Return the status of the task in a boolean way
     *
     * @return boolean True if enabled
     */
    public function isEnabled(){
        return $this->is_enabled;
    }
    
    /**
     * Get the last updated time.
     *
     * @return \DateTime
     */
    public function getLastUpdated(){
        return $this->last_updated;
    }
    
    /**
     * Check if the last result has been successful.
     *
     * @return bool
     */
    public function isLastRunSuccess(){
        return $this->last_result;
    }
    
    /**
     * Get the task frequency.
     *
     * @return int
     */
    public function getFrequency(){
        return $this->frequency;
    }
	
	/**
     * Set the task frequency.
     *
	 * @param int $frequency
     * @return self Enable method-chaining
     */
    public function setFrequency($frequency){
        $this->frequency = $frequency;
		return $this;
    }
    
    /**
     * Get the number of remaining occurrences.
     *
     * @return int
     */
    public function getRemainingOccurrences(){
        return $this->nb_occurrences;
    }
	
	/**
     * Set the number of remaining occurrences.
     *
	 * @param int $nb_occur
     * @return self Enable method-chaining
     */
    public function setRemainingOccurrences($nb_occur){
        $this->nb_occurrences = $nb_occur;
		return $this;
    }
    
    /**
     * Check if the task if running.
     *
     * @return bool
     */
    public function isRunning(){
        return $this->is_running;
    }
    
    
    /**
     * Return the name to display for the task
     *
     * @return string Title for the task
     */
    abstract public function getTitle();
    
    /**
     * Return the default frequency for the execution of the task
     *
     * @return int Frequency for the execution of the task
     */
    abstract public function getDefaultFrequency();
    
    /**
     * Execute the task's actions
     */
    abstract protected function executeSteps();
    
	/**
	 * Persist task state into database.
	 * @return bool
	 */
	public function save() {
	    if(!$this->provider) throw new \Exception('The task has not been initialised with a provider.');
		return $this->provider->updateTask($this);
	}
	
    /**
     * Execute the task, default skeleton
     *
     */
    public function execute(){
    
        if($this->last_updated->add(new \DateInterval('PT'.self::TASK_TIME_OUT.'S')) < new \DateTime())
            $this->is_running = false;
    
        if(!$this->is_running){
            $this->last_result = false;
            $this->is_running = true;
            $this->save();
    
            Log::addDebugLog('Start execution of Admin task: '.$this->getTitle());
            $this->last_result = $this->executeSteps();
            if($this->last_result){
                $this->last_updated = new \DateTime();
                if($this->nb_occurrences > 0){
                    $this->nb_occurrences--;
                    if($this->nb_occurrences == 0) $this->is_enabled = false;
                }
            }
            $this->is_running = false;
            $this->save();
            Log::addDebugLog('Execution completed for Admin task: '.$this->getTitle().' - '.($this->last_result ? 'Success' : 'Failure'));
        }
    }
    
    
}
 