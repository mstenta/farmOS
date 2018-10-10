(function ($) {
  Drupal.behaviors.farm_theme_action_links = {
    attach: function(context, settings) {
      $('ul.action-links', context).first().wrap('<div id="action-links-dock"></div>');
    }
  }
})(jQuery);
