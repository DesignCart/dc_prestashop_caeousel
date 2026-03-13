/**
 * DC Carousel – inicjalizacja Owl Carousel tylko dla naszego modułu.
 * - korzysta z jQuery + Owl, jeśli są dostępne,
 * - nie dotyka innych karuzel,
 * - GLightbox działa tylko w obrębie modułu.
 */
(function () {
  'use strict';

  function initGLightbox() {
    if (typeof GLightbox === 'undefined') {
      return;
    }

    GLightbox({
      selector: '.dc-carousel-wrapper .glightbox'
    });
  }

  function getJQuery() {
    if (typeof jQuery !== 'undefined') {
      return jQuery;
    }
    if (typeof $ !== 'undefined') {
      return $;
    }
    return null;
  }

  function owlLoaded($) {
    return !!($ && $.fn && $.fn.owlCarousel);
  }

  function initOwlCarousels() {
    var $ = getJQuery();

    // GLightbox możemy zainicjować niezależnie od Owl
    initGLightbox();

    if (!owlLoaded($)) {
      // Brak Owl – zostawiamy statyczny układ, ale lightbox działa.
      return;
    }

    document.querySelectorAll('.dc-carousel-wrapper').forEach(function (wrapper) {
      var track = wrapper.querySelector('.dc-carousel');
      if (!track || !track.querySelector('.dc-carousel-item')) return;

      var $track = $(track);
      if ($track.data('dc-owl-initialized')) {
        return;
      }

      var itemsDesktop = parseInt(track.getAttribute('data-items-desktop') || '4', 10);
      var itemsTablet = parseInt(track.getAttribute('data-items-tablet') || '2', 10);
      var itemsMobile = parseInt(track.getAttribute('data-items-mobile') || '1', 10);
      var speed = parseInt(track.getAttribute('data-speed') || '4000', 10);
      var autoplay = track.getAttribute('data-autoplay') === '1' || track.getAttribute('data-autoplay') === 'true';

      $track.owlCarousel({
        items: itemsDesktop,
        loop: true,
        nav: false,
        dots: false,
        autoplay: autoplay,
        autoplayTimeout: speed,
        autoplayHoverPause: true,
        margin: 10,
        responsive: {
          0: { items: itemsMobile },
          768: { items: itemsTablet },
          1024: { items: itemsDesktop }
        }
      });

      $track.data('dc-owl-initialized', true);

      var prevBtn = wrapper.querySelector('.dc-nav-prev');
      var nextBtn = wrapper.querySelector('.dc-nav-next');
      if (prevBtn) prevBtn.addEventListener('click', function (e) { e.preventDefault(); $track.trigger('prev.owl.carousel'); });
      if (nextBtn) nextBtn.addEventListener('click', function (e) { e.preventDefault(); $track.trigger('next.owl.carousel'); });
    });
  }

  function run() {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initOwlCarousels);
    } else {
      initOwlCarousels();
    }
  }

  run();
})();
