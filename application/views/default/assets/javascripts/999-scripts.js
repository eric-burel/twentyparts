(function($) {
    $(document).ready(function() {
        //refresh captcha
        $("body").on("click", ".refresh-captcha", function() {
            refreshCaptcha($(this).find(".captach-image"), $(this).attr('href'));
            return false;
        });
        function refreshCaptcha(img, url) {
            $.ajax({
                type: 'GET',
                url: url,
                dataType: 'json',
                success: function(datas) {
                    if (datas.imageUrl && img !== "undefined")
                        img.attr("src", datas.imageUrl + '/' + Math.floor(Math.random() * 100));
                }
            });
        }

        //refresh audio captcha
        $("body").on("click", ".play-captcha", function() {
            $(this).find(".captach-audio").remove();
            if ($.browser.msie) {
                $(this).append('<embed src="' + $(this).attr('href') + '/' + Math.floor(Math.random() * 100) + '" hidden="true" class="captach-audio">').appendTo('body');
            } else {
                $(this).append('<audio src="' + $(this).attr('href') + '/' + Math.floor(Math.random() * 100) + '" hidden="true" autoplay="true" class="captach-audio"></audio>');
            }

            return false;
        });

        // language updater
        $('.updateLanguage').click(function() {
            var language = $(this).attr('id');
            if (language === $('html').attr("lang"))
                return false;
            $.ajax({
                type: 'GET',
                url: urls['language'] + '/' + language,
                dataType: 'json',
                success: function(datas) {
                    if (datas.updated === true)
                        window.location.replace(urls['index']);
                }
            });
            return false;
        });
    });
})(jQuery);