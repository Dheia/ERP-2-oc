<?php if(!defined('ABSPATH')) exit;?>
<!DOCTYPE html>
<html <?php language_attributes()?>>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width">
	<meta http-equiv="X-UA-Compatible" content="IE=Edge">
	<title><?php wp_title('')?></title>

	<style>
	html, body { margin: 0; padding: 0; width: 1px; min-width: 100%; *width: 100%; }
	a { color: #545861; }
	img { border: 0; }
	</style>

	<?php
	// SEO 정보 출력
	$seo->head();

	// 고유주소 또는 아이프레임으로 접근시 실행
	do_action('board_iframe_head');
	?>
</head>
<body class="board board-<?php echo $board_id?>">
	<div id="board" style="float:left;width:100%;min-height:250px">
		<?php echo board_builder(array('id'=>$board_id))?>
	</div>

	<?php if(board_iframe_id()):?>
	<script>
	function board_iframe_resize(){
		var board = document.getElementById('board');
		if(board.offsetHeight != 0 && parent.document.getElementById("board-iframe-<?php echo board_iframe_id()?>")){
			parent.document.getElementById("board-iframe-<?php echo board_iframe_id()?>").style.height = board.offsetHeight + "px";
		}
	}
	var board_iframe_resize_interval = setInterval(function(){
		board_iframe_resize();
	}, 100);
	</script>
	<?php endif?>

	<!--[if lt IE 9]><script src="<?php echo KBOARD_URL_PATH?>/template/js/html5.js"></script><![endif]-->
	<!--[if lt IE 9]><script src="<?php echo KBOARD_URL_PATH?>/template/js/respond.js"></script><![endif]-->

	<?php
	if(is_admin()) do_action('admin_print_footer_scripts');

	wp_footer();
	?>
</body>
</html>
