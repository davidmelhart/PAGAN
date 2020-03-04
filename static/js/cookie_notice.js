$('#cookie_notice button').click(function(e) {
	e.preventDefault();
	$('#cookie_notice').addClass('hidden');
	$('#cookie_wall').addClass('hidden');
	setTimeout(function(){
  		$('#cookie_notice').remove();
  		$('#cookie_wall').remove();
	}, 350);
});