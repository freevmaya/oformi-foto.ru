$(window).ready(()=>{

	let top = -1000;

	setInterval(()=>{

		let block = $('.adsbygoogle[data-anchor-status="displayed"]');
		let page = $('.ui-page-active');
		let panel = $('.ui-panel');
		if (block.length > 0) {

			block.find('ins > span ').css({
				left: 'auto',
				right: '-20px'
			});

			let ntop = block.position().top;
			if (ntop != top) {
				top = ntop;
				page.css('margin-top', 60);
				panel.css('margin-top', 60);
			}
		} else {
			page.css('margin-top', 0);
			panel.css('margin-top', 0);
		}

	}, 100);
});