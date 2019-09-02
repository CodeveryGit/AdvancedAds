jQuery(document).ready(function($){
    $('input.peel-color-picker').wpColorPicker({defaultColor: '#5d5d5d'});
});

jQuery(document).on('change', '.corner-close', function () {
    console.log(jQuery(this).val());
    console.log(jQuery(this).parents('div[id^=advads-close-]').children('.corner-close-for'));
    var close_for = jQuery(this).parents('div[id^=advads-close-]').children('.corner-close-for');
    if (jQuery(this).val() != 'never') {
        jQuery(close_for).slideDown();
    }
    else jQuery(close_for).slideUp()
});