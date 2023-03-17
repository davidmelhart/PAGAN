// Global variables
var
    video_container = $('video')[0],    //video container
    canvas = $('canvas')[0],            //canvas for the annotator app
    context = canvas.getContext('2d'),  //context of the drawing board
    paused = true,                      //boolean tracking if the video is on pause
    annotatorPosY = canvas.height/2,    //default annotator cursor position Y
    annotatorPosX = 20,                 //default annotator cursor position X
    annotatorValue = 0,                 //annotator starting value
    annotatorMax = 0,                   //annotator max vaule for normalisation
    annotatorMin = 0,                   //annotator min vaule for normalisation
    currentTime = 0,                    //current time of the video since the beginning
    trace = [],                         //container for recoreded annotator values
    normTrace = [],                     //container for normalised positions for rendering the trace
    previousTime = -1,                  //helper variable for timed checks
    previousValue = 0,                  //helper variable for change in value checks
    sessionStart = 0,                   //helper variable for storing start of the session
    logDir = 'logs',                    //tells the post method the log directory
    firstStart = true,                  //helper variable that tells the app the first startup
    ended = false,                      //helper variable that tells the app the video is started
    player,                             //variable holding the youtube player
    aspect_ratio,                       //helper variable that tells the app the aspect ratio of the video
    seen_trigger = false,               //helper variable that tells the app the video has been registered as seen
    tolerance = 0.99,                   //controls how much of the annotation has to be completed to be registered as seen
    keyPress = false,                   //helper variable that tells the app if a key is pressed
    end_trigger = false,                //helper variable that tells the app if the end section is triggered
    storedTime = 0,                     //helper variable that stores last checked time for Gtrace
    storedValue = 0,                    //helper variable that stores last checked value for Gtrace
    annotationMod = 1,                  //variable controlling the step in values during the annotation
    mod = 0                             //modulator in the annotationMod exponential function
    monochrome = false                  //sets the colour of annotation graphs
    ranktrace_smooth = true             //controls the smoothing of the RankTrace curve
    ranktrace_rate = 15                 //controls the rate of update of the RankTrace graph (30 is roughly one new point per second at 30 FPS)
    last_click = false                  //helper variable that tells the app that a mousedown event occured
    gtrace_control = "mouse"            //controls the input type of gtrace ("keyboard" or "mouse")
    gtrace_click = true                 //controls whether gtrace takes mouse-click as an input
    gtrace_update = true                //controls whether gtrace records a continuous stream of values
    gtrace_rate = 1000					//controls the rate of update for the GTrace annotator
;

let RANKTRACE_DEBUG = false;

var
    annotation_type,
    renderer,
    video,
    videoname,
    target,
    project_id,
    session_id,
    name,
    sound,
    test_mode
;

var test_mode_cache = {'Timestamp': [], 'VideoTime': [], 'Value': []};

// Loading video based on the url passed by the Python server
// annotation_type controls which interface is loaded
// video is the url to the video to be played
// target is the annotation target
// custom user is set when a user is initated the application from the upload page
    // custom user is used to recreate the log's filename and pass it to the endplate url
function loadVideo(
	_annotation_type,
    _video_type,
    _video,
    _videoname,
    _target,
    _project_id,
    _entry_id,
    _session_id,
    _name,
    _sound,
    _monochrome,
    _ranktrace_smooth,
    _ranktrace_rate,
    _gtrace_control,
    _gtrace_update,
    _gtrace_click,
    _tolerance,
    _gtrace_rate,
    _test_mode,
    _aspect_ratio
	){
    name = _name;
    annotation_type = _annotation_type;
    video_type = _video_type;
    video = _video;
    videoname = _videoname;
    target = _target;
    project_id = _project_id;
    entry_id = _entry_id;
    session_id = _session_id;
    sound = _sound;
    test_mode = _test_mode == "1" ? true : false;
    monochrome = _monochrome == "on" ? true : false;
    ranktrace_rate = _ranktrace_rate == "" ? 15 : Math.ceil(parseFloat(_ranktrace_rate));
    ranktrace_smooth = _ranktrace_smooth == "on" ? true : false;
    gtrace_control = _gtrace_control == "" ? "keyboard" : gtrace_control;
    gtrace_click = _gtrace_click == "on" ? true : false;
    gtrace_update = _gtrace_update == "on" ? true : false;
    tolerance = _tolerance == "" ? 0.5 : parseInt(_tolerance)/100;
    gtrace_rate = _gtrace_rate == "" ? 1000 : parseInt(_gtrace_rate);
    aspect_ratio = _aspect_ratio == "" ? "16:9" : _aspect_ratio;

    // Set aspect ratio of video container
    if (aspect_ratio.includes(':')){
        aspect_ratio = aspect_ratio.split(':');
        aspect_ratio = aspect_ratio[0]/aspect_ratio[1];
        $('.inner').css('max-width', 'calc((100vh - 245px) * ' + aspect_ratio + ')');
    }

    // Set label for the annotation
    console.log("Annotation target is set to " + target + ".");
    if (test_mode) {
        console.log("TEST MODE");
    }

    // Load video if the source is file upload
    if (video_type == 'upload' || video_type == 'ftp' || video_type == 'user_upload' || video_type == 'game') {
        console.log("Loading...")
        var source = document.createElement('source');
        source.setAttribute('src', video);
        video_container.append(source);
        console.log("Loading video: " + video + "...");
        // Start annotator when video can start playing
        video_container.oncanplaythrough = function() {
            showStart();
        }

        // Stop annotator when the video finished and load the end page
        $('video').on('ended',function(){
            initEndSession();
        });
    }

    // Load youtube API if YouTube video is the source
    if (video_type == 'youtube' || video_type == 'user_youtube') {
        var tag = document.createElement('script');
        tag.src = "https://www.youtube.com/iframe_api";
        var firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
    }
}

// Youtube player mandatory functions and setup
// Has to be global!
function onYouTubeIframeAPIReady() {
    player = new YT.Player('player', {
        height: $('video').height(),
        width: $('video').width(),
        videoId: video,
        playerVars: {
            'autoplay': 1,
            'color': 'white',
            'controls': 0,
            'disablekb': 1,
            'enablejsapi': 1,
            'fs': 0,
            'modestbranding': 1,
            'host': 'https://www.youtube.com',
            'origin': new URL(window.location.href).origin,
            'playsinline': 1,
            'rel': 0
        },
        events: {
            'onReady': onPlayerReady,
            'onStateChange': onPlayerStateChange
        }
    });
}

// The video autoloads to start buffering. On Ready the app pauses the video and gives control to the user.
function onPlayerReady(event) {
    console.log('ready!')
    player.mute();
    // Workaround stuck buffering
    let failSafe = setTimeout(function() {
        if (firstStart) {
            console.warn("Failsafe startup triggered.")
            player.playVideo();
            setTimeout(function() {
                player.playVideo();
            }, 1000);
        }
    }, 5000);

    player.addEventListener("onStateChange", function(){
        if(player.getPlayerState() == 1 && firstStart) {
            player.pauseVideo();
            player.removeEventListener();
            player.unMute();
            clearTimeout(failSafe);
            showStart();
        }
    });
}

function onPlayerStateChange(event) {
    var status = event.data
    switch (status) {
        case -1: //unstarted
            break;
        case 0: //ended
            initEndSession();
            break;
        case 1: //playing
            break;
        case 2: //paused
            break;
        case 3: //buffering
            player.pauseVideo();
            paused = true;
            $('#pause').removeClass('hidden');
            $('#video-shade').removeClass('hidden');
            break;
        case 5: //video cued
            break;
    }
}

function showStart() {
    $('#video-load-notice')[0].innerHTML = 'Your video is loaded. To start, press <span class="key space"></span>.'
    $('#video-load-icon').addClass('hidden');
    console.log("Video loaded.");
    startControls();
}

function initEndSession(){
    if (!test_mode) {
        end_trigger = true;
        $('#ended').removeClass('hidden');
        $('#video-shade').removeClass('hidden');
        context.fillStyle = "#b1b1b3";
        context.fillRect(canvas.width - 33, 10, 21, 20);
        paused = true;

        // Register video as seen if it fell through the previous check
        if(seen_trigger == false) {
            seen_trigger = true;
            var seen;

            if (video_type == 'upload' || video_type == 'ftp' || video_type == 'user_upload' || video_type == 'game') {
                seen = video;
            } else {
                seen = "https://www.youtube.com/watch?v="+video;
            }
            $.post("util/reg_seen.php", {project_id: project_id, entry_id: entry_id});
            console.log("Video registered as 'seen'.");
        }

        // Make sure all packages have been recieved
        // This is to make sure that the log is closed and can be downloaded after the redirect
        // If a custom user was passed to the application, generate filename for the log
        $( document ).ajaxStop(function() {
            endSession();
        });
        // If no ajax call has been initated or the process is taking too long,
        // end the session after 3 sec of idleness
        setTimeout(function(){ endSession();}, 3000);
    } else {
        var csv = "Timestamp,VideoTime,Value" + '\r\n';
        for (var row = 0; row < test_mode_cache['Timestamp'].length; row ++) {
            var e = test_mode_cache['Timestamp'][row];
            var t = test_mode_cache['VideoTime'][row];
            var v = test_mode_cache['Value'][row];
            var row_data = e + ',' + t + ',' + v + '\r\n';
            csv += row_data;
        }
        var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        if (navigator.msSaveBlob) { // IE 10+
            navigator.msSaveBlob(blob, exportedFilenmae);
        } else {
            var link = document.createElement("a");
            if (link.download !== undefined) { // feature detection
                // Browsers that support HTML5 download attribute
                var url = URL.createObjectURL(blob);
                link.setAttribute("href", url);
                link.setAttribute("download", "Annotation_"+(new Date).getTime()+".csv");
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }
        location.href='test.php';
    }
}

// Renders the end of session
    // If a custom user is set, it constructs a path name for the logfile and pass it to the endplate url
function endSession() {
    if (!ended){
        sendAnnotation((new Date).getTime(), currentTime, 0);
        ended = true;
        if(video_type != 'game') {
            // Refresh the window -> loads next video based on where the player at in the project process
            //                -> navigates to the endplate if the player finished annotating
            location.href='annotation.php?id='+project_id;
        } else {
            // If the annotator played a game before, load up the play interface instead
            location.href='play.php?id='+project_id;
        }

    }
}

// Listens for keyboard controls
function startControls() {
    sessionStart = Math.floor((new Date).getTime()/1000);

    var keySpeed = 10;
    if(annotation_type == "ranktrace"){
        keySpeed = 10;
    }
    if(annotation_type == "ranktrace2"){
        keySpeed = 100;
    }
    if(annotation_type == "binary"){
        keySpeed = 100;
    }
    if(annotation_type == "gtrace"){
        keySpeed = 22;
    }

    // When Space is pressed start/pause the video
    // 1 sec delay on start/pause to stop flickering when space held down
    KeyboardController({
        32: function() { startPause(); },
    }, 1000);

    // Keyboard controller for a smoother register of keydown
    if (annotation_type == "gtrace") {
        KeyboardController({
            // Key Up and W, Right and D
            39: function() { addValue(); },
            68: function() { addValue(); },
            // Key Down and S, Left and A
            37: function() { subtractValue(); },
            65: function() { subtractValue(); },
        }, keySpeed);
    }
    if (annotation_type == "binary") {
        KeyboardController({
            // Key Up and W, Right and D
            38: function() { addValue(); },
            87: function() { addValue(); },
            // Key Down and S, Left and A
            40: function() { subtractValue(); },
            83: function() { subtractValue(); },
        }, keySpeed);
    }

    if (annotation_type == "ranktrace" || annotation_type == "ranktrace2") {
        $(window).on('wheel', function(event){
            if(event.originalEvent.deltaY>0){
                subtractValue();
            } else if(event.originalEvent.deltaY<0){
                addValue();
            }
        });
    }
}

// If the help icon is clicked or the window becomes unfocused, pause the video
$(".help").click(function(){
    if (video_type == 'youtube' || video_type == 'user_youtube') {
        player.pauseVideo();
    }
    video_container.pause();
    paused = true;
    $('#pause').removeClass('hidden');
    $('#video-shade').removeClass('hidden');
});
$(window).blur(function(e) {
    if (video_type == 'youtube' || video_type == 'user_youtube') {
        player.pauseVideo();
    }
    video_container.pause();
    paused = true;
    $('#pause').removeClass('hidden');
    $('#video-shade').removeClass('hidden');
});
$(window).focus(function(e) {
    if (video_type == 'youtube' || video_type == 'user_youtube') {
        player.pauseVideo();
    }
    video_container.pause();
    paused = true;
    $('#pause').removeClass('hidden');
    $('#video-shade').removeClass('hidden');
});

// Animation loop for the canvas element when running RankTrace
// Grapical implementation of the RankTraceTool
// Sends packages to the server with annotator values
function animateRankTrace(){
    if (paused) {
        //Draw pause symbol and exit loop if the video is paused
        context.fillStyle = "#4d4d4d";
        context.fillRect(canvas.width - 35, 8, 25, 24);
        context.fillStyle = "#b1b1b3";
        context.fillRect(canvas.width - 20, 10, 8, 20);
        context.fillRect(canvas.width - 33, 10, 8, 20);
        return;
    }
    canvas.width = $('#trace').width();
    // Output for the video length bar
    $('#video-length #bar').css('width', (getCurrentTime()/getDuration())*100 + '%');
    // If video passed 99% of viewing time, register it as seen
    if(getCurrentTime()/getDuration() > tolerance && seen_trigger == false) {
        seen_trigger = true;
        var seen;
        if (video_type == 'upload' || video_type == 'ftp' || video_type == 'user_upload' || video_type == 'game') {
            seen = video;
        } else {
            seen = "https://www.youtube.com/watch?v="+video;
        }
        $.post("util/reg_seen.php", {project_id: project_id, entry_id: entry_id});
        console.log("Video registered as 'seen'.");
    }
    // Automatically send new annotations values every ms while the animation loop is running
    currentTime = Math.round(getCurrentTime() * 1000); // Current time of the video in ms
    recordAnnotation(currentTime, 'unbounded');

    // Request new frame, clear canvas, and redraw background
    requestAnimationFrame(animateRankTrace);
    context.clearRect(0, 0, canvas.width, canvas.height);
    context.fillStyle = '#4d4d4d';
    context.fillRect(0, 0, canvas.width, canvas.height);

    // Calculate the normalised values for the annotaton trace for the current tick
    trace.push(annotatorValue);
    for (var j = 0; j < trace.length; j ++) {
        normTrace.push({
            x: normPosX(trace, j),
            y: normPosY(trace[j], annotatorMax, annotatorMin)
        });
    }

    // Sytle setup
    context.lineCap="round";
    context.strokeStyle = '#86a3c6';
    context.lineWidth = 6;
    context.beginPath();
    // Move to start position
    context.moveTo(normTrace[0].x, normTrace[0].y);

    // Smooth curve for display
    function resample(arr, len) {
        let chunks_x = [];
        let copy_x = [...arr.map(i => i.x)];
        while(copy_x.length > len) {
            let slice_x = copy_x.splice(0, len)
            chunks_x.push(slice_x.reduce((a, b) => (a + b)) / slice_x.length);
        }

        let chunks_y = [];
        let copy_y = [...arr.map(i => i.y)];
        while(copy_y.length > len) {
            let slice_y = copy_y.splice(0, len)
            chunks_y.push(slice_y.reduce((a, b) => (a + b)) / slice_y.length);
        }

        return chunks_x.map(function(e, i) {
          return {'x': e, 'y': chunks_y[i]};
        });
    }

    smoothTrace = [normTrace[0]];
    smoothTrace = smoothTrace.concat(resample(normTrace, ranktrace_rate));

    // Hot-Cold colour update
    var grad = context.createLinearGradient(smoothTrace[0].x, 0, smoothTrace[smoothTrace.length-1].x, 0);
    grad.addColorStop(0, "rgba(77, 77, 77, 0)");
    let down_trend = 0;
    let up_trend = 0;
    let last_chage = 0;
    let color = "#b2b2b2";
    if (!monochrome){
        grad.addColorStop(smoothTrace[0].x/smoothTrace[smoothTrace.length-1].x, "#b2b2b2");
        for (let i = 2; i < smoothTrace.length; i++) {
            let prev_point = smoothTrace[i-1];
            let this_point = smoothTrace[i];
            let position = this_point.x/smoothTrace[smoothTrace.length-1].x;
            position = position > 0.01 ? position - 0.01 : position;
            let change = prev_point.y - this_point.y;
            if (change > 0){
                if (last_chage == 0){
                    up_trend += 20;
                }
                last_chage = 1;
                up_trend +=15;
                rgb = hsl2rgb(360, up_trend, 65);
                color = 'rgba('+ rgb[0] +','+ rgb[1] +','+ rgb[2] +', 1)';
            } else if (change < 0){
                if (last_chage == 0){
                    down_trend += 20;
                }
                last_chage = -1;
                down_trend +=15;
                rgb = hsl2rgb(213, 20+down_trend, 65);
                color = 'rgba('+ rgb[0] +','+ rgb[1] +','+ rgb[2] +', 1)';
            } else {
                up_trend -= 10;
                down_trend -= 10;
                up_trend = up_trend < 0 ? 0 : up_trend;
                down_trend = down_trend < 0 ? 0 : down_trend;
                up_trend = up_trend > 100 ? 100 : up_trend;
                down_trend = down_trend > 100 ? 100 : down_trend;
                if(last_chage == 1){
                    rgb = hsl2rgb(360, up_trend, 65);
                    color = 'rgba('+ rgb[0] +','+ rgb[1] +','+ rgb[2] +', 1)';
                } else if(last_chage == -1){
                    rgb = hsl2rgb(213, down_trend, 65);
                    color = 'rgba('+ rgb[0] +','+ rgb[1] +','+ rgb[2] +', 1)';
                }
                if (up_trend == 0 && down_trend == 0){
                    last_chage = 0;
                    color = "#b2b2b2";
                }
            }
            grad.addColorStop(position, color);
        }
    } else {
        color = '#86a3c6';
        grad.addColorStop(smoothTrace[0].x/smoothTrace[smoothTrace.length-1].x, color);
    }

    context.strokeStyle = grad;

    // For every point, calculate the control point and draw quadratic curve
    // Curve goes through the middle points between registered annotations to get a smooth curve
    if (ranktrace_smooth) {
        let xc;
        let yc;
        for (let i = 1; i < smoothTrace.length - 1; i ++) {

            xc = (smoothTrace[i].x + smoothTrace[i + 1].x) / 2;
            yc = (smoothTrace[i].y + smoothTrace[i + 1].y) / 2;


           context.quadraticCurveTo(smoothTrace[i].x, smoothTrace[i].y, xc, yc);
        }

        context.moveTo(xc, yc);

        xc = (smoothTrace[smoothTrace.length-1].x + normTrace[normTrace.length-1].x) / 2;
        yc = (smoothTrace[smoothTrace.length-1].y + normTrace[normTrace.length-1].y) / 2;

        context.quadraticCurveTo(smoothTrace[smoothTrace.length-1].x, smoothTrace[smoothTrace.length-1].y, xc, yc);
    } else {
        for (let i = 0; i < smoothTrace.length; i ++) {
            context.lineTo(smoothTrace[i].x, smoothTrace[i].y);
        }
    }

    context.lineTo(normTrace[normTrace.length-1].x, normTrace[normTrace.length-1].y);
    context.stroke();
    context.beginPath();
    context.strokeStyle = color;
    context.arc(normTrace[normTrace.length-1].x, normTrace[normTrace.length-1].y, 10, 0, 2 * Math.PI, false);
    context.stroke();

    // ===============================
    if (RANKTRACE_DEBUG) {
        // Sytle setup
        context.lineCap="round";
        context.strokeStyle = 'black';
        context.lineWidth = 2;
        context.beginPath();
        // Move to start position
        context.moveTo(normTrace[0].x, normTrace[0].y);

        smoothTrace = [normTrace[0]];
        smoothTrace = smoothTrace.concat(resample(normTrace, 1));

        // Hot-Cold colour update
        var grad = context.createLinearGradient(smoothTrace[0].x, 0, smoothTrace[smoothTrace.length-1].x, 0);
        grad.addColorStop(0, "rgba(77, 77, 77, 0)");
        color = 'black';
        grad.addColorStop(smoothTrace[0].x/smoothTrace[smoothTrace.length-1].x, color);

        context.strokeStyle = grad;

        // For every point, calculate the control point and draw quadratic curve
        // Curve goes through the middle points between registered annotations to get a smooth curve
        for (let i = 1; i < smoothTrace.length - 1; i ++) {

            xc = (smoothTrace[i].x + smoothTrace[i + 1].x) / 2;
            yc = (smoothTrace[i].y + smoothTrace[i + 1].y) / 2;

            context.quadraticCurveTo(smoothTrace[i].x, smoothTrace[i].y, xc, yc);
        }
        context.moveTo(xc, yc);

        xc = (smoothTrace[smoothTrace.length-1].x + normTrace[normTrace.length-1].x) / 2;
        yc = (smoothTrace[smoothTrace.length-1].y + normTrace[normTrace.length-1].y) / 2;

        context.quadraticCurveTo(smoothTrace[smoothTrace.length-1].x, smoothTrace[smoothTrace.length-1].y, xc, yc);
        context.lineTo(normTrace[normTrace.length-1].x, normTrace[normTrace.length-1].y);
        context.stroke();
        context.beginPath();
        context.strokeStyle = color;
        context.arc(normTrace[normTrace.length-1].x, normTrace[normTrace.length-1].y, 10, 0, 2 * Math.PI, false);
        context.stroke();
    }
    // =============================
    normTrace = [];
}

// Animation loop for the canvas element when running Gtrace style
// Sends packages to the server with annotator values
function animateGtrace(){
    if (paused) {
        //Draw pause symbol and exit loop if the video is paused
        context.fillStyle = "#4d4d4d";
        context.fillRect(canvas.width - 35, 8, 25, 24);
        context.fillStyle = "#b1b1b3";
        context.fillRect(canvas.width - 20, 10, 8, 20);
        context.fillRect(canvas.width - 33, 10, 8, 20);
        return;
    }
    canvas.width = $('#trace').width();

    // Output for the video length bar
    $('#video-length #bar').css('width', (getCurrentTime()/getDuration())*100 + '%');
    // If video passed 25% of viewing time, register it as seen
    if(getCurrentTime()/getDuration() > tolerance && seen_trigger == false) {
        seen_trigger = true;
        var seen;
        if (video_type == 'upload' || video_type == 'ftp' || video_type == 'user_upload' || video_type == 'game') {
            seen = video;
        } else {
            seen = "https://www.youtube.com/watch?v="+video;
        }
        $.post("util/reg_seen.php", {project_id: project_id, entry_id: entry_id});
        console.log("Video registered as 'seen'.");
    }

    currentTime = Math.round(getCurrentTime() * 1000); // Current time of the video in ms

    // Request new frame, clear canvas, and redraw background
    requestAnimationFrame(animateGtrace);
    context.clearRect(0, 0, canvas.width, canvas.height);
    context.fillStyle = '#4d4d4d';
    context.fillRect(0, 0, canvas.width, canvas.height);

    // Clamp annotator value
    annotatorValue = clamp(annotatorValue, -100, 100);

    if (gtrace_control == "mouse"){
        let canvasWidth = canvas.getBoundingClientRect().width;
        let canvasPadding = (window.innerWidth - canvasWidth + 80)/2;

        mouseX = clamp(mouseX, canvasPadding, window.innerWidth-canvasPadding);
        annotatorValue = (mouseX - canvasPadding)/((window.innerWidth-(2*canvasPadding)));

        if (annotatorValue >= 0.5) {
            annotatorValue = (annotatorValue - 0.5)/0.5;
        } else {
            annotatorValue = -1*(-1*(annotatorValue/0.5)+1);
        }
        annotatorValue *= 100;
    } else {
        if (keyPress) {
            annotationMod = annotationMod + (Math.pow(2, mod)-1);
            mod += 0.01;
        } else {
            annotationMod = 1;
            mod = 0;
        }
    }

    // Draws previous annotator cursor positions
    for (var j = trace.length -1; j >= 0 ; j--) {
        var drawTime = Math.round(getCurrentTime() * 1000);
        var rgb = hsl2rgb(trace[j].h, trace[j].s, trace[j].l);
        var alpha = 0;
        if (drawTime - trace[j].t < 100) {
            alpha = (drawTime - trace[j].t)/100;
        } else {
            alpha = clamp(0.9 - ((drawTime - trace[j].t - (3*gtrace_rate))/3000), 0, 1);
        }
        if (!monochrome){
            context.strokeStyle = 'rgba('+ rgb[0] +','+ rgb[1] +','+ rgb[2] +','+alpha+')';
        } else {
            context.strokeStyle = 'rgba(134, 163, 198,'+alpha+')';
        }
        context.lineWidth = 4;
        context.beginPath();
        context.arc(canvas.width/2 + trace[j].pos, canvas.height/2, 30*alpha, 0, 2 * Math.PI, false);
        context.stroke();
        if (drawTime - trace[j].t > 5000){
            trace.splice(j, 1);
        }
    }

    // Annotation cursor
    hue = (annotatorValue + 100)/2;
    saturation = 85 * (Math.abs(annotatorValue)/100);
    lightness = 55 + (Math.abs(annotatorValue)/10);
    if (!monochrome){
        context.strokeStyle = 'hsl('+ hue +','+ saturation +'%,'+ lightness +'%)';
    } else {
        context.strokeStyle = '#86a3c6';
    }
    context.lineWidth = 10;
    context.beginPath();
    xPos = (annotatorValue/100)*((canvas.width/2)-40);
    context.arc(canvas.width/2 + xPos, canvas.height/2, 20, 0, 2 * Math.PI, false);
    context.stroke();

    if (gtrace_update){
        if ((currentTime > storedTime+gtrace_rate) && !keyPress && storedValue != annotatorValue) {
            recordAnnotation(currentTime, 'bounded');
            trace.push({pos: xPos,
                        h: (annotatorValue + 100)/2,
                        s: 85 * (Math.abs(annotatorValue)/100),
                        l: 55 + (Math.abs(annotatorValue)/10),
                        t: Math.round(getCurrentTime() * 1000)});
            storedTime = currentTime;
            storedValue = annotatorValue;
        }
    }

    if (gtrace_control == "mouse" && gtrace_click){
        if (last_click) {
            recordAnnotation(currentTime, 'bounded');
            trace.push({pos: xPos,
                        h: (annotatorValue + 100)/2,
                        s: 85 * (Math.abs(annotatorValue)/100),
                        l: 55 + (Math.abs(annotatorValue)/10),
                        t: Math.round(getCurrentTime() * 1000)});
            storedTime = currentTime;
            storedValue = annotatorValue;
        }
    }

    // Draw bounds of the annotator over the cursor
    let likertWidth = (canvas.width-80)/6;
    context.beginPath();
    context.lineWidth = 2;
    if (!monochrome){
        context.fillStyle = 'hsl('+ 10 +','+ 55 +'%,'+ 53 +'%)';
        context.fillRect(40+likertWidth, 0, 2, canvas.height);
        context.fillStyle = 'hsl('+ 30 +','+ 55 +'%,'+ 53 +'%)';
        context.fillRect(40+(2*likertWidth), 0, 2, canvas.height);
        context.fillStyle = 'hsl('+ 80 +','+ 55 +'%,'+ 53 +'%)';
        context.fillRect(canvas.width-40-likertWidth, 0, 2, canvas.height);
        context.fillStyle = 'hsl('+ 60 +','+ 55 +'%,'+ 53 +'%)';
        context.fillRect(canvas.width-40-(2*likertWidth), 0, 2, canvas.height);

        context.fillStyle = 'hsl('+ 50 +','+ 0 +'%,'+ 50 +'%)';
        context.fillRect(canvas.width/2-1, 0, 2, canvas.height);

        context.fillStyle = 'hsl('+ 0 +','+ 85 +'%,'+ 53 +'%)';
        context.fillRect(40, 0, 2, canvas.height);

        context.fillStyle = 'hsl('+ 100 +','+ 85 +'%,'+ 53 +'%)';
        context.fillRect(canvas.width - 40, 0, 2, canvas.height);
    } else {
        context.fillStyle = '#86a3c6';
        context.fillRect(40+likertWidth, 0, 2, canvas.height);
        context.fillRect(40+(2*likertWidth), 0, 2, canvas.height);
        context.fillRect(canvas.width-40-likertWidth, 0, 2, canvas.height);
        context.fillRect(canvas.width-40-(2*likertWidth), 0, 2, canvas.height);
        context.fillRect(canvas.width/2-1, 0, 2, canvas.height);
        context.fillRect(40, 0, 2, canvas.height);
        context.fillRect(canvas.width - 40, 0, 2, canvas.height);
    }


    // context.fillStyle = context.strokeStyle = 'hsl('+ 50 +','+ 0 +'%,'+ 50 +'%)';
    // context.fillRect(canvas.width/2-1, 0, 2, canvas.height/2-34);
    // context.fillRect(canvas.width/2-1, canvas.height/2+34, 2, canvas.height/2-34);
    // context.arc(canvas.width/2, canvas.height/2, 34, 0, 2 * Math.PI, false);
    // context.stroke();

    // context.beginPath();
    // context.strokeStyle = 'hsl('+ 0 +','+ 85 +'%,'+ 53 +'%)';
    // context.arc(40, canvas.height/2, 34, Math.PI/2, Math.PI + (Math.PI * 1) / 2, false);
    // context.stroke();
    // context.beginPath();
    // context.strokeStyle = 'hsl('+ 100 +','+ 85 +'%,'+ 53 +'%)';
    // context.arc(canvas.width - 40, canvas.height/2, 34, Math.PI/2, Math.PI + (Math.PI * 1) / 2, true);
    // context.stroke();
    last_click = false;
}

// Animation loop for the canvas element when running Binary labelling with memory
// Sends packages to the server with annotator values
function animateBinary(){
    if (paused) {
        //Draw pause symbol and exit loop if the video is paused
        context.fillStyle = "#4d4d4d";
        context.fillRect(canvas.width - 35, 8, 25, 24);
        context.fillStyle = "#b1b1b3";
        context.fillRect(canvas.width - 20, 10, 8, 20);
        context.fillRect(canvas.width - 33, 10, 8, 20);
        return;
    }
    canvas.width = $('#trace').width();
    // Output for the video length bar
    $('#video-length #bar').css('width', (getCurrentTime()/getDuration())*100 + '%');
    // If video passed 25% of viewing time, register it as seen
    if(getCurrentTime()/getDuration() > tolerance && seen_trigger == false) {
        seen_trigger = true;
        var seen;
        if (video_type == 'upload' || video_type == 'ftp' || video_type == 'user_upload' || video_type == 'game') {
            seen = video;
        } else {
            seen = "https://www.youtube.com/watch?v="+video;
        }
        $.post("util/reg_seen.php", {project_id: project_id, entry_id: entry_id});
        console.log("Video registered as 'seen'.");
    }

    // Request new frame, clear canvas, and redraw background
    requestAnimationFrame(animateBinary);
    context.clearRect(0, 0, canvas.width, canvas.height);
    context.fillStyle = '#4d4d4d';
    context.fillRect(0, 0, canvas.width, canvas.height);

    // Calculate annotation value for each input and send it to sever (1 for positive change -1 for negative)
    // Automatically send new annotations values every ms while the animation loop is running
    currentTime = Math.round(getCurrentTime() * 1000); // Current time of the video in ms
    recordAnnotation(currentTime, 'binary');

    context.lineCap="round";
    context.lineWidth = 4;
    for (var j = 1; j < trace.length; j ++) {
        if (!monochrome){
	        if (trace[j].y == 40) {
	            context.strokeStyle = '#82ba84';
	            context.fillStyle = '#a6eda8';
	        } else {
	            context.strokeStyle = '#bd5a57';
	            context.fillStyle = '#f0736e';
	        }
        } else {
            context.strokeStyle = '#6388b6';
            context.fillStyle = '#86a3c6';
        }
        context.beginPath();
        context.arc(((trace[j].x / currentTime) * (canvas.width - 40)) + 20, trace[j].y,//canvas.height/2,
                    10, 0, 2 * Math.PI, false);
        context.fill();
        context.stroke();
    }
}

/*###############################################################################
#                               Helper functions                                #
###############################################################################*/
// Records an annotation
// Requires 'currentTime'
// Mode can be:
// 'binary': 1 positive -1 negative annotation
// 'bounded': between -100 and 100, where 0 is the middle
// 'unbounded' (default): between -inf and +inf, where 0 is the initial value
function recordAnnotation(currentTime, mode){
    if ((currentTime > previousTime) && (previousValue != annotatorValue) || (last_click && mode == 'bounded')) {
        if(mode == 'binary'){
            if (annotatorValue > previousValue) {
                trace.push({x: currentTime, y: 40});
                sendAnnotation((new Date).getTime(), currentTime, 1);
            } else {
                trace.push({x: currentTime, y: 110});
                sendAnnotation((new Date).getTime(), currentTime, -1);
            }
        } else if(mode =='bounded') {
            sendAnnotation((new Date).getTime(), currentTime, clamp(annotatorValue, -100, 100));
        } else {
            sendAnnotation((new Date).getTime(), currentTime, annotatorValue);
        }
        previousTime = currentTime;
        previousValue = annotatorValue;
    }
}

// Posts a package to the server
function sendAnnotation(epoch ,timestamp, value){
    if (!test_mode) {
        let video_name = name + " - " + getDuration();
        if (video_type == 'youtube' || video_type == 'user_youtube') {
            video_name = player.getVideoData().title + " (" + name + ")" + " - " + getDuration();
        }

        $.post(
            "util/logger.php",
            {
                epoch: epoch,
                timestamp: timestamp,
                value: value,
                project_id: project_id,
                entry_id: entry_id,
                session_id: session_id,
                original_name: video_name,
                annotation_type: annotation_type
            });
    } else {
        test_mode_cache['Timestamp'].push(epoch);
        test_mode_cache['VideoTime'].push(timestamp);
        test_mode_cache['Value'].push(Math.round(value));
    }

}

// Returns a number value clamped between a min and max value
function clamp(number, min, max) {
    return Math.max(min, Math.min(number, max));
}

// Returns a normalised value
function norm(val, max, min) {
    return (val - min) / (max - min);
}

// Return normalised Y and X positions for the display
function normPosY(val, max, min) {
    var position = Math.abs((norm(val, max, min) * (canvas.height - 40)) - (canvas.height - 40)) + 20;
    if (position !== position) {
        return canvas.height / 2;
    } else {
        return position;
    }
}

function normPosX(points, i) {
    return (((canvas.width - 50) / points.length) * i) + 20;
}

// Keyboard input with customisable repeat (set to 0 for no key repeat)
function KeyboardController(keys, repeat) {
    // Lookup of key codes to timer ID, or null for no repeat
    var timers= {};

    // When key is pressed and we don't already think it's pressed, call the
    // key action callback and set a timer to generate another one after a delay
    $(document).keydown(function(event) {
        var key= (event || window.event).which;
        if (!(key in keys))
            return true;
        if (!(key in timers)) {
            timers[key]= null;
            keys[key]();
            if (repeat!==0)
                timers[key]= setInterval(keys[key], repeat);
        }
        return false;
    });

    // Cancel timeout and mark key as released on keyup
    $(document).keyup(function(event) {
        var key= (event || window.event).which;
        if (key in timers) {
            if (timers[key]!==null)
                clearInterval(timers[key]);
            delete timers[key];
        }
    });

    // Prevent key from get stuck when window becomes unfocused When window is unfocused we may not get key events.
    $(window).blur(function(event) {
        for (key in timers)
            if (timers[key]!==null)
                clearInterval(timers[key]);
        timers= {};
    });
}

function startPause() {
    console.log("Space Pressed");
    var youtubePaused = false;
    if (!end_trigger) {
        if (video_type == 'youtube' || video_type == 'user_youtube') {
            if (player.getPlayerState() == 2){
                youtubePaused = true;
            }
        }
        if (video_container.paused || youtubePaused) {
            // Remove tutorial plate and start the video
            // Tutorial plate is set to opacity: 0 to prevent clicking on video (relevant for youtube playback)
            if (firstStart) {
                firstStart = false;
                $('#tutorial').css('opacity',0);
                canvas.width = $('#trace').width();

                if(sound == 'off') {
                    if (video_type == 'youtube' || video_type == 'user_youtube') {
                        player.mute();
                    } else {
                        video_container.muted = true;
                    }
                }
                // Log start of the annotation
                sendAnnotation((new Date).getTime(), currentTime, 0);
            }
            if (video_type == 'youtube' || video_type == 'user_youtube') {
                player.playVideo();
            }
            video_container.play();

            paused = false;
            $('#pause').addClass('hidden');
            $('#video-shade').addClass('hidden');
            if (annotation_type == "binary") {
                animateBinary();
            } else if (annotation_type == "gtrace") {
                animateGtrace();
            } else {
                animateRankTrace();
            }
        } else {
            if (video_type == 'youtube' || video_type == 'user_youtube') {
                player.pauseVideo();
            } else {
                video_container.pause();
            }
            paused = true;
            $('#pause').removeClass('hidden');
            $('#video-shade').removeClass('hidden');
        }
    }
}

function addValue() {
    if (paused == false) {
        annotatorPosY -= 1;
        annotatorValue = annotatorValue + annotationMod;
        if (annotatorValue > annotatorMax) {
            annotatorMax = annotatorValue;
        }
    }
    $('.keys>.up').addClass('pressed');
    $('.keys>.right').addClass('pressed');
    $('.keys>.scroll-up').addClass('pressed');
    keyPress = true;
}

function subtractValue() {
    if (paused == false) {
        annotatorPosY += 1;
        annotatorValue = annotatorValue - annotationMod;
        if (annotatorValue < annotatorMin) {
            annotatorMin = annotatorValue;
        }
    }
    $('.keys>.down').addClass('pressed');
    $('.keys>.left').addClass('pressed');
    $('.keys>.scroll-down').addClass('pressed');
    keyPress = true;
}

// Removes pressed state from visual buttons on the UI
$(window).keyup(function(e) {
    if (e.keyCode == 38 || e.keyCode == 87 || e.keyCode == 39 || e.keyCode == 68) {
        $('.keys>.up').removeClass('pressed');
        $('.keys>.right').removeClass('pressed');
    } else if(e.keyCode == 40 || e.keyCode == 83 || e.keyCode == 37 || e.keyCode == 65) {
        $('.keys>.down').removeClass('pressed');
        $('.keys>.left').removeClass('pressed');
    }
    keyPress = false;
});

// Removes pressed state from visual buttons when we stop scrolling
var wheeling;
$(window).on('wheel', function(event){
  clearTimeout(wheeling);
  wheeling = setTimeout(function() {
    $('.keys>.scroll-up').removeClass('pressed');
    $('.keys>.scroll-down').removeClass('pressed');
    wheeling = undefined;
    keyPress = false;
  }, 100);
});

function getCurrentTime(){
    var curr_time;
    if(video_type == 'upload' || video_type == 'ftp' || video_type == 'user_upload' || video_type == 'game') {
        curr_time = video_container.currentTime;
    } else if(video_type == 'youtube' || video_type == 'user_youtube') {
        curr_time = player.getCurrentTime();
    }
    return curr_time;
}

function getDuration(){
    var dur;
    if(video_type == 'upload' || video_type == 'ftp' || video_type == 'user_upload' || video_type == 'game') {
        dur = video_container.duration;
    } else if(video_type == 'youtube' || video_type == 'user_youtube') {
        dur = player.getDuration();
    }
    return dur;
}

function hsl2rgb(h,s,l) {
    s = s/100;
    l = l/100;
    let a=s*Math.min(l,1-l);
    let f= (n,k=(n+h/30)%12) => l - a*Math.max(Math.min(k-3,9-k,1),-1);
    return [f(0)*255,f(8)*255,f(4)*255];
}

var mouseX = null;
var mouseY = null;

document.addEventListener('mousemove', onMouseUpdate, false);
document.addEventListener('mouseenter', onMouseUpdate, false);
document.addEventListener('mousedown', onMouseClickUpdate, false);

function onMouseUpdate(e) {
    mouseX = e.pageX;
    mouseY = e.pageY;
}

function onMouseClickUpdate(e) {
    onMouseUpdate(e);
    last_click = true;
}
