<?php
if (!defined('ABSPATH')) exit;

class Ceducarte_Onepage_Defaults {
  public static function defaults(){
    $blocks = [];
    for($i=1;$i<=15;$i++){
      $blocks[] = [
        'enabled' => 1,
        'anchor' => 'p' . str_pad((string)$i, 2, '0', STR_PAD_LEFT),
        'nav_label' => 'Bloco ' . str_pad((string)$i, 2, '0', STR_PAD_LEFT),
        'image' => 0,
        'text_enabled' => 0,
        'text' => '',
      ];
    }

    $projects = [];
    for($i=1;$i<=8;$i++){
      $projects[] = [
        'enabled' => ($i<=4) ? 1 : 0,
        'image' => 0,
        'title' => 'Projeto ' . $i,
        'text' => '',
        'link' => '',
      ];
    }

    return [
      // branding / palette
      'brand_text' => 'Ceducarte',
      'logo' => 0, // attachment id
      'primary' => '#1E88E5',
      'accent'  => '#FB8C00',
      'bg_1'    => '#F7F9FC',
      'bg_2'    => '#EEF3FA',
      'ink'     => '#0F2A43',
      'ink_2'   => '#2C4A66',

      // hero
      'hero_title' => "CEDUCARTE\nCentro de Educação, Cultura e Arte",
      'hero_subtitle' => 'Conheça nossos projetos e ações.',
      'hero_btn1_text' => 'Conheça nossos projetos',
      'hero_btn1_url'  => '#projetos',
      'hero_btn2_text' => 'Fale conosco',
      'hero_btn2_url'  => '#contato',
      'hero_image' => 0, // attachment id

      // menu labels (B)
      'menu_custom_enabled' => 1,
      'menu_items' => "Início|#inicio\nVídeos|#videos\nBlocos|#p01\nProjetos|#projetos\nContato|#contato",

      // videos slider (fixed 6 fields)
      'videos_enabled' => 1,
      'videos_title' => 'Assista e conheça mais',
      'videos_1' => '',
      'videos_2' => '',
      'videos_3' => '',
      'videos_4' => '',
      'videos_5' => '',
      'videos_6' => '',

      // blocks
      'blocks' => $blocks,

      // projects section
      'projects_enabled' => 1,
      'projects_title' => 'Conheça nossos projetos',
      'projects' => $projects,

      // contact/footer
      'contact_title' => 'Contato',
      'contact_text' => 'Coloque aqui as informações de contato, endereço e horários.',
      'contact_btn_text' => 'Chamar no WhatsApp',
      'contact_btn_url' => '',
      'footer_text' => 'Ceducarte — Transformando vidas pela cultura, educação e esporte.',
      'footer_instagram' => '',
      'footer_tiktok' => '',
      'footer_whatsapp' => '',
      'footer_facebook' => '',
    ];
  }

  public static function merge($opt){
    $d = self::defaults();
    if (!is_array($opt)) $opt = [];
    // deep merge for blocks/projects
    $out = array_merge($d, $opt);
    if (!isset($out['blocks']) || !is_array($out['blocks'])) $out['blocks'] = $d['blocks'];
    for($i=0;$i<15;$i++){
      $out['blocks'][$i] = array_merge($d['blocks'][$i], isset($out['blocks'][$i]) && is_array($out['blocks'][$i]) ? $out['blocks'][$i] : []);
    }
    if (!isset($out['projects']) || !is_array($out['projects'])) $out['projects'] = $d['projects'];
    for($i=0;$i<8;$i++){
      $out['projects'][$i] = array_merge($d['projects'][$i], isset($out['projects'][$i]) && is_array($out['projects'][$i]) ? $out['projects'][$i] : []);
    }
    return $out;
  }
}
