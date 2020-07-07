$('.mylogs').click(function(e) {
	e.preventDefault();
	$('#logs').css('display', 'block');
	$('html').css('overflow-x', 'hidden');
	setTimeout(function(){
  		$('#logs').addClass('open');
	}, 50);
});

$('.close').click(function(e) {
	e.preventDefault();
	$('#logs').removeClass('open');
	setTimeout(function(){
  		$('#logs').css('display', 'none');
  		$('html').css('overflow-x', 'auto');
	}, 550);
});


$('.remove-log').click(function(e) {
	e.preventDefault();
	var filename = $($(e.target).parent().parent()).attr('data-item');
	$.post( '../util/logs.php',
		{
			delete: true,
			filename: filename
		});
	var item = $(this).parent().parent().parent()[0];
	$(item).addClass('deleted');
	$(item).children().remove();
	setTimeout(function(){
  		$(item).remove();
	}, 550);
});