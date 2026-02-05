<?php
if (!defined('ABSPATH')) exit;

class Ceducarte_Onepage_Admin {

  public static function init(){
    add_action('admin_menu', [__CLASS__, 'menu']);
    add_action('admin_init', [__CLASS__, 'settings']);
    add_action('admin_enqueue_scripts', [__CLASS__, 'assets']);
  }

  public static function menu(){
    add_menu_page(
      'Ceducarte Onepage',
      'Ceducarte Onepage',
      'manage_options',
      'ceducarte-onepage',
      [__CLASS__, 'page'],
      'dashicons-welcome-widgets-menus',
      62
    );
  }

  public static function assets($hook){
    if ($hook !== 'toplevel_page_ceducarte-onepage') return;
    wp_enqueue_media();
    wp_enqueue_style('cedu-admin', CEDUCARTE_ONEPAGE_URL . 'assets/css/admin.css', [], CEDUCARTE_ONEPAGE_VERSION);
    wp_enqueue_script('cedu-admin', CEDUCARTE_ONEPAGE_URL . 'assets/js/admin.js', ['jquery'], CEDUCARTE_ONEPAGE_VERSION, true);
  }

  public static function settings(){
    register_setting('ceducarte_onepage_group', CEDUCARTE_ONEPAGE_OPT, [__CLASS__, 'sanitize']);
  }

  public static function sanitize($input){
    $d = Ceducarte_Onepage_Defaults::defaults();
    if (!is_array($input)) $input = [];

    $out = Ceducarte_Onepage_Defaults::merge($input);

    $text_keys = [
      'brand_text','hero_title','hero_subtitle','hero_btn1_text','hero_btn1_url','hero_btn2_text','hero_btn2_url',
      'menu_items','videos_title','projects_title','contact_title','contact_text','contact_btn_text','contact_btn_url',
      'footer_text','footer_instagram','footer_tiktok','footer_whatsapp','footer_facebook'
    ];
    foreach($text_keys as $k){
      if (isset($input[$k])) $out[$k] = sanitize_text_field($input[$k]);
    }

    // colors
    foreach(['primary','accent','bg_1','bg_2','ink','ink_2'] as $k){
      $v = isset($input[$k]) ? trim((string)$input[$k]) : '';
      $out[$k] = preg_match('/^#([a-f0-9]{3}|[a-f0-9]{6})$/i', $v) ? $v : $d[$k];
    }

    // attachments
    foreach(['logo','hero_image'] as $k){
      $out[$k] = isset($input[$k]) ? intval($input[$k]) : 0;
    }

    // toggles
    foreach(['videos_enabled','projects_enabled','menu_custom_enabled'] as $k){
      $out[$k] = !empty($input[$k]) ? 1 : 0;
    }

    // videos (fixed 6)
    for($i=1;$i<=6;$i++){
      $k = 'videos_'.$i;
      $out[$k] = isset($input[$k]) ? esc_url_raw(trim((string)$input[$k])) : '';
    }

    // blocks
    if (isset($input['blocks']) && is_array($input['blocks'])){
      for($i=0;$i<15;$i++){
        $b = isset($input['blocks'][$i]) && is_array($input['blocks'][$i]) ? $input['blocks'][$i] : [];
        $out['blocks'][$i]['enabled'] = !empty($b['enabled']) ? 1 : 0;
        $out['blocks'][$i]['nav_label'] = isset($b['nav_label']) ? sanitize_text_field($b['nav_label']) : $out['blocks'][$i]['nav_label'];
        $out['blocks'][$i]['image'] = isset($b['image']) ? intval($b['image']) : 0;
        $out['blocks'][$i]['text_enabled'] = !empty($b['text_enabled']) ? 1 : 0;
        $out['blocks'][$i]['text'] = isset($b['text']) ? wp_kses_post($b['text']) : '';
        // anchor readonly from defaults to keep stable
        $out['blocks'][$i]['anchor'] = $d['blocks'][$i]['anchor'];
      }
    }

    // projects
    if (isset($input['projects']) && is_array($input['projects'])){
      for($i=0;$i<8;$i++){
        $p = isset($input['projects'][$i]) && is_array($input['projects'][$i]) ? $input['projects'][$i] : [];
        $out['projects'][$i]['enabled'] = !empty($p['enabled']) ? 1 : 0;
        $out['projects'][$i]['image'] = isset($p['image']) ? intval($p['image']) : 0;
        $out['projects'][$i]['title'] = isset($p['title']) ? sanitize_text_field($p['title']) : $out['projects'][$i]['title'];
        $out['projects'][$i]['text'] = isset($p['text']) ? sanitize_textarea_field($p['text']) : '';
        $out['projects'][$i]['link'] = isset($p['link']) ? esc_url_raw(trim((string)$p['link'])) : '';
      }
    }

    return $out;
  }

  public static function page(){
    if (!current_user_can('manage_options')) return;

    $settings = Ceducarte_Onepage_Defaults::merge(get_option(CEDUCARTE_ONEPAGE_OPT, []));
    ?>
    <div class="wrap cedu-admin">
      <h1>Ceducarte Onepage</h1>
      <p class="description">Edite tudo: cores, logo, textos, botões, vídeos, imagens de cada bloco e cartões de projetos.</p>

      <form method="post" action="options.php">
        <?php settings_fields('ceducarte_onepage_group'); ?>

        <div class="cedu-tabs" data-cedu-tabs>
          <div class="cedu-tabbar">
            <button type="button" class="cedu-tab is-active" data-tab="geral">Geral</button>
            <button type="button" class="cedu-tab" data-tab="hero">Hero</button>
            <button type="button" class="cedu-tab" data-tab="videos">Vídeos</button>
            <button type="button" class="cedu-tab" data-tab="blocos">Blocos (PDF)</button>
            <button type="button" class="cedu-tab" data-tab="projetos">Projetos</button>
            <button type="button" class="cedu-tab" data-tab="rodape">Rodapé</button>
          </div>

          <div class="cedu-panels">
            <?php self::panel_geral($settings); ?>
            <?php self::panel_hero($settings); ?>
            <?php self::panel_videos($settings); ?>
            <?php self::panel_blocos($settings); ?>
            <?php self::panel_projetos($settings); ?>
            <?php self::panel_rodape($settings); ?>
          </div>
        </div>

        <?php submit_button('Salvar alterações'); ?>
      </form>

      <div class="cedu-help">
        <b>Shortcode:</b> <code>[ceducarte_onepage]</code>
      </div>
    </div>
    <?php
  }

  private static function field_media($name, $val, $label='Selecionar imagem'){
    $id = esc_attr($val);
    $img = $val ? wp_get_attachment_image_url(intval($val), 'thumbnail') : '';
    ?>
    <div class="cedu-media" data-cedu-media>
      <input type="hidden" name="<?php echo esc_attr($name); ?>" value="<?php echo $id; ?>" data-cedu-media-id>
      <div class="cedu-media-preview" data-cedu-media-preview>
        <?php if($img): ?><img src="<?php echo esc_url($img); ?>" alt=""><?php else: ?><span>Sem imagem</span><?php endif; ?>
      </div>
      <div class="cedu-media-actions">
        <button type="button" class="button button-secondary" data-cedu-pick><?php echo esc_html($label); ?></button>
        <button type="button" class="button button-link-delete" data-cedu-clear>Remover</button>
      </div>
    </div>
    <?php
  }

  private static function panel_geral($s){
    ?>
    <section class="cedu-panel is-active" data-panel="geral">
      <div class="cedu-grid">
        <div class="cedu-card">
          <h3>Marca</h3>
          <label class="cedu-field">
            <span>Texto da marca</span>
            <input type="text" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[brand_text]" value="<?php echo esc_attr($s['brand_text']); ?>">
          </label>
          <label class="cedu-field">
            <span>Logo (cabeçalho)</span>
            <?php self::field_media(CEDUCARTE_ONEPAGE_OPT.'[logo]', $s['logo']); ?>
          </label>
        </div>

        <div class="cedu-card">
          <h3>Paleta</h3>
          <div class="cedu-row">
            <label class="cedu-field"><span>Primária (azul)</span><input type="text" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[primary]" value="<?php echo esc_attr($s['primary']); ?>"></label>
            <label class="cedu-field"><span>Destaque (laranja)</span><input type="text" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[accent]" value="<?php echo esc_attr($s['accent']); ?>"></label>
          </div>
          <div class="cedu-row">
            <label class="cedu-field"><span>Fundo 1</span><input type="text" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[bg_1]" value="<?php echo esc_attr($s['bg_1']); ?>"></label>
            <label class="cedu-field"><span>Fundo 2</span><input type="text" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[bg_2]" value="<?php echo esc_attr($s['bg_2']); ?>"></label>
          </div>
          <div class="cedu-row">
            <label class="cedu-field"><span>Texto</span><input type="text" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[ink]" value="<?php echo esc_attr($s['ink']); ?>"></label>
            <label class="cedu-field"><span>Texto 2</span><input type="text" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[ink_2]" value="<?php echo esc_attr($s['ink_2']); ?>"></label>
          </div>
          <p class="description">Dica: use HEX (#RRGGBB). Ex: <code>#1E88E5</code></p>
        </div>

        <div class="cedu-card">
          <h3>Menu (Âncoras)</h3>
          <label class="cedu-field inline">
            <input type="checkbox" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[menu_custom_enabled]" value="1" <?php checked($s['menu_custom_enabled'],1); ?>>
            <span>Usar menu personalizado (opção B)</span>
          </label>
          <label class="cedu-field">
            <span>Itens do menu (1 por linha: <code>Texto|#ancora</code>)</span>
            <textarea rows="6" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[menu_items]"><?php echo esc_textarea($s['menu_items']); ?></textarea>
          </label>
          <p class="description">Âncoras úteis: <code>#inicio</code>, <code>#videos</code>, <code>#p01</code> ... <code>#p15</code>, <code>#projetos</code>, <code>#contato</code></p>
        </div>
      </div>
    </section>
    <?php
  }

  private static function panel_hero($s){
    ?>
    <section class="cedu-panel" data-panel="hero">
      <div class="cedu-grid">
        <div class="cedu-card">
          <h3>Hero</h3>
          <label class="cedu-field"><span>Título</span>
            <textarea rows="3" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[hero_title]"><?php echo esc_textarea($s['hero_title']); ?></textarea>
          </label>
          <label class="cedu-field"><span>Subtítulo</span>
            <input type="text" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[hero_subtitle]" value="<?php echo esc_attr($s['hero_subtitle']); ?>">
          </label>

          <div class="cedu-row">
            <label class="cedu-field"><span>Botão 1 (texto)</span><input type="text" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[hero_btn1_text]" value="<?php echo esc_attr($s['hero_btn1_text']); ?>"></label>
            <label class="cedu-field"><span>Botão 1 (link)</span><input type="text" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[hero_btn1_url]" value="<?php echo esc_attr($s['hero_btn1_url']); ?>"></label>
          </div>
          <div class="cedu-row">
            <label class="cedu-field"><span>Botão 2 (texto)</span><input type="text" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[hero_btn2_text]" value="<?php echo esc_attr($s['hero_btn2_text']); ?>"></label>
            <label class="cedu-field"><span>Botão 2 (link)</span><input type="text" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[hero_btn2_url]" value="<?php echo esc_attr($s['hero_btn2_url']); ?>"></label>
          </div>

          <label class="cedu-field"><span>Imagem do Hero</span>
            <?php self::field_media(CEDUCARTE_ONEPAGE_OPT.'[hero_image]', $s['hero_image']); ?>
          </label>
        </div>
      </div>
    </section>
    <?php
  }

  private static function panel_videos($s){
    ?>
    <section class="cedu-panel" data-panel="videos">
      <div class="cedu-grid">
        <div class="cedu-card">
          <h3>Slider de vídeos</h3>
          <label class="cedu-field inline">
            <input type="checkbox" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[videos_enabled]" value="1" <?php checked($s['videos_enabled'],1); ?>>
            <span>Mostrar slider de vídeos (logo após o Hero)</span>
          </label>

          <label class="cedu-field"><span>Título da seção</span>
            <input type="text" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[videos_title]" value="<?php echo esc_attr($s['videos_title']); ?>">
          </label>

          <div class="cedu-note">Suporta: YouTube, MP4 do site, Google Drive (/preview) e outros serviços via iframe.</div>

          <?php for($i=1;$i<=6;$i++): ?>
            <label class="cedu-field">
              <span>Vídeo <?php echo $i; ?></span>
              <input type="text" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[videos_<?php echo $i; ?>]" value="<?php echo esc_attr($s['videos_'.$i]); ?>" placeholder="https://...">
            </label>
          <?php endfor; ?>
        </div>
      </div>
    </section>
    <?php
  }

  private static function panel_blocos($s){
    ?>
    <section class="cedu-panel" data-panel="blocos">
      <div class="cedu-card">
        <h3>Blocos do PDF (15)</h3>
        <p class="description">Cada bloco tem imagem (padrão do PDF) + texto opcional abaixo. As âncoras são fixas (#p01 a #p15).</p>

        <div class="cedu-accordion" data-cedu-accordion>
          <?php for($i=0;$i<15;$i++):
            $b = $s['blocks'][$i];
            $num = $i+1;
          ?>
            <div class="cedu-acc">
              <button type="button" class="cedu-acc-head" data-acc-head>
                <span>Bloco <?php echo str_pad((string)$num,2,'0',STR_PAD_LEFT); ?></span>
                <code>#<?php echo esc_html($b['anchor']); ?></code>
              </button>
              <div class="cedu-acc-body" data-acc-body>
                <div class="cedu-row">
                  <label class="cedu-field inline">
                    <input type="checkbox" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[blocks][<?php echo $i; ?>][enabled]" value="1" <?php checked($b['enabled'],1); ?>>
                    <span>Mostrar este bloco</span>
                  </label>
                  <label class="cedu-field">
                    <span>Label no menu</span>
                    <input type="text" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[blocks][<?php echo $i; ?>][nav_label]" value="<?php echo esc_attr($b['nav_label']); ?>">
                  </label>
                </div>

                <label class="cedu-field"><span>Imagem do bloco</span>
                  <?php self::field_media(CEDUCARTE_ONEPAGE_OPT.'[blocks]['.$i.'][image]', $b['image'], 'Selecionar imagem'); ?>
                </label>

                <label class="cedu-field inline">
                  <input type="checkbox" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[blocks][<?php echo $i; ?>][text_enabled]" value="1" <?php checked($b['text_enabled'],1); ?>>
                  <span>Mostrar texto abaixo da imagem</span>
                </label>

                <label class="cedu-field"><span>Texto (opcional)</span>
                  <textarea rows="4" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[blocks][<?php echo $i; ?>][text]"><?php echo esc_textarea($b['text']); ?></textarea>
                </label>
              </div>
            </div>
          <?php endfor; ?>
        </div>
      </div>
    </section>
    <?php
  }

  private static function panel_projetos($s){
    ?>
    <section class="cedu-panel" data-panel="projetos">
      <div class="cedu-card">
        <h3>Projetos (Cards 1 por linha)</h3>
        <label class="cedu-field inline">
          <input type="checkbox" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[projects_enabled]" value="1" <?php checked($s['projects_enabled'],1); ?>>
          <span>Mostrar seção de projetos (#projetos)</span>
        </label>
        <label class="cedu-field">
          <span>Título da seção</span>
          <input type="text" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[projects_title]" value="<?php echo esc_attr($s['projects_title']); ?>">
        </label>

        <div class="cedu-note">Cada card: imagem + (opcional) texto abaixo. Links podem ser preenchidos depois.</div>

        <?php for($i=0;$i<8;$i++): $p=$s['projects'][$i]; ?>
          <div class="cedu-subcard">
            <div class="cedu-row" style="align-items:center;">
              <label class="cedu-field inline" style="margin:0;">
                <input type="checkbox" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[projects][<?php echo $i; ?>][enabled]" value="1" <?php checked($p['enabled'],1); ?>>
                <span>Ativo</span>
              </label>
              <b>Projeto <?php echo ($i+1); ?></b>
            </div>

            <label class="cedu-field"><span>Imagem</span>
              <?php self::field_media(CEDUCARTE_ONEPAGE_OPT.'[projects]['.$i.'][image]', $p['image'], 'Selecionar'); ?>
            </label>

            <label class="cedu-field"><span>Título</span>
              <input type="text" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[projects][<?php echo $i; ?>][title]" value="<?php echo esc_attr($p['title']); ?>">
            </label>

            <label class="cedu-field"><span>Texto (opcional)</span>
              <textarea rows="3" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[projects][<?php echo $i; ?>][text]"><?php echo esc_textarea($p['text']); ?></textarea>
            </label>

            <label class="cedu-field"><span>Link (opcional)</span>
              <input type="text" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[projects][<?php echo $i; ?>][link]" value="<?php echo esc_attr($p['link']); ?>" placeholder="https://... ou #ancora">
            </label>
          </div>
        <?php endfor; ?>
      </div>
    </section>
    <?php
  }

  private static function panel_rodape($s){
    ?>
    <section class="cedu-panel" data-panel="rodape">
      <div class="cedu-grid">
        <div class="cedu-card">
          <h3>Contato</h3>
          <label class="cedu-field"><span>Título</span>
            <input type="text" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[contact_title]" value="<?php echo esc_attr($s['contact_title']); ?>">
          </label>
          <label class="cedu-field"><span>Texto</span>
            <textarea rows="4" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[contact_text]"><?php echo esc_textarea($s['contact_text']); ?></textarea>
          </label>

          <div class="cedu-row">
            <label class="cedu-field"><span>Botão (texto)</span><input type="text" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[contact_btn_text]" value="<?php echo esc_attr($s['contact_btn_text']); ?>"></label>
            <label class="cedu-field"><span>Botão (link)</span><input type="text" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[contact_btn_url]" value="<?php echo esc_attr($s['contact_btn_url']); ?>"></label>
          </div>
        </div>

        <div class="cedu-card">
          <h3>Rodapé</h3>
          <label class="cedu-field"><span>Texto do rodapé</span>
            <input type="text" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[footer_text]" value="<?php echo esc_attr($s['footer_text']); ?>">
          </label>

          <div class="cedu-row">
            <label class="cedu-field"><span>Instagram</span><input type="text" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[footer_instagram]" value="<?php echo esc_attr($s['footer_instagram']); ?>"></label>
            <label class="cedu-field"><span>TikTok</span><input type="text" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[footer_tiktok]" value="<?php echo esc_attr($s['footer_tiktok']); ?>"></label>
          </div>
          <div class="cedu-row">
            <label class="cedu-field"><span>WhatsApp</span><input type="text" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[footer_whatsapp]" value="<?php echo esc_attr($s['footer_whatsapp']); ?>"></label>
            <label class="cedu-field"><span>Facebook</span><input type="text" name="<?php echo CEDUCARTE_ONEPAGE_OPT; ?>[footer_facebook]" value="<?php echo esc_attr($s['footer_facebook']); ?>"></label>
          </div>
          <p class="description">Os ícones só aparecem se tiver URL preenchida.</p>
        </div>
      </div>
    </section>
    <?php
  }
}
Ceducarte_Onepage_Admin::init();
