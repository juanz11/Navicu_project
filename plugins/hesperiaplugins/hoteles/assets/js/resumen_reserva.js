(function($) {
  $(document).ready(function(){

    $("#info-huesped-form").on("submit", function(e){
      $(this).request('onSaveReserva', {
        /*update: {
          '@habitaciones_disponibles': '#result',
          '@list_monedas': '#moneda-container',
          '@resumen_reserva': '#resumen',
        },*/
        success: function(data) {
          console.log(data);
          this.success(data);
          $(this).find("button").removeAttr("disabled");
        },
        /*error: function(response){
          swal({
            type: 'error',
            title: 'Ha ocurrido un error',
            html: 'Intenta efectuar la acción una vez más, sino comunícate con nosotros vía email o por teléfono',
            onClose: function(){
              location.reload();
            }
          });
        }*/
        /*complete: function(){
          $('.hab-modal').modal('hide');
          $('#go-bottom').show();
        }*/
      });
      e.preventDefault();
    });

    $(".payInSite").on("click", function(e){
      var code = $(this).data("code");
      data = {
        'id': code 
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
