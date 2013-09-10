jQuery.noConflict();

(function($){

    $(document).ready(function(){

 		$('.block-area').each(function(){
 			var self = $(this);
 			var label = '<span>' + self.data('areaid') + ' Block Area</span>';
 			self.append('<div class="block-preview-overlay"></div><div class="block-preview-label">' + label + '</div>');

 		});
    });
 
}(jQuery));