define(
	[
		'emberjs',
		'Library/jquery-with-dependencies',
		'Library/backbone',
		'Shared/EventDispatcher',
		'Shared/Configuration',
		'text!./CommentsEditor.html'
	],
	function (Ember, $, Backbone, EventDispatcher, Configuration, template) {
		return Ember.View.extend({

			TextArea: Ember.TextArea,
			template: Ember.Handlebars.compile(template),

			newCommentField: '',
			commentsList: null,

			init: function() {
				this._super();
				this.set('commentsList', []);
			},

			didInsertElement: function() {
				this.set('commentsList', JSON.parse(this.get('inspector.selectedNode.attributes.comments') || '[]'));
			},

			_hasComments: function() {
				return !!this.get('commentsList').findBy('isDeleted', false)
			}.property('commentsList.@each.isDeleted'),

			/**
			 * Actions
			 */
			add: function() {
				var newComment = this.get('newCommentField');

				var parsedComments = this.get('commentsList');

				parsedComments.pushObject({
					date: parseInt(new Date().getTime()),
					user: 'myuser', // TODO
					comment: newComment,
					isDeleted: false,
					isResolved: false // TODO: Maybe use this to resolve comment?
				});
				this._updateComments();

				this.set('newCommentField', '');
			},
			_updateComments: function() {
				this.get('inspector.selectedNode').setAttribute('comments', JSON.stringify(this.get('commentsList')));
				EventDispatcher.triggerExternalEvent('Sandstorm.ContentComments.CommentsChanged', 'Comments have changed.');

				var entity = this.get('inspector.selectedNode._vieEntity');
				if (entity.isValid() !== true) {
					return;
				}
				Backbone.sync('update', entity);
			},

			deleteComment: function(currentComment) {
				var commentsList = this.get('commentsList');

				// find comment in commentsList and set deleted = true
				commentsList.forEach(function(comment) {
					if(currentComment.date == comment.date && currentComment.user == comment.user) {
						// use Ember.set so observers are fired correctly
						Ember.set(comment, 'isDeleted', true);
					}
				});

				var that = this;
				Ember.run.next(function() {
					that.set('commentsList', commentsList);
					that._updateComments();
				});
			}
		});
	}
);