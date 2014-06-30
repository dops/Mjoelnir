/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function inputcounter_checkMaxlengthExceedance(element, maxlength) {
    if (maxlength > 0 && element.val().length > maxlength) {
        element.closest('.field').addClass('error');
//        $('#mainWrapper').css({'background': '#f00 none'});
    }
    else {
        if (element.closest('.field').hasClass('error')) {
            element.closest('.field').removeClass('error');
//            $('#mainWrapper').css({'background': '#fff none'});
        }
    }
}

$(document).ready(function() {
    $('input, textarea').each(function() {
        if ($(this).data('maxlength') != undefined) {
            inputcounter_checkMaxlengthExceedance($(this), $(this).data('maxlength'));
            $(this).on('focus', function() {  // Enter the form element
                var elementId       = $(this).attr('id');
                var elementPosition = $(this).position();
                var length          = $(this).val().length;
                var maxlength       = $(this).data('maxlength');
                
                // Remove eventually left counter
                $('#' + elementId + 'Counter').remove();
                
                if (maxlength > 0) {
                    var outputContent = length + ' / ' + maxlength;
                }
                else {
                    var outputContent = length;
                }

                var couterWrapper = $('<div></div>')
                .attr({
                    id: elementId + 'Counter',
                    'class': 'formTextCounter'
                })
                .text(outputContent)
                .css({
                    'position': 'absolute',
                    'top': elementPosition.top - 20,
                    'left': elementPosition.left + $('#' + elementId).width() + 5,
                });

                couterWrapper.insertAfter($('#' + elementId));

                $(elementId + 'Counter').show();
                inputcounter_checkMaxlengthExceedance($(this), maxlength);
            })
            .on('blur', function() {  // Leave the form element
                $('#' + $(this).attr('id') + 'Counter').remove();
                inputcounter_checkMaxlengthExceedance($(this), $(this).data('maxlength'));
            })
            .on('keyup', function() {  // Write in the element
                var elementId = $(this).attr('id');
                var length    = $(this).val().length;
                var maxlength = $(this).data('maxlength');
                
                if (maxlength > 0) {
                    var outputContent = length + ' / ' + maxlength;
                }
                else {
                    var outputContent = length;
                }

                // Check if text length is greater than maxlength
                inputcounter_checkMaxlengthExceedance($(this), maxlength);

                $('#' + elementId + 'Counter').text(outputContent);
            });
        }
    });
});
