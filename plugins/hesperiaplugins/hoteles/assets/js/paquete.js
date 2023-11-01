(function($) {

  $(document).ready(function(){

    $("#paquete-form").on("submit", function(e){

      $(this).request('onCheck', {
        update: {
          '@habs_disponibles': '#habitaciones-content-paquete',
          '@lista_upsellings': '#upsellings-content',
          '@lista_seleccion': '#seleccion-content',
          '@resumen_precios': '#resumen-precio-content',
        },
        success: function(data) {
          this.success(data);
          //$('#modal-content-form').modal('show');
        },
        complete: function(){
          $(".slider-2-items").owlCarousel({
              autoPlay: 3000, //Set AutoPlay to 3 seconds
              items : 2,
              itemsDesktop : [1199,1],
              itemsDesktopSmall : [979,1]
          });
        }
      });
      e.preventDefault();
    });

    $('#habitaciones-content-paquete').on('click', '.ver-precio-button', function(e){
      var habitacion = $(this).data("value");
      $(this).request('onCargarPrecios', {
        data: {
          habitacion : habitacion
        },
        /*update: {
          '@habs_disponibles': '#habitaciones-content-paquete',
        },*/

        success: function(data) {
          this.success(data);
          $('#modal-hab-precios').modal('show');
          //$('#modal-content-form').modal('show');
        },
        error: function(data){
          alert("Hubo un problema, no fue tu culpa... Lo estamos resolviendo");
        }
      });
    });

    $('#modal-hab-precios').on('change', '.selector_modal_hab', function(e){

			var ocupacion = $("#ocupacion_selector").val();
			var regimen = $("#regimen_selector").val();

			if ($("#container-precio-"+ocupacion+"-"+regimen).length > 0){
				/*SI EXISTE LA COMBINACION DE OCUPACION - REGIMEN*/

				$(".precios-hab").addClass("hidden");
				//oculto todos los precios
				$("#container-precio-"+ocupacion+"-"+regimen).removeClass("hidden");
				//muestro el precio que corresponde a la combinacion

				$(".precios-hab .precio-input").prop("disabled", "disabled");
				//pongo todos los hidden inabilitados
				$('#preciototal-'+ocupacion+"-"+regimen).removeProp("disabled");
				//habilito el precio que corresponde para ser enviado

			}else{
				//si no existe la combinacion oculto todos los precios
				$(".precios-hab").addClass("hidden");
				//muestro el letrero de no hay precio disponible
				$("#no-price").removeClass("hidden");
        $(".precios-hab .precio-input").prop("disabled", "disabled");
				//$(this).find('.precio-input').prop("disabled", "disabled");
				$('#preciototal-'+ocupacion+"-"+regimen).removeProp("disabled");
			}
			actualizar_total();
		});

    $('#modal-content-form').on('submit', '#hab-modal-form', function(e){

      e.preventDefault();
      var ocupacion = $(this).find("select[name=ocupacion] option:selected").text();
			var regimen = $(this).find("select[name=regimen_id] option:selected").text();
			var codigo_moneda = $("#monedas-select option:selected").data("acronimo");

      $(this).request('onGuardarSeleccion', {
        data:{
					ocup_desc: ocupacion,
					reg_desc: regimen,
					codigo_moneda, codigo_moneda
				},
        update: {
          '@resumen_precios': '#resumen-precio-content',
        },

        success: function(data) {
          if (data.X_OCTOBER_FLASH_MESSAGES!=null) {
						swal("Disculpa",data.X_OCTOBER_FLASH_MESSAGES.warning.toString(), "warning");

					}else{
						this.success(data);
					 	$('.hab-modal').modal('hide');
            actualizar_total();
					}
          //$('#modal-content-form').modal('show');
        },
        complete: function(){
          //$('#modal-hab-precios').modal('hide');
        },
        error: function(){
          swal("Esto es embarazoso", "pero ha ocurrido un error inesperado", "warning");
        }
      });
    });

    $('#seleccion-content').on('click', '.delete-hab-selected', function(e){
			//alert("hola");
			var form = new FormData();
			$(this).request('onDeleteSeleccion', {
				data:{
					index: $(this).data("index")
				},
				update: {
					'@lista_seleccion': '#seleccion-content',
          '@resumen_precios': '#resumen-precio-content',
				},
				success: function(data) {
			    this.success(data);
          actualizar_total();
				},
			});
		  e.preventDefault();
		});

    $('#upsellings-content').on('change', '.select-upselling', function(e){
      var upselling_id = $(this).data("ups-id");
      var value = $(this).val();
      $(".precios-paq-"+upselling_id).addClass("hidden");
      $("#precio-"+upselling_id+"-"+value).removeClass("hidden");

      actualizar_total();
    });
    /*
    $('#upsellings-content').on('change', '.select-upselling', function(e){
      actualizar_total();
    });*/

    $("#main-content").on("submit",  "#form-pago-paquete", function(e){
      $(this).request('onIrApagar', {
				success: function(data) {
	    		this.success(data);
				},
			});

      e.preventDefault();
    });

    function actualizar_total(){
      var total_ups = 0;
      var total_full = 0;
      var selects = $("#upsellings-content").find(".select-upselling");
      var checks = $("#upsellings-content").find(".sumables-paq");
      var alojamiento = $("#seleccion-content").find("#total-solo-alojamiento");
      var acronimo = $("#upsellings-content").find("#acronimo");
      selects.each(function(){
        var valor = $(this).data("base");
        var cantidad = $(this).val();
        total_ups = total_ups+(valor*cantidad);
      });

      checks.each(function(){
        if(this.checked){
          var valor = $(this).data("base");
          total_ups = total_ups+(valor);
        }
      });

      if(alojamiento !== null && alojamiento.data("totalraw")!== null && typeof alojamiento.data("totalraw") !== 'undefined'){
        //alert(alojamiento.data("totalraw"));

        var aux_aloj = alojamiento.data("totalraw").toString();
        var aloj = aux_aloj.replace(",",".");
        var ups = total_ups.toString().replace(".","");
        total_full = parseFloat(aloj)+parseFloat(ups);
        var retorno_full = total_full.toFixed(2);

      }else{
        var retorno_full = 0;
      }
      var retorno_ups = total_ups.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");

      $("#precio-ups-total").text(retorno_ups);
      $("#total-upselling-resumen").text(retorno_ups);

      var minimumFractionDigits = 0;
      var currency = acronimo.val();
      
      if(currency !== "VES" && currency !== "VEF"){
        minimumFractionDigits = 2;
      }

      const formatter = new Intl.NumberFormat('de-DE', {
        style: 'decimal',
        currency: acronimo.val(),
        minimumFractionDigits: minimumFractionDigits,
      });

      //alert("formater:"+formatter.format(retorno_full));
      $("#total-paquete-full").text(formatter.format(retorno_full));
    }

    $('#habitaciones-content-paquete').on('click', '.detalle', function(e){
			e.preventDefault();

			/*$('html, body').animate({
	      scrollTop: $("#pagar-btn").offset().top
			}, 500);*/

			$("#hab-detalle-"+$(this).data("index")).owlCarousel({
		    navigation : false, // Show next and prev buttons
		    slideSpeed : 300,
		    paginationSpeed : 400,
		    singleItem:true
		    // "singleItem:true" is a shortcut for:
		    // items : 1,
		    // itemsDesktop : false,
		    // itemsDesktopSmall : false,
		    // itemsTablet: false,
		    // itemsMobile : false
		  });
		 });
  });

})(jQuery);
