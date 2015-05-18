# Sandstorm.ContentComments

Created by Sandstorm Media in 2015.

This Package Adds Content Comments / Sticky Notes to TYPO3 Neos. You can comment on arbitrary nodes.
It has been tested with the current TYPO3 Neos Master, and will run with Neos 2.0.

## Usage

* install the package
* use the new "Comments" Tab in the Inspector


## Internal Working

* the "comments" tab is added to all node types, alongside with the property `comments`. (API)
* The `comments` property is a JSON-encoded array with comment objects which is manipulated by a custom `Comments` editor. (API)
* A `CommentingContentModuleExtension` displays the commenting indicators as overlays on the current page (non-API)
* JS and CSS extensions are added using TypoScript to the page rendering.
* A `WorkspaceAspect` takes care of merging comments together on publishing (non-API)


## Why did we choose this implementation?

There generally are multiple ways of storing comments:

* we can add them to the nodes themselves, as node property (the way it is done currently)
* we can add them to the nodes themselves, using a special database column
* we can store them as extra nodes and somehow link them together
* we can store them as extra domain objects and somehow link them together.

As a first step, we wanted the exact same publishing behavior than with normal content, i.e. a content note should
appear exactly in the workspace/content dimension where the specific content is located. Furthermore, we thought it
would be very strange if the user created completely new content, leaves TODO notes for himself, but does not publish
yet -- and still notes would already be visible to other people in their workspaces. I think that behavior does
not really make sense -- that's why we handle the notes currently in the same manner as normal content, with all the same
workspace and shine-through logic.

## TODOs

This is currently beta quality; expect to find bugs. 

## License

GNU GPLv3 or later.