<?php
 
namespace F3\Blog\ViewHelpers;

/*                                                                        *
 * This script belongs to the FLOW3 package "Blog".                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License as published by the Free   *
 * Software Foundation, either version 3 of the License, or (at your      *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        *
 * You should have received a copy of the GNU General Public License      *
 * along with the script.                                                 *
 * If not, see http://www.gnu.org/licenses/gpl.html                       *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * A view helper to display a Gravatar
 *
 * = Examples =
 *
 * <code title="Simple">
 * <blog:gravatar email="{emailAddress}" default="http://domain.com/gravatar_default.gif" class="gravatar" />
 * </code>
 *
 * Output:
 * <img class="gravatar" src="http://www.gravatar.com/avatar/<hash>?d=http%3A%2F%2Fdomain.com%2Fgravatar_default.gif" />
 *
 * @version $Id: GravatarViewHelper.php 3453 2009-11-05 13:56:17Z k-fish $
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @scope prototype
 */
class GravatarViewHelper extends \F3\Fluid\Core\ViewHelper\TagBasedViewHelper {

	/**
	 * @var string
	 */
	protected $tagName = 'img';

	/**
	 * Initialize arguments
	 *
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('email', 'string', 'Gravatar Email', TRUE);
		$this->registerArgument('default', 'string', 'Default URL if no gravatar was found');
		$this->registerArgument('size', 'Integer', 'Size of the gravatar');

		$this->registerUniversalTagAttributes();
	}

	/**
	 * Render the link.
	 *
	 * @return string The rendered link
	 */
	public function render() {
		$baseUri = $this->controllerContext->getRequest()->getBaseUri();
		$gravatarUri = 'http://www.gravatar.com/avatar/' . md5((string)$this->arguments['email']);
		$uriParts = array();
		if ($this->arguments['default']) {
			$uriParts[] = 'd=' . urlencode($baseUri . $this->arguments['default']);
		}
		if ($this->arguments['size']) {
			$uriParts[] = 's=' . $this->arguments['size'];
		}
		if (count($uriParts)) {
			$gravatarUri .= '?' . implode('&', $uriParts);
		}

		$this->tag->addAttribute('src', $gravatarUri);
		return $this->tag->render();
	}
}


?>