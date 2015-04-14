/**
 * Extension to the Content Module which directly hooks into Neos (NON-API); displaying the content module overlays.
 */
require(
// The following RequireJS Configuration is exactly the same as in ContentModuleBootstrap.js
{
	baseUrl: window.T3Configuration.neosJavascriptBasePath,
	urlArgs: window.localStorage.showDevelopmentFeatures ? 'bust=' +  (new Date()).getTime() : '',
	paths: requirePaths,
	context: 'neos'
},
[
	'Library/jquery-with-dependencies'
],
function($) {

	// Helper function to show/hide comment handles
	var updateCommentMarkers = function() {
		$('.neos-contentelement[data-node-comments]').each(function() {
			var $element = $(this);

			var currentComments = JSON.parse($element.attr('data-node-comments') || '[]');
			var hasActiveComments = false;
			currentComments.forEach(function(comment) {
				if (comment.isDeleted == false) {
					hasActiveComments = true;
				}
			});

			if (hasActiveComments) {
				$element.addClass('neos-contentelement-has-comments');
			} else {
				$element.removeClass('neos-contentelement-has-comments');
			}
		});
	};

	// Initially show Comment Markers
	updateCommentMarkers();

	if (typeof document.addEventListener === 'function') {
		document.addEventListener('Neos.PageLoaded', function() {
			// When switching pages, show comment markers
			updateCommentMarkers();
		}, false);

		document.addEventListener('Sandstorm.ContentComments.CommentsChanged', function() {
			// When Comments have changed, redraw comment markers as well
			updateCommentMarkers();
		}, false);
	}
});