<?php
/**
 * Plugin Name: Ceducarte Onepage (PDF + Vídeos)
 * Description: Página onepage estilo PDF com painel editável (imagens, textos, cores, menu âncoras e slider de vídeos).
 * Version: 1.2.1
 * Author: Zati / ChatGPT
 * Text Domain: ceducarte-onepage
 */
if (!defined('ABSPATH')) exit;

define('CEDUCARTE_ONEPAGE_VERSION', '1.2.1');
define('CEDUCARTE_ONEPAGE_SLUG', 'ceducarte-onepage');
define('CEDUCARTE_ONEPAGE_OPT', 'ceducarte_onepage_settings');

define('CEDUCARTE_ONEPAGE_PATH', plugin_dir_path(__FILE__));
define('CEDUCARTE_ONEPAGE_URL', plugin_dir_url(__FILE__));

require_once CEDUCARTE_ONEPAGE_PATH . 'includes/defaults.php';
require_once CEDUCARTE_ONEPAGE_PATH . 'includes/shortcode.php';
require_once CEDUCARTE_ONEPAGE_PATH . 'includes/admin.php';

add_action('wp_enqueue_scripts', function(){
  // only enqueue when shortcode is present
  if (!is_singular()) return;
  global $post;
  if (!$post || !has_shortcode($post->post_content, 'ceducarte_onepage')) return;

  wp_enqueue_style('ceducarte-onepage', CEDUCARTE_ONEPAGE_URL . 'assets/css/ceducarte-onepage.css', [], CEDUCARTE_ONEPAGE_VERSION);
  wp_enqueue_script('ceducarte-onepage', CEDUCARTE_ONEPAGE_URL . 'assets/js/ceducarte-onepage.js', [], CEDUCARTE_ONEPAGE_VERSION, true);

  $settings = Ceducarte_Onepage_Defaults::merge(get_option(CEDUCARTE_ONEPAGE_OPT, []));
  wp_localize_script('ceducarte-onepage', 'CEDUCARTE_ONEPAGE', [
    'headerOffset' => 88,
    'hasTop' => true,
    'hasReveal' => true,
  ]);

  // inline css vars from settings
  $css = Ceducarte_Onepage_Shortcode::css_vars($settings);
  if ($css) wp_add_inline_style('ceducarte-onepage', $css);
});

register_activation_hook(__FILE__, function(){
  $cur = get_option(CEDUCARTE_ONEPAGE_OPT, null);
  if ($cur === null){
    add_option(CEDUCARTE_ONEPAGE_OPT, Ceducarte_Onepage_Defaults::defaults());
  }
});
