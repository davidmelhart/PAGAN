$("[name='type']")[0].defaultValue = "ranktrace";
var selected = 'upload';

$('[name="source_type"]').on('click', function(){
	var youtubeSource = $('.youtube-source');
	var fileSource = $('#file-source');
	var researcherUpload = $('.researcher-upload');
	var subjectUpload = $('.subject-upload')

	selected = $(this).val();
	
	if(selected == 'youtube'){
		youtubeSource.removeClass('hidden');
		fileSource.addClass('hidden');
	}
	if(selected == 'upload'){
		fileSource.removeClass('hidden');
		youtubeSource.addClass('hidden');
	}
	if(selected == 'user_upload' || selected == 'user_youtube'){
		fileSource.addClass('hidden');
		youtubeSource.addClass('hidden');
		$('#source-select').addClass('rounded');
		subjectUpload.removeClass('hidden');
		researcherUpload.addClass('hidden');
		$('input[value="sequence"]').prop("checked", true);
	} else {
		$('#source-select').removeClass('rounded');
		subjectUpload.addClass('hidden');
		researcherUpload.removeClass('hidden');
	}
});

$('[name="video_loading"]').on('click', function(){
	var participant_runs = $('#participant-runs');
	if($(this).val() == 'random' && !$('[name="endless"]').is(':checked')){
		participant_runs.removeClass('hidden');
	}
	if($(this).val() == 'sequence'){
		participant_runs.addClass('hidden');
	}
});

$('[name="endless"]').on('click', function(){
	var participant_runs = $('#participant-runs');
	var participant_uploads = $('#participant-uploads');
	var end_plate = $('#end-message');
	var survey_link = $('#survey-link');
	var auto_id = $('#autofill-id');
	if($('[name="video_loading"]:checked').val() == 'random') {
		if($(this).is(':checked') == true){
			participant_runs.addClass('hidden');
		}
		if($(this).is(':checked') == false){
			participant_runs.removeClass('hidden');
		}
	}

	if($(this).is(':checked') == true){
		end_plate.addClass('hidden');
		survey_link.addClass('hidden');
		auto_id.addClass('hidden');
		participant_uploads.addClass('hidden');
	}
	if($(this).is(':checked') == false){
		end_plate.removeClass('hidden');
		survey_link.removeClass('hidden');
		auto_id.removeClass('hidden');
		participant_uploads.addClass('hidden');
	}

});

var lastYouTube = "";
$(document).on("change paste keyup", function() {
	if($('.youtube-source:last-of-type').val() != ""){
		$('#project-entries').append('<input type="text" name="source_url[]" class="form-control youtube-source" value="">');
	}
	if ($('.youtube-source').length > 1){
		for(var i = 0; i < $('.youtube-source').length-1; i++){
			if($($('.youtube-source')[i]).val() == ""){
				$($('.youtube-source')[i]).remove();
			}
		}
	}

	if (selected == "youtube"){
		$('#entry-n')[0].innerHTML = $('.youtube-source').length-1;
	} else if(selected == 'upload'){
		$('#entry-n')[0].innerHTML = $('#file-source')[0]['files'].length;
	}
});

$('[type="reset"]').on("click", function(){
	selected = 'upload';
	$('#file-source').removeClass('hidden');
	$('.youtube-source').addClass('hidden');
	for(var i = 0; i < $('.youtube-source').length-1; i++){
		$($('.youtube-source')[i]).remove();
	}
})