(function($) {
  $(document).ready(function(){

    $("#info-huesped-form").on("submit", function(e){
    //  $(this).find("button").attr("disabled","disabled");
      $(this).request('onSaveCompra', {
        success: function(data) {
          console.log(data);
          this.success(data);
        }

      });
      e.preventDefault();
    });

    $(".payInSite").on("click", function(e){
      var code = $(this).data("code");
      var hotel = $(this).data("hotel");
      data = {
        'id': code,
        'hotel': hotel
      }
      $.request('onPayInSite', {
        data: data,
        success: function(data) {
          location.reload();
        },
      });
    });
  });

})(jQuery);
