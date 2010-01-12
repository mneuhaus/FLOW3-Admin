<?php
namespace F3\Blog\Domain\Validator;

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
 * A Blogvalidator
 *
 * @version $Id: BlogValidator.php 3546 2009-12-15 15:26:47Z k-fish $
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @scope singleton
 */
class BlogValidator extends \F3\FLOW3\Validation\Validator\AbstractValidator {

	/**
	 * If the given blog is valid
	 *
	 * @param \F3\Blog\Domain\Model\Blog $blog The blog
	 * @return boolean true
	 */
	public function isValid($blog) {
		if (!$blog instanceof \F3\Blog\Domain\Model\Blog) {
			$this->addError('The blog is not a blog', 1);
			return FALSE;
		}
		return TRUE;
	}

}
?>