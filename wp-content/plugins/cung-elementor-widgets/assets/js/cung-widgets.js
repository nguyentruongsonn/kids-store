/**
 * Cưng Store Elementor Widgets - Interactive JS
 * Handles Countdown Timer and Product Tabs Switching
 */

(function ($) {
  'use strict';

  // 1. FLASH SALE COUNTDOWN HANDLER
  function initCountdown($scope) {
    var $countdown = $scope ? $scope.find('.cung-countdown-wrap') : $('.cung-countdown-wrap');

    $countdown.each(function () {
      var $this = $(this);
      var endTimeStr = $this.data('end-time');
      if (!endTimeStr) return;

      var targetDate = new Date(endTimeStr.replace(/-/g, '/')).getTime();
      if (isNaN(targetDate)) {
        targetDate = new Date().getTime() + 8 * 60 * 60 * 1000;
      }

      var $hours = $this.find('.cung-hours');
      var $minutes = $this.find('.cung-minutes');
      var $seconds = $this.find('.cung-seconds');

      function updateTimer() {
        var now = new Date().getTime();
        var distance = targetDate - now;

        if (distance <= 0) {
          $hours.text('00');
          $minutes.text('00');
          $seconds.text('00');
          return;
        }

        var hours = Math.floor(distance / (1000 * 60 * 60));
        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((distance % (1000 * 60)) / 1000);

        $hours.text(hours < 10 ? '0' + hours : hours);
        $minutes.text(minutes < 10 ? '0' + minutes : minutes);
        $seconds.text(seconds < 10 ? '0' + seconds : seconds);
      }

      updateTimer();
      setInterval(updateTimer, 1000);
    });
  }

  // 2. PRODUCT TABS SWITCHER
  function initTabs($scope) {
    var $tabSections = $scope ? $scope.find('.cung-product-tabs-wrapper') : $('.cung-product-tabs-wrapper');

    $tabSections.each(function () {
      var $section = $(this);
      var $navBtns = $section.find('.cung-tab-btn');
      var $panels = $section.find('.cung-tab-panel');

      $navBtns.on('click', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var targetId = $btn.data('target');

        $navBtns.removeClass('is-active').attr('aria-selected', 'false');
        $btn.addClass('is-active').attr('aria-selected', 'true');

        $panels.removeClass('is-active');
        $section.find('#' + targetId).addClass('is-active');
      });
    });
  }

  // 3. HERO BACKGROUND AUTO SLIDESHOW WITH DYNAMIC CONFIG & CONTROLS
  function initHeroSlideshow() {
    var $hero = $('.elementor-element-e06974b');
    if (!$hero.length) return;

    var config = window.cungHeroConfig || {};
    var slides = config.slides && config.slides.length ? config.slides : [
      {
        src: 'http://localhost/cung/wp-content/uploads/2026/09/08d91db3-9019-4aae-aadb-33c40f102c0c.png',
        title: 'Đầm Hoa Thêu Cổ Sen',
        link: 'http://localhost/cung/san-pham/'
      },
      {
        src: 'http://localhost/cung/wp-content/uploads/2026/09/410483d4-1bf3-46ec-b12a-ccf899600ba9-1.png',
        title: 'Áo Dệt Kim Kẻ Sọc',
        link: 'http://localhost/cung/san-pham/'
      },
      {
        src: 'http://localhost/cung/wp-content/uploads/2026/09/dam-be-gai-soc-1081x980-1.png',
        title: 'Đầm Đũi Mộc Tự Nhiên',
        link: 'http://localhost/cung/san-pham/'
      }
    ];

    var intervalTime = config.interval ? parseInt(config.interval, 10) : 5000;
    var fitMode = config.fit || 'cover';

    // Remove existing to re-render fresh
    $hero.find('.cung-hero-slideshow').remove();

    var $slideshow = $('<div class="cung-hero-slideshow fit-' + fitMode + '" aria-label="Bộ sưu tập nổi bật"></div>');
    var $slidesContainer = $('<div class="cung-hero-slides-inner"></div>');
    var $controls = $('<div class="cung-hero-controls"></div>');
    var $prevBtn = $('<button type="button" class="cung-hero-arrow cung-hero-prev" aria-label="Ảnh trước">‹</button>');
    var $nextBtn = $('<button type="button" class="cung-hero-arrow cung-hero-next" aria-label="Ảnh kế tiếp">›</button>');
    var $dotsContainer = $('<div class="cung-hero-dots"></div>');

    $.each(slides, function (idx, item) {
      var activeClass = (idx === 0) ? ' is-active' : '';
      var linkTagOpen = item.link ? '<a href="' + item.link + '" class="cung-hero-slide-link">' : '';
      var linkTagClose = item.link ? '</a>' : '';

      var $slide = $(
        '<div class="cung-hero-slide' + activeClass + '" style="background-image: url(\'' + item.src + '\')" data-index="' + idx + '" title="' + (item.title || '') + '">' +
          linkTagOpen + '<span class="cung-slide-clickable-area"></span>' + linkTagClose +
        '</div>'
      );
      $slidesContainer.append($slide);

      $dotsContainer.append(
        '<button type="button" class="cung-hero-dot' + activeClass + '" data-index="' + idx + '" aria-label="Chuyển đến ảnh ' + (idx + 1) + '"><span class="cung-hero-dot-fill"></span></button>'
      );
    });

    $controls.append($prevBtn).append($dotsContainer).append($nextBtn);
    $slideshow.append($slidesContainer).append($controls);

    var $titleCol = $hero.find('.elementor-element-125e13c');
    if ($titleCol.length) {
      $titleCol.after($slideshow);
    } else {
      $hero.prepend($slideshow);
    }

    var $slideItems = $hero.find('.cung-hero-slide');
    var $dotItems = $hero.find('.cung-hero-dot');
    var totalSlides = $slideItems.length;
    if (totalSlides <= 1) {
      $controls.hide();
      return;
    }

    var currentIdx = 0;
    var isPaused = false;

    function goToSlide(idx) {
      if (idx < 0) idx = totalSlides - 1;
      if (idx >= totalSlides) idx = 0;
      currentIdx = idx;

      $slideItems.removeClass('is-active');
      $slideItems.eq(currentIdx).addClass('is-active');

      $dotItems.removeClass('is-active');
      $dotItems.eq(currentIdx).addClass('is-active');
    }

    // Dot click events
    $hero.off('click.cungSlideDot', '.cung-hero-dot').on('click.cungSlideDot', '.cung-hero-dot', function (e) {
      e.preventDefault();
      e.stopPropagation();
      var idx = parseInt($(this).data('index'), 10);
      goToSlide(idx);
      resetTimer();
    });

    // Arrow click events
    $hero.off('click.cungSlidePrev', '.cung-hero-prev').on('click.cungSlidePrev', '.cung-hero-prev', function (e) {
      e.preventDefault();
      e.stopPropagation();
      goToSlide(currentIdx - 1);
      resetTimer();
    });

    $hero.off('click.cungSlideNext', '.cung-hero-next').on('click.cungSlideNext', '.cung-hero-next', function (e) {
      e.preventDefault();
      e.stopPropagation();
      goToSlide(currentIdx + 1);
      resetTimer();
    });

    // Hover pause/resume
    $hero.find('.cung-hero-slideshow').off('mouseenter.cungSlide mouseleave.cungSlide')
      .on('mouseenter.cungSlide', function () { isPaused = true; })
      .on('mouseleave.cungSlide', function () { isPaused = false; });

    function resetTimer() {
      if (window.cungHeroInterval) clearInterval(window.cungHeroInterval);
      window.cungHeroInterval = setInterval(function () {
        if (!isPaused) {
          goToSlide(currentIdx + 1);
        }
      }, intervalTime);
    }

    resetTimer();
  }

  // Document ready
  $(document).ready(function () {
    initCountdown();
    initTabs();
    initHeroSlideshow();
  });

  // Elementor Frontend Hook (for live preview in Elementor Editor)
  $(window).on('elementor/frontend/init', function () {
    initHeroSlideshow();
    if (typeof elementorFrontend !== 'undefined') {
      elementorFrontend.hooks.addAction('frontend/element_ready/cung_flash_sale.default', function ($scope) {
        initCountdown($scope);
      });
      elementorFrontend.hooks.addAction('frontend/element_ready/cung_product_tabs.default', function ($scope) {
        initTabs($scope);
      });
    }
  });

})(jQuery);
