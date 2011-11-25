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
class ArrayOptionsProvider extends \Admin\Core\OptionsProvider\AbstractOptionsProvider {
	
	public function getOptions(){
		$being = $this->property->parent;
		$options = array();
		
		if(isset($this->property->optionsProvider->property))
			$optionsProperty = $this->property->optionsProvider->property;
		else
			$optionsProperty = "_" . $this->property->name;
		
		$rawOptions = $being->getValue($optionsProperty);
		if(is_array($rawOptions)){
			foreach($rawOptions as $key => $value) {
				$options[] = new \Admin\Core\Option($key, $value, $key == $this->property->getValue());
			}
		}
		
		return $options;
	}
}

?>