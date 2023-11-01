(function($) {
  $(document).ready(function(){

    var input = document.getElementById('input-id');
    var datepicker = new HotelDatepicker(input,{
      format: 'DD-MM-YYYY',
      separator:' | '
    });

    $('#contentPreciosModal').on('change', '.selector-modal', function(e){
      var habitacion = $(this).data("hab");
      var ocupacion = $("#ocupacion-select").val();
      var regimen = $("#regimen-select").val();

      if ($("#container-precio-"+ocupacion+"-"+regimen+"-"+habitacion).length > 0){
        //SI EXISTE LA COMBINACION DE OCUPACION - REGIMEN
        $(".precios-hab-"+habitacion).addClass("hidden");
        //oculto todos los precios
        $("#container-precio-"+ocupacion+"-"+regimen+"-"+habitacion).removeClass("hidden");
        //muestro el precio que corresponde a la combinacion
        $(".precios-hab-"+habitacion+" .precio-input").prop("disabled", "disabled");
        //pongo todos los hidden inabilitados

        $('#preciototal-'+ocupacion+"-"+regimen+"-"+habitacion).attr("disabled", false);
        //habilito el precio que corresponde para ser enviado
      }else{
        //si no existe la combinacion oculto todos los precios
        $(".precios-hab-"+habitacion).addClass("hidden");
        //muestro el letrero de no hay precio disponible
        $("#no-price-"+habitacion).removeClass("hidden");

        $("#contentPreciosModal").find('.precio-input').prop("disabled", "disabled");
      }
      actualizarTotalHab();
    });

    $("#seleccion-content").on("click", '.eliminar-detalle', function(){
      var id = $(this).data("indice");
      $("#detalle-"+id).remove();
      var valorHidden = JSON.parse($("#valor-seleccion").val());
      delete valorHidden[id];

      console.log(valorHidden);

     $("#valor-seleccion").val(JSON.stringify(valorHidden));

      actualizarTotalHab();
    });

    $('#contentUpsellingsHabs').on('change', '.select-upselling', function(e){
      var upselling_id = $(this).data("ups-id");
      var base = $(this).data("base");
      var value = $(this).val();
      $("#contentUpsellingsHabs #label-precio-ups-"+upselling_id).text(parseInt(base)*parseInt(value));
      //var precio_alojamiento = $(".precio-input:not('disabled')").val();
      //alert(precio_alojamiento);
      actualizarTotalHab();

    });

    $('#contentUpsellingsSecundarios').on('change', '.select-upselling', function(e){
      var upselling_id = $(this).data("ups-id");
      var base = $(this).data("base");
      var value = $(this).val();
      $("#contentUpsellingsSecundarios #label-precio-ups-"+upselling_id).text(parseInt(base)*parseInt(value));
      //var precio_alojamiento = $(".precio-input:not('disabled')").val();
      //alert(precio_alojamiento);
      actualizarTotalUpsellingsSec();

    });

    $("#content-precios-upsellings-sec").on("change", "#DatePicker-formFechaDisfrute-date-fecha_disfrute", function(e){
      $("#Form-field-fecha_disfrute").val($(this).val());
      cargarPrecioModal();
    });

    $("#content-precios-upsellings-sec").on("change", "select[name='cantidad']", function(){
      var cantidad = $(this).val();
      var precio_field = $("#content-precios-upsellings-sec").find("input[name='precio']");
      var spanPrecio = $("#form-precio-upselling-sec").find(".spanPrecio");

      var acronimo = $("#contentFechasEstadia").find("#acronimo");
   
      var currency = acronimo.val();

      if(currency.toString() == "VES" || currency.toString() == "VEF" ){
        var minimumFractionDigits = 0;
      }else{
        var minimumFractionDigits = 2;
      }

      
      var formatter = new Intl.NumberFormat('de-DE', {
        style: 'decimal',
        currency: currency,
        minimumFractionDigits: minimumFractionDigits,
      });

      var result = parseInt(cantidad)*parseFloat(precio_field.val());


      spanPrecio.text(formatter.format(result));

      
    });

  });


})(jQuery);

function cargarPrecioModal(){
  var formulario = $("#formContentPreciosUps").find("#form-precio-upselling-sec");
    //console.log(formulario.serialize());

    formulario.request('onCambiarFechaDisfrute', {
      success: function(data) {
        console.log(data);
        this.success(data);
        setValoresCamposModales(data);
      },
    });
}

function setValoresCamposModales(data){

  var result = JSON.parse(data.result);


  var options ="";
  for (var i = 1; i <= result.disponible; i++) {
    options+="<option value="+i+">"+i+"</option>";
  }
  $("select[name='cantidad']").html(options);
  $("input[name='precio']").val(result.precio);
  var spanPrecio = $("#form-precio-upselling-sec").find(".spanPrecio");
  //alert(spanPrecio.text());
  spanPrecio.text(result.precio);
}

function actualizarTotalHab(){
  //var valor = $("#valor-seleccion").val();
  var total = 0;
  var total_ups = 0;
  var valor;
  var selects = $("#contentUpsellingsHabs").find(".select-upselling");
  var acronimo = $("#contentFechasEstadia").find("#acronimo");
  var precio_alojamiento = $(".precio-input:not([disabled])").val();
  selects.each(function(){
    var valor = $(this).data("base");
    var cantidad = $(this).val();
    total_ups = total_ups+(valor*cantidad);
  });

  var currency = acronimo.val();

  if(currency.toString() == "VES" || currency.toString() == "VEF" ){
    var minimumFractionDigits = 0;
  }else{
    var minimumFractionDigits = 2;
  }

  
  var formatter = new Intl.NumberFormat('de-DE', {
    style: 'decimal',
    currency: currency,
    minimumFractionDigits: minimumFractionDigits,
  });

  $("#label-total-upsellings-habs").text(formatter.format(total_ups));
  var total = parseFloat(precio_alojamiento)+parseFloat(total_ups);
  
  $("#total_hab_modal").text(formatter.format(total));

}

function actualizarTotalUpsellingsSec(){
  var total_ups = 0;
  var selects = $("#contentUpsellingsSecundarios").find(".select-upselling");
  selects.each(function(){
    var valor = $(this).data("base");
    var cantidad = $(this).val();
    total_ups = total_ups+(valor*cantidad);
  });
  var label_precio = $("#contentUpsellingsSecundarios").find("#label-total-upsellings-secundarios");
  label_precio.text(total_ups);

}

function refreshForm(){
  $('#form-habs-modal').trigger('reset');
  var selects = $("#form-habs-modal ").on("#contentUpsellingsHabs").find(".select-upselling");
  selects.each(function(){
    $(this).val(0).change();
    $(this).siblings(".select2-container").find(".select2-selection__rendered").text("0");
    //$(this).trigger('change');
  });

}
