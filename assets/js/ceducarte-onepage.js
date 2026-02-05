
(function(){
  function qs(sel, root){ return (root||document).querySelector(sel); }
  function qsa(sel, root){ return Array.prototype.slice.call((root||document).querySelectorAll(sel)); }

  function smoothScrollTo(hash){
    try{
      var el = document.getElementById(hash.replace('#',''));
      if(!el) return;
      var y = el.getBoundingClientRect().top + window.pageYOffset - 88;
      window.scrollTo({top: y, behavior: 'smooth'});
    }catch(e){}
  }

  function initMenu(){
    var burger = qs('[data-cedu-burger]');
    var mobile = qs('[data-cedu-mobile]');
    if(!burger || !mobile) return;

    burger.addEventListener('click', function(){
      var open = mobile.classList.toggle('open');
      burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    qsa('a[href^="#"]', mobile).forEach(function(a){
      a.addEventListener('click', function(e){
        mobile.classList.remove('open');
        burger.setAttribute('aria-expanded', 'false');
      });
    });

    // smooth scroll for all anchors inside component
    qsa('.cedu-onepage a[href^="#"]').forEach(function(a){
      a.addEventListener('click', function(e){
        var href = a.getAttribute('href');
        if(!href || href.length < 2) return;
        var id = href.slice(1);
        if(document.getElementById(id)){
          e.preventDefault();
          smoothScrollTo(href);
        }
      });
    });
  }

  function initTop(){
    var btn = qs('[data-cedu-top]');
    if(!btn) return;
    function onScroll(){
      if(window.scrollY > 480) btn.classList.add('show');
      else btn.classList.remove('show');
    }
    window.addEventListener('scroll', onScroll, {passive:true});
    onScroll();
    btn.addEventListener('click', function(){ window.scrollTo({top:0, behavior:'smooth'}); });
  }

  function initReveal(){
    var els = qsa('.cedu-reveal');
    if(!els.length) return;
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(en){
        if(en.isIntersecting){
          en.target.classList.add('in');
          io.unobserve(en.target);
        }
      });
    }, {threshold: 0.12});
    els.forEach(function(el){
      if(el.classList.contains('in')) return;
      io.observe(el);
    });
  }

  function stopSlide(slide){
    if(!slide) return;
    var iframe = qs('iframe', slide);
    if(iframe){
      if(!iframe.dataset.src && iframe.getAttribute('src')) iframe.dataset.src = iframe.getAttribute('src');
      iframe.removeAttribute('src');
    }
    var video = qs('video', slide);
    if(video){
      try{ video.pause(); }catch(e){}
      if(!video.dataset.src && video.getAttribute('src')) video.dataset.src = video.getAttribute('src');
      video.removeAttribute('src');
      try{ video.load(); }catch(e){}
    }
  }
  function playSlide(slide){
    if(!slide) return;
    var iframe = qs('iframe', slide);
    if(iframe && iframe.dataset.src && !iframe.getAttribute('src')){
      iframe.setAttribute('src', iframe.dataset.src);
    }
    var video = qs('video', slide);
    if(video){
      if(video.dataset.src && !video.getAttribute('src')){
        video.setAttribute('src', video.dataset.src);
        try{ video.load(); }catch(e){}
      }
      // ensure autoplay (muted)
      video.muted = true;
      var p = video.play && video.play();
      if(p && p.catch) p.catch(function(){});
    }
  }

  function initSoundButtons(root){
    qsa('[data-cedu-sound]', root).forEach(function(btn){
      btn.addEventListener('click', function(){
        var slide = btn.closest('.cedu-vslide');
        if(!slide) return;
        var v = qs('video', slide);
        if(!v) return;
        v.muted = false;
        try{ v.volume = 1; }catch(e){}
        var p = v.play && v.play();
        if(p && p.catch) p.catch(function(){});
        btn.textContent = 'Som ativado';
        btn.setAttribute('aria-label','Som ativado');
        setTimeout(function(){ btn.style.display='none'; }, 800);
      });
    });
  }

  function initSlider(root){
    var track = qs('.cedu-vtrack', root);
    if(!track) return;
    var slides = Array.prototype.slice.call(track.children);
    var dots = qsa('[data-cedu-dot]', root);
    var btnPrev = qs('[data-cedu-prev]', root);
    var btnNext = qs('[data-cedu-next]', root);
    var index = 0;

    function updateDots(){
      dots.forEach(function(d){
        var di = parseInt(d.getAttribute('data-cedu-dot'), 10);
        if(di === index) d.classList.add('is-active');
        else d.classList.remove('is-active');
      });
    }
    function goTo(i){
      if(i < 0) i = slides.length - 1;
      if(i >= slides.length) i = 0;
      if(i === index && track.style.transform) return;
      stopSlide(slides[index]);
      index = i;
      track.style.transform = 'translateX(' + (-index * 100) + '%)';
      updateDots();
      playSlide(slides[index]);
      initSoundButtons(slides[index]);
    }

    if(btnPrev) btnPrev.addEventListener('click', function(){ goTo(index - 1); });
    if(btnNext) btnNext.addEventListener('click', function(){ goTo(index + 1); });
    dots.forEach(function(d){
      d.addEventListener('click', function(){
        var di = parseInt(d.getAttribute('data-cedu-dot'), 10);
        if(!isNaN(di)) goTo(di);
      });
    });

    // init: first slide may have src empty -> load
    updateDots();
    playSlide(slides[0]);
    initSoundButtons(slides[0]);
  }

  document.addEventListener('DOMContentLoaded', function(){
    initMenu();
    initTop();
    initReveal();
    qsa('[data-cedu-slider]').forEach(initSlider);
  });
})();
