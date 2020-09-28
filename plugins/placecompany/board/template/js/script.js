/**
 * @author https://www.cosmosfarm.com
 */

/**
 * inViewport jQuery plugin by Roko C.B.
 * http://stackoverflow.com/a/26831113/383904 Returns a callback function with
 * an argument holding the current amount of px an element is visible in
 * viewport (The min returned value is 0 (element outside of viewport)
 */
(function($, win){
	$.fn.boardViewport = function(cb){
		return this.each(function(i, el){
			function visPx(){
				var elH = $(el).outerHeight(), H = $(win).height(), r = el.getBoundingClientRect(), t = r.top, b = r.bottom;
				return cb.call(el, Math.max(0, t > 0 ? Math.min(elH, H - t) : (b < H ? b : H)));
			}
			visPx();
			$(win).on("resize scroll", visPx);
		});
	};
}(jQuery, window));

var board_ajax_lock = false;

jQuery(document).ready(function(){
	var board_mod = jQuery('input[name=mod]', '.board-form').val();
	if(board_mod == 'editor'){
		if(board_current.use_tree_category == 'yes'){
			board_tree_category_parents();
		}

		if(board_current.use_editor == 'snote'){ // summernote
			jQuery('.summernote').each(function(){
				var height = parseInt(jQuery(this).height());
				var placeholder = jQuery(this).attr('placeholder');
				var lang = 'en-US';

				if(board_settings.locale == 'ko_KR'){
					lang = 'ko-KR';
				}
				else if(board_settings.locale == 'ja'){
					lang = 'ja-JP';
				}

				jQuery(this).summernote({
					toolbar: [
						['style', ['style']],
						['fontsize', ['fontsize']],
						['font', ['bold', 'italic', 'underline', 'clear']],
						['fontname', ['fontname']],
						['color', ['color']],
						['para', ['ul', 'ol', 'paragraph']],
						['height', ['height']],
						['table', ['table']],
						['insert', ['link', 'picture', 'hr']],
						['view', ['fullscreen', 'codeview']],
						['help', ['help']]
					],
					fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Helvetica Neue', 'Helvetica', 'Impact', 'Lucida Grande', 'Tahoma', 'Times New Roman', 'Verdana', 'Nanum Gothic', 'Malgun Gothic', 'Noto Sans KR', 'Apple SD Gothic Neo'],
					fontNamesIgnoreCheck: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Helvetica Neue', 'Helvetica', 'Impact', 'Lucida Grande', 'Tahoma', 'Times New Roman', 'Verdana', 'Nanum Gothic', 'Malgun Gothic', 'Noto Sans KR', 'Apple SD Gothic Neo'],
					fontSizes: ['8','9','10','11','12','13','14','15','16','17','18','19','20','24','30','36','48','64','82','150'],
					lang: lang,
					height: height,
					placeholder: placeholder
				});
			});
		}
	}
});

function board_tree_category_search(index, value){
	var length = jQuery('.board-search-option-wrap').length;
	var tree_category_index = parseInt(index) +1;

	if(value){
		jQuery('input[name="board_search_option[tree_category_'+index+'][value]"]').val(value);
	}
	else{
		jQuery('input[name="board_search_option[tree_category_'+index+'][value]"]').val('');
	}

	for(var i=tree_category_index; i<=length; i++){
		jQuery('.board-search-option-wrap-'+i).remove();
	}
	jQuery('#board-tree-category-search-form-'+board_current.board_id).submit();

	return false;
}

function board_tree_category_parents(){
	if(board_current.use_tree_category){
		var tree_category = board_current.tree_category;
		var tree_category_name;
		var tree_category_index = 1;

		tree_category_name = 'board_option_tree_category_';

		jQuery('.board-tree-category-wrap').prepend('<select id="board-tree-category-'+tree_category_index+'" class="board-tree-category board-tree-category-'+tree_category_index+'"></select>');
		jQuery('#board-tree-category-'+tree_category_index).append('<option value="">카테고리 선택</option>');
		jQuery('#board-tree-category-'+tree_category_index).after('<input type="hidden" id="'+tree_category_name+tree_category_index+'" name="'+tree_category_name+tree_category_index+'" class="board-tree-category-hidden-'+tree_category_index+'">');

		jQuery('#board-tree-category-'+tree_category_index).change(function(){
			board_tree_category_children(this.value, tree_category_index, tree_category_name);
			jQuery('#board-tree-category-search-form-'+board_current.board_id).submit();
		});

		jQuery.each(tree_category, function(index, element){
			if(!element.parent_id){
				jQuery('#board-tree-category-'+tree_category_index).append('<option value="'+element.id+'">'+element.category_name+'</option>');
			}
		});

		board_tree_category_selected(tree_category_index, tree_category_name);
	}
}

function board_tree_category_children(category_id, tree_category_index, tree_category_name){
	var tree_category = board_current.tree_category;
	var length = jQuery('.board-tree-category').length;
	var check = 0;

	for(var i=tree_category_index+1; i<=length; i++){
		jQuery('.board-tree-category-'+i).remove();
		jQuery('.board-tree-category-hidden-'+i).remove();
	}

	jQuery.each(tree_category, function(index, element){
		if(jQuery('#board-tree-category-'+tree_category_index).val() == element.id){
			jQuery('#'+tree_category_name+tree_category_index).val(element.category_name);
		}
	});

	if(jQuery('#board-tree-category-'+tree_category_index).val()){
		jQuery.each(tree_category, function(index, element){
			if(category_id === element.parent_id){
				if(check==0){
					jQuery('#board-tree-category-'+tree_category_index).after('<select id="board-tree-category-'+(tree_category_index+1)+'" class="board-tree-category board-tree-category-'+(tree_category_index+1)+'"></select>');
					jQuery('#board-tree-category-'+(tree_category_index+1)).append('<option value="">카테고리 선택</option>');

					jQuery('#board-tree-category-'+(tree_category_index+1)).after('<input type="hidden" id="'+tree_category_name+(tree_category_index+1)+'" name="'+tree_category_name+(tree_category_index+1)+'" class="board-tree-category-hidden-'+(tree_category_index+1)+'">');

					jQuery('#board-tree-category-'+(tree_category_index+1)).change(function(){
						board_tree_category_children(this.value, (tree_category_index+1), tree_category_name);
						jQuery('#board-tree-category-search-form-'+board_current.board_id).submit();
					});
				}
				check++;
				jQuery('#board-tree-category-'+(tree_category_index+1)).append('<option value="'+element.id+'">'+element.category_name+'</option>');
			}
		});
		board_tree_category_selected(tree_category_index+1, tree_category_name);
	}
	else{
		for(var i=tree_category_index; i<=length; i++){
			jQuery('.board-tree-category-hidden-'+i).val('');
			jQuery('.board-tree-category-hidden-'+(i+1)).remove();
		}
	}

	if(jQuery('.board-tree-category-search').length){
		jQuery('input[name="board_search_option[tree_category_'+tree_category_index+'][value]"').val(jQuery('#'+tree_category_name+tree_category_index).val());
		jQuery('input[name="board_search_option[tree_category_'+tree_category_index+'][key]"').val('tree_category_'+tree_category_index);
	}
}

function board_tree_category_selected(tree_category_index, tree_category_name){
	var check = jQuery('#tree-category-check-'+tree_category_index).val();

	if(check){
		jQuery('#board-tree-category-'+tree_category_index+' option').each(function(index, element){
			if(jQuery(element).text() == check){
				jQuery(element).attr('selected', 'selected');
				board_tree_category_children(this.value, tree_category_index, tree_category_name);
			}
		});
	}
	return false;
}

function board_editor_open_media(){
	var w = 900;
	var h = 500;
	var media_popup_url = board_current.add_media_url;

	if(board_current.board_id){
		if(jQuery('#board_media_wrapper').length){
			jQuery('#board_media_wrapper').show();
			jQuery('#board_media_wrapper').html(jQuery('<iframe frameborder="0"></iframe>').attr('src', media_popup_url));
			jQuery('#board_media_background').show();
		}
		else{
			var wrapper = jQuery('<div id="board_media_wrapper"></div>');
			var background = jQuery('<div id="board_media_background"></div>').css({opacity:'0.5'}).click(function(){
				board_media_close();
			});

            function init_window_size(){
                if(window.innerWidth <= 900){
                    wrapper.css({left:0, top:0, margin:'10px', width:(window.innerWidth-20), height:(window.innerHeight-20)});
                }
                else{
                    wrapper.css({left:'50%', top:'50%', margin:0, 'margin-left':(w/2)*-1, 'margin-top':(h/2)*-1, width:w, height:h});
                }
            }

			init_window_size();
			jQuery(window).resize(init_window_size);

			wrapper.html(jQuery('<iframe frameborder="0"></iframe>').attr('src', media_popup_url));
			jQuery('body').append(background);
			jQuery('body').append(wrapper);

			if(!jQuery('input[name="media_group"]').filter(function(){return this.value === board_settings.media_group}).length){
				jQuery('[name="board_id"]').parents('form').append(jQuery('<input type="hidden" name="media_group">').val(board_settings.media_group));
			}
		}
	}
}

function board_editor_insert_media(url){
	if(board_current.use_editor == 'snote'){ // summernote
		jQuery('#board_content').summernote('editor.saveRange');
		jQuery('#board_content').summernote('editor.restoreRange');
		jQuery('#board_content').summernote('editor.focus');
		jQuery('#board_content').summernote('editor.pasteHTML', "<img src=\""+url+"\" alt=\"\">");
	}
	else if(typeof tinyMCE != 'undefined' && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden()){
		tinyMCE.activeEditor.execCommand('mceInsertContent', false, "<img id=\"last_board_media_content\" src=\""+url+"\" alt=\"\">");
		tinyMCE.activeEditor.focus();
		tinyMCE.activeEditor.selection.select(tinyMCE.activeEditor.dom.select('#last_board_media_content')[0], true);
		tinyMCE.activeEditor.selection.collapse(false);
		tinyMCE.activeEditor.dom.setAttrib('last_board_media_content', 'id', '');
	}
	else if(jQuery('#board_content').length){
		jQuery('#board_content').val(function(index, value){
			return value + (!value?'':' ') + "<img src=\""+url+"\" alt=\"\">";
		});
	}
}

function board_media_close(){
	jQuery('#board_media_wrapper').hide();
	jQuery('#board_media_background').hide();
}

function board_document_print(url){
	window.open(url, 'board_document_print');
	return false;
}

function board_document_like(button, callback){
	if(!board_ajax_lock){
		board_ajax_lock = true;

        jQuery.request('onDocumentLike', {
            data: {
                document_id: jQuery(button).data('id'),
            },
            success: function(res) {
                board_ajax_lock = false;
                if(typeof callback === 'function'){
                    callback(res);
                }
                else{
                    if(res.result === 'error'){
                        alert(res.message);
                    }
                    else{
                        jQuery('.board-document-like-count', button).text(res.data.like);
                    }
                }
            }
        });
	}
	else{
		alert(board_localize_strings.please_wait);
	}
	return false;
}

function board_document_unlike(button, callback){
	if(!board_ajax_lock){
		board_ajax_lock = true;

        jQuery.request('onDocumentUnlike', {
            data: {
                document_id: jQuery(button).data('id'),
            },
            success: function(res) {
                board_ajax_lock = false;
                if(typeof callback === 'function'){
                    callback(res);
                }
                else{
                    if(res.result === 'error'){
                        alert(res.message);
                    }
                    else{
                        jQuery('.board-document-unlike-count', button).text(res.data.unlike);
                    }
                }
            }
        });
	}
	else{
		alert(board_localize_strings.please_wait);
	}
	return false;
}

function board_comment_like(button, callback){
	if(!board_ajax_lock){
		board_ajax_lock = true;
		jQuery.post(board_settings.ajax_url, {'action':'board_comment_like', 'comment_uid':jQuery(button).data('uid'), 'security':board_settings.ajax_security}, function(res){
			board_ajax_lock = false;
			if(typeof callback === 'function'){
				callback(res);
			}
			else{
				if(res.result == 'error'){
					alert(res.message);
				}
				else{
					jQuery('.board-comment-like-count', button).text(res.data.like);
				}
			}
		});
	}
	else{
		alert(board_localize_strings.please_wait);
	}
	return false;
}

function board_comment_unlike(button, callback){
	if(!board_ajax_lock){
		board_ajax_lock = true;
		jQuery.post(board_settings.ajax_url, {'action':'board_comment_unlike', 'comment_uid':jQuery(button).data('uid'), 'security':board_settings.ajax_security}, function(res){
			board_ajax_lock = false;
			if(typeof callback === 'function'){
				callback(res);
			}
			else{
				if(res.result == 'error'){
					alert(res.message);
				}
				else{
					jQuery('.board-comment-unlike-count', button).text(res.data.unlike);
				}
			}
		});
	}
	else{
		alert(board_localize_strings.please_wait);
	}
	return false;
}

function board_fields_validation(form, callback){
	jQuery('.board-attr-row.required', form).each(function(index, element){
		var required = jQuery(element).find('.required');

		if(jQuery(required).length == 1 && jQuery(required).val() == 'default' || !jQuery(required).val()){
			alert(board_localize_strings.required.replace('%s', jQuery(element).find('.field-name').text()));
			callback(required);

			return false;
		}
		else if((jQuery(required).is(':radio') || jQuery(required).is(':checkbox')) && jQuery(element).find('.required:checked').length == 0){
			alert(board_localize_strings.required.replace('%s', jQuery(element).find('.field-name').text()));
			callback(jQuery(required).eq(0));

			return false;
		}
	});
}

function board_content_update(content_uid, data, callback){
	if(!board_ajax_lock){
		board_ajax_lock = true;
		jQuery.post(board_settings.ajax_url, {'action':'board_content_update', 'content_uid':content_uid, 'data':data, 'security':board_settings.ajax_security}, function(res){
			board_ajax_lock = false;
			if(typeof callback === 'function'){
				callback(res);
			}
		});
	}
	else{
		alert(board_localize_strings.please_wait);
	}
	return false;
}

function board_ajax_builder(args, callback){
	if(!board_ajax_lock){
		board_ajax_lock = true;
		var callback2 = (typeof callback === 'function') ? callback : args['callback'];
		args['action'] = 'board_ajax_builder';
		args['callback'] = '';
		args['security'] = board_settings.ajax_security;
		jQuery.get(board_settings.ajax_url, args, function(res){
			board_ajax_lock = false;
			if(typeof callback2 === 'function'){
				callback2(res);
			}
		});
	}
	else{
		alert(board_localize_strings.please_wait);
	}
	return false;
}
