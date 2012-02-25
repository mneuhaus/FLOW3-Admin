<?php
namespace Admin\SearchProvider;

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

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * OptionsProvider for the SecurityPolicies which generates policies based
 * on the active beings and actions
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @author Marc Neuhaus <marc@mneuhaus.com>
 */
class SimpleSearchProvider extends AbstractSearchProvider{
	/**
	 * @var \Admin\Core\ConfigurationManager
	 * @FLOW3\Inject
	 */
	protected $configurationManager;
	
	public function search($search, $query){
		$constraints = array();
		$configuration = $this->configurationManager->getClassConfiguration($query->getType());
		foreach ($configuration["properties"] as $property => $annotations) {
			if(isset($annotations["search"])){
				$constraints[] = $query->like($property, "%" . $search . "%", false);
			}
		}
		$query->matching($query->logicalOr($constraints));
		
		return $query;
	}
}

?>