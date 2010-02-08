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
	 * Index action
	 *
	 * @return void
	 */
	public function indexAction() {
		$repository = $this->objectManager->getObject("F3\Admin\Domain\Repository\TagRepository");
		$tags = $repository->findAll();
		$added = $repository->getAddedObjects();
		if(count($tags) < 1 && count($added) < 1){
			$tags = array("typo3","flow3","php","aop","oop","ddd","mvc","reflection");
			foreach ($tags as $tag) {
				$object = $this->objectFactory->create("F3\Admin\Domain\Model\Tag");
				$object->setName($tag);
				$repository->add($object);
			}
		}
		
		$this->redirect('index',"model");
	}

}

?>
