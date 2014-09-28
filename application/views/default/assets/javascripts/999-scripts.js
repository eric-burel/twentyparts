(function ($) {
    $(document).ready(function () {
        validateForms();

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

        /* SUBMIT CONTACT */
        $("body").on("submit", "#contacts_form", function () {
            resetForm('contacts');
            var captchaImg = $(this).find(".captach-image");
            var captchaUrl = $(this).find(".refresh-captcha").attr('href');
            $.ajax({
                type: 'POST',
                url: $(this).attr('action'),
                data: {
                    name: $('#contacts_form_name').val(),
                    mail: $('#contacts_form_mail').val(),
                    subject: $('#contacts_form_subject').val(),
                    message: $('#contacts_form_message').val(),
                    captcha: $('#contacts_form_captcha').val(),
                    token: $('#contacts_form_token').val()
                },
                dataType: "json",
                success: function (datas) {
                    if (typeof (datas.notifyError) !== "undefined" && datas.notifyError !== null) {
                        //show errors
                        if (typeof (datas.errors) !== "undefined" && datas.errors !== null) {
                            for (key in datas.errors)
                                $('#contacts_form_' + key).after('<label class="error" for="' + key + '">' + datas.errors[key] + '</label>');
                        }
                        //notify error
                        $('#contacts_form_error_box').append(datas.notifyError.heading).removeClass('hide').delay(5000).queue(function () {
                            resetForm('contacts');
                            $(this).dequeue();
                        });
                    } else {
                        //notify sucess
                        $('#contacts_form_success_box').append(datas.notifySuccess.heading).removeClass('hide').delay(5000).queue(function () {
                            resetForm('contacts');
                            $(this).dequeue();
                        });
                    }
                    //validate plugin
                    $("#contacts_form").validate();
                    //re-assign securities
                    $('input#contacts_form_token').val(datas.token);
                    refreshCaptcha(captchaImg, captchaUrl);
                }
            });
            return false;
        });

        function resetForm(name) {
            $('#' + name + '_form_error_box').empty().addClass('hide');
            $('#' + name + '_form').find('.error').each(function () {
                this.remove();
            });
            $('#' + name + '_form_success_box').empty().addClass('hide');
        }


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
        function resetLastError() {
            $('#success').html("").hide();
            $('#error').html("").hide();
            $("body").find('.error').each(function () {
                this.remove();
            });
        }
        function validateForms() {
            if ($('html').attr("lang") === 'fr_FR') {
                $.extend($.validator.messages, {
                    required: langs['validate_required'],
                    email: langs['validate_email'],
                    maxlength: $.validator.format(langs['validate_maxlenght'] + " {0} " + langs['validate_chars']),
                    minlength: $.validator.format(langs['validate_minlenght'] + " {0} " + langs['validate_chars'])
                });
            }
            $("#contacts_form").validate({
                rules: {
                    "name": {
                        "required": true,
                        "minlength": 2,
                        "maxlength": 100
                    },
                    "email": {
                        "email": true,
                        "maxlength": 100
                    },
                    "subject": {
                        "required": true,
                        "minlength": 2,
                        "maxlength": 100
                    },
                    "message": {
                        "required": true,
                        "minlength": 2,
                        "maxlength": 255
                    },
                    "captcha": {
                        "required": true,
                    }
                }}
            );
            $("#contacts_form").validate();

        }
    });
})(jQuery);