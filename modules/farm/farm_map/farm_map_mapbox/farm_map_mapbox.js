(function () {
  farmOS.map.behaviors.farm_map_mapbox = {
    attach: function (instance) {
      var key = Drupal.settings.farm_map_mapbox.api_key;
      var opts = {
        title: 'MapBox Satellite',
        url: 'https://api.mapbox.com/v4/mapbox.satellite/{z}/{x}/{y}.png?access_token=' + key,
        group: 'Base layers',
        base: true,
        visible: false,
      };
      instance.addLayer('xyz', opts);
    }
  };
}());
