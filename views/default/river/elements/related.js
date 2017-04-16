define(function(require) {

	$(document).on('click', '.elgg-river-show-related', function(e) {
		e.preventDefault();
		$(this).closest('.elgg-river-item').find('.elgg-river-related-items').slideToggle();
	});
});