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
class IdentifierInputViewHelper extends \F3\Fluid\Core\ViewHelper\TagBasedViewHelper {

	/**
	 * @var \F3\FLOW3\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * Injects the FLOW3 Persistence Manager
	 *
	 * @param \F3\FLOW3\Persistence\PersistenceManagerInterface $persistenceManager
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectPersistenceManager(\F3\FLOW3\Persistence\PersistenceManagerInterface $persistenceManager) {
		$this->persistenceManager = $persistenceManager;
	}

	/**
	 * Prefixes / namespaces the given name with the form field prefix
	 *
	 * @param string $fieldName field name to be prefixed
	 * @return string namespaced field name
	 */
	protected function prefixFieldName($fieldName) {
		if ($fieldName === NULL || $fieldName === '') {
			return '';
		}
		if (!$this->viewHelperVariableContainer->exists('F3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')) {
			return $fieldName;
		}
		$fieldNamePrefix = (string)$this->viewHelperVariableContainer->get('F3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix');
		if ($fieldNamePrefix === '') {
			return $fieldName;
		}
		$fieldNameSegments = explode('[', $fieldName, 2);
		$fieldName = $fieldNamePrefix . '[' . $fieldNameSegments[0] . ']';
		if (count($fieldNameSegments) > 1) {
			$fieldName .= '[' . $fieldNameSegments[1];
		}
		return $fieldName;
	}

	/**
	 * Renders a hidden form field containing the technical identity of the given object.
	 *
	 * @param object $object
	 * @param string $type
	 * @param string $name
	 * @param string $value
	 */
	public function render($object, $type="hidden", $name="", $value=null) {
		if (!is_object($object)
			|| !($object instanceof \F3\FLOW3\Persistence\Aspect\DirtyMonitoringInterface)
			|| ($object->FLOW3_Persistence_isNew() && !$object->FLOW3_Persistence_isClone())
			){
			return '';
		}
		$identifier = $this->persistenceManager->getIdentifierByObject($object);
		if ($identifier === NULL) {
			return chr(10) . '<!-- Object of type ' . get_class($object) . ' is without identity -->' . chr(10);
		}
		$name = $this->prefixFieldName($name) . '[__identity]['.$identifier.']';

        if($value == null){
            $value = $identifier;
        }

		return chr(10) . '<input type="'.$type.'" name="'. $name . '" value="' . $value .'" />' . chr(10);
	}
}

?>
