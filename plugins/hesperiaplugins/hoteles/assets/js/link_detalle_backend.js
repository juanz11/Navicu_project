(function($) {
	$(document).ready(function(){
		$("#partialContents").on("click",'.rowlink', function(){
		  var link = $(this).data("href");
		  window.location = link;
		});
	});	
})(jQuery);