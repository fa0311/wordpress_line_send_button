<?php
/*
Plugin Name: line-liff-send
Plugin URI: https://github.com/yukinashi/wordpress_line_send_button/
Description: ボタンを押すとlineで喋ります
Author: ふぁ
Version: 0.1
Author URI: https://yuki0311.com
*/

class ShowText {
    function __construct() {
      add_action('admin_menu', array($this, 'add_pages'));
    }
    function add_pages() {
      add_menu_page('line-liff-send-setting','line-liff-send-setting',  'level_8', __FILE__, array($this,'show_text_option_page'), '', 26);
	}
	function show_text_option_page() {
		if ( isset($_POST['showtext_options'])) {
			check_admin_referer('shoptions');
			$opt = $_POST['showtext_options'];
			update_option('showtext_options', $opt);
			?><div class="updated fade"><p><strong><?php _e('保存しました'); ?></strong></p></div><?php
		}
		?>
		<div class="wrap">
		<div id="icon-options-general" class="icon32"><br /></div><h2>url設定</h2>
			<form action="" method="post">
				<?php
				wp_nonce_field('shoptions');
				$opt = get_option('showtext_options');
				$show_text = isset($opt['text']) ? $opt['text']: null;
				?> 
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="inputtext">liffのurlを入力(line://以降)</label></th>
						<tr><td><input name="showtext_options[text]" type="text" id="inputtext" placeholder="0000000000-xxxxxxxx" value="<?php  echo $show_text ?>"/></td></tr>

					</tr>
				</table>
				<p class="submit"><input type="submit" name="Submit" class="button-primary" value="変更を保存" /></p>
			</form>
			<div>
				<h3>使い方</h3>
				<p>記事内に [liff send="XXX" text="XXX"]と入力するとliff内でしか表示されないボタンが現れます。</p>
				<p>引数はsendに送信したい言葉、textにブラウザ上に表示させるボタンの文字を指定します</p>
				<h3>ボタンのカスタマイズについて</h3>
				<p>liff-boxというdiv要素の中にpタグという形で表示されますのでwordpressのダッシュボードからcssを記述してください</p>
				<p>cssの例</p>
				<code>
.liff-box p{
  text-align: center;
  padding-top:8px;
}
.liff-box {
  display: inline-block;
  width: 150px;
  height:50px;
  text-align: left;
  border: 2px solid #9ec34b;
  font-size: 16px;
  color: #9ec34b;
  text-decoration: none;
  font-weight: bold;
  border-radius: 4px;
  transition: .4s;
}

.liff-box:hover {
  background-color: #9ec34b;
  border-color: #cbe585;
  color: #FFF;
}
</code>
						</div>
		<!-- /.wrap --></div>
		<?php
	}
	function get_text() {
		$opt = get_option('showtext_options');
		return isset($opt['text']) ? $opt['text']: null;
	  }
}
$showtext = new ShowText;









function liff($line_liff_atts){
    global $line_liff_num;
    $line_liff_num++;
	return '<div class="liff-box" id="liff-box'.$line_liff_num.'" onclick="send(\''.$line_liff_atts["send"].'\')"><p>'.$line_liff_atts["text"].'</p></div>
	<script>
	line_liff_num++;
	</script>
	';
}
add_shortcode('liff','liff');

add_filter( 'wp_header', function() {
	?>
<script src="https://static.line-scdn.net/liff/edge/2.1/sdk.js"></script>
<script>
var line_liff_num = 1;
initializeLiff("<?php echo esc_html($showtext->get_text()); ?>");
function initializeLiff(myLiffId) {
    liff
        .init({
            liffId: myLiffId
        })
        .then(() => {
            initializeApp();
        })
        .catch((err) => {
	var line_liff_loop = 1;
	while(line_liff_loop <= line_liff_num){
		const box = document.getElementById("liff-box"+line_liff_loop);
			 box.style.display ="none";
		 line_liff_loop ++;
		 }
        });
}

function send(text) {
	liff.sendMessages([{
		type: 'text',
		text: text
	}]).then(function () {
		window.alert("送信しました");
	}).catch(function (error) {
		window.alert("送信エラーが発生しました: " + error);
	});
}

window.addEventListener("load",
function (){
	var line_liff_loop = 1;
	while(line_liff_loop < line_liff_num){
		const box = document.getElementById("liff-box"+line_liff_loop);
		 if(liff.isInClient()){
			 box.style.display ="block";
		 }else{
			 box.style.display ="none";
		 }
		 line_liff_loop ++;
		}
}, false)

</script>
    <?php
} );
