<?php
 
namespace F3\Admin\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "Admin".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Standard controller for the Admin package
 *
 * @version $Id: $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class StandardController extends \F3\FLOW3\MVC\Controller\ActionController {
	/**
	 * @var \F3\Admin\Helper
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @inject
	 */
	protected $helper;
	
	/**
	 * @var \F3\FLOW3\Object\ObjectManagerInterface
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @inject
	 */
	protected $objectManager;
	
	protected $model = "";
	
	/**
	 * Create action
	 *
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function createAction() {
		$this->prepare("create");
		$sets = $this->getAdapter()->getSets();
		$this->view->assign("sets",$sets);
	}
	
	/**
	 * Index action
	 *
	 * @return void
	 */
	public function indexAction() {
		$this->prepare("index");
		
		$groups = $this->helper->getGroups();
		if($this->request->hasArgument("group")){
			$group = $this->request->getArgument("group");
			$groups = array($group => $groups[$group]);
		}
		
		$this->view->assign('groups',$groups);
	}
	
	/**
	 * List action
	 *
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function listAction() {
		$this->prepare("list");
		
		// Redirect to creating a new Object if there aren't any (Clean Slate)
		#if(count($objects) < 1){
			$arguments = array("being"=>$this->being,"adapter" => $this->adapter);
			$this->redirect("create",NULL,NULL,	$arguments);
		#}
	}

	private function prepare($action){
		$this->adapters = $this->helper->getAdapters();
		$this->settings = $this->helper->getSettings();
		if($this->request->hasArgument("being")){
			$GLOBALS["Admin"]["being"] = $this->being = $this->request->getArgument("being");
			$GLOBALS["Admin"]["group"] = $this->group = $this->helper->getGroupByBeing($this->being);
		}
		if($this->request->hasArgument("adapter"))
			$GLOBALS["Admin"]["adapter"] = $this->adapter = $this->request->getArgument("adapter");
			
		$this->setTemplate($action);
	}
	
	public function setTemplate($action){
		$variant = "default";
		$replacements = array(
			"@action" => $action,
			"@variant" => $variant,
			"@package" => "Admin",
		);
		if(class_exists($this->model)){
			$tags = $this->reflectionService->getClassTagsValues($this->model);
			if(in_array($action."view",array_keys($tags))){
				$variant = $tags[$action."view"][0];
			}
			$replacements["@package"] =$this->helper->getPackageByClassName($this->model);
			$replacements["@model"] =$this->helper->getObjectNameByClassName($this->model);
		}
		
		$template = $this->helper->getPathByPatternFallbacks("Views",$replacements);
		$this->view->setTemplatePathAndFilename($template);
	}
	
	private function getAdapter(){
		return $this->objectManager->getObject($this->adapter);
	}
}

?>
