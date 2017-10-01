var Vue = require('vue');
window.Occupied = window.Occupied || (function(){
  return {
    init: function(args){
      //validate args
      var app = new Vue({
        el: '#' + args.el,
        data: {
          lock: args.lock,
          screen_id: args.screen,
          back: args.back
        },
        methods: {
          take_over: function(){
            //TODO: validations and what not
            var that = this;
            jQuery.post(ajaxurl, { action: 'occupied_take_over', screen: this.screen_id }, function(response){
              that.lock = JSON.parse(response);
            });
          },
          go_back: function(){
            window.location = this.back;
          }
        },
        // register heartbeat listener
        mounted: function(){
          var that = this;
          var occupied_data = { screen: this.screen_id }

          jQuery(document).on('heartbeat-send', function(evt, data){
            // add some data to the heartbeat so that we get a response.
            data.occupied_data = occupied_data;
          });

          jQuery(document).on('heartbeat-tick', function(evt, data){
            that.lock = data.occupied_lock;
          });
        },
      });
      return app;
    }
  }
})();

