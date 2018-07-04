<?php
/**
 * Created by IntelliJ IDEA.
 * User: sebastian
 * Date: 18.05.15
 * Time: 07:16
 */

namespace Sandstorm\ContentComments;

use Neos\Flow\Annotations as Flow;
use Neos\Neos\Service\DataSource\AbstractDataSource;
use Neos\ContentRepository\Domain\Model\NodeInterface;


class CommentingCurrentUserDataSource extends AbstractDataSource {


	static protected $identifier = 'commenting-current-user';

	/**
	 * @Flow\Inject
	 * @var \Neos\Neos\Service\UserService
	 */
	protected $userService;

	/**
	 * Get data
	 *
	 * @param NodeInterface $node The node that is currently edited (optional)
	 * @param array $arguments Additional arguments (key / value)
	 * @return mixed JSON serializable data
	 * @api
	 */
	public function getData(NodeInterface $node = NULL, array $arguments) {
		return array('name' => $this->userService->getBackendUser()->getName()->getFullName());
	}
}