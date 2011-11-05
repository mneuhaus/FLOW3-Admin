<?php
namespace Admin\ViewHelpers;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


/**
 * View helper which renders the flash messages (if there are any) as an unsorted list.
 *
 * In case you need custom Flash Message HTML output, please write your own ViewHelper for the moment.
 *
 *
 * = Examples =
 *
 * <code title="Simple">
 * <f:flashMessages />
 * </code>
 * Renders an ul-list of flash messages.
 *
 * <code title="Output with css class">
 * <f:flashMessages class="specialClass" />
 * </code>
 * <output>
 * <ul class="specialClass">
 *  ...
 * </ul>
 * </output>
 *
 * @api
 */
class FlashMessagesViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper {	
	var $classMapping = array(
		"TYPO3\FLOW3\Error\Message" => "success",
		"TYPO3\FLOW3\Error\Notice" 	=> "info",
		"TYPO3\FLOW3\Error\Warning" => "warning",
		"TYPO3\FLOW3\Error\Error" 	=> "error",
	);
	
	/**
	 * Render method.
	 *
	 * @return string rendered Flash Messages, if there are any.
	 * @api
	 */
	public function render($class= "alert-message") {
		$flashMessages = $this->controllerContext->getFlashMessageContainer()->getMessagesAndFlush();
		if (count($flashMessages) > 0) {
			$tagContent = '';
			foreach ($flashMessages as $singleFlashMessage) {
				if(isset($this->classMapping[get_class($singleFlashMessage)]))
					$tmpClass = $class . " " . $this->classMapping[get_class($singleFlashMessage)];
				$tagContent .=  '<div class="' . $tmpClass . '">' . htmlspecialchars($singleFlashMessage) . '</div>';
			}
			return $tagContent;
		}
		return '';
	}
}

?>
