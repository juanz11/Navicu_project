(function($) {

	$(document).ready(function(){

		$("#check-in").datepicker({
	    format: "dd-mm-yyyy",
	    language: "es",
			orientation: "bottom",
			startDate: "0",
			disableTouchKeyboard: true,
			autoclose: "true",
	  });

		$("#check-out").datepicker({
	    format: "dd-mm-yyyy",
	    language: "es",
			orientation: "bottom",
			startDate: "+1d",
			disableTouchKeyboard: true,
			autoclose: "true",
	  });

		/*$(window ).scroll(function(){
			if ($("#resumen-reserva-content").length>0) {
					if($("#resumen-reserva-content").isOnScreen()){
						//alert("me oculto");
						$("#go-bottom").hide();
						//console.log("me oculto");
					}else{
						//alert("me muestro");
						$("#go-bottom").show();
						//console.log("me muestro");
					}
			}

		});*/

		$('#result').on('click', '.detalle', function(e){
			e.preventDefault();

			/*$('html, body').animate({
	      scrollTop: $("#pagar-btn").offset().top
			}, 500);*/

			$("#hab-detalle-"+$(this).data("index")).owlCarousel({
		    navigation : false, // Show next and prev buttons
		    slideSpeed : 300,
		    paginationSpeed : 400,
			singleItem:true,
			navigationText : ["<",">"]
		    // "singleItem:true" is a shortcut for:
		    // items : 1,
		    // itemsDesktop : false,
		    // itemsDesktopSmall : false,
		    // itemsTablet: false,
		    // itemsMobile : false
		  });
		 });

		$("#modal-hab-precios").on('change', '#regimen_selector', function(){
			$(".descripcion-planes").addClass("hidden");
			var value = $(this).val();
			var descripcion = $("#modal-hab-precios").find("#descripcion-"+value);
			//alert($(this).val());
			$(descripcion).removeClass("hidden");
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
				//$(this).find('.precio-input').prop("disabled", "disabled");
				$('#preciototal-'+ocupacion+"-"+regimen).removeProp("disabled");
			}
			actualizar_total();
		});

		$('#modal-hab-precios').on('submit', '.hab-modal-form', function(e){

			var ocupacion = $(this).find("select[name=ocupacion] option:selected").text();
			var regimen = $(this).find("select[name=regimen_id] option:selected").text();
			var codigo_moneda = $("#monedas-select option:selected").data("acronimo");
			var codigo_ocupacion = $(this).find("select[name=ocupacion]").val();
			var codigo_regimen = $(this).find("select[name=regimen_id]").val();
			var precio = $(this).find("#preciototal-"+codigo_ocupacion+"-"+codigo_regimen).val();

			if (precio!=null && precio > 0 ) {
				$(this).request('onAddSeleccion', {
					data:{
						ocup_desc: ocupacion,
						reg_desc: regimen,
						codigo_moneda, codigo_moneda
					},
					update: {
						/*'@resumen_reserva': '#resumen',*/
						'@seleccion-reserva': '#sidebar1-wrapper'
					},
					success: function(data) {
						//console.log(data);
						if (data.error!=null) {
							swal("Disculpa",data.error.toString(), "warning");

						}else{
							this.success(data);
							$('.hab-modal').modal('hide');
							$('#go-bottom').show();
						}
					},
					complete: function(){

					},
					error: function(data){
						swal("Disculpa","Esto es embarazoso, pero ha ocurrido un error inesperado, intenta nuevamente", "error");
					}
				});
			}else{
				swal("Disculpa","Ésta combinación ocupacion/regimen no se encuentra disponible", "warning");
			}

		  	e.preventDefault();
		});

		/*$('#resumen').on('click', '#go-bottom', function(e){
			e.preventDefault();
			$('html, body').animate({
	      scrollTop: $("#pagar-btn").offset().top
			}, 500);
		 });*/

     $('#moneda-container').on('change', '#monedas-select', function(e){
			//var data = new FormData($('#cl').serialize());
			var formData = new FormData($("reservas-form")[0]);
			var moneda = $("#monedas-select").val();

			$("#reservas-form").request('onCheck', {
				data:{
					moneda: moneda,
				},
				update: {
					'@habitaciones_disponibles': '#result',
			    	'@list_monedas': '#moneda-container',
					/*'@resumen_reserva': '#resumen'*/
					'@seleccion-reserva': '#sidebar1-wrapper'
				},
				success: function(data) {
	    		//console.log(data);
			    this.success(data);
					$("#moneda-container").find("#monedas-select").val(moneda);
					//alert(moneda);
				},
				/*complete: function(){
					//$('.hab-modal').modal('hide');
					$('#go-bottom').show();

				}*/
			});
			//console.log(formData);
 			//alert($(this).val());
 		});

		$("#reservas-form").on("submit", function(e){
			var moneda = null;

			if ($("#monedas-select").length > 0) {
				moneda = $("#monedas-select").val();
				//alert(codigo_moneda);
			}

			$(this).request('onCheck', {

				data:{
					moneda: moneda,
				},
				update: {
					'@habitaciones_disponibles': '#result',
			    	'@list_monedas': '#moneda-container',
					/*'@resumen_reserva': '#resumen',*/
					'@seleccion-reserva': '#sidebar1-wrapper'
				},
				success: function(data) {
	    		//console.log(data);
			    this.success(data);
					$("#moneda-container").find("#monedas-select").val(moneda);
				}

			});
		  e.preventDefault();
		});

		$('#sidebar1-wrapper').on('submit', '#form-seleccion', function(e){
			//alert("hola");
			$(this).request('onIrApagar', {

				success: function(data) {
	    		console.log(data);
			    this.success(data);
				},
			});
		  e.preventDefault();
		});

		$('#sidebar1-wrapper').on('click', '.delete-hab-selected', function(e){
			//alert("hola");
			var form = new FormData();
			$(this).request('onDeleteSeleccion', {
				data:{
					index: $(this).data("index")
				},
				update: {
					//'@resumen_reserva': '#resumen',
					'@seleccion-reserva': '#sidebar1-wrapper'
				},
				success: function(data) {
	    		console.log(data);
			    this.success(data);
				},
			});
		  e.preventDefault();
		});
		//NUEVO
		$('#result').on('click', '.ver-precio-button', function(e){
      var habitacion = $(this).data("value");

      $(this).request('onCargarPrecios', {
        data: {
          habitacion : habitacion
        },
        success: function(data) {
          this.success(data);
					$(".slider-default").owlCarousel({
				      navigation : false, // Show next and prev buttons
				      slideSpeed : 300,
				      paginationSpeed : 400,
				      singleItem:true,
					  autoPlay: false,
					  autoHeight : true,
					  navigationText : ["<",">"]
				  });
					actualizar_total();
          $('#modal-hab-precios').modal('show');
          //$('#modal-content-form').modal('show');
        },
        error: function(data){
          //alert("Hubo un problema, no fue tu culpa... Lo estamos resolviendo");
					swal("Disculpa","Esto es embarazoso, pero ha ocurrido un error inesperado, intenta nuevamente", "error");
        }
      });
    });

		$('#modal-hab-precios').on('change', '.select-upselling', function(e){
      var upselling_id = $(this).data("ups-id");
      var value = $(this).val();
      $(".precios-paq-"+upselling_id).addClass("hidden");
      $("#precio-"+upselling_id+"-"+value).removeClass("hidden");
			actualizar_total();
    });

		function actualizar_total(){
      var total_ups = 0;
      var total_full = 0;

			var ocupacion = $("#ocupacion_selector").val();
			var regimen = $("#regimen_selector").val();
      var selects = $("#upsellings-content").find(".select-upselling");
      var checks = $("#upsellings-content").find(".sumables-paq");
      var alojamiento = $("#preciototal-"+ocupacion+"-"+regimen).val();
			var acronimo = $("#modal-hab-precios").find("#acronimo");
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

			total_full = parseFloat(alojamiento) + parseFloat(total_ups);
      
			var currency = acronimo.val();

			if(currency.toString() == "VES" || currency.toString() == "VEF" ){
				//alert("tengo ves");
				var minimumFractionDigits = 0;
			}else{
				var minimumFractionDigits = 2;
			}

			
			var formatter = new Intl.NumberFormat('de-DE', {
        style: 'decimal',
        currency: currency.toString(),
        minimumFractionDigits: minimumFractionDigits,
			});


			
			//alert(formatter.format(total_full));
      //$("#precio-ups-total").text(retorno_ups);
      $("#total-upselling-resumen").text(formatter.format(total_ups));
      $("#total-full-hab-modal").text(formatter.format(total_full));
    }
		//FIN NUEVO

	}); //document.ready

	$.fn.isOnScreen = function(){

	    var win = $(window);
	    var viewport = {
	        top : win.scrollTop(),
	        left : win.scrollLeft()
	    };
	    viewport.right = viewport.left + win.width();
	    viewport.bottom = viewport.top + win.height();

	    var bounds = this.offset();
	    bounds.right = bounds.left + this.outerWidth();
	    bounds.bottom = bounds.top + this.outerHeight();

	    return (!(viewport.right < bounds.left || viewport.left > bounds.right || viewport.bottom < bounds.top || viewport.top > bounds.bottom));
	  };

})(jQuery);
