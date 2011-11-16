<?php
namespace Admin\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "Admin.System".               *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Standard controller for the Admin.System package 
 *
 * @FLOW3\Scope("singleton")
 */
class SystemController extends \TYPO3\FLOW3\MVC\Controller\ActionController {

	/**
	 * @FLOW3\Inject
	 * @var \Admin\Service\DoctrineService
	 */
	protected $doctrineService;
	
	/**
	 * @var \TYPO3\FLOW3\Package\PackageManagerInterface
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $packageManager;
	
	/**
	 *
	 * @param boolean $migrate
	 * @return void
	 * @author Marc Neuhaus
	 * @Admin\Annotations\Navigation(title="System", position="top", priority="10")
	 * @Admin\Annotations\Navigation(title="Overview", position="system:left", priority="200")
	 * @Admin\Annotations\Access(admin="true")
	 */
	public function indexAction($migrate = false){
		if($migrate){
			$this->doctrineService->migrateToLatest();
		}
		
		$status = $this->doctrineService->getMigrationStatus();
		$this->view->assign("status", $status);
		
		$schemaDiff = $this->doctrineService->getDatabaseDiff();
		
		$changes = array();
		if(empty($stats["migrations-waiting"])){
			$labels = array(
				"newTables"				=> "New Tables",
				"changedTables"			=> "Changed Tables",
				"removedTables"			=> "Removed Tables",
				"newSequences"			=> "New Sequences",
				"changedSequences"		=> "Changed Sequences",
				"removedSequences"		=> "Removed Sequences",
				"orphanedForeignKeys"	=> "Orphaned Sequences"
			);
			foreach (get_object_vars($schemaDiff) as $key => $value) {
				if(!empty($value))
					$changes[$labels[$key]] = implode(",", array_keys($value));
			}
		}
		$this->view->assign("changes", $changes);
	}
	
	/**
	 *
	 * @param array $tables 
	 * @param string $package
	 * @return void
	 * @author Marc Neuhaus
	 * @Admin\Annotations\Navigation(title="Migration Generator", position="system:left", priority="10")
	 * @Admin\Annotations\Access(admin="true")
	 */
	public function schemaAction($tables = array(), $package = null){
		if(count($tables) > 0){
			$this->doctrineService->generateAndExecutePartialMigrationFor($tables, $package);
		}
		
		$schemaDiff = $this->doctrineService->getDatabaseDiff();
		$this->view->assign("schemaDiff", $schemaDiff);
		
		$packages = $this->packageManager->getActivePackages();
		$this->view->assign("packages", $packages);
	}
}

?>