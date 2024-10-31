(function() {
	tinymce.create('tinymce.plugins.NEPlugin', {
		init: function(ed, url) {
			ed.addCommand('NEChooseView', function() {
				//expected incoming url looks something like: 
				//http://localhost/KnowledgeTown/ne_test/wordpress/wp-content/plugins/news-and-events/assets/
				plugin_url = url.replace('assets','ajax.php?r=newsAndEvents_ajaxEditorButtonDialog.php');
				ed.windowManager.open({
					file: plugin_url,
					width: 320,
					height: 420,
					inline: 1
				}, {
					plugin_url: plugin_url
				});
			});

			ed.addButton('newsandevents', {
				title: 'Insert News & Events',
				cmd: 'NEChooseView',
				image: url + '/icon.png'
			});

			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('newsandevents', n.nodeName == 'IMG');
			});
		},

		createControl: function(n, cm) {
			return null;
		},

		getInfo: function() {
			return {
				longname: 'News And Events Editor Button',
				author: 'Knowledgetown',
				authorurl: 'http://knowledgetown.com',
				infourl: 'http://knowledgetown.com',
				version: '1.0'
			}
		}
	});
	tinymce.PluginManager.add('NEPlugin', tinymce.plugins.NEPlugin);
})();
