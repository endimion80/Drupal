+function ($) {
    var votingSeting = function () {
        if (Drupal.settings.indra_universidad_voting) {
            $.each(Drupal.settings.indra_universidad_voting, function (index, item) {
                var $voting = $('#' + item);
                if ($voting.length) {
                    $voting.removeClass('uni-voting');
                    $voting.addClass('no-uni-voting');
                }
            });
        }
    };
    var votingAjax = function () {
        $(".uni-voting").click(function (e) {
            e.preventDefault();
            var id = $(this).attr("id");
            var num = $(this).html();
            var dataString = 'nid=' + id + '&num=' + num;
            var parent = $(this);
            $(this).fadeOut(300);
            var baseurl = Drupal.settings.basePath;
            var url = baseurl + "universidad/voting/ajax";
            $.ajax({
                type: "POST",
                url: url,
                data: dataString,
                cache: false,
                success: function (text) {
                    if ('' != text) {
                        parent.unbind("click");
                        parent.html(text);
                        parent.removeClass('uni-voting');
                        parent.addClass('no-uni-voting');
                        parent.fadeIn(200);
                    }
                },
            });
            return false;
        });

    };

    var votingFullHeart = function () {
        $('div.voting').each(function () {
            var amount = $(this).html();
            if (100 < amount) {
                $(this).addClass('full-heart');
            }
        });
    }

    var limitTextarea = function () {
        var max_chars = 280;
        $('#max').html(max_chars);
        $('.page-aprendernoshacemejores-participa #edit-testimony').keyup(function () {
            var chars = $(this).val().length;
            var diff = max_chars - chars;
            $('#textarea-lenght-contador').html(diff);
        });
    }

    var universidadHiddenItaliano = function () {
        $(".teaser-universidad-header #hlang a[href^='/it/']").css('display', 'none');
    }
    var universidadLogoHome = function () {
        var $logo = $('body header.teaser-universidad-header .top-navbar .container .navbar-header a.navbar-brand');
        if (Drupal.settings.hasOwnProperty("pathPrefix")) {
            $logo.attr('href', '\/' + Drupal.settings.pathPrefix + 'aprendernoshacemejores');
        }
    }

    Drupal.behaviors.universidadVotingSeting = {
        attach: function (context, settings) {
            votingSeting();
            votingAjax();
            votingFullHeart();
            limitTextarea();
            universidadHiddenItaliano();
            universidadLogoHome();
        }
    };
}(jQuery);


