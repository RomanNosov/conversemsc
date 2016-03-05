/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */
$.flexdiscount = {
    masks: {},
    init: function (params) {
//        this.contactCategories = params.contactCategories || {};
//        this.coupons = params.coupons || {};
        this.masks = params.masks || {};
        // Активируем / деактивируем плагин
        var current_type = 'flexdiscount';
        // IButton
        $('#s-discount-type-status').iButton({labelOn: "", labelOff: "", className: 'mini'}).change(function () {
            var self = $(this);
            var enabled = self.is(':checked');
            if (enabled) {
                self.closest('.field-group').siblings().show(200);
                $('#discount-types a[rel="' + current_type + '"] i.icon16').attr('class', 'icon16 status-blue-tiny');
            } else {
                self.closest('.field-group').siblings().hide(200);
                $('#discount-types a[rel="' + current_type + '"] i.icon16').attr('class', 'icon16 status-gray-tiny');
            }
            $.post('?module=settings&action=discountsEnable', {type: current_type, enable: enabled ? '1' : '0'});
        });
        $('.switcher').iButton({labelOn: "", labelOff: "", className: 'mini'}).change(function () {
            var onLabelSelector = '#' + this.id + '-on-label',
                    offLabelSelector = '#' + this.id + '-off-label';
            var additinalField = $(this).closest('.ibutton-checkbox').next('.onopen');
            if (!this.checked) {
                if (additinalField.length) {
                    additinalField.hide();
                }
                $(onLabelSelector).addClass('unselected');
                $(offLabelSelector).removeClass('unselected');
            } else {
                if (additinalField.length) {
                    additinalField.css('display', 'inline-block');
                }
                $(onLabelSelector).removeClass('unselected');
                $(offLabelSelector).addClass('unselected');
            }
        }).each(function () {
            var additinalField = $(this).closest('.ibutton-checkbox').next('.onopen');
            if (!this.checked) {
                if (additinalField.length) {
                    additinalField.hide();
                }
            } else {
                if (additinalField.length) {
                    additinalField.css('display', 'inline-block');
                }
            }
        });

        // Полет экрана к инструкции 
        $(".go-top").click(function () {
            $('html, body').animate({
                scrollTop: 0
            }, 800);
            if ($("#flexdiscount-instruction").hasClass("hidden")) {
                $(".f-instruction").click();
            }
            return false;
        });

        // Отправка формы
        var form = $('#s-discountcount-form');
        form.submit(function () {
            form.find(':submit').attr('disabled', true).after("<i class='icon16 loading'></i>");
            $.post(form.attr('action'), form.serialize(), function (r) {
                $('#s-discounts-content').html(r);
            });
            return false;
        });
        // Инициализация масок
        $.each($(".mask"), function (i, v) {
            $.flexdiscount.initMask($(this));
        });
        // Переинициализация масок при смене какой-либо
        form.on('change', '.mask, .contact-mask', function () {
            $.flexdiscount.initMask($(this));
        });

        var table = $("#discount-table");
        // Удаление правила скидок
        table.on('click', 'i.delete', function () {
            var tr = $(this).closest('tr');
            tr.next(".table-inside").remove();
            tr.remove();
            if (table.find('tbody tr.rate-row:not(.template)').length <= 0) {
                table.find('tbody tr.hint').show();
            }
        });
        // Добавление нового правила скидок
        table.on('click', 'tfoot a', function () {
            var tmpl = table.find('tr.template');
            var tmplAdvanced = table.find('tr.template-advanced');
            var clone = tmpl.clone();
            var cloneAdvanced = tmplAdvanced.clone();
            var select = clone.find(".mask");
            $.flexdiscount.initMask(select);
            clone.removeClass('template').removeClass('hidden').insertBefore(tmpl);
            cloneAdvanced.removeClass('template-advanced').removeClass('hidden').insertAfter(clone);

            tmpl.siblings('tr.hint').hide();
        });
        // Появление / исчезание подсказок для масок
        table.on({
            mouseover: function () {
                var btn = $(this);
                var mask = btn.parent().next().find(".mask").val();
                btn.next().find('div[data-mask="' + mask + '"]').show();
            },
            mouseout: function () {
                $(this).next().children().hide();
            }
        }, "i.info");
        // Сортировка скидок 
        var upTimeout;
        table.on('click', '.f-up', function () {
            var tr = $(this).closest("tr");
            var sortme = tr.add(tr.next(".table-inside"));
            var trBefore = tr.prev(".table-inside");
            if (trBefore.length) {
                clearTimeout(upTimeout);
                sortme.addClass("selected").insertBefore(trBefore.prev(".sortme"));
                upTimeout = setTimeout(function () {
                    sortme.removeClass("selected");
                }, 1000);
            }
        });
        var downTimeout;
        table.on('click', '.f-down', function () {
            var tr = $(this).closest("tr");
            var hiddenTr = tr.next(".table-inside");
            var sortme = tr.add(hiddenTr);
            var trAfter = hiddenTr.next(".sortme").not(".hidden");
            if (trAfter.length) {
                clearTimeout(downTimeout);
                sortme.addClass("selected").insertAfter(trAfter.next(".table-inside"));
                downTimeout = setTimeout(function () {
                    sortme.removeClass("selected");
                }, 1000);
            }
        });

        // Подгрузка скидок
        $(".f-more-discounts").click(function () {
            var that = $(this);
            var page = that.attr("data-page");
            that.hide().after("<i class='icon16 loading'></i>");
            $.post('?plugin=flexdiscount&module=settings&action=moreDiscounts', {page: that.attr("data-page")}, function (response) {
                that.show().next("i.icon16").remove();
                if (response.status == 'ok' && response.data.discounts) {
                    that.attr("data-page", parseInt(page) + 1);
                    table.find(".template").before($.flexdiscount.buildDiscountRows(response.data));
                    if (response.data.end) {
                        that.remove();
                    }
                } else {
                    that.remove();
                }
            }, "json");
        });

        var tableCoupon = $("#coupon-table");
        // Удаление купона
        tableCoupon.on('click', 'i.delete', function () {
            var btn = $(this);
            var couponId = btn.parents("tr").attr("rel");
            $("<div id='delete-coupon-dialog'></div>").waDialog({
                disableButtonsOnSubmit: true,
                width: '350px',
                height: '200px',
                url: '?plugin=flexdiscount&module=coupons&action=prepareDelete&coupon=' + couponId,
                buttons: '<input type="submit" value="Удалить" class="button red"><i class="icon16 loading"></i> или <a href="#" class="cancel">отменить</a>',
                onLoad: function () {
                    $(this).find('.dialog-buttons i.loading').hide();
                },
                onCancel: function () {
                    $(this).find(".dialog-content-indent").html('');
                },
                onSubmit: function (d) {
                    var form = d.find("form");
                    var submitButton = form.find("input[type='submit']");
                    submitButton.next().show();
                    $.post("?plugin=flexdiscount&module=coupons&action=delete", {coupon: couponId}, function (response) {
                        if (typeof response.errors != 'undefined') {
                            if (typeof response.errors.messages != 'undefined') {
                                $.each(response.errors.messages, function (i, v) {
                                    form.find(".errormsg").empty().append(v + "<br />");
                                });
                            }
                            submitButton.removeAttr('disabled');
                            form.find(".loading").hide();
                        } else if (response.status == 'ok') {
                            submitButton.after("<i class='icon16 yes'></i>");
                            location.reload();
                        }
                    }, "json");
                    return false;
                }
            });
        });
        // Создание купона
        tableCoupon.on('click', 'tfoot a', function () {
            var tmpl = tableCoupon.find('tr.template');
            var clone = tmpl.clone();

            clone.find(".coupon-code").val($.flexdiscount.generateCode());
            $.flexdiscount.initColorIcon(clone.find(".color-icon"));
            clone.removeClass('template').removeClass('hidden').insertBefore(tmpl);
            tmpl.siblings('tr.hint').hide();
            $.flexdiscount.datepicker.add(clone.find(".datepicker-input"));
        });
        this.datepicker.init();

        // Инициализируем выбор цвета у купонов
        $.each($(".color-icon"), function () {
            $.flexdiscount.initColorIcon($(this));
        });

        // Появление поля для выбора купона для правила скидок
        $(document).on('click', '.coupon-discount-edit', function () {
            $(this).next().show();
        });

        // Изменения купона у скидки
        $(document).on('change', '.coupon-discount', function () {
            var btn = $(this);
            var value = btn.val();
            var option = btn.find("option:selected");
            var color = option.attr("rel");
            var code = option.text();
            var td = btn.parents("td");
            if (value !== '0') {
                btn.hide();
                td.find(".coupon-discount-value").val(value);
                td.find(".code").text(code);
                var i = td.find(".coupon-discount-edit i");
                if (!i.hasClass("coupon-edit")) {
                    i.removeClass("coupon-add").addClass("coupon-edit");
                }
                var couponIcon = td.find(".f-coupon");
                if (couponIcon.hasClass("coupon-off")) {
                    couponIcon.removeClass("coupon-off").addClass("coupon-hameleon");
                }
                couponIcon.css('backgroundColor', '#' + color);
                option.prop('selected', false);
                td.find(".coupon-delete").show();
                td.find(".coupon-desk").hide();
            }
        });

        // Удаление купона у скидки
        $(document).on('click', '.coupon-delete', function () {
            var btn = $(this);
            var td = btn.parents("td");
            var couponIcon = td.find(".f-coupon");
            var i = td.find(".coupon-discount-edit i");

            td.find(".coupon-discount").hide();
            td.find(".coupon-discount-value").val('0');
            td.find(".code").text('Купон отсутствует');
            i.removeClass("coupon-edit").addClass("coupon-add");
            couponIcon.removeClass("coupon-hameleon").addClass("coupon-off");
            couponIcon.css('backgroundColor', '');
            btn.hide();
            td.find(".coupon-desk").hide();
        });

        // Очищаем html страницу от модуля ColorPicker
        $(".sidebar a").click(function () {
            $(".colorpicker, #ui-datepicker-div").remove();
        });

        // Показать / скрыть инструкцию
        $(".f-instruction").click(function () {
            var instruction = $("#flexdiscount-instruction");
            if (instruction.hasClass("hidden")) {
                $(this).find("b").text("Скрыть инструкцию");
                instruction.removeClass("hidden");
            } else {
                $(this).find("b").text("Показать инструкцию");
                instruction.addClass("hidden");
            }
            return false;
        });
        // Открытие / закрытие вопроса
        $(".flexdiscount-question").click(function () {
            var a = $(this).find("a");
            if (!a.hasClass("open")) {
                a.find("i").removeClass("rarr").addClass("darr");
                a.addClass("open");
                $(this).next().slideDown();
            } else {
                a.find("i").removeClass("darr").addClass("rarr");
                a.removeClass("open");
                $(this).next().slideUp();
            }
            return false;
        });
    },
//    buildDiscountRows: function(data) {
//        var html = '';
//        if (data) {
//
//            $.each(data, function(i, v) {
//                html += '<tr class="sortme ' + (v.mask == '-' ? 'deny' : '') + '" rel="' + v.id + '" ' + (typeof v.coupon !== 'undefined' && typeof v.coupon.code !== 'undefined' ? 'data-coupon-code="' + v.coupon.code + '"' : '') + '>' +
//                        '<td>' +
//                        '<a href="javascript:void(0)" class="f-up inline" title="вверх"><i class="icon16 upload"></i></a>' +
//                        '<a href="javascript:void(0)" class="f-down inline" title="вниз"><i class="icon16 download"></i></a>' +
//                        '</td>' +
//                        '<td>' +
//                        '<div style="position: relative;">' +
//                        (v.block ? '<i class="icon16-custom exclamation top-left" title="Срок истек"></i>' : '') +
//                        '<div class="coupon-desk">' +
//                        '<a href="javascript:void(0)" onclick="$(this).parents(\".coupon-desk\").hide();" class="close-desk"><i class="icon16-custom close"></i></a>' +
//                        '<div class="code">' + (typeof v.coupon !== 'undefined' && typeof v.coupon.code !== 'undefined' ? v.coupon.code : 'Купон отсутствует') + '</div>' +
//                        '<ul class="menu-v">' +
//                        '<li>' +
//                        '<a href="javascript:void(0)" class="coupon-discount-edit">' +
//                        (v.coupon_id ? '<i class="icon16-custom coupon-edit"></i> Изменить купон' : '<i class="icon16-custom coupon-add"></i> Присвоить купон') + '</a>';
//                if ($.flexdiscount.coupons) {
//                    html += '<select class="coupon-discount small">' +
//                            '<option value="0">--- Выберите купон ---</option>';
//                    $.each($.flexdiscount.coupons, function(ii, c) {
//                        html += '<option value="' + c.id + '" ' + (c.id == v.coupon_id ? 'selected="selected"' : '') + ' rel="' + c.color + '">' + $(c.code).html() + '</option>';
//                    });
//                    html += '</select>';
//                } else {
//                    html += '<div class="hint hidden">Создайте купон, чтобы его присвоить</div>';
//                }
//                html += '</li>' +
//                        '<li class="coupon-delete" ' + (v.coupon_id == '0' ? 'style="display: none;"' : '') + '><a href="javascript:void(0)"><i class="icon16-custom coupon-delete"></i> Удалить купон</a></li>' +
//                        '</ul>' +
//                        '</div>' +
//                        '</div>' +
//                        '<a onclick="$.flexdiscount.couponDesk(this);" title="Управление купоном" href="javascript:void(0)">' +
//                        '<i class="icon16-custom f-coupon coupon' + (v.coupon_id == '0' ? '-off' : '-hameleon') + '" ' + (v.coupon_id > 0 && typeof $.flexdiscount.coupons[v.coupon_id] !== 'undefined' ? 'style="background-color: #' + $.flexdiscount.coupons[v.coupon_id]["color"] + '"' : '') + '></i>' +
//                        '</a>' +
//                        '<input type="hidden" name="coupon_id[]" class="coupon-discount-value" value="' + v.coupon_id + '" />' +
//                        '</td>' +
//                        '<td>' +
//                        '<a onclick="$.flexdiscount.advancedSettings(this);" title="Дополнительные настройки" href="javascript:void(0)">' +
//                        '<i class="icon16 settings"></i>' +
//                        '</a>' +
//                        '</td>' +
//                        '<td>' +
//                        '<i class="icon16 info"></i>' +
//                        '<div style="position: relative">';
//                $.each($(".mask-description"), function(i, m) {
//                    html += $(m).prop('outerHTML');
//                });
//                html += '</div>' +
//                        '</td>' +
//                        '<td>' +
//                        '<select name="mask[]" class="mask">';
//                $.each($.flexdiscount.mask, function(i, mask) {
//                    html += '<option value="' + i + '" ' + (v.mask == i ? 'selected="selected"' : '') + ' data-pattern="' + mask.pattern + '" data-replacement="' + mask.replacement + '">' + i + '</option>';
//                });
//                if ($.flexdiscount.contactCategories) {
//                    html += '<optgroup label="Категория контакта">';
//                    $.each($.flexdiscount.contactCategories, function(contactMask, c) {
//                        html += '<option value="' + contactMask + '" ' + (v.mask == contactMask ? 'selected="selected"' : '') + ' data-pattern=".*" data-replacement="-">' + c.name + '</option>';
//                    });
//                    html += '</optgroup>';
//                }
//                html += '</select>' +
//                        '</td>' +
//                        '<td><input type="text" name="value[]" value="' + v.value + '" class="value-field width70px"></td>' +
//                        '<td><input type="text" name="discount_percentage[]" value="' + v.discount_percentage + '" class="width70px"></td>' +
//                        '<td><input type="text" name="discount[]" value="' + v.discount + '" class="width70px"></td>' +
//                        '<td><input type="text" name="affiliate[]" value="' + v.affiliate + '" class="width70px"></td>' +
//                        '<td>';
//                var categories = $(".products-categories");
//                if (categories.length) {
//                    var firstCat = categories.first().clone();
//                    firstCat.find(":selected").removeAttr("selected");
//                    firstCat.find("option[value='" + v.category_id + "']").addClass("selected");
//                    html += firstCat.prop('outerHTML');
//                }
//                html += '</td>' +
//                        '<td>';
//                var types = $(".products-types");
//                if (types.length) {
//                    var firstType = types.first().clone();
//                    firstType.find(":selected").removeAttr("selected");
//                    firstType.find("option[value='" + v.type_id + "']").addClass("selected");
//                    html += firstType.prop('outerHTML');
//                }
//                html += '</td>' +
//                        '<td><a href="javascript:void(0)"><i class="icon16 delete"></i></a></td>' +
//                        '</tr>' +
//                        '<tr class="table-inside">' +
//                        '<td colspan="11" class="no-padded">' +
//                        '<div class="f-advanced">' +
//                        '<div class="fields clearfix" style="padding: 10px 0; margin: 0; float: none;">' +
//                        '<div class="field">' +
//                        '<div class="name">Название скидки</div>' +
//                        '<div class="value">' +
//                        '<textarea maxlength="200" class="width400px" name="name[]">' + (v.name ? $(v.name).html() : '') + '</textarea>' +
//                        '<div class="hint">Максимальное количество символов - 200</div>' +
//                        '</div>' +
//                        '</div>' +
//                        '<div class="field">' +
//                        '<div class="name">Дата истечения</div>' +
//                        '<div class="value">' +
//                        '<input type="text" style="float: left;" name="expire_datetime[]" value="' + (v.expire_datetime ? v.expire_datetime.substr(0, 10) : '') + '" class="datepicker-input-advanced width70px" />' +
//                        '<a href="javascript:void(0)"><i class="icon16 calendar"></i></a>' +
//                        '</div>' +
//                        '</div>' +
//                        '<div class="field">' +
//                        '<div class="name">Символьный код</div>' +
//                        '<div class="value">' +
//                        '<input type="text" name="code[]" onkeypress="$.flexdiscount.isValid(event, /[a-zA-Z0-9\-_]/);" value="' + (v.code ? $(v.code).html() : '') + '" maxlength="50" />' +
//                        '<div class="hint">Необходим для распознавания правила скидок при выводе в шаблоне. <br>Допустимые символы <b>a-z A-Z 0-9 - _</b></div>' +
//                        '</div>' +
//                        '</div>' +
//                        '</div>' +
//                        '</div>' +
//                        '</td>' +
//                        '</tr>';
//            });
//
//        }
//        return html;
//    },
    initColorIcon: function (btn) {
        btn = $(btn);
        var couponColor = btn.next(".coupon-color");
        btn.ColorPicker({
            color: couponColor.val(),
            onShow: function (colpkr) {
                $(colpkr).fadeIn(500);
                return false;
            },
            onHide: function (colpkr) {
                $(colpkr).fadeOut(500);
                return false;
            },
            onChange: function (hsb, hex, rgb) {
                btn.find("i").css('backgroundColor', '#' + hex);
                couponColor.val(hex);
                couponColor.ColorPickerHide();
            }
        });
    },
    // Datepicker
    datepicker: {
        init: function () {
            $.each($(".datepicker-input").not(".template"), function (i, v) {
                $.flexdiscount.datepicker.add(v);
            });
        },
        add: function (elem) {
            if (!(elem instanceof jQuery)) {
                elem = $(elem);
            }
            var datetime_input = elem;
            datetime_input.datepicker({
                'dateFormat': 'yy-mm-dd',
                beforeShow: function () {
                    setTimeout(function () {
                        $('.ui-datepicker').css('z-index', 1000);
                    }, 0);
                }
            });
            datetime_input.next('a').click(function () {
                elem.datepicker('show');
            });
            // widget appears in bottom left corner for some reason, so we hide it
            datetime_input.datepicker('widget').hide();
        }
    },
    // Инициализация масок
    initMask: function (select) {
        var option = select.find("option:selected");
        var mask = option.val();
        var tr = select.closest("tr");
        var contactSelect = select.next();

        if (option.data('mask') == 'contact') {
            contactSelect.show();
            this.initMask(contactSelect);
            return false;
        } else {
            contactSelect.hide();
        }

        if (typeof this.masks[mask] !== 'undefined' && this.masks[mask].discountEachItem) {
            tr.next(".table-inside").find(".discounteachitem").show();
        } else {
            tr.next(".table-inside").find(".discounteachitem").hide();
        }

        if (mask == '-') {
            tr.addClass("deny");
        } else {
            tr.removeClass("deny");
        }

        if (typeof this.masks[mask] !== 'undefined') {
            var regexp = this.masks[mask].regexp;
            var regLen = regexp.length;
            for (var i = 0; i < regLen; i++) {
                mask = mask.replace(new RegExp(regexp[i].pattern, 'g'), regexp[i].replacement);
            }
            select.closest("td").next().find(".value-field").mask(mask, {
                'maskChars': {'>': '\>', '<': '\<', '%': '\%', '#': '#', '=': '='}
            });
        }
    },
    // Генерация кода купона
    generateCode: function () {
        var alphabet = "";
        var result = "QWERTYUIOPASDFGHJKLZXCVBNM1234567890";
        for (var i = 0; i < 8; i++) {
            alphabet += result.charAt(Math.floor(Math.random() * result.length));
        }
        return alphabet;
    },
    // Показать форму управления купоном
    couponDesk: function (btn) {
        btn = $(btn);
        $(".coupon-desk").hide();
        btn.prev().find(".coupon-desk").show();
    },
    // Дополнительные настройки для правила скидок
    advancedSettings: function (btn) {
        btn = $(btn);
        var tableInside = btn.parents("tr").next(".table-inside");
        // активная вкладка
        var activeTab = tableInside.find('td > div.open');
        // если вкладка открыта
        if (tableInside.is(":visible")) {
            activeTab.slideUp(500, function () {
                $(this).removeClass("open");
                // если нажали на ту же самую вкладку, то она закрывается
                // иначе открывается новая
                if (activeTab.hasClass("f-advanced")) {
                    $(this).parent().parent().hide();
                } else {
                    tableInside.find('td > div.f-advanced').addClass("open").slideDown(700);
                }
            });
        } else {
            tableInside.show().find('td > div.f-advanced').addClass("open").slideDown(700);
            tableInside.find("tr").show();
            var datepickerInput = tableInside.find(".datepicker-input-advanced");
            if (!datepickerInput.hasClass("hasDatepicker")) {
                $.flexdiscount.datepicker.add(datepickerInput);
            }
        }
    },
    isValid: function (evt, pattern) {
        var theEvent = evt || window.event;
        var key = theEvent.keyCode || theEvent.which;
        key = String.fromCharCode(key);
        var regex = pattern;
        if (!regex.test(key) && evt.charCode !== 0) {
            theEvent.returnValue = false;
            if (theEvent.preventDefault)
                theEvent.preventDefault();
        }
    }
};