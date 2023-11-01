(function($) {

  $(document).ready(function(){

    $("#form-upselling").on("change", ".selector", function(){
        var moneda = $("#selector-moneda").val();
        var cantidad = $("#selector-cantidad").val();
        var base = $("#container-precio-"+moneda).data("base");
        var precio = parseInt(base)*parseInt(cantidad);

        $(".precio-content").addClass("hidden");
        $("#container-precio-"+moneda).removeClass("hidden");

        $("#precio-"+moneda).text(precio.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."));

    });

    $("#form-upselling").on("submit", function(e){
      e.preventDefault();

      $(this).request('onIrApagar', {

				success: function(data) {
          console.log(data);
			    this.success(data);
          //actualizar_total();
				},
			});
    });


  });

})(jQuery);

function consultarDisponibilidad(data){
  console.log(data);
  $.request('onConsultarDisponibilidad', {
    data: data,
    update: {
      '@precios': '#contenedor-precios'
    },
    success: function(data) {
        this.success(data);
        console.log('Finished!');
    }
  });
}
