(function($) {
    "use strict";
    $.instantorder = {
        options: {},
        init: function(options) {
            var that = this;
            that.options = options;
            this.initInstantorder();

        },
        initInstantorder: function() {
            var that = this;
            $('#instantorder_dialog').appendTo('body');
            $('#instantorder_dialog').append('<form id="instantorder_form"></form>');
            $('#instantorder_dialog > table').appendTo('#instantorder_form');

            $('#instantorder_dialog').dialog({
                draggable: that.options.instantorder_draggable,
                resizable: that.options.instantorder_resizable,
                title: that.options.instantorder_title,
                width: that.options.instantorder_width,
                height: that.options.instantorder_height,
                modal: true,
                autoOpen: false
            });

            $('.instantorder_button').click(function() {
                $('#instantorder_dialog').dialog('open');
                $('#instantorder_dialog').find('input[type=submit]').removeAttr('disabled');
                return false;
            });

            this.initSubmitForm();
            this.initLoadRegions();


        },
        initLoadRegions: function() {
            var that = this;
            if ($('#instantorder_form input[name="fields[address.region]"]')) {
                this.loadRegions();
                $('.select_countries').change(function() {
                    that.loadRegions(that.options.$wa_app_url);
                });
            }
        },
        loadRegions: function($wa_app_url) {
            var that = this;
            if (that.options.$wa_app_url === undefined) {
                that.options.$wa_app_url = $wa_app_url;
            }

            var country = $('.select_countries').val();
            if (!country) {
                return;
            }
            $('.region_block').append('<div id="instantorder-loading"><i class="icon16 loading"></i>Загрузка</div>');
            $.ajax({
                type: 'POST',
                url: that.options.$wa_app_url + 'instantorder/regions/',
                dataType: 'json',
                data: {
                    'country': country
                },
                success: function(data, textStatus, jqXHR) {
                    if (data.status == 'ok') {
                        if (Object.keys(data.data.options).length) {
                            $('.region_block select.select_region').html('<option value="">Выберите регион</option>');
                            var key = null;
                            for (key in data.data.options) {
                                $('.region_block select.select_region').append('<option value="' + key + '">' + data.data.options[key] + '</option>');
                            }
                            $(".region_block input").prop('disabled', true);
                            $('.region_block input').hide();
                            $(".region_block select.select_region").prop('disabled', false);
                            $('.region_block select.select_region').show();
                        } else {
                            $(".region_block select.select_region").prop('disabled', true);
                            $('.region_block select.select_region').hide();
                            $(".region_block input").prop('disabled', false);
                            $('.region_block input').show();
                        }

                    }
                    $('#instantorder-loading').remove();
                }
            });
        },
        initSubmitForm: function() {
            var that = this;
            $('#instantorder_form').submit(function() {
                var form = $(this);
                var required = false;
                $(this).find('.required_field').each(function() {
                    if ($(this).prop('disabled') == false && !$(this).val().length) {
                        required = true;
                        $(this).css('border', '2px solid red');
                    }
                });
                if (required) {
                    $('.response').html('Заполните обязательные поля');
                    $('.response').css('color', 'red');
                    $('.response').show();

                    setTimeout(function() {
                        $('.required_field').css('border', '');
                        $('.response').hide();
                    }, 5000);
                } else {
                    form.find('input[type=submit]').attr('disabled', true);
                    form.find('input[type=submit]').after('<i class="icon16 loading"></i>');
                    $.ajax({
                        type: 'POST',
                        url: that.options.$wa_app_url + 'instantorder/',
                        dataType: 'json',
                        data: $(this).serialize() + '&' + $('#cart-form').serialize(),
                        success: function(data, textStatus, jqXHR) {
                            form.find('.loading').remove();
                            if (data.status == 'ok') {
                                $('.response').css('color', 'green');
                                $('.response').html(data.data.message);
                            } else {
                                form.find('input[type=submit]').removeAttr('disabled');
                                $('.response').css('color', 'red');
                                $('.response').html(data.errors);
                                setTimeout(function() {
                                    $('.response').hide();
                                }, 10000);
                            }
                            $('.response').show();
                        }
                    });
                }
                return false;
            });
        }
    };
})(jQuery);
