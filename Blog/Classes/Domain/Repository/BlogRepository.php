<?php
namespace F3\Blog\Domain\Repository;

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
 * A repository for Blogs
 *
 * @version $Id: BlogRepository.php 3480 2009-11-19 16:45:21Z robert $
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class BlogRepository extends \F3\FLOW3\Persistence\Repository {

	/**
	 * @inject
	 * @var \F3\Blog\Domain\Repository\PostRepository
	 */
	protected $postRepository;

	/**
	 * Remove the blog's posts before removing the blog itself.
	 *
	 * @param \F3\Blog\Domain\Model\Blog
	 * @return void
	 */
	public function remove($blog) {
		foreach ($blog->getPosts() as $post) {
			$this->postRepository->remove($post);
		}
		parent::remove($blog);
	}

	/**
	 * Finds the active blog.
	 *
	 * As of now only one Blog is supported anyway so we just assume that only one
	 * Blog object resides in the Blog Repository.
	 *
	 * @return \F3\Blog\Domain\Model\Blog The active blog or FALSE if none exists
	 */
	public function findActive() {
		$query = $this->createQuery();
		$result = $query->setLimit(1)->execute();
		return current($result);
	}
}
?>