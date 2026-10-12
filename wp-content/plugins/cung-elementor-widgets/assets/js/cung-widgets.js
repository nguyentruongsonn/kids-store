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

      var targetDate = new Date(endTimeStr).getTime();
      if (isNaN(targetDate)) {
        // Fallback: 8 hours from now if invalid
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

        var hours = Math.floor((distance / (1000 * 60 * 60)));
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
    var $tabSections = $scope ? $scope.find('.cung-product-tabs-section') : $('.cung-product-tabs-section');

    $tabSections.each(function () {
      var $section = $(this);
      var $navBtns = $section.find('.cung-tab-btn');
      var $panels = $section.find('.cung-tab-panel');

      $navBtns.on('click', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var targetTab = $btn.data('target');

        $navBtns.removeClass('is-active');
        $btn.addClass('is-active');

        $panels.removeClass('is-active');
        $section.find('#' + targetTab).addClass('is-active');
      });
    });
  }

  // Document ready
  $(document).ready(function () {
    initCountdown();
    initTabs();
  });

  // Elementor Frontend Hook (for live preview in Elementor Editor)
  $(window).on('elementor/frontend/init', function () {
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
