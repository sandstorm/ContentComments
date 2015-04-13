<?php

use Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use PHPUnit_Framework_Assert as Assert;
use Symfony\Component\Yaml\Yaml;
use TYPO3\Flow\Utility\Arrays;
use TYPO3\Neos\EventLog\Domain\Model\Event;

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
		$notesSerialized = ($currentNode->getProperty('notes') ?: '[]');
		$notes = json_decode($notesSerialized, TRUE);

		// in sync with CommentsEditor.js
		$notes[] = array(
			'comment' => $comment,
			'date' => time(),
			'user' => rand(0, 10000)
		);

		$currentNode->setProperty('notes', json_encode($notes));
		$this->objectManager->get('TYPO3\Flow\Persistence\PersistenceManagerInterface')->persistAll();
		$this->resetNodeInstances();
	}

	/**
	 * @Then /^this node has (\d+) comments?$/
	 */
	public function thisNodeHasComments($expectedNumberOfComments) {
		$currentNode = $this->currentNodes[0];
		/* @var \TYPO3\TYPO3CR\Domain\Model\NodeInterface $currentNode */
		$notesSerialized = ($currentNode->getProperty('notes') ?: '[]');

		$notes = json_decode($notesSerialized, TRUE);
		Assert::assertEquals($expectedNumberOfComments, count($notes), sprintf('Expected %s number of comments, but only got %s.', $expectedNumberOfComments, count($notes)));
	}
}
