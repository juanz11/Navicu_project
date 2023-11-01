$(document).ready(function(){
  // Create a Stripe client
  stripe = Stripe('pk_live_uSWaZXDlQmSpdbipMfnKsxdK00V4esivHC');

  //key anterior pk_test_zf57RZ3Om1f1jFNtrOyN2Ixb
  // key de aquoss pk_test_xQf9GTa0xwBJSsqU4QC2Zb7d00LFCdOpHP
  // Create an instance of Elements
  var elements = stripe.elements();

  // Custom styling can be passed to options when creating an Element.
  // (Note that this demo uses a wider set of styles than the guide below.)

  stripeFlag = true;

  var style = {
    base: {
      color: '#999',
      lineHeight: '24px',
      fontFamily: '"Poppins", sans-serif',
      fontSmoothing: 'antialiased',
      fontSize: '14px',
      '::placeholder': {
        color: '#999'
      }
    },
    invalid: {
      color: '#fa755a',
      iconColor: '#fa755a'
    }
  };

  // Create an instance of the card Element


  card = elements.create('card', {style: style});
  // Add an instance of the card Element into the `card-element` <div>
  card.mount('#card-element');
  var mensaje = "";
  var displayError = document.getElementById('card-errors');
  //var errorElement = document.getElementById('card-errors');
  // Handle real-time validation errors from the card Element.
  card.addEventListener('change', function(event) {
    
    if (event.error) {
      displayError.textContent = event.error.message;
    }else if(mensaje !==""){
      displayError.textContent = mensaje;
    } else {
      displayError.textContent = '';
      
    }
  });

  $("#tarjetahabiente").on("focusout", function (e){
    var displayError = document.getElementById('card-errors');
     mensaje = validarTarjetaHabiente();
    //if(mensaje !== ""){
    //  alert("di error");
    displayError.textContent = mensaje;
    
    //$("input[name=cardnumber]").trigger("change");
    //}
    //alert("salí");
  });
});

function validarTarjetaHabiente(){
  var value = $("#tarjetahabiente").val();
  var mensaje = "";
  var regex = new RegExp("^([^0-9]*)$");
  if(value === ""){
    mensaje = '\nNombre y Apellido son requeridos';
  }else if(value.length < 4 && value.length > 15){
    mensaje = '\nNombre y Apellido deben tener entre 5 y 15 caracteres en total';
  }else if(!regex.test(value)){
    mensaje = '\nCaracteres numéricos no son permitidos';
  }

  return mensaje;
}

function stripeTokenHandler(token) {
  // Insert the token ID into the form so it gets submitted to the server
  var form = document.getElementById('payment-form');
  var hiddenInput = document.createElement('input');
  hiddenInput.setAttribute('type', 'hidden');
  hiddenInput.setAttribute('name', 'stripeToken');
  hiddenInput.setAttribute('value', token.id);
  form.appendChild(hiddenInput);
}

$.fn.payStripe = function() {
  var dataN = 'error';
 
  var displayError = document.getElementById('card-errors');

  var dfd = jQuery.Deferred();
    stripe.createToken(card).then(function(result) {
      var mensaje = validarTarjetaHabiente();
      if (result.error) {
          
        displayError.textContent = result.error.message;
          
          dfd.reject( error );
      }else if(mensaje !== ""){
        displayError.textContent = mensaje;
      }else{
        if(stripeFlag){
          stripeFlag = false;
          $("#payment-form, #payment-form-test").find("button").attr("disabled","disabled").text("Espere..").append("<i class='fa fa-spinner fa-spin fa-fw'></i>");
          $(this).request('onPayStripe', {
            data:{
              token: result.token.id,
              owner: $("#tarjetahabiente").val(),
              mt: $("#mt").val(),
              ref: $("#ref").val(),
              id: $("#id").val(),
              type: $("#type").val()
            },
            success: function(data) {
                //alert('success'+data+"esto");
                //this.dataN = data;
                console.log(data);
                dataN = data;
                dfd.resolve( data );
            },
            error: function(error){
              console.log(error);
                //dataN = data;
                dfd.reject( error );
            },
            complete: function(){
              //console.log(data);
              //alert('completado');
            }
          });
        }else{
          console.log("wait");
        }
        
      }
    });
  return dfd.promise();
};
