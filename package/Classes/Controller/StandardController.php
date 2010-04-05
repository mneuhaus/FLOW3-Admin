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
		$being = $this->request->getArgument("being");
		$attributeSets = $this->getAdapter()->getAttributeSets($being);
		
		if($this->request->hasArgument("create")){
			$errors = $this->getAdapter()->createObject($being,$this->request->getArgument("item"));
			if(empty($errors)){
				$arguments = array("being"=>$this->being,"adapter" => $this->adapter);
				$this->redirect('list',NULL,NULL,$arguments);
			}else{
				foreach ($attributeSets as $set => $attributes) {
					foreach ($attributes as $key => $attribute) {
						if(array_key_exists($attribute["name"],$errors)){
							$attributeSets[$set][$key]["error"] = $errors[$attribute["name"]];
						}
					}
				}
			}
		}
		
		$this->view->assign("sets",$attributeSets);
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
		
		$objects = $this->getAdapter()->getObjects($this->being);
		
		$this->view->assign("objects",$objects);
		
		// Redirect to creating a new Object if there aren't any (Clean Slate)
		if(count($objects) < 1){
			$arguments = array( "being" => $this->being , "adapter" => $this->adapter);
			$this->redirect("create",NULL,NULL,	$arguments);
		}
	}
	
	/**
	 * Confirm previous requested action
	 *
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function confirmAction() {
		$this->prepare("confirm");
		$object = $this->getAdapter()->getObject($this->being,$this->request->getArgument("id"));
		$this->view->assign("object",$object);
	}
	
	/**
	 * delete action
	 *
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function deleteAction() {
		$this->prepare("delete");
		if($this->request->hasArgument("confirm")){
			$this->getAdapter()->deleteObject($this->being,$this->request->getArgument("id"));
			
			$arguments = array("adapter"=>$this->adapter,"being"=>$this->being);
			$this->redirect('list',NULL,NULL,$arguments);
		}else{
			$this->redirect('confirm',NULL,NULL,$this->request->getArguments());
		}
	}
	
	/**
	 * update action
	 *
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function updateAction() {
		$this->prepare("update");
		
#		if($this->request->hasArgument("delete")){
#			$arguments = $this->request->getArguments();
#			$this->redirect('confirm',NULL,NULL,$arguments);
#		}
		
		$being = $this->request->getArgument("being");
		$attributeSets = $this->getAdapter()->getAttributeSets($being);
		
		if($this->request->hasArgument("update")){
			$errors = $this->getAdapter()->updateObject($being,$this->request->getArgument("item"));
			if(empty($errors)){
				$arguments = array("being"=>$this->being,"adapter" => $this->adapter);
				$this->redirect('list',NULL,NULL,$arguments);
			}else{
				foreach ($attributeSets as $set => $attributes) {
					foreach ($attributes as $key => $attribute) {
						if(array_key_exists($attribute["name"],$errors)){
							$attributeSets[$set][$key]["error"] = $errors[$attribute["name"]];
						}
					}
				}
			}
		}
		
		$object = $this->getAdapter()->getObject($this->being,$this->request->getArgument("id"));
		foreach ($attributeSets as $set => $attributes) {
			foreach ($attributes as $key => $attribute) {
				if(array_key_exists($attribute["name"],$object["properties"])){
					$attributeSets[$set][$key]["value"] = $object["properties"][$attribute["name"]]["value"];
				}
			}
		}
		$this->view->assign("sets",$attributeSets);
	}
	
	private function prepare($action){
		\F3\Dump\Dump::getInstance();
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
		
		if($this->request->hasArgument("being")){
			$this->view->assign("being",$this->request->getArgument("being"));
			$this->view->assign("beingName",$this->getAdapter()->getName($this->request->getArgument("being")));
		}
			
		if($this->request->hasArgument("adapter")){
			$this->view->assign("adapter",$this->request->getArgument("adapter"));
		}
		
		if($this->request->hasArgument("id")){
			$this->view->assign("id",$this->request->getArgument("id"));
		}
	}
	
	private function getAdapter(){
		return $this->objectManager->getObject($this->adapter);
	}
}

?>
