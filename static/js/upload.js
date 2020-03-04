// Visual override for the default upload button
$('#file-label').click(function () {
    $('#file-upload').click();
});

$('#file-upload').change(function () {
    $('#file-label').html(function(){
    	var files = $('#file-upload')[0].files;
    	var display_names = [];

    	for (var i = 0; i < files.length; i++) {
    		display_names.push(files[i].name);
    	}

    	var name = display_names.join(", ");

        if (name.length == 0) {
        	name = "click here to select your file";
        } else {
        	$("#file-label").removeClass('alert');
        }
        return name;
    });
});

// On submit, post package to the server
// Package contains form data and selected file
// On success, the document renders the response (annotation app)
$('#upload-form').submit(function(e) {
	e.preventDefault();
    var annotation_type = $('input[name=annotation_type]:checked').val();
    var target = ($('#target')[0].value == "") ? $('#target')[0].placeholder : $('#target')[0].value;
    var file = $('#file-upload')[0].files[0]

    if (file == undefined) {
    	$("#file-label").addClass('alert');
	} else {
		var URL = window.URL || window.webkitURL;
    	var fileURL = URL.createObjectURL(file);
  		var fileName = file.name;

		$("#file-label").removeClass('alert');
		$('#divLoading').css('display', 'block');
		$('#submit').blur();

		var formData = new FormData();
		formData.append('target', target);
		formData.append('type', annotation_type);
		formData.append('filename', fileName)
		formData.append('fileURL', fileURL)

		$.ajax({
		    url: '../util/upload_form.php',
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
	}
});