$(document).ready(function() {
    $(document).on('show.bs.modal', '#audiobox', function(e) {
        var song_id = $(e.relatedTarget).attr('data-id');
        var audio_element = $(this).find('audio');
        $.ajax({
            type : "GET",
            url : "/songs/getSongInfo",
            data : {
                'id' : song_id
            },
            success : function(data)
            {
                if(data.success)
                {
                    playSongFunctions.setSongDetails(data, audio_element);
                }
                else
                {
                    e.preventDefault();
                    alert('Error playing the song. Please try again');
                }
            }
        });
    });

    $(document).on('hide.bs.modal', '#audiobox', function(e) {
        playSongFunctions.resetSongDetails($(this).find('audio'))
    });
});

var playSongFunctions = {
    setSongDetails : function(data, audio_element)
    {
        var player_wrap = audio_element.closest('.song-details');
        player_wrap.find('.jsSongTitle').text(data.title);
        player_wrap.find('img').attr('src', data.image_src);
        player_wrap.find('.jsSingers').text(data.singers.join(', '));
        player_wrap.find('.jsComposers').text(data.composers.join(', '));
        player_wrap.find('.jsLyricists').text(data.lyricists.join(', '));
        audio_element.attr('src', data.audio_src);
        playSongFunctions.startSong(audio_element[0]);

    },

    resetSongDetails : function(audio_element)
    {
        audio_element.attr("src", '');
        var player_wrap = audio_element.closest('.song-details');
        player_wrap.find('.jsSongTitle').text('');
        player_wrap.find('img').attr('src', '');
    },

    startSong : function(audio_element)
    {
        audio_element.play();
    }
};