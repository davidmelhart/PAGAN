/*
*  Copyright (c) 2016 The WebRTC project authors. All Rights Reserved.
*
*  Use of this source code is governed by a BSD-style license
*  that can be found in the LICENSE file in the root of the source
*  tree.
*/

'use strict';

var project_id,
    entry_id,
    source_url,
    original_name;

const mediaSource = new MediaSource();
mediaSource.addEventListener('sourceopen', handleSourceOpen, false);
let mediaRecorder;
let recordedBlobs;
let sourceBuffer;

var canvas = null;
var stream = null;
//console.log('Started stream capture from canvas element: ', stream);

function initRecord(){
    console.log( "ready!" );
    canvas = document.querySelector('canvas');
    stream = canvas.captureStream(24);
    console.log('Started stream capture from canvas element: ', stream);        
}

function handleSourceOpen(event) {
    console.log('MediaSource opened');
    sourceBuffer = mediaSource.addSourceBuffer('video/webm; codecs="vp8"');
    console.log('Source buffer: ', sourceBuffer);
}

function handleDataAvailable(event) {
    if (event.data && event.data.size > 0) {
        recordedBlobs.push(event.data);
    }
}

function handleStop(event) {
    console.log('Recorder stopped: ', event);
    const superBuffer = new Blob(recordedBlobs, {type: 'video/webm'});
    // video.src = window.URL.createObjectURL(superBuffer);
}

// The nested try blocks will be simplified when Chrome 47 moves to Stable
function startRecord() {
    if (canvas == null){
        initRecord();
    }
    let options = {mimeType: 'video/webm',
                   videoBitsPerSecond : 5000000};
    recordedBlobs = [];
    try {
        mediaRecorder = new MediaRecorder(stream, options);
    } catch (e0) {
        console.log('Unable to create MediaRecorder with options Object: ', e0);
        try {
            options = {mimeType: 'video/webm,codecs=vp9'};
            mediaRecorder = new MediaRecorder(stream, options);
        } catch (e1) {
            console.log('Unable to create MediaRecorder with options Object: ', e1);
            try {
                options = 'video/vp8'; // Chrome 47
                mediaRecorder = new MediaRecorder(stream, options);
            } catch (e2) {
                alert('MediaRecorder is not supported by this browser.\n\n' +
                    'Try Firefox 29 or later, or Chrome 47 or later, ' +
                    'with Enable experimental Web Platform features enabled from chrome://flags.');
                console.error('Exception while creating MediaRecorder:', e2);
                return;
            }
        }
    }
    console.log('Created MediaRecorder', mediaRecorder, 'with options', options);
    mediaRecorder.onstop = handleStop;
    mediaRecorder.ondataavailable = handleDataAvailable;
    mediaRecorder.start(100); // collect 100ms of data
    console.log('MediaRecorder started', mediaRecorder);
}

function stopRecord() {    
    mediaRecorder.stop();
    console.log('Recorded Blobs: ', recordedBlobs);
    var blob = new Blob(recordedBlobs, {type: 'video/webm'});

    var fileObject = new File([blob], project_id+'-'+entry_id+'.webm', {
        type: 'video/webm'
    });

    var formData = new FormData();

    // recorded data
    formData.append('video-blob', fileObject);

    // file name
    formData.append('video-filename', fileObject.name);

    formData.append('project_id', project_id);
    formData.append('entry_id', entry_id);
    formData.append('original_name', original_name);

    // upload using jQuery
    $.ajax({
        url: '../../util/record.php',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        type: 'POST',
        success: function() {    
            $.ajax({
                url: '../../util/reg_game.php',
                data: {
                    project_id: project_id,
                    game: original_name
                },
                type: 'POST',
                success: function() {
                    document.location = '../annotation.php?id='+project_id+'&entry='+entry_id;
                }
            });
            //document.location = '../annotation.php?id='+project_id+'&entry='+entry_id;
        }
    });
}