jQuery(document).ready(function ($) {
    $.post(ggpush_obj.ajax_url, {
        action: 'ggpush_publish',
        _ajax_nonce: ggpush_obj.nonce
    }, function (data) {
    });
});