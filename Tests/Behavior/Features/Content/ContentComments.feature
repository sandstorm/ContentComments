Feature: Commenting Content or Documents
  In order to collect additional thoughts about content, such as:
    - a TODO list
    - a content review comment
    - a comment for a reviewer
  As an editor
  I want to be able to comment on content.

  Background:
    Given I have the following nodes:
      | Identifier                           | Path                                     | Node Type                      | Properties              | Workspace |
      | ecf40ad1-3119-0a43-d02e-55f8b5aa3c70 | /sites                                   | unstructured                   |                         | live      |
      | fd5ba6e1-4313-b145-1004-dad2f1173a35 | /sites/neosdemotypo3                     | TYPO3.Neos.NodeTypes:Page      | {"title": "Home"}       | live      |
      | ece553d3-57f8-4a0b-82fe-190a1bf1d8e7 | /sites/neosdemotypo3/twocol              | TYPO3.Neos.NodeTypes:TwoColumn | {}                      | live      |
      | b05de125-fc63-440d-ad08-716d148b9d36 | /sites/neosdemotypo3/twocol/column0/text | TYPO3.Neos.NodeTypes:Text      | {"text": "Hello world"} | live      |

  @fixtures
  Scenario: Add a comment to a node adds the comment in the user workspace
    When I get a node by path "/sites/neosdemotypo3/twocol/column0/text" with the following context:
      | Workspace |
      | user-demo |
    And I add a comment "Hallo Welt" to this node
    Then I expect to have 1 unpublished node for the following context:
      | Workspace |
      | user-demo |

  @fixtures
  Scenario: An added comment is not visible in another workspace before publishing
    When I get a node by path "/sites/neosdemotypo3/twocol/column0/text" with the following context:
      | Workspace |
      | user-demo |
    And I add a comment "Hallo Welt" to this node
    And I get a node by path "/sites/neosdemotypo3/twocol/column0/text" with the following context:
      | Workspace   |
      | user-second |
    Then this node has 0 comments

  @fixtures
  Scenario: An added comment is visible in another workspace after publishing
    When I get a node by path "/sites/neosdemotypo3/twocol/column0/text" with the following context:
      | Workspace |
      | user-demo |
    And I add a comment "Hallo Welt" to this node
    And I publish the workspace "user-demo"
    And I get a node by path "/sites/neosdemotypo3/twocol/column0/text" with the following context:
      | Workspace   |
      | user-second |
    Then this node has 1 comment

  @fixtures
  Scenario: If two people write comments at the same time on the same node, they do not override each other.
    When I get a node by path "/sites/neosdemotypo3/twocol/column0/text" with the following context:
      | Workspace |
      | user-demo |
    And I add a comment "Hallo Welt" to this node
    And I get a node by path "/sites/neosdemotypo3/twocol/column0/text" with the following context:
      | Workspace |
      | user-second |
    And I add a comment "Comment by second user" to this node
    And I publish the workspace "user-demo"
    And I publish the workspace "user-second"
    And I get a node by path "/sites/neosdemotypo3/twocol/column0/text" with the following context:
      | Workspace   |
      | user-second |
    Then this node has 2 comments

  @fixtures
  Scenario: If a comment already exists in the live workspace, and a new user comment is created, they are merged correctly.
    When I get a node by path "/sites/neosdemotypo3/twocol/column0/text" with the following context:
      | Workspace |
      | user-demo |
    And I add a comment "Hallo Welt" to this node
    And I publish the workspace "user-demo"
    And I get a node by path "/sites/neosdemotypo3/twocol/column0/text" with the following context:
      | Workspace |
      | user-demo |
    And I add a comment "Second Comment" to this node
    And I publish the workspace "user-demo"
    And I get a node by path "/sites/neosdemotypo3/twocol/column0/text" with the following context:
      | Workspace   |
      | user-second |
    Then this node has 2 comments