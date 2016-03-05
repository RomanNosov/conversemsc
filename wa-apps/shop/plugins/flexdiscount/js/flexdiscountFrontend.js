/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */
if (typeof jQuery === 'undefined') {
    alert("Для корректной работы плагина Гибкие скидки подключите библиотеку jQuery");
} else {
    (function ($) {
        $.flexdiscountFrontend = {
            isCartPage: 0,
            params: {},
            features: {},
            requestTrack: [],
            url: '',
            refreshUrl: '',
            skuId: null,
            init: function (options) {
                this.url = options.url || '';
                this.refreshUrl = options.refreshUrl || '';
                this.refreshCartUrl = options.refreshCartUrl || '';
                this.features = options.features || {};
                this.currency = options.currency || {};
                this.ruble = options.ruble || 'text';
                this.skuId = options.skuId || null;
                // Отправка купона
                $(".flexdiscount-submit-button").click(function () {
                    var btn = $(this);
                    var coupon = btn.parents(".flexdiscount-form").find(".flexdiscount-coupon-code");
                    var couponCode = $.trim(coupon.val());
                    btn.attr("disabled", "disabled");
                    $.flexdiscountFrontend.addLoading(btn);
                    if (couponCode == '') {
                        $.flexdiscountFrontend.fieldIsEmpty(coupon);
                        $.flexdiscountFrontend.hideLoading(btn);
                        btn.removeAttr("disabled");
                        return false;
                    }
                    $.post($.flexdiscountFrontend.url, {coupon: couponCode}, function () {
                        btn.removeAttr("disabled");
                        location.reload();
                    }, "json");
                    return false;
                });

                // Обновляем значение скидки в случае наличия артикулов и изменения количества товаров
                $(document).on("change", ".sku-feature, input[name*='quantity'], select[name*='quantity']", function () {
                    var formRefreshButton = $(this).closest("form").find(".flexdiscount-refresh");
                    var refreshButton = formRefreshButton.length ? formRefreshButton : $(".flexdiscount-refresh");
                    $.flexdiscountFrontend.preRefresh($(this), refreshButton);
                    $.flexdiscountFrontend.refreshAllDiscounts();
                });
                $(document).on("click", "#product-skus input[type=radio], .skus input[type=radio]", function () {
                    var formRefreshButton = $(this).closest("form").find(".flexdiscount-refresh");
                    var refreshButton = formRefreshButton.length ? formRefreshButton : $(".flexdiscount-refresh");
                    $.flexdiscountFrontend.preRefresh($(this), refreshButton);
                    $.flexdiscountFrontend.refreshAllDiscounts();
                });
                
                // Сделано на случай того, если разработчики тем не передают событие изменения количества
                var quantityBlock = '';
                if ($("input[name*='quantity']").length) {
                    quantityBlock = $("input[name*='quantity']");
                } else if ($("select[name*='quantity']").length) {
                    quantityBlock = $("select[name*='quantity']");
                }
                if (quantityBlock) {
                    quantityBlock.each(function (i, v) {
                        $(v).change(function () {
                            var formRefreshButton = $(this).closest("form").find(".flexdiscount-refresh");
                            var refreshButton = formRefreshButton.length ? formRefreshButton : $(".flexdiscount-refresh");
                            $.flexdiscountFrontend.preRefresh($(this), refreshButton);
                        });
                        if ($(v).val() > 1) {
                            var formRefreshButton = $(this).closest("form").find(".flexdiscount-refresh");
                            var refreshButton = formRefreshButton.length ? formRefreshButton : $(".flexdiscount-refresh");
                            $.flexdiscountFrontend.preRefresh($(this), refreshButton);
                        }
                    });
                }
                // Изменение услуг
                $(document).on("click", ".services input[type=checkbox]", function () {
                    $.flexdiscountFrontend.updatePrice($(this).closest("form"));
                });
                $(document).on("change", ".services .service-variants", function () {
                    $.flexdiscountFrontend.updatePrice($(this).closest("form"));
                });
                // Обновляем доступные скидки
                $.flexdiscountFrontend.refreshAllDiscounts();
            },
            // Обновление блока доступных скидок (не требует отправки данных)
            refreshAllDiscounts: function () {
                var allDiscountsBlock = $(".flexdiscount-alldiscounts");
                if (allDiscountsBlock.length) {
                    allDiscountsBlock.show();
                    $(".discount-skus").hide();
                    var skuId = $.flexdiscountFrontend.getSkuID();
                    if (skuId) {
                        $(".discount-skus[data-sku-id='" + skuId + "']").show();
                    } else {
                        if ($(".discount-skus[data-sku-id='0']").length) {
                            $(".discount-skus[data-sku-id='0']").show();
                        } else {
                            allDiscountsBlock.hide();
                        }
                    }
                    allDiscountsBlock.find(".flexdiscount-interactive").show();
                    allDiscountsBlock.find(".flexdiscount-loader").hide();
                }
                $(".flexdiscount-show-all .discount-skus").show();
            },
            preRefresh: function (elem, refreshButton) {
                if (typeof refreshButton !== 'undefined' && refreshButton.length) {
                    refreshButton.click();
                } else {
                    this.refresh(elem, elem.closest("form").find("input[name='product_id']").val(), true);
                }
            },
            // Вызывать метод после сохранения изменения количества товаров в корзине
            cartChange: function () {
                var fields = $(".flexdiscount-cart-price");
                var userDiscounts = $(".flexdiscount-user-discounts");
                var data = {};
                if (fields.length) {
                    fields.each(function (i, v) {
                        var updateField = $(this);
                        if (updateField.length) {
                            updateField.addClass("c" + i);
                            if (typeof data[updateField.data("cart-id")] == 'undefined') {
                                data[updateField.data("cart-id")] = {};
                            }
                            data[updateField.data("cart-id")]["c" + i] = {
                                "mult": updateField.data("mult"),
                                "html": updateField.data("html"),
                                "format": updateField.data("format")
                            };
                        }
                    });
                }
                if (data || userDiscounts.length) {
                    $.flexdiscountFrontend.addLoading(fields);
                    if (userDiscounts.length) {
                        userDiscounts.html('<div class="align-center"><i class="flexdiscount-big-loading"></i></div>');
                    }
                    $.post($.flexdiscountFrontend.refreshCartUrl, {
                        fields: data
                    }, function (response) {
                        $.flexdiscountFrontend.hideLoading(fields);
                        if (response.status == 'ok' && response.data) {
                            if (typeof response.data.fields !== 'undefined') {
                                $.each(response.data.fields, function (i, v) {
                                    $(v.elem).removeClass(v.removeClass).html($(v.price).html());
                                });
                            }
                            if (typeof response.data.user_discounts !== 'undefined') {
                                $.flexdiscountFrontend.refreshUserDiscounts(response.data.user_discounts);
                            }
                        }
                    }, "json");
                }
            },
            refreshUserDiscounts: function (user_discounts) {
                if ($(".flexdiscount-user-discounts").length) {
                    $(".flexdiscount-user-discounts.fl-discounts").each(function () {
                        $(this).replaceWith(user_discounts.discounts);
                    });
                    $(".flexdiscount-user-discounts.fl-affiliate").each(function () {
                        $(this).replaceWith(user_discounts.affiliate);
                    });
                }
            },
            refresh: function (btn, productId, withoutLoader) {
                btn = $(btn);
                var skuId = '';
                $($.flexdiscountFrontend.requestTrack).each(function (i, jqXHR) {
                    jqXHR.abort();
                    $.flexdiscountFrontend.requestTrack.splice(i, 1);
                });

                if (!$.flexdiscountFrontend.isCartPage) {
                    // Проверяем, находимся ли мы внутри формы, или снаружи (на странице товара).
                    // Это необходимо для подсчета скидки товара на лету с учетом количества
                    var insideForm = btn.closest("form");
                    var form = insideForm.length ? insideForm : $("#cart-form");
                    skuId = $.flexdiscountFrontend.getSkuID(form);

                    var viewTypes = [];
                    $(".flexdiscount-product-discount").each(function () {
                        viewTypes.push($(this).data('view-type'));
                    });

                    var flexdiscountPrice = $(".flexdiscount-price.product-id-" + productId + "[data-sku-id='0']" + (skuId ? ", .flexdiscount-price.product-id-" + productId + "[data-sku-id='" + skuId + "']" : ""));
                    var flexdiscountPDiscount = $(".flexdiscount-product-discount.product-id-" + productId);
                    var userDiscounts = $(".flexdiscount-user-discounts");

                    $.flexdiscountFrontend.removeLoading();
                    if (!withoutLoader) {
                        $.flexdiscountFrontend.addLoading(btn);
                    }
                    flexdiscountPrice.append("<i class='icon16-flexdiscount loading'></i>");

                    if (flexdiscountPDiscount.length) {
                        flexdiscountPDiscount.find('.flexdiscount-interactive').hide().prev().show();
                    }

                    if (userDiscounts.length) {
                        userDiscounts.html('<div class="align-center"><i class="flexdiscount-big-loading"></i></div>');
                    }

                    $.ajax({
                        type: 'post',
                        url: $.flexdiscountFrontend.refreshUrl,
                        cache: false,
                        dataType: "json",
                        data: {
                            product_id: productId,
                            sku_id: skuId,
                            quantity: form.find("input[name='quantity']").length ? form.find("input[name='quantity']").val() : (form.find("select[name='quantity']").length ? form.find("select[name='quantity']").val() : 1),
                            view_types: viewTypes
                        },
                        beforeSend: function (jqXHR) {
                            $.flexdiscountFrontend.requestTrack.push(jqXHR);
                        },
                        success: function (response) {
                            $.flexdiscountFrontend.hideLoading(btn);
                            flexdiscountPrice.find(".icon16-flexdiscount").remove();
                            if (response.status == 'ok') {
                                btn.closest(".flexdiscount-current-discount").replaceWith(response.data.html);
                                if (typeof response.data.price !== 'undefined') {
                                    flexdiscountPrice.html(response.data.price).attr('data-price', response.data.clear_price);
                                    var priceBlock = flexdiscountPrice.closest('.flexdiscount-price-block');
                                    if (priceBlock.length && response.data.discount !== 0) {
                                        priceBlock.show();
                                    } else {
                                        priceBlock.hide();
                                    }
                                }
                                if (flexdiscountPDiscount.length) {
                                    flexdiscountPDiscount.each(function () {
                                        $(this).html(response.data.product_discounts[$(this).data('view-type')]).find(".flexdiscount-interactive").show().prev().hide();
                                    });
                                }
                                $.flexdiscountFrontend.updatePrice(form);
                                if (typeof response.data.user_discounts !== 'undefined') {
                                    $.flexdiscountFrontend.refreshUserDiscounts(response.data.user_discounts);
                                }
                            }
                        },
                        error: function () {
                            $.flexdiscountFrontend.hideLoading(btn);
                            flexdiscountPrice.find(".icon16-flexdiscount").remove();
                            userDiscounts.hide();
                        }
                    });
                }
            },
            fieldIsEmpty: function (field) {
                field.addClass('flexdiscount-empty-field').click(function () {
                    $(this).removeClass("flexdiscount-empty-field");
                });
            },
            removeLoading: function () {
                var loading = $(".icon16-flexdiscount.loading");
                if (loading.length) {
                    loading.remove();
                }
            },
            getSkuID: function (form) {
                var skuId = '';
                form = form ? form : $(document);
                if (form.find("#product-skus").length) {
                    skuId = form.find("#product-skus input[type=radio]:checked").val();
                } else if (form.find(".skus").length) {
                    skuId = form.find(".skus input[type=radio]:checked").val();
                }

                if (form.find(".sku-feature").length) {
                    var key = "";
                    form.find(".sku-feature").each(function () {
                        key += $(this).data('feature-id') + ':' + $(this).val() + ';';
                    });
                    var sku = $.flexdiscountFrontend.features[key];
                    if (sku) {
                        skuId = sku.id;
                    } else if (typeof sku_features !== 'undefined' && sku_features[key]) {
                        skuId = sku_features[key].id;
                    }
                }
                if (!skuId) {
                    skuId = $.flexdiscountFrontend.skuId;
                }
                return skuId;
            },
            addLoading: function (elem) {
                elem.after("<i class='icon16-flexdiscount loading'></i>");
            },
            hideLoading: function (elem) {
                elem.next(".icon16-flexdiscount.loading").remove();
            },
            currencyFormat: function (number, no_html) {
                var i, j, kw, kd, km;
                var decimals = $.flexdiscountFrontend.currency.frac_digits;
                var dec_point = $.flexdiscountFrontend.currency.decimal_point;
                var thousands_sep = $.flexdiscountFrontend.currency.thousands_sep;
                if (isNaN(decimals = Math.abs(decimals))) {
                    decimals = 2;
                }
                if (dec_point == undefined) {
                    dec_point = ",";
                }
                if (thousands_sep == undefined) {
                    thousands_sep = ".";
                }
                i = parseInt(number = (+number || 0).toFixed(decimals)) + "";
                if ((j = i.length) > 3) {
                    j = j % 3;
                } else {
                    j = 0;
                }
                km = (j ? i.substr(0, j) + thousands_sep : "");
                kw = i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands_sep);
                kd = (decimals && (number - i) ? dec_point + Math.abs(number - i).toFixed(decimals).replace(/-/, 0).slice(2) : "");
                var number = km + kw + kd;
                var s = no_html ? $.flexdiscountFrontend.currency.sign : $.flexdiscountFrontend.currency.sign_html;
                if (!this.currency.sign_position) {
                    return s + $.flexdiscountFrontend.currency.sign_delim + number;
                } else {
                    return number + $.flexdiscountFrontend.currency.sign_delim + s;
                }
            },
            updatePrice: function (form) {
                var productId = form.find("input[name='product_id']").val();
                var flexdiscountPrice = $(".flexdiscount-price.product-id-" + productId);
                var price = parseFloat(flexdiscountPrice.attr('data-price'));
                form.find(".services input:checked").each(function () {
                    var s = $(this).val();
                    if (form.find('.service-' + s + '  .service-variants').length) {
                        price += parseFloat(form.find('.service-' + s + '  .service-variants :selected').data('price'));
                    } else if (form.find('#service-' + s + '  .service-variants').length) {
                        price += parseFloat(form.find('#service-' + s + '  .service-variants :selected').data('price'));
                    } else {
                        price += parseFloat($(this).data('price'));
                    }
                });
                flexdiscountPrice.html($.flexdiscountFrontend.currencyFormat(price, ($.flexdiscountFrontend.ruble == 'html' ? 0 : 1)));
            }
        };
    })(jQuery);
}