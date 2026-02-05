<?php
if (!defined('ABSPATH')) exit;

class Ceducarte_Onepage_Shortcode {

  public static function register(){
    add_shortcode('ceducarte_onepage', [__CLASS__, 'render']);
  }

  public static function css_vars($s){
    $hex = function($v, $fallback){
      $v = is_string($v) ? trim($v) : '';
      if ($v === '') return $fallback;
      if (!preg_match('/^#([a-f0-9]{3}|[a-f0-9]{6})$/i', $v)) return $fallback;
      return $v;
    };
    $primary = $hex($s['primary'] ?? '', '#1E88E5');
    $accent  = $hex($s['accent'] ?? '', '#FB8C00');
    $bg1     = $hex($s['bg_1'] ?? '', '#F7F9FC');
    $bg2     = $hex($s['bg_2'] ?? '', '#EEF3FA');
    $ink     = $hex($s['ink'] ?? '', '#0F2A43');
    $ink2    = $hex($s['ink_2'] ?? '', '#2C4A66');

    return ":root{--cedu-primary:$primary;--cedu-accent:$accent;--cedu-bg:$bg1;--cedu-bg-2:$bg2;--cedu-ink:$ink;--cedu-ink-2:$ink2;}";
  }

  public static function render($atts = []){
    $settings = Ceducarte_Onepage_Defaults::merge(get_option(CEDUCARTE_ONEPAGE_OPT, []));
    $plugin_url = CEDUCARTE_ONEPAGE_URL;

    ob_start();
    include CEDUCARTE_ONEPAGE_PATH . 'templates/onepage.php';
    return ob_get_clean();
  }

  public static function media_url($val, $fallback_rel){
    $val = is_string($val) ? trim($val) : $val;
    if (is_numeric($val) && intval($val) > 0){
      $u = wp_get_attachment_image_url(intval($val), 'full');
      if ($u) return $u;
    }
    if (is_string($val) && filter_var($val, FILTER_VALIDATE_URL)){
      return esc_url($val);
    }
    return CEDUCARTE_ONEPAGE_URL . ltrim($fallback_rel, '/');
  }

  public static function social_svg($name){
    switch($name){
      case 'instagram':
        return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5zm10 2H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3z" fill="currentColor"/><path d="M12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10zm0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6z" fill="currentColor"/><circle cx="17.5" cy="6.5" r="1.1" fill="currentColor"/></svg>';
      case 'tiktok':
        return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2h2c.2 2.2 1.8 4.4 4 4.7V9c-1.6-.1-3.1-.7-4-1.6v7.3a6 6 0 1 1-6-6c.4 0 .7 0 1 .1v2.2c-.3-.1-.7-.2-1-.2a3.8 3.8 0 1 0 3.8 3.8V2z" fill="currentColor"/></svg>';
      case 'whatsapp':
        return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 4H4a2 2 0 0 0-2 2v16l4-4h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z" fill="currentColor"/></svg>';
      case 'facebook':
        return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13.5 22v-8h2.7l.4-3h-3.1V9.1c0-.9.2-1.5 1.6-1.5h1.7V5c-.3 0-1.4-.1-2.7-.1-2.7 0-4.5 1.6-4.5 4.6V11H7v3h2.6v8h3.9z" fill="currentColor"/></svg>';
      default:
        return '';
    }
  }

  public static function normalize_video($url){
    $u = trim((string)$url);
    if ($u === '') return '';
    // youtube
    if (preg_match('~(youtu\.be/|youtube\.com)~i', $u)){
      $id = '';
      if (preg_match('~youtu\.be/([A-Za-z0-9_-]{6,})~', $u, $m)) $id = $m[1];
      if (preg_match('~v=([A-Za-z0-9_-]{6,})~', $u, $m)) $id = $m[1];
      if (preg_match('~/embed/([A-Za-z0-9_-]{6,})~', $u, $m)) $id = $m[1];
      if ($id){
        return 'https://www.youtube.com/embed/' . $id . '?autoplay=1&mute=1&controls=0&playsinline=1&rel=0&modestbranding=1';
      }
      return $u;
    }
    // google drive file
    if (preg_match('~drive\.google\.com/file/d/([^/]+)/view~i', $u, $m)){
      return 'https://drive.google.com/file/d/' . $m[1] . '/preview';
    }
    return $u;
  }

  public static function is_mp4($url){
    return (bool)preg_match('~\.mp4(\?|$)~i', $url);
  }

  public static function video_slide_html($url, $is_first){
    $u = self::normalize_video($url);
    if ($u === '') return '';
    $html = '<div class="cedu-vslide"><div class="cedu-vframe">';
    if (self::is_mp4($u)){
      $html .= '<video class="cedu-vvideo" playsinline muted loop preload="metadata" ' . ($is_first?'autoplay':'') . ' data-src="'.esc_url($u).'"></video>';
      $html .= '<button class="cedu-sound" type="button" aria-label="Ativar som" data-cedu-sound>Ativar som</button>';
    } else {
      $src = $is_first ? esc_url($u) : '';
      $data = !$is_first ? ' data-src="'.esc_url($u).'"' : '';
      $html .= '<iframe class="cedu-viframe" title="Vídeo" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen ' . ($src?'src="'.$src.'"':'') . $data . '></iframe>';
    }
    $html .= '</div></div>';
    return $html;
  }
}
Ceducarte_Onepage_Shortcode::register();
