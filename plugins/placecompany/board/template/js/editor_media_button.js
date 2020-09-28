/**
 * @author https://www.cosmosfarm.com
 */
(function(){
	tinymce.create('tinymce.plugins.KBoard_Media_Button', {
		init:function(ed, url){
			ed.addButton('board_media', {
				title : board_localize_strings.board_add_media,
				image : board_settings.plugin_url+'/images/media-button-icon.png',
				onclick : board_editor_open_media
			});
		},
		createControl : function(n,cm){
			return null;
		}
	});
	tinymce.PluginManager.add('board_media_button_script', tinymce.plugins.KBoard_Media_Button);
})();
