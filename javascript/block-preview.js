jQuery.noConflict();

(function($){

    $(document).ready(function(){
 		$('.block-area').each(function(){
 			var self = $(this);
 			self.html('<span>' + self.data('areaid') + '</span>');
 		});
    });
 
}(jQuery));