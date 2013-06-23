// Ajax
function ScriptRequest(url, callback) {
    ScriptRequest.handler = function(data) {
        callback(data);
    }
    
    url += (url.indexOf('?') > -1 ? '&' : '?') + 'callback=ScriptRequest.handler';
    var script = document.createElement('script');
    script.onload = script.onerror = function() {
        document.body.removeChild(script);
    }
    
    document.body.appendChild(script);
    script.src = url;
}

function play_audio(aid) {
    if (typeof play_audio.now != 'undefined') {
        if (play_audio.now == aid) return;
        
        var el = document.querySelector('[data-id="'+play_audio.now+'"] audio');
        el.pause();
        el.currentTime = 0;
        el.style.display = "none";
        el.parentElement.style.backgroundColor = "#fff";
    }
    
    var audio = document.querySelector('[data-id="'+aid+'"] audio')
    if (audio != null) {
        audio.style.display = "block";
        audio.play();
    } else {
        var url = 'https://api.vk.com//method/audio.getById.json?audios='+aid+'&access_token='+vk_access_token;
        ScriptRequest(url, function(data) {
            audio = document.createElement('audio');
            audio.controls = audio.autoplay = true;
            document.querySelector('[data-id="'+aid+'"]').appendChild(audio);
            audio.setAttribute('src', data.response[0].url);
            
            audio.addEventListener('ended', function() {
                var el = document.querySelector('[data-id="'+aid+'"]').nextElementSibling;
                if (el != null) {
                    play_audio(el.getAttribute('data-id'));
                }
            }, true);
        });
    }
    document.querySelector('[data-id="'+aid+'"]').style.backgroundColor = "#efefef";
    play_audio.now = aid;
}

// Set events to play link
window.onload = function() {
    var links = document.querySelectorAll('.search-result a.play');
    for (var i = 0, il = links.length; i < il; ++i) {
        links[i].addEventListener('click', function(event) {
            play_audio((this.parentElement).getAttribute('data-id'));
            event.preventDefault();
        }, true);
    }
}