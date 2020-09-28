<script type="text/javascript" src="//code.jquery.com/jquery-1.11.3.min.js"></script>
<script type="text/javascript" src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
<script type="text/javascript" src="/modules/system/assets/js/framework.js"></script>
<script type="text/javascript" src="/modules/system/assets/js/framework.extras.js"></script>
<style>
    * {
        font-family: Apple SD Gothic Neo, Malgun Gothic, arial, sans-serif, arial, sans-serif;
    }

    html, body {
        margin: 0;
        padding: 0;
        background-color: white;
    }

    img {
        border: 0;
    }

    .board-media-header {
        padding: 0 20px;
        font-size: 20px;
        overflow: hidden;
    }

    .board-media-header .title {
        float: left;
        padding-right: 10px;
        line-height: 64px;
    }

    .board-media-header .controller {
        float: left;
        line-height: 64px;
    }

    .board-media-header .header-button {
        display: inline-block;
        *display: inline;
        zoom: 1;
        vertical-align: middle;
        margin: 0;
        padding: 0;
        padding: 0 10px;
        line-height: 40px;
        border: 0;
        background-color: white;
        color: #757575;
        font-size: 12px;
        cursor: pointer;
        text-decoration: none;
    }

    .board-media-header .header-button img {
        vertical-align: middle;
    }

    .media-wrap {
        padding: 0 10px;
        overflow: hidden;
    }

    .media-wrap .no-media {
        margin: 20px 10px;
        padding: 30px 10px;
        overflow: hidden;
        line-height: 30px;
        border: 1px solid #eeeeee;
        color: #757575;
    }

    .media-wrap .no-media a {
        color: #757575;
        text-decoration: none;
    }

    .media-wrap .media-item {
        position: relative;
        display: block;
        float: left;
        margin: 5px;
        padding: 5px;
        cursor: pointer;
    }

    .media-wrap .media-item .selected-media {
        display: none;
        position: absolute;
        left: 0;
        top: 0;
        border-radius: 12px;
        box-shadow: 2px 2px 2px RGBA(0, 0, 0, 0.2);
    }

    .media-wrap .media-item .media-image-wrap {
        width: 150px;
    }

    .media-wrap .media-item .media-image-wrap .media-image {
        width: 100%;
        height: 150px;
        background-size: cover;
        background-position: center;
    }

    .media-wrap .media-item .media-control {
        text-align: center;
        background-color: #f5f5f5;
    }

    .media-wrap .media-item .media-control input {
        display: none;
    }

    .media-wrap .media-item .media-control button {
        margin: 0;
        padding: 5px 10px;
        border: 0;
        background-color: transparent;
        color: #757575;
        font-size: 14px;
        cursor: pointer;
        text-decoration: none;
    }

    .media-wrap .media-item:hover .selected-media {
        display: block;
    }

    .media-wrap .media-item.selected-item {
        padding: 5px;
        border: 0px solid #0073ea;
    }

    .media-wrap .media-item.selected-item .selected-media {
        display: block;
    }

    .media-wrap .media-item.selected-item .media-image-wrap {
        width: 130px;
        padding: 10px;
        background-color: #f5f5f5;
    }

    .media-wrap .media-item.selected-item .media-image-wrap .media-image {
        height: 130px;
    }

    .board-loading {
        position: fixed;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: black;
        opacity: 0.5;
        text-align: center;
    }

    .board-loading img {
        position: relative;
        top: 50%;
        margin-top: -32px;
        border: 0;
    }

    .board-hide {
        display: none !important;
    }

    @media screen and (max-width: 899px) {
        .board-media-header {
            line-height: normal;
        }

        .board-media-header .title {
            float: none;
            padding-right: 0;
            text-align: center;
        }

        .board-media-header .controller {
            float: none;
            line-height: 30px;
            text-align: center;
        }

        .board-media-header .controller input[type="file"] {
            display: block;
        }

        .media-wrap .media-item {
            float: none;
        }

        .media-wrap .media-item .media-image-wrap {
            width: auto;
        }

        .media-wrap .media-item .media-image-wrap .media-image {
            height: 200px;
        }

        .media-wrap .media-item:hover .selected-media {
            display: none;
        }

        .media-wrap .media-item.selected-item .selected-media {
            display: block;
        }

        .media-wrap .media-item.selected-item .media-image-wrap {
            width: auto;
        }

        .media-wrap .media-item.selected-item .media-image-wrap .media-image {
            height: 180px;
        }
    }
</style>

<form id="board-media-form" enctype="multipart/form-data" method="post" onsubmit="return board_media_form_execute(this)" data-allow="gif|jpg|jpeg|png">
{{ csrf_field() }}
<input type="hidden" name="board_id" value="{{ $media->board_id }}">
<input type="hidden" name="content_id" value="{{ $media->content_id }}">
<input type="hidden" name="media_group" value="{{ $media->media_group }}">
<input type="hidden" name="media_id" value="">

<div class="board-media-header">
    <div class="title">{{ trans('placecompany.board::lang.Board Add Media') }}</div>
    <div class="controller">
        <a href="javascript:void(0)" class="header-button upload-button" data-name="board_media_file[]"
           title="{{ trans('placecompany.board::lang.이미지 선택하기') }}"><img
                src="{{ url('plugins/placecompany/board/assets') }}/images/icon-upload.png"> {{ trans('placecompany.board::lang.업로드') }}
        </a>
        <a href="javascript:void(0)" class="header-button" onclick="board_selected_media_insert();return false;"
           title="{{ trans('placecompany.board::lang.선택된 이미지 삽입하기') }}"><img
                src="{{ url('plugins/placecompany/board/assets') }}/images/icon-add.png"> {{ trans('placecompany.board::lang.선택 삽입') }}
        </a>
        <a href="javascript:void(0)" class="header-button" onclick="board_media_select_all();return false;"
           title="{{ trans('placecompany.board::lang.전체선택') }}">{{ trans('placecompany.board::lang.전체선택') }}</a>
        <a href="javascript:void(0)" class="header-button" onclick="board_media_close();return false;"
           title="{{ trans('placecompany.board::lang.창닫기') }}">{{ trans('placecompany.board::lang.창닫기') }}</a>
    </div>
</div>
</form>

<div class="media-wrap">
    @foreach ($media->getList() as $key => $item)
    <label class="media-item" data-media-id="{{ $item->id }}">
        <img class="selected-media" src="{{ url('plugins/placecompany/board/assets') }}/images/selected-media.png"
             alt="{{ trans('placecompany.board::lang.선택됨') }}">
        <div class="media-image-wrap">
            <div class="media-image" style="background-image:url('{{ $item->thumbnail_url }}')"></div>
        </div>
        <div class="media-control">
            <input type="checkbox" name="media_src" value="{{ $item->thumbnail_url }}" data-media-id="{{ $item->id }}"
                   onchange="board_media_select()">
            <button type="button" onclick="board_media_insert('{{ $item->thumbnail_url }}');"
                    title="{{ trans('placecompany.board::lang.삽입') }}">{{ trans('placecompany.board::lang.삽입') }}</button>
            <button type="button" onclick="board_media_delete('{{ $item->id }}');"
                    title="{{ trans('placecompany.board::lang.삭제') }}">{{ trans('placecompany.board::lang.삭제') }}</button>
        </div>
    </label>
    @endforeach

    @if (!count($media->getList()))
    <div class="no-media">
        {{ trans('placecompany.board::lang.업로드된 이미지가 없습니다.') }}<br>
        {{ trans('placecompany.board::lang.업로드 버튼을 눌러 이미지 파일을 선택하면 이곳에 표시됩니다 :D') }}<br>
    </div>
    @endif
</div>

<div class="board-loading board-hide">
    <img src="{{ url('plugins/placecompany/board/assets') }}/images/loading2.gif"
         alt="{{ trans('placecompany.board::lang.로딩중') }}">
</div>

<script>
    function board_media_select_all() {
        if (jQuery('.media-item').length) {
            jQuery('.media-item').each(function () {
                if (jQuery('.media-wrap').hasClass('media-all-selected')) {
                    if (jQuery(this).find('input[type=checkbox]').is(':checked')) {
                        jQuery(this).find('input[type=checkbox]').click();
                    }
                } else {
                    if (!jQuery(this).find('input[type=checkbox]').is(':checked')) {
                        jQuery(this).find('input[type=checkbox]').click();
                    }
                }
            });
            setTimeout(function () {
                if (jQuery('.media-wrap').hasClass('media-all-selected')) {
                    jQuery('.media-wrap').removeClass('media-all-selected');
                } else {
                    jQuery('.media-wrap').addClass('media-all-selected');
                }
            }, 0);
        }
    }

    function board_media_select() {
        jQuery('.media-item').removeClass('selected-item');
        jQuery('input[name=media_src]:checked').each(function () {
            var media_id = jQuery(this).data('media-id');
            jQuery('.media-item[data-media-id=' + media_id + ']').addClass('selected-item');
        });
    }

    function board_selected_media_insert() {
        var total = jQuery('input[name=media_src]:checked').length;
        var index = 0;
        if (!total) {
            alert("{{ trans('placecompany.board::lang.선택한 이미지가 없습니다.') }}");
        } else {
            jQuery('input[name=media_src]:checked').each(function () {
                var media_src = jQuery(this).val();
                if (media_src) {
                    parent.board_editor_insert_media(media_src);
                }
                if (++index === total) {
                    if (confirm("{{ trans('placecompany.board::lang.선택한 이미지를 본문에 삽입했습니다. 창을 닫을까요?') }}")) {
                        board_media_close();
                    }
                }
            });
        }
    }

    function board_media_insert(media_src) {
        if (media_src) {
            parent.board_editor_insert_media(media_src);
            if (confirm("{{ trans('placecompany.board::lang.선택한 이미지를 본문에 삽입했습니다. 창을 닫을까요?') }}")) {
                board_media_close();
            }
        }
    }

    function board_media_delete(media_id) {
        if (media_id) {
            if (confirm("{{ trans('placecompany.board::lang.Are you sure you want to delete?') }}")) {
                jQuery('input[name=media_id]', '#board-media-form').val(media_id);
                var form = jQuery('#board-media-form');
                form.attr("action", '{{ route('placecompany.board::mediaDelete') }}');
                form.submit();
            }
        }
    }

    function board_media_form_execute(form) {
        jQuery('.board-loading').removeClass('board-hide');
        setTimeout(function () {
            alert("{{ trans('placecompany.board::lang.Network connection with the server is unstable. Please proceed to upload again.') }}");
            jQuery('.board-loading').addClass('board-hide');
        }, (1000 * 30));
        return true;
    }

    function board_media_close() {
        parent.board_media_close();
    }

    function board_media_upload_button(button) {
        jQuery('input[type=file]', button).remove();

        var allow = jQuery('form').attr('data-allow');
        var extension = "\.(" + allow + ")$";

        var input = function () {
            var obj = jQuery('<input type="file" accept="image/*" multiple>').attr('name', jQuery(button).attr('data-name')).css({
                'position': 'absolute',
                'cursor': 'pointer',
                'opacity': 0,
                'outline': 0
            }).change(function () {
                var files = jQuery(this).get(0).files;

                if (files) {
                    var total = files.length;
                    var index = 0;

                    jQuery.each(files, function (i, file) {
                        if (!(new RegExp(extension, "i")).test(file.name)) {
                            alert("{{ trans('placecompany.board::lang.이미지 파일만 업로드 가능합니다.') }}");

                            board_media_upload_button(button);
                            return false;
                        } else {
                            index++;
                        }
                        if (index === total) {
                            var form = jQuery('#board-media-form');
                            form.attr("action", '{{ route('placecompany.board::mediaUpload') }}');
                            form.submit();
                        }
                    });
                } else {
                    if (!(new RegExp(extension, "i")).test(jQuery(this).val())) {
                        alert("{{ trans('placecompany.board::lang.이미지 파일만 업로드 가능합니다.') }}");

                        board_media_upload_button(button);
                        return false;
                    } else {
                        var form = jQuery('#board-media-form');
                        form.attr("action", '{{ route('placecompany.board::mediaUpload') }}');
                        form.submit();
                    }
                }
            });
            return obj;
        };

        var event = function (event_input) {
            jQuery(button).css({
                'position': 'relative',
                'overflow': 'hidden'
            }).append(event_input).on('mousemove', function (event) {
                var left = event.pageX - jQuery(this).offset().left - jQuery(event_input).width() + 10;
                var top = event.pageY - jQuery(this).offset().top - 10;
                event_input.css({'left': left, 'top': top});
            }).hover(function () {
                event_input.show();
            }, function () {
                event_input.hide();
            }).keydown(function (e) {
                if (e.keyCode === 13) {
                    e.preventDefault();
                    jQuery('input[type=file]', button)[0].click();
                }
            });
        }
        event(input());
    }

    jQuery(document).ready(function ($) {
        var allow = jQuery('form').attr('data-allow');
        var extension = "\.(" + allow + ")$";

        jQuery('.upload-button').each(function () {
            board_media_upload_button(this);
        });

        jQuery(document).on('dragover drop', function (e) {
            e.preventDefault();
            e.stopPropagation();
        }).on('drop', function (e) {
            if (e.originalEvent && e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files) {
                jQuery('input[type=file]').prop('files', e.originalEvent.dataTransfer.files);

                var total = e.originalEvent.dataTransfer.files.length;
                var index = 0;

                jQuery.each(e.originalEvent.dataTransfer.files, function (i, file) {
                    if (!(new RegExp(extension, "i")).test(file.name)) {
                        alert("{{ trans('placecompany.board::lang.이미지 파일만 업로드 가능합니다.') }}");

                        jQuery('.upload-button').each(function () {
                            board_media_upload_button(this);
                        });
                        return false;
                    } else {
                        index++;
                    }
                    if (index === total) {
                        var form = jQuery('#board-media-form');
                        form.attr("action", '{{ route('placecompany.board::mediaUpload') }}');
                        form.submit();
                    }
                });
            }
        });
    });
</script>
<script>
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', 'UA-23680192-8']);
    _gaq.push(['_setAllowLinker', true]);
    _gaq.push(['_trackPageview']);
    _gaq.push(['_trackEvent', 'location_host', window.location.host]);
    _gaq.push(['_trackEvent', 'location_href', window.location.href]);
    (function () {
        var ga = document.createElement('script');
        ga.type = 'text/javascript';
        ga.async = true;
        ga.src = ('https:' === document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(ga, s);
    })();
</script>
