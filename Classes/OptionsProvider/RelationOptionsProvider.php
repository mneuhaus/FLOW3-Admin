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
 * OptionsProvider for related Beings
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @author Marc Neuhaus <marc@mneuhaus.com>
 */
class RelationOptionsProvider extends \Admin\Core\OptionsProvider\AbstractOptionsProvider {
	/**
	* @var \TYPO3\FLOW3\Persistence\PersistenceManagerInterface
	* @FLOW3\Inject
	*
	* @author Marc Neuhaus <apocalip@gmail.com>
	*/
	protected $persistenceManager;
	
	/**
	* Reflection service
	* @var TYPO3\FLOW3\Reflection\ReflectionService
	* @author Marc Neuhaus <apocalip@gmail.com>
	* @FLOW3\Inject
	*/
	protected $reflectionService;
	
	public function getOptions(){
		$being = $this->property->being;
		$selected = $this->property->getIds();
		
		$options = array();
		if( is_string($being) ){
			$beings = $this->property->adapter->getBeings($being);
		}
		
		if( ! is_array($selected) )
			$selected = explode(",", $selected);
		
		if( empty($beings) )
			return array ();
			
		foreach($beings as $being) {
			$being->selected = in_array($being->id, $selected);
			$options [] = $being;
		}
		
		return $options;
	}
}

?>