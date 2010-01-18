<?php
 
namespace F3\Admin\ViewHelpers;

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
class ModelPropertyViewHelper extends \F3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Render the "Form"
	 *
	 *
	 * @param object $model
	 * @param string $property
	 * @return string "Form"-Tag.
	 * @api
	 */
	public function render($model, $property) {
		$propertyFunction = "get".ucfirst($property);
		#echo $propertyFunction."<br>";
		if(is_callable(array($model,$propertyFunction))){
			$result = call_user_func(array($model, $propertyFunction));
			if(is_array($result)){
				return count($result);
			}elseif(is_object($result) && get_class($result) == "SplObjectStorage"){
				return $result->count();
			}elseif(is_object($result) && method_exists($result,"getLabel")){
				return $result->getLabel();
			}elseif(is_object($result)){
				return $result->__toString();
			}else{
				return $result;
			}
		}
	}
}

?>
