define(
	[
		'emberjs',
		'Library/jquery-with-dependencies',
		'Library/backbone',
		'Shared/EventDispatcher',
		'Shared/Configuration',
		'Shared/HttpClient',
		'text!./CommentsEditor.html'
	],
	function (Ember, $, Backbone, EventDispatcher, Configuration, HttpClient, template) {

		Ember.Handlebars.helper('sandstorm-comments-format-date', function(value, options) {
			return new Date(value).toISOString().slice(0, 16).replace('T', ' ');
		});

		return Ember.View.extend({

			TextArea: Ember.TextArea,
			template: Ember.Handlebars.compile(template),

			newCommentField: '',
			commentsList: null,

			init: function() {
				this._super();
				this.set('commentsList', []);
				this._fetchCurrentUserName();
			},

			didInsertElement: function() {
				this.set('commentsList', JSON.parse(this.get('inspector.selectedNode.attributes.comments') || '[]'));
			},

			currentUserName: null,
			addButtonDisabled: function() {
				return !this.get('currentUserName');
			}.property('currentUserName'),

			_fetchCurrentUserName: function() {
				var uri = HttpClient._getEndpointUrl('neos-data-source') + '/commenting-current-user';
				var that = this;
				HttpClient.getResource(uri, {dataType: 'json'}).then(function(thisUser) {
					that.set('currentUserName', thisUser.name);
				});
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
					user: this.get('currentUserName'),
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

				// The "debounce" here is crucially important: If we leave it out, we trigger too many change events
				// on the same node in a short time if the user e.g. deletes many comments, leading to locking exceptions
				// on the server side (which we cannot catch)
				Ember.run.debounce({}, function() {
					Backbone.sync('update', entity);
				}, 250);
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