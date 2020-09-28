/**
 * @author http://www.cosmosfarm.com/
 */

function board_comments_execute(form){
    jQuery.fn.exists = function(){
        return this.length>0;
    };

    /*
     * 잠시만 기다려주세요.
     */
    if(jQuery(form).data('submitted')){
        alert(board_comments_localize_strings.please_wait);
        return false;
    }

    /*
     * 폼 유효성 검사
     */
    if(jQuery('input[name=member_display]', form).exists() && !jQuery('input[name=member_display]', form).val()){
        alert(board_comments_localize_strings.please_enter_the_author);
        jQuery('[name=member_display]', form).focus();
        return false;
    }
    else if(jQuery('input[name=password]', form).exists() && !jQuery('input[name=password]', form).val()){
        alert(board_comments_localize_strings.please_enter_the_password);
        jQuery('input[name=password]', form).focus();
        return false;
    }
    else if(jQuery('input[name=captcha]', form).exists() && !jQuery('input[name=captcha]', form).val()){
        alert(board_comments_localize_strings.please_enter_the_CAPTCHA);
        jQuery('input[name=captcha]', form).focus();
        return false;
    }

    jQuery(form).data('submitted', 'submitted');
    return true;
}

function board_comments_delete(id){
    if(confirm(board_comments_localize_strings.are_you_sure_you_want_to_delete)){
        jQuery.request('onDelete', {
            data: {
                id: id
            },
            success: function(data) {
                window.location.href = data.url;
            }
        })
    }
    return false;
}

function board_comments_open_confirm(url){
    var width = 500;
    var height = 250;
    window.open(url, 'board_comments_password_confirm', 'top='+(screen.availHeight*0.5-height*0.5)+',left='+(screen.availWidth*0.5-width*0.5)+',width='+width+',height='+height+',resizable=0,scrollbars=1');
    return false;
}

function board_comments_open_edit(url){
    var width = 500;
    var height = 250;
    window.open(url, 'board_comments_edit', 'top='+(screen.availHeight*0.5-height*0.5)+',left='+(screen.availWidth*0.5-width*0.5)+',width='+width+',height='+height);
    return false;
}

function board_comments_reply(obj, form_id, cancel_id, content_uid){
    var parents = jQuery(obj).parents('#board-comments-'+content_uid);
    if(jQuery(obj).hasClass('board-reply-active')){
        jQuery(cancel_id).append(jQuery('.board-comments-form', parents));
        jQuery('.board-reply', parents).text(board_comments_localize_strings.reply).removeClass('board-reply-active');
    }
    else{
        jQuery(form_id).append(jQuery('.board-comments-form', parents));
        jQuery('textarea[name=comment_content]', parents).focus();
        jQuery('.board-reply', parents).text(board_comments_localize_strings.reply).removeClass('board-reply-active');
        jQuery(obj).text(board_comments_localize_strings.cancel).addClass('board-reply-active');
    }
    if(typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor){
        tinyMCE.EditorManager.execCommand('mceFocus', false, 'comment_content_'+content_uid);
        tinyMCE.EditorManager.execCommand('mceRemoveEditor', true, 'comment_content_'+content_uid);
        tinyMCE.EditorManager.execCommand('mceAddEditor', true, 'comment_content_'+content_uid);
    }
    return false;
}

function board_comments_field_show(form){
    if(typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor){
        form = jQuery(form.target.formElement);
    }

    jQuery('.comments-field-wrap').hide();
    jQuery('.comments-submit-button').hide();

    jQuery('.comments-field-wrap', form).show();
    jQuery('.comments-submit-button', form).show();
}

jQuery(document).ready(function(){
    jQuery(document).on('focus', 'textarea[name=comment_content]', function(){
        board_comments_field_show(jQuery(this).parents('.board-comments-form'));
    });
});
