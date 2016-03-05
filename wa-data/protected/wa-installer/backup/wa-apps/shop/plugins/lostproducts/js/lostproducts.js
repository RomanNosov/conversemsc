$(function() {
    
    $('.lostproducts-expand-menu').on('click', function() {
        var expand_icon = $(this).find('.icon16');
        expand_icon.toggleClass('rarr darr');
        $('#s-sidebar .lostproducts-actions').toggle();
        $.post(
            '?plugin=lostproducts&action=saveMenuState',
            {expanded: (expand_icon.hasClass('darr') ? 1 : 0)},
            'json'
        );
    });
    
});