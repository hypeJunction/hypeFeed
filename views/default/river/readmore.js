define(function (require) {

	var elgg = require('elgg');
	require('readmore');

	var readmore = {
		init: function () {
			$('.elgg-river-message:not([data-readmore]),.interactions-comment-body:not([data-readmore])')
				.css('overflow', 'hidden')
				.readmore({
					speed: 75,
					collapsedHeight: 150,
					lessLink: '<a class="river-read-less" href="#">' + elgg.echo('river:read:less') + '</a>',
					moreLink: '<a class="river-read-more" href="#">' + elgg.echo('river:read:more') + '</a>',
				});
		}
	};

	readmore.init();
	$('.elgg-list').on('change', readmore.init);

	return readmore;
});
