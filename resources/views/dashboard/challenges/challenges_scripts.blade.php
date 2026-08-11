<script>

    function showChallenge(data) {
        $('.challenge-media video').each(function () {
            this.pause();
        });
        $('#challenge-popup .story-wrap').html(data);
        $.magnificPopup.open({
            items: {
                src: $('#challenge-popup')
            },
            type:'inline',
            midClick: true,
            callbacks: {
                open: function() {
                    var thVideo = this.content.find('video')
                    if(thVideo.length) {
                        thVideo[0].play();
                    }
                },
                close: function() {
                    var thVideo = this.content.find('video')
                    if(thVideo.length) {
                        thVideo[0].pause()
                        thVideo[0].currentTime = 0
                    }
                }
            }
        });
    }
</script>
