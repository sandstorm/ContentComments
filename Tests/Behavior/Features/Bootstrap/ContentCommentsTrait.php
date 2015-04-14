<?php

use PHPUnit_Framework_Assert as Assert;
use Symfony\Component\Yaml\Yaml;

/**
 * A trait with shared step definitions for common use by other contexts
 *
 * Note that this trait requires:
 * - $this->objectManager containing the Flow object manager
 * - the NodeOperationsTrait from CR
 */
trait ContentCommentsTrait {

	/**
	 * @Given /^I add a comment "([^"]*)" to this node$/
	 */
	public function iAddACommentToThisNode($comment) {
		$currentNode = $this->currentNodes[0];
		/* @var \TYPO3\TYPO3CR\Domain\Model\NodeInterface $currentNode */
		$commentsSerialized = ($currentNode->getProperty('comments') ?: '[]');
		$comments = json_decode($commentsSerialized, TRUE);

		// in sync with CommentsEditor.js
		$comments[] = array(
			'comment' => $comment,
			'date' => time(),
			'user' => rand(0, 10000)
		);

		$currentNode->setProperty('comments', json_encode($comments));
		$this->objectManager->get('TYPO3\Flow\Persistence\PersistenceManagerInterface')->persistAll();
		$this->resetNodeInstances();
	}

	/**
	 * @Then /^this node has (\d+) comments?$/
	 */
	public function thisNodeHasComments($expectedNumberOfComments) {
		$currentNode = $this->currentNodes[0];
		/* @var \TYPO3\TYPO3CR\Domain\Model\NodeInterface $currentNode */
		$commentsSerialized = ($currentNode->getProperty('comments') ?: '[]');

		$comments = json_decode($commentsSerialized, TRUE);
		Assert::assertEquals($expectedNumberOfComments, count($comments), sprintf('Expected %s number of comments, but only got %s.', $expectedNumberOfComments, count($comments)));
	}
}
