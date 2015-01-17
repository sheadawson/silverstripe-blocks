(function($) {
	$.entwine("ss", function($) {
		$(".cms-edit-form :input[name=ClassName].block-type").entwine({
			onchange: function() {
				alert(ss.i18n._t('BLOCKS.ALERTCLASSNAME'));
			}
		});

	});
})(jQuery);


