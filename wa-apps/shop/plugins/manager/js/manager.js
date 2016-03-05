$(document).ready(function () {
    $(document).on('append_order_list', '#order-list', function (e, orders) {
        var ids = [];
        for (var i = 0; i < orders.length; i++) {
            ids.push(orders[i].id);
        }

        if ($('#order-list').is('ul')) {
            var type = 'ul';
        } else if ($('#order-list').is('table')) {
            var type = 'table';
            if (!$('#order-list th.manager').length) {
                $('#order-list tr.header').append('<th class="manager">Менеджер</th>');
            }
        }

        $.post('?plugin=manager&module=managers', {ids: ids}, function (response) {
            if (response.status == 'ok') {
                for (var order_id in response.data) {
                    var c = response.data[order_id];
                    if (type == 'ul') {
                        if (c) {
                            var li = $('#order-list li[data-order-id="' + order_id + '"]');
                            var html = $('<span class="small"></span>').html('Менеджер: ' + c.name);
                            li.find('.details').append($('<p class="manager"></p>').append(html));
                        }
                    } else if (type == 'table') {
                        var tr = $('#order-list tr.order[data-order-id="' + order_id + '"]');
                        if (c) {
                            tr.append($('<td style="color:#aaa;"></td>').append(c.name));
                        } else {
                            tr.append('<td></td>');
                        }
                    }
                }
            }
        }, "json");
    });
    $('#maincontent').on('click', '#edit-manager', function () {
        $('#manager').load('?plugin=manager&module=users&contact_id=' + $('#manager').data('contact_id'), function () {
            $('#manager > select').change(function () {
                var id = $(this).val();
                var name = $(this).find('option[value="' + id + '"]').html();
                $.post('?plugin=manager&module=save', {order_id: $.order.id, manager_id: id}, function (response) {
                    if (response.status == 'ok') {
                        $.order.reload();
                    } else {
                        alert(response.errors);
                    }
                }, 'json');
            });
        });
        $(this).hide();
        return false;
    });
});