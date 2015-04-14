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

## TODO

This is currently beta quality; expect to find bugs.

* Use real user name of logged in user when creating comments. 

## License

GNU GPLv3 or later.