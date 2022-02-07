<?php
defined( 'ABSPATH' ) || exit;
/*
Plugin Name: PageRedirectToURL
Plugin URI: https://github.com/HayateKinjou/PageRedirectToURL
Description: リダイレクトリンクを固定ページと投稿ページで行えるようにする
Author: HayateKinjou
Version: 1.0
Author URI: https://github.com/HayateKinjou/PageRedirectToURL
*/

///////////////////////////////////////
// カスタムボックスの追加
///////////////////////////////////////
add_action('admin_menu', 'add_redirect_custom_box');
function add_redirect_custom_box(){
  add_meta_box( 'singular_redirect_settings', 'リダイレクト', 'redirect_custom_box_view', 'post', 'side' );
  add_meta_box( 'singular_redirect_settings', 'リダイレクト', 'redirect_custom_box_view', 'page', 'side' );
}
 
///////////////////////////////////////
// リダイレクト
///////////////////////////////////////
function redirect_custom_box_view(){
  $redirect_url = get_post_meta(get_the_ID(),'redirect_url', true);

  echo '<label for="redirect_url">リダイレクトURL</label>';
  echo '<input type="text" name="redirect_url" size="20" value="'.esc_attr(stripslashes_deep(strip_tags($redirect_url))).'" placeholder="https://" style="width: 100%;">';
  echo '<p class="howto">このページに訪れるユーザーを設定したURLに301リダイレクトします。</p>';
}
 
add_action('save_post', 'redirect_custom_box_save_data');
function redirect_custom_box_save_data(){
  $id = get_the_ID();
  //リダイレクトURL
  if ( isset( $_POST['redirect_url'] ) ){
    $redirect_url = $_POST['redirect_url'];
    $redirect_url_key = 'redirect_url';
    add_post_meta($id, $redirect_url_key, $redirect_url, true);
    update_post_meta($id, $redirect_url_key, $redirect_url);
  }
}
//記事削除時にMeta情報も削除する
function redirect_custom_delete_meta($post_id){
  delete_post_meta($post_id,'redirect_url');
}
add_action( 'before_delete_post', 'redirect_custom_delete_meta');

//リダイレクトURLの取得
function get_singular_redirect_url(){
  return trim(get_post_meta(get_the_ID(), 'redirect_url', true));
}
 
//リダイレクト処理
function redirect_to_url($url){
  header( "HTTP/1.1 301 Moved Permanently" );
  header( "location: " . $url  );
  exit;
}
 
//URLの正規表現
define('URL_REG_STR', '(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)');
define('URL_REG', '/'.URL_REG_STR.'/');
 
//リダイレクト
add_action( 'wp','wp_singular_page_redirect', 0 );
function wp_singular_page_redirect() {
  //リダイレクト
  if (is_singular() && $redirect_url = get_singular_redirect_url()) {
    //URL形式にマッチする場合
    if (preg_match(URL_REG, $redirect_url)) {
      redirect_to_url($redirect_url);
    }
  }
}
?>
