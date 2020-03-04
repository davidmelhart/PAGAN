$(".link").click(function () {
   $(this).select();
});

$(".button.archive").click(function () {
	var projectID = $(this).data("projectid");
	var archive = $(this).data("archive");
    $.post(
    "util/archive.php",
    {
        archived: archive,
        project_id: projectID
    });
    $("#"+projectID).remove();
});