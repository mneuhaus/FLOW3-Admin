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
	 * @param array $tables 
	 * @param string $package
	 * @return void
	 * @author Marc Neuhaus
	 * @Admin\Annotations\Navigation(title="System", position="top", priority="10")
	 */
	public function indexAction($tables = array(), $package = null){
#		$user = $this->helper->getUser();
		
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