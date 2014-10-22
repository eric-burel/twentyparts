(function ($) {
    $(document).ready(function () {
        $(window).scroll(function () {
            if ($(this).scrollTop() < 40)
                $('.nicescroll-rails').addClass('hide');
            else
                $('.nicescroll-rails').removeClass('hide');
        });
        $("html").niceScroll({styler: "fb", cursorcolor: "#d04040", cursorwidth: '10', cursorborderradius: '10px', background: '#404040', spacebarenabled: false, cursorborder: '', zindex: '99999'});
        $('.nicescroll-rails').addClass('hide');

        //refresh captcha
        $("body").on("click", ".refresh-captcha", function () {
            refreshCaptcha($(this).find(".captach-image"), $(this).attr('href'));
            return false;
        });


        //refresh audio captcha
        $("body").on("click", ".play-captcha", function () {
            $(this).find(".captach-audio").remove();
            if ($.browser.msie) {
                $(this).append('<embed src="' + $(this).attr('href') + '/' + Math.floor(Math.random() * 100) + '" hidden="true" class="captach-audio">').appendTo('body');
            } else {
                $(this).append('<audio src="' + $(this).attr('href') + '/' + Math.floor(Math.random() * 100) + '" hidden="true" autoplay="true" class="captach-audio"></audio>');
            }

            return false;
        });

        // language updater
        $('.updateLanguage').click(function () {
            var language = $(this).attr('id');
            if (language === $('html').attr("lang"))
                return false;
            $.ajax({
                type: 'GET',
                url: urls['language'] + '/' + language,
                dataType: 'json',
                success: function (datas) {
                    if (datas.updated === true)
                        window.location.reload();
                    //window.location.replace(urls['index']);
                }
            });
            return false;
        });


        function refreshCaptcha(img, url) {
            $.ajax({
                type: 'GET',
                url: url,
                dataType: 'json',
                success: function (datas) {
                    if (datas.imageUrl && img !== "undefined")
                        img.attr("src", datas.imageUrl + '/' + Math.floor(Math.random() * 100));
                }
            });
        }
    });
})(jQuery);