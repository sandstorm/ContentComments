<?php
namespace Sandstorm\ContentComments\Aspects;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Aop\JoinPointInterface;
use Neos\ContentRepository\Domain\Model\Node;
use Neos\ContentRepository\Domain\Model\NodeData;
use Neos\ContentRepository\Domain\Model\NodeInterface;

/**
 * Hook into the Workspace and merge comments together on "replaceNodeData" call.
 *
 * @Flow\Scope("singleton")
 * @Flow\Aspect
 */
class WorkspaceAspect {

	/**
	 * @Flow\Around("method(Neos\ContentRepository\Domain\Model\Workspace->replaceNodeData())")
	 * @param \Neos\Flow\Aop\JoinPointInterface $joinPoint The current join point
	 * @return string The result of the target method if it has not been intercepted
	 */
	public function replaceNodeData(JoinPointInterface $joinPoint) {
		/** @var Node $node */
		$node = $joinPoint->getMethodArgument('sourceNode');
		if ($node->isRemoved()) {
			// If the node is supposed to be removed, we do not need to do anything as the node will be gone anyways afterwards
			return $joinPoint->getAdviceChain()->proceed($joinPoint);
		}

		/** @var NodeData $targetNodeData */
		$targetNodeData = $joinPoint->getMethodArgument('targetNodeData');

		$commentsForToBePublishedNode = $this->extractComments($node);
		$commentsInTargetWorkspace = $this->extractComments($targetNodeData);

		// Call original Method
		$result = $joinPoint->getAdviceChain()->proceed($joinPoint);

		if (count($commentsForToBePublishedNode) == 0 && count($commentsInTargetWorkspace) == 0) {
			return $result;
		}

		// After publishing the node, we update the published node with the merged comments. We cannot do this
		// before publishing, as otherwise the NodeData which is underneath the to-be-published Node will be "dirty"
		// and marked as "removed" at the same time, leading to a CR crash. This also is a CR bug which only occurs in
		// very rare occasions.
		$mergedComments = $this->mergeComments($commentsInTargetWorkspace, $commentsForToBePublishedNode);
		$this->writeComments($node, $mergedComments);

		return $result;
	}

	/**
	 * Extract comments and deserialize them
	 *
	 * @param NodeInterface|NodeData $nodeOrNodeData
	 * @return array
	 */
	protected function extractComments($nodeOrNodeData) {
		if ($nodeOrNodeData->hasProperty('comments')) {
			$comments = $nodeOrNodeData->getProperty('comments');
			if (is_string($comments) && strlen($comments) > 0) {
				return json_decode($comments, TRUE);
			}
		}
		return array();
	}

	/**
	 * Merge the $second comments array onto the $first comments array; the $second one wins. Returns the merged result.
	 *
	 * @param array $first
	 * @param array $second
	 * @return array
	 */
	protected function mergeComments($first, $second) {
		$result = [];
		foreach ($first as $value) {
			$result[$value['date'] . $value['user']] = $value;
		}

		foreach ($second as $value) {
			$result[$value['date'] . $value['user']] = $value;
		}

		ksort($result);
		return array_values($result);
	}

	/**
	 * Write back the merged comments onto the node
	 *
	 * @param NodeInterface $node
	 * @param array $mergedComments
	 */
	protected function writeComments(NodeInterface $node, $mergedComments) {
		// We directly write to the NodeData instead of Node here; as the Node is not fully correct after publishing - the.
		// node's context is still the same as before publishing.
		// (This is a bug in TYPO3CR which only manifests when trying to read the node after publishing in the same request)
		// If we would write to $node directly then we would create a copy in the user's workspace; which is not what we want effectively :)
		$node->getNodeData()->setProperty('comments', json_encode($mergedComments));
	}
}