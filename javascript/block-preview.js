(function($){

	$(function(){
 		$('.block_area').each(function(){
 			var self = $(this);
 			var label = '<span>' + self.data('areaid') + ' Block Area</span>';
 			self.append('<div class="block_preview_overlay"></div><div class="block_preview_label">' + label + '</div>');
 		});
	});

}(jQuery));