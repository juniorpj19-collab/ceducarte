
jQuery(function($){
  function activate(tab){
    var $root = $('[data-cedu-tabs]');
    $root.find('.cedu-tab').removeClass('is-active');
    $root.find('.cedu-panel').removeClass('is-active');

    $root.find('.cedu-tab[data-tab="'+tab+'"]').addClass('is-active');
    $root.find('.cedu-panel[data-panel="'+tab+'"]').addClass('is-active');
  }

  $(document).on('click', '.cedu-tab', function(){
    activate($(this).data('tab'));
  });

  // accordion
  $(document).on('click', '[data-acc-head]', function(){
    $(this).closest('.cedu-acc').toggleClass('open');
  });

  // media picker
  function initMedia($wrap){
    var frame;
    $wrap.on('click', '[data-cedu-pick]', function(e){
      e.preventDefault();
      var $box = $(this).closest('[data-cedu-media]');
      var $id = $box.find('[data-cedu-media-id]');
      var $prev = $box.find('[data-cedu-media-preview]');

      frame = wp.media({
        title: 'Selecionar imagem',
        button: { text: 'Usar esta imagem' },
        multiple: false
      });

      frame.on('select', function(){
        var att = frame.state().get('selection').first().toJSON();
        $id.val(att.id);
        var url = (att.sizes && att.sizes.thumbnail) ? att.sizes.thumbnail.url : att.url;
        $prev.html('<img src="'+url+'" alt="">');
      });

      frame.open();
    });

    $wrap.on('click', '[data-cedu-clear]', function(e){
      e.preventDefault();
      var $box = $(this).closest('[data-cedu-media]');
      $box.find('[data-cedu-media-id]').val('');
      $box.find('[data-cedu-media-preview]').html('<span>Sem imagem</span>');
    });
  }
  initMedia($(document));

  // init first tab
  activate('geral');
});
