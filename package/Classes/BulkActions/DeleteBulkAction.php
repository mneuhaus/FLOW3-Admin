<?php
 
namespace F3\Admin\BulkActions;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
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
 * @api
 * @scope prototype
 */
class DeleteBulkAction extends \F3\FLOW3\MVC\Controller\ActionController{
	/**
	 * @var \F3\FLOW3\Persistence\ManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * Injects the FLOW3 Persistence Manager
	 *
	 * @param \F3\FLOW3\Persistence\ManagerInterface $persistenceManager
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectPersistenceManager(\F3\FLOW3\Persistence\ManagerInterface $persistenceManager) {
		$this->persistenceManager = $persistenceManager;
	}

	/**
	 * @var \F3\FLOW3\Object\ManagerInterface
	 * @api
	 */
	protected $objectManager;

	/**
	 * Injects the object manager
	 *
	 * @param \F3\FLOW3\Object\ManagerInterface $manager
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectManager(\F3\FLOW3\Object\ManagerInterface $manager) {
		$this->objectManager = $manager;
	}

	/**
	 *
	 */
    public function action($identifiers){
		foreach($identifiers as $identifier){
			$object = $this->persistenceManager->getBackend()->getObjectByIdentifier($identifier);

			$repository = str_replace(array("Domain\Model","_AOPProxy_Development"),array("Domain\Repository",""),get_class($object)) . "Repository";
            if(class_exists($repository)){
                $repositoryObject = $this->objectManager->getObject($repository);
                $repositoryObject->remove($object);
                $this->flashMessageContainer->add('Removed selected Items.');
            }else{
                $this->flashMessageContainer->add('Model Repository not Found');
            }
		}
    }
}

?>
