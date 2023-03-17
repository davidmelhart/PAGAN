$("[name='type']")[0].defaultValue = "ranktrace";
var selected = 'youtube';

$('[name="source_type"]').on('click', function(){
	var youtubeSource = $('.youtube-source');
	var fileSource = $('#file-source');
	var researcherUpload = $('.researcher-upload');
	var subjectUpload = $('.subject-upload')

	selected = $(this).val();

	if(selected == 'youtube'){
		youtubeSource.removeClass('hidden');
		fileSource.addClass('hidden');
		$('.youtube-source').attr('placeholder', 'One entry per row.');
	}
	if(selected == 'upload'){
		fileSource.removeClass('hidden');
		youtubeSource.addClass('hidden');
	}
	if(selected == 'ftp'){
		fileSource.addClass('hidden');
		youtubeSource.removeClass('hidden');
		$('.youtube-source').attr('placeholder', 'Provide original name for each entry. One entry per row.');
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

$('[type="reset"]').on("click", function(){
	selected = 'upload';
	$('#file-source').removeClass('hidden');
	$('.youtube-source').addClass('hidden');
	for(var i = 0; i < $('.youtube-source').length-1; i++){
		$($('.youtube-source')[i]).remove();
	}
})

$('[name="tolerance"]').on('input', function() {
    $('#tolerance-value').html(this.value);
});

$('[name="ranktrace_rate"]').on('input', function() {
	let smoothValue = this.value;
	if (smoothValue == 0){
		$('#ranktrace_rate-value').html("<b>No smoothing.</b>");
	} else {
		let displayValue = Math.ceil(smoothValue*33.33333333);
		if (displayValue >= 1000){
			displayValue = (displayValue/1000) + "sec";
		} else {
			displayValue = displayValue + "ms";
		}

		$('#ranktrace_rate-value').html("Update graph at a <b>" + displayValue + "</b> interval.");
	}

});

$('[name="type"]').on('input', function() {
	if (this.value == "ranktrace"){
		$('#gtrace-config').addClass('hidden');
		$('#ranktrace-config').removeClass('hidden');
	} else if (this.value == "gtrace"){
		$('#gtrace-config').removeClass('hidden');
		$('#ranktrace-config').addClass('hidden');
	} else {
		$('#gtrace-config').addClass('hidden');
		$('#ranktrace-config').addClass('hidden');
	}
});

$('[name="n_of_participant_runs"]').on('input', function() {
	$('#n_run-value').html(this.value);
});

$('[name="gtrace_control"]').on('input', function() {
	if (this.value == "keyboard") {
		$('#mouse-click-box').addClass('hidden');
		$('#rate-box').removeClass('hidden');
	} else {
		$('#mouse-click-box').removeClass('hidden');
		if ($('[name="gtrace_update"]:checked').val() == "on"){
			$('#rate-box').removeClass('hidden');
		} else {
			$('#rate-box').addClass('hidden');
		}
	}
});

$('[name="gtrace_update"]').on('input', function() {
	if ($('[name="gtrace_update"]:checked').val() == "on"){
		$('#rate-box').removeClass('hidden');
	} else {
		$('#rate-box').addClass('hidden');
	}
});

$('[name="gtrace_rate"]').on('input', function() {
	let displayValue = this.value;
	if (displayValue >= 1000){
		displayValue = (displayValue/1000) + "sec";
	} else {
		displayValue = displayValue + "ms";
	}
	$('#gtrace_rate-value').html("Update graph at a <b>" + displayValue + "</b> interval.");
});

var n_entry = 0;
$(document).on("change paste keyup cut", function() {
	setTimeout(function function_name(argument) {
		if (selected == "youtube" || selected == "ftp"){
			let entries = $('.youtube-source')[0].value.split("\n");
			entries = entries.filter( (element) => element.length > 0);
			n_entry = entries.length;
			$('[name="n_of_participant_runs"]').attr('max', n_entry);
			$('#entry-n').html(n_entry);
		} else if(selected == 'upload'){
			n_entry = $('#file-source')[0]['files'].length;
			$('[name="n_of_participant_runs"]').attr('max', n_entry);
			$('#entry-n').html(n_entry);
		}

		if (parseInt($('[name="n_of_participant_runs"]').attr('max')) <= parseInt($('[name="n_of_participant_runs"]').val())){
			$('[name="n_of_participant_runs"]').val($('[name="n_of_participant_runs"]').attr('max'));
			$('#n_run-value').html($('[name="n_of_participant_runs"]').attr('max'));
		}
	}, 100);
});