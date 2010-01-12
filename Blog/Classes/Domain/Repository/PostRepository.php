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
 * A repository for Blog Posts
 *
 * @version $Id: PostRepository.php 2881 2009-07-24 14:18:37Z k-fish $
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PostRepository extends \F3\FLOW3\Persistence\Repository {

	/**
	 * @inject
	 * @var \F3\FLOW3\Persistence\ManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * Finds posts by the specified blog
	 *
	 * @param \F3\Blog\Domain\Model\Blog $blog The blog the post must refer to
	 * @param integer $limit The number of posts to return at max
	 * @return array The posts
	 */
	public function findByBlog(\F3\Blog\Domain\Model\Blog $blog, $limit = 20) {
		$query = $this->createQuery();
		return $query->matching($query->equals('blog', $blog))
			->setOrderings(array('date' => \F3\FLOW3\Persistence\QueryInterface::ORDER_DESCENDING))
			->setLimit($limit)
			->execute();
	}

	/**
	 * Finds the previous of the given post
	 *
	 * @param \F3\Blog\Domain\Model\Post $post The reference post
	 * @return \F3\Blog\Domain\Model\Post
	 */
	public function findPrevious(\F3\Blog\Domain\Model\Post $post) {
		$query = $this->createQuery();
		$posts = $query->matching($query->lessThan('date', $post->getDate()))
			->setOrderings(array('date' => \F3\FLOW3\Persistence\QueryInterface::ORDER_DESCENDING))
			->setLimit(1)
			->execute();
		return (count($posts) == 0) ? NULL : current($posts);
	}

	/**
	 * Finds the post next to the given post
	 *
	 * @param \F3\Blog\Domain\Model\Post $post The reference post
	 * @return \F3\Blog\Domain\Model\Post
	 */
	public function findNext(\F3\Blog\Domain\Model\Post $post) {
		$query = $this->createQuery();
		$posts = $query->matching($query->greaterThan('date', $post->getDate()))
			->setOrderings(array('date' => \F3\FLOW3\Persistence\QueryInterface::ORDER_ASCENDING))
			->setLimit(1)
			->execute();
		return (count($posts) == 0) ? NULL : current($posts);
	}

	/**
	 * Finds most recent posts by the specified blog
	 *
	 * @param \F3\Blog\Domain\Model\Blog $blog The blog the post must refer to
	 * @param integer $limit The number of posts to return at max
	 * @return array The posts
	 */
	public function findRecentByBlog(\F3\Blog\Domain\Model\Blog $blog, $limit = 5) {
		$query = $this->createQuery();
		return $query->matching($query->equals('blog', $blog))
			->setOrderings(array('date' => \F3\FLOW3\Persistence\QueryInterface::ORDER_DESCENDING))
			->setLimit($limit)
			->execute();
	}
}
?>