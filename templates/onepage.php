<?php
if (!defined('ABSPATH')) exit;
$s = $settings;

$esc = function($v){ return esc_html((string)$v); };
$txt = function($k) use ($s){ return isset($s[$k]) ? (string)$s[$k] : ''; };

$media = function($key, $fallback_rel) use ($s){
  $val = $s[$key] ?? 0;
  return Ceducarte_Onepage_Shortcode::media_url($val, $fallback_rel);
};

$btn_href = function($raw){
  $v = trim((string)$raw);
  if($v==='') return '';
  if(strpos($v,'#')===0){
    $anchor = preg_replace('/[^a-zA-Z0-9_\-]/','', substr($v,1));
    return '#'.$anchor;
  }
  if(preg_match('/^(tel:|mailto:)/i', $v)) return esc_attr($v);
  return esc_url($v);
};

$menu = [];
if (!empty($s['menu_custom_enabled'])) {
  $raw = trim((string)($s['menu_items'] ?? ''));
  if ($raw !== ''){
    $lines = preg_split('/\r\n|\r|\n/', $raw);
    foreach($lines as $ln){
      $ln = trim($ln);
      if(!$ln) continue;
      $parts = explode('|', $ln, 2);
      if(count($parts)===2){
        $label = trim($parts[0]);
        $href  = trim($parts[1]);
        if($label!=='' && $href!=='') $menu[] = ['label'=>$label, 'href'=>$href];
      }
    }
  }
}

$video_urls = [];
if (!empty($s['videos_enabled'])){
  for($i=1;$i<=6;$i++){
    $u = trim((string)($s['videos_'.$i] ?? ''));
    if($u!=='') $video_urls[] = $u;
  }
}

$footer_social = [
  'instagram' => trim((string)($s['footer_instagram'] ?? '')),
  'tiktok' => trim((string)($s['footer_tiktok'] ?? '')),
  'whatsapp' => trim((string)($s['footer_whatsapp'] ?? '')),
  'facebook' => trim((string)($s['footer_facebook'] ?? '')),
];

?><div class="cedu-onepage" id="inicio">
  <div class="cedu-header cedu-space-top">
    <div class="cedu-wrap">
      <nav class="cedu-nav cedu-reveal in" aria-label="Menu Ceducarte">
        <div class="cedu-brand">
          <img src="<?php echo esc_url($media('logo','assets/img/geom.svg')); ?>" alt="Logo" />
          <div class="cedu-brand-text"><?php echo $esc($txt('brand_text')); ?></div>
        </div>

        <div class="cedu-menu" aria-label="Menu">
          <?php foreach($menu as $it): ?>
            <a href="<?php echo esc_attr($btn_href($it['href'])); ?>"><?php echo $esc($it['label']); ?></a>
          <?php endforeach; ?>
        </div>

        <button class="cedu-burger" type="button" data-cedu-burger aria-label="Abrir menu" aria-expanded="false">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 7h14M5 12h14M5 17h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </button>
      </nav>

      <div class="cedu-mobile" data-cedu-mobile>
        <div class="cedu-mobile-panel">
          <?php foreach($menu as $it): ?>
            <a href="<?php echo esc_attr($btn_href($it['href'])); ?>">
              <span><?php echo $esc($it['label']); ?></span>
              <small>→</small>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <header class="cedu-hero cedu-space">
    <div class="cedu-wrap">
      <div class="cedu-hero-split">
        <div class="cedu-hero-left cedu-reveal">
          <h1 class="cedu-h1"><?php echo nl2br($esc($txt('hero_title'))); ?></h1>
          <p class="cedu-lead"><?php echo $esc($txt('hero_subtitle')); ?></p>
          <div class="cedu-btns">
            <?php if($txt('hero_btn1_text') && $txt('hero_btn1_url')): ?>
              <a class="cedu-btn primary" href="<?php echo esc_attr($btn_href($txt('hero_btn1_url'))); ?>"><?php echo $esc($txt('hero_btn1_text')); ?></a>
            <?php endif; ?>
            <?php if($txt('hero_btn2_text') && $txt('hero_btn2_url')): ?>
              <a class="cedu-btn" href="<?php echo esc_attr($btn_href($txt('hero_btn2_url'))); ?>"><?php echo $esc($txt('hero_btn2_text')); ?></a>
            <?php endif; ?>
          </div>
        </div>

        <div class="cedu-hero-right cedu-reveal">
          <div class="cedu-hero-glow"></div>
          <img class="cedu-hero-photo" src="<?php echo esc_url($media('hero_image','assets/img/hero.png')); ?>" alt="Ceducarte" />
        </div>
      </div>
    </div>
  </header>

  <?php if(!empty($s['videos_enabled']) && !empty($video_urls)): ?>
    <section class="cedu-space cedu-videos" id="videos" aria-label="Vídeos">
      <div class="cedu-wrap">
        <div class="cedu-reveal">
          <h2 class="cedu-section-title"><?php echo $esc($txt('videos_title')); ?></h2>
        </div>

        <div class="cedu-video-slider" data-cedu-slider>
          <button class="cedu-vnav prev" type="button" aria-label="Anterior" data-cedu-prev>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18l-6-6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>

          <div class="cedu-vviewport">
            <div class="cedu-vtrack">
              <?php foreach($video_urls as $i=>$u){ echo Ceducarte_Onepage_Shortcode::video_slide_html($u, $i===0); } ?>
            </div>
          </div>

          <button class="cedu-vnav next" type="button" aria-label="Próximo" data-cedu-next>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>

          <div class="cedu-vdots" data-cedu-dots>
            <?php foreach($video_urls as $i=>$u): ?>
              <button type="button" class="cedu-vdot <?php echo $i===0?'is-active':''; ?>" aria-label="Vídeo <?php echo ($i+1); ?>" data-cedu-dot="<?php echo $i; ?>"></button>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <?php
    $blocks = isset($s['blocks']) && is_array($s['blocks']) ? $s['blocks'] : [];
    for($i=0;$i<15;$i++):
      $b = isset($blocks[$i]) ? $blocks[$i] : null;
      if(!$b || empty($b['enabled'])) continue;
      $num = $i+1;
      $anchor = $b['anchor'];
      $fallback = 'assets/img/page_' . str_pad((string)$num, 2, '0', STR_PAD_LEFT) . '.png';
      $img = Ceducarte_Onepage_Shortcode::media_url($b['image'], $fallback);
  ?>
    <section class="cedu-space cedu-block" id="<?php echo esc_attr($anchor); ?>" aria-label="Bloco <?php echo esc_attr($anchor); ?>">
      <div class="cedu-wrap">
        <?php $has_text = (!empty($b['text_enabled']) && trim((string)$b['text'])!==''); ?>
        <div class="cedu-block-card cedu-reveal">
          <div class="cedu-block-grid <?php echo $has_text ? 'has-text' : 'no-text'; ?> <?php echo ($i%2===1) ? 'is-even' : 'is-odd'; ?>">
            <?php if($has_text): ?>
              <div class="cedu-block-content">
                <?php echo wpautop(wp_kses_post($b['text'])); ?>
              </div>
            <?php endif; ?>
            <div class="cedu-block-media">
              <img class="cedu-block-img" src="<?php echo esc_url($img); ?>" alt="Bloco <?php echo esc_attr($anchor); ?>">
            </div>
          </div>
        </div>
      </div>
    </section>
  <?php endfor; ?>

  <?php if(!empty($s['projects_enabled'])): ?>
    <section class="cedu-space cedu-projects" id="projetos" aria-label="Projetos">
      <div class="cedu-wrap">
        <h2 class="cedu-section-title cedu-reveal"><?php echo $esc($txt('projects_title')); ?></h2>

        <div class="cedu-project-list">
          <?php
            $projects = isset($s['projects']) && is_array($s['projects']) ? $s['projects'] : [];
            foreach($projects as $p):
              if(empty($p['enabled'])) continue;
              $pimg = Ceducarte_Onepage_Shortcode::media_url($p['image'], 'assets/img/footer.png');
              $plink = trim((string)($p['link'] ?? ''));
          ?>
            <article class="cedu-proj cedu-reveal">
              <img src="<?php echo esc_url($pimg); ?>" alt="<?php echo esc_attr($p['title']); ?>">
              <div class="cedu-proj-body">
                <h3><?php echo $esc($p['title']); ?></h3>
                <?php if(trim((string)$p['text'])!==''): ?><p><?php echo $esc($p['text']); ?></p><?php endif; ?>
                <?php if($plink!==''): ?><a class="cedu-btn small" href="<?php echo esc_attr($btn_href($plink)); ?>">Saiba mais</a><?php endif; ?>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <section class="cedu-space cedu-contact" id="contato" aria-label="Contato">
    <div class="cedu-wrap">
      <div class="cedu-contact-card cedu-reveal">
        <h2 class="cedu-section-title" style="margin:0 0 10px;"><?php echo $esc($txt('contact_title')); ?></h2>
        <div class="cedu-text"><?php echo wpautop(esc_html($txt('contact_text'))); ?></div>

        <?php if($txt('contact_btn_text') && $txt('contact_btn_url')): ?>
          <div class="cedu-btns" style="margin-top:10px;">
            <a class="cedu-btn primary" href="<?php echo esc_attr($btn_href($txt('contact_btn_url'))); ?>"><?php echo $esc($txt('contact_btn_text')); ?></a>
          </div>
        <?php endif; ?>

        <div class="cedu-footerline">
          <b><?php echo $esc($txt('footer_text')); ?></b>
          <div class="cedu-social" aria-label="Redes sociais">
            <?php foreach($footer_social as $k=>$v): if(!$v) continue; ?>
              <a href="<?php echo esc_url($v); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr(ucfirst($k)); ?>"><?php echo Ceducarte_Onepage_Shortcode::social_svg($k); ?></a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </section>

  <button class="cedu-top" type="button" aria-label="Subir ao topo" data-cedu-top>
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5l-7 7h4v7h6v-7h4z" fill="currentColor"/></svg>
  </button>
</div>
