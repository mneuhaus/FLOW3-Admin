<?php

namespace Admin\OptionsProvider;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
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

use Doctrine\ORM\Mapping as ORM;
use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * OptionsProvider for the SecurityPolicies which generates policies based
 * on the active beings and actions
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @author Marc Neuhaus <marc@mneuhaus.com>
 */
class PolicyOptionsProvider extends \Admin\Core\OptionsProvider\AbstractOptionsProvider {
    /**
     * @var \Admin\Security\PolicyRepository
     * @FLOW3\Inject
     * 
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
    protected $policyRepository;
    
	/**
	 * @var \TYPO3\FLOW3\Persistence\PersistenceManagerInterface
	 * @FLOW3\Inject
     * 
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	protected $persistenceManager;

	/**
	 * Reflection service
	 * @var \TYPO3\FLOW3\Reflection\ReflectionService
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $reflectionService;
    
    public function getOptions(){
        $options = array();
        $groups = $this->helper->getGroups();
        $actions = $this->getActions();
        foreach($groups as $group => $beings){
            foreach($beings["beings"] as $being => $conf){
                foreach($actions as $action => $label){
                    $label = str_replace("@being",$conf["name"],$label);
                    $name = $group . " | " . $conf["name"] . " | " . $label;
                    $this->createOrUpdate($name, $action, $being);
                }
            }
        }
        $this->persistenceManager->persistAll();
        
        $options = $this->property->getAdapter()->getOptions($this->property->getBeing(), $this->property->getIds());
        
        return $options;
    }

    protected function createOrUpdate($name,$action,$being){
        $policy = $this->policyRepository->findOneByName($name);
        if(!is_object($policy)){
            $policy = $this->objectManager->get("Admin\Security\Policy");
            $policy->setName($name);
            $policy->setAction($action);
            $policy->setBeing($being);
            $this->policyRepository->add($policy);
        }else{
            #$policy->setName($name);
            #$policy->setAction($action);
            #$policy->setBeing($being);
            #$this->policyRepository->update($policy);
        }
    }

    public function getActions(){
        $actions = array();
        $blacklist = explode(",","index,list");
        foreach($this->reflectionService->getAllImplementationClassNamesForInterface('\Admin\Controller\Actions\ActionInterface') as $actionClassName) {
            $a = new $actionClassName();
            if(!in_array($a->getAction(),$blacklist))
                $actions[$a->getAction()] = $a->__toString();
		}
        ksort($actions);
        return $actions;
    }
}

?>