// On submit, post package to the server
// Package contains form data and selected file
// On success, the document renders the response (annotation app)
$('#survey-form').submit(function(e) {
	e.preventDefault();

	var age = $('#age')[0].value;
	var gender = $('#gender')[0].value;
	var occupation = $('#occupation')[0].value;
	var gamer = $('input[name=gamer]:checked').val();
	var playtime = $('input[name=playtime]:checked').val();
	var favourite = $('#favourite')[0].value;

	$('#divLoading').css('display', 'block');
	$('#submit').blur();

	var formData = new FormData();
	formData.append('age',age);
	formData.append('gender',gender);
	formData.append('occupation',occupation);
	formData.append('gamer',gamer);
	formData.append('playtime',playtime);
	formData.append('favourite',favourite);

	$.ajax({
	    url: '/survey',
	    cache: false,
	    contentType: false,
	    processData: false,
	    data: formData,
	    type: 'POST',
	    success: function(response) {
			document.write(response);
	    },
	    error: function(error) {
	        console.log(error);
	    }
	});
});