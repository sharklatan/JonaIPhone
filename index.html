
<html><head><title>Jona IPhone REPO</title>
    <link rel="stylesheet" type="text/css" href="https://sharklatan.github.io/repo/depictions/css/menes.css">
    <link rel="stylesheet" type="text/css" href="https://sharklatan.github.io/repo/depictions/css/style.css">
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">
    <style>
        div.input{height:43px!important}
        div.input div{margin-right:10px!important}
        div.input input{width:200px!important;height:43px!important;background-color:transparent!important;margin-left:15px;padding-right:5px!important;margin-top:-15px!important;display:inline}
        div.input strong{width:auto;font-size:17px;display:inline}
        div.important{background:#FEE;-moz-border-radius-bottomleft:9px;-webkit-border-bottom-left-radius:9px;-moz-border-radius-bottomright:9px;-webkit-border-bottom-right-radius:9px;background-clip:padding-box}
    </style>
</head><body class="pinstripe modern">
<panel>

    <label>Setup Instructions</label>
    <fieldset>
        <a href="cydia://url/https://cydia.saurik.com/api/share#?source=https://jonaiphonerepo.herokuapp.com/"><img class="icon" src="https://cydia.saurik.com/icon/cydia.png"><div><label>Add Source to Cydia</label></div></a>
        <div style="background:#FEE">
            <p>This repository contains beta software.</p>
        </div>
    </fieldset>

    <fieldset>
        <a href="https://www.paypal.me/jonaiphone">
            <img class="icon" src="https://cache.saurik.com/cydia/icon/paypal.gif"><div>
                <label>Donate with PayPal JonaIPhone</label>
            </div></a>
    </fieldset>
    
        <fieldset>
        <a href="https://www.paypal.me/sharklatan">
            <img class="icon" src="https://cache.saurik.com/cydia/icon/paypal.gif"><div>
                <label>Donate with PayPal Shark-Design</label>
            </div></a>
    </fieldset>
    
        <fieldset>
<div id="scrolly" style="width: 665px; height: 310px;">
  <link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
  <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
    
<style>
#player {
    width: 660px;
    height: 300px;
    overflow: hidden;
    background: none;
    position: absolute;
    border: none;
}

.youtube .carousel {
    width: 20%;
    height: 100%;
    overflow: auto;
    position: absolute;
    right: 0px;
    z-index: 3;
}

.youtube .thumbnail {
    margin: 2px 0 0 10px;
    width: 80%;
    border: 1px solid black;
    cursor: pointer;
}

.youtube iframe.player {
    width: 80%;
    height: 300px;  
    overflow: auto;
    border: 0;
}
</style>

       <div id="player">
        </div>

        <script>
            (function() {
    function createPlayer(jqe, video, options) {
        var ifr = $('iframe', jqe);
        if (ifr.length === 0) {
            ifr = $('<iframe scrolling="no">');
            ifr.addClass('player');
        }
        var src = 'http://www.youtube.com/embed/' + video.id;
        if (options.playopts) {
            src += '?';
            for (var k in options.playopts) {
                src+= k + '=' + options.playopts[k] + '&';
            }  
            src += '_a=b';
        }
        ifr.attr('src', src);
        jqe.append(ifr);  
    }
    
    function createCarousel(jqe, videos, options) {
        var car = $('div.carousel', jqe);
        if (car.length === 0) {
            car = $('<div>');
            car.addClass('carousel');
            jqe.append(car);
            
        }
        $.each(videos, function(i,video) {
            options.thumbnail(car, video, options); 
        });
    }
    
    function createThumbnail(jqe, video, options) {
        var imgurl = video.thumbnails[0].url;
        var img = $('img[src="' + imgurl + '"]');
        if (img.length !== 0) return;
        img = $('<img>');    
        img.addClass('thumbnail');
        jqe.append(img);
        img.attr('src', imgurl);
        img.attr('title', video.title);
        img.click(function() {
            options.player(options.maindiv, video, $.extend(true,{},options,{playopts:{autoplay:1}}));
        });
    }
    
    var defoptions = {
        autoplay: false,
        user: null,
        carousel: createCarousel,
        player: createPlayer,
        thumbnail: createThumbnail,
        loaded: function() {},
        playopts: {
            autoplay: 0,
            egm: 1,
            autohide: 1,
            fs: 1,
            showinfo: 0
        }
    };
    
    
    $.fn.extend({
        youTubeChannel: function(options) {
            var md = $(this);
            md.addClass('youtube');
            md.addClass('youtube-channel');
            var allopts = $.extend(true, {}, defoptions, options);
            allopts.maindiv = md;
            $.getJSON('http://gdata.youtube.com/feeds/users/' + allopts.user + '/uploads?alt=json-in-script&format=5&callback=?', null, function(data) {
                var feed = data.feed;
                var videos = [];
                $.each(feed.entry, function(i, entry) {
                    var video = {
                        title: entry.title.$t,
                        id: entry.id.$t.match('[^/]*$'),
                        thumbnails: entry.media$group.media$thumbnail
                    };
                    videos.push(video);
                });
                allopts.allvideos = videos;
                allopts.carousel(md, videos, allopts);
                allopts.player(md, videos[0], allopts);
                allopts.loaded(videos, allopts);
            });
        } 
    });
    
})();
        
$(function() {
    $('#player').youTubeChannel({user:'JonaiPhone'});
});
        </script>
</div>
    </fieldset>
</panel>


</body></html>
