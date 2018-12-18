(function ($) {
  Drupal.behaviors.farm_theme_datefield = {
    attach: function(context, settings) {
      $('#log-form #edit-timestamp', context).append('<span class="datefield-toggle"></span>');
      var element = $('#log-form #edit-timestamp .datefield-toggle', context);
      Drupal.behaviors.farm_theme_datefield.toggle();
    },
    toggle: function() {

    }
  }
})(jQuery);
