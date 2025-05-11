// assets/js/frontend.js
(function($) {
  $(document).ready(function() {
    // Regola la posizione dell'overlay in base allo spazio disponibile
    $('.bl-just-finished-section .bl-item').on('mouseenter', function() {
      var $overlay = $(this).find('.cover-overlay');
      var itemOffset = $(this).offset();
      var itemWidth = $(this).outerWidth();
      var overlayWidth = $overlay.outerWidth();
      var windowWidth = $(window).width();

      // Se non c'è spazio a destra, posiziona l'overlay a sinistra
      if (itemOffset.left + itemWidth + overlayWidth > windowWidth) {
        $overlay.css({ left: 'auto', right: '100%' });
      } else {
        $overlay.css({ left: '100%', right: 'auto' });
      }
    });
  });
})(jQuery);
