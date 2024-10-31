(function() {
	tinymce.create('tinymce.plugins.NEPlugin', {
		init: function(ed, url) {
			ed.addCommand('NEChooseView', function() {
				ed.windowManager.open({
					file: url.replace(/\/wp-content\/plugins\/[a-zA-Z]+\/pluglets\/newsandevents\/js/, '/wp-admin/admin.php?page=ktf_newsandevents/newsAndEvents&action=ajaxEditorButtonDialog'),
					width: 320,
					height: 320,
					inline: 1
				}, {
					plugin_url: url
				});
			});

			ed.addButton('newsandevents', {
				title: 'Insert News & Events',
				cmd: 'NEChooseView',
				image: url.replace("/js", "/img/icon.png")
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
