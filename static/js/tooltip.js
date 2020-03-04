$('.help').click(function(e) {
	e.preventDefault();
  	$('#tooltip').css('display', 'block');
	$('html').css('overflow-y', 'hidden');
	setTimeout(function(){
  		$('#tooltip').addClass('open');
	}, 50);

	$('.close').click(function(e) {
		e.preventDefault();
		$('#tooltip').removeClass('open');
		setTimeout(function(){
	  		$('#tooltip').css('display', 'none');
	  		$('html').css('overflow-y', 'auto');
		}, 550);
	});
});

setTimeout(function(){
	$('.help-tooltip').addClass('hidden');
}, 2500);

$('#tutorial button').click(function(e) {
	e.preventDefault();
  	$('#tooltip').css('display', 'block');
	$('html').css('overflow-y', 'hidden');
	setTimeout(function(){
  		$('#tooltip').addClass('open');
	}, 50);
});