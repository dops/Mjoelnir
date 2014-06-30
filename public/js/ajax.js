/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function hideDialog() {
    $('#ajaxDialogWrapper #ajaxDialogWindow').animate({
            left: ($(document).width() + 1000) + 'px'
        },
        {
        duration: 100,
        easing: 'easeInBack',
        complete: function() {
            $('#ajaxDialogWrapper').animate(
            {
                opacity: 0
            },
            {
                duration: 100,
                queue: true,
                complete: function() {
                    $('#ajaxDialogWrapper').detach();
                }
            });
        }
    });
};

function showDialog() {
    $('#ajaxDialogWrapper').animate(
    {
        opacity: 1
    },
    {
        duration: 100,
        complete: function() {
            var padding = $('#ajaxDialogWindow').css('padding').replace(/px/, '');
            var posLeft = ($(document).width() / 2) - (($('#ajaxDialogWindow').width() + (padding * 2)) / 2);

            $('#ajaxDialogWrapper #ajaxDialogWindow').animate({
                left: posLeft + 'px'
                }, {
                duration: 100, 
                easing: 'easeOutBack'
            });
        }
    });
}

/**
 * <p>This function adds messages to a given message display.</p>
 * @param   messageDisplay <p>An instance of the messageDisplay widget.</p>
 * @param   string         <p>The type of the messages to add.</p>
 * @param   messages       <p>The messages to add to the display.</p>
 * @return  boolean
 */
function showMessages(msgDisplay, type, messages) {
    for (key in messages) {
        msgDisplay.messageDisplay('add', type, key, messages[key]);
    }
    return true;
}

/**
 * <p>
 * DEFAULT AJAX FIELD LOADER<br>
 * <br>
 * The following functions are the default loaders for some ajax fields. If you need a different approach, you should implement a special function for it.
 * </p>
 */

/**
 * <p>Default loader for ajax status update elements.</p>
 */
function loadAjaxStatusUpdateElements(parentElement, msgDisplay) {
    parentElement.find('.fieldAjaxstatusupdate').ajaxFormStatusUpdate({
        messageDisplay: msgDisplay,
        itemSelect: function (event, data) {
            if (data.status == 'PAUSED' || data.status == 'DELETED') {
            $(event.target).closest('tr').addClass('textGrey');
        }
        else {
            $(event.target).closest('tr').removeClass('textGrey');
        }
            if (data.status == 'confirmed' || data.status == 'unconfirmed') {
                    var infoCell = $('#infoCell_' + data.sModelId);
            if (infoCell.text() != '') {
                    infoCell.append('<br />');
            }
            infoCell.append('Confirmed-Status geändert: ' + data.status);
        }
            if (data.status == 'deleteNegKw') {
                    var infoCell = $('#infoCell_' + data.sModelId);
                    if (infoCell.text() != '') {
                    infoCell.append('<br />');
            }
            infoCell.append('Keyword gelöscht');

            //remove ajax fields after neg kw has been deleted
            $('#toggleConfirmedCell_' + data.sModelId).text(''); 
            $('#deleteNegKwCell_' + data.sModelId).text(''); 
            $('#moveToListCell_' + data.sModelId).text(''); 
            $('#keyphraseCell_' + data.sModelId).text(''); 

            }
        }
    });
}

/**
 * <p>Default loader for ajax number fields.</p>
 */
function loadAjaxNumberFields(parentElement, msgDisplay) {
    parentElement.find('.fieldAjaxnumber').ajaxFormNumber({messageDisplay: msgDisplay});
}

/**
 * <p>END DEFAULT AJAX FIELD LOADER</p>
 */

$(document).ready(function() {
    $('.ajaxDialog').click(function() {
        var requestUri  = $(this).attr('href');
        var title       = $(this).attr('title');
        var dialogClass = $(this).data('dialog-class');

        var dialogFrame = '<div id="ajaxDialogWrapper" class="' + dialogClass + '"><div id="ajaxDialogWindow"><span onclick="hideDialog()" class="close">close</span><h3></h3><div class="content">#content#</div></div></div>';

        $.ajax({
            type: 'POST',
            url: requestUri,
            data: 'ajaxRequest=1',
            success: function(data) {
                // todo: Validate errors: data.error
                if (data.error != undefined) {
                    var msgDisplay = $(document).messageDisplay();
                    showMessages(msgDisplay, 'error', data.error);
                }
                else {
                    $('body').append(dialogFrame);
                    $('#ajaxDialogWrapper h3').html(title);
                    $('#ajaxDialogWrapper .content').html(data);

                    showDialog();
                }
            },
            error: function(data) {
                var msgDisplay = $(document).messageDisplay();
                msgDisplay.messageDisplay('add', 'error', 'serverCommunication', 'Während der Kommunikation mit dem Server ist ein Fehler aufgetreten. Bitte wende Dich an die IT.');
            }
        });

        return false;
    });
    
    // Send updated keywords to server
    $('.ajaxDisplay.edit').find('input[type="submit"]').on('click', function() {
        var keywords         = $(this).prevAll('textarea').val();
        var negKeywordListId = $(this).data('negkwlistid');
        var displayLink      = $(this).closest('td').find('a[data-type="display"]');
        
        $.ajax({
            type: 'POST',
            url: 'http://' + window.location.hostname + '/semmanagement/updateNegKeywordListKeywords/',
            data: {
                'ajaxRequest': 1,
                'negKeywordListId': negKeywordListId,
                'keywords': keywords
            },
            dataType: 'json',
            success: function(data) {
                messageDisplay = $(document).messageDisplay();
                for (key in data.success) {
                    messageDisplay.messageDisplay('add', 'success', key, data.success[key]);
                }
                
                // Update num of keywords in display link
                numKeywords = 0;
                var keywordArray = keywords.split("\n");
                for (i = 0; i < keywordArray.length; i++) {
                    if (keywordArray[i].length > 0) {
                        numKeywords++;
                    }
                }
                displayLink.text(numKeywords);
            },
            error: function(data) {
                
            }
        });
    });
    
 // Display and hide neg keyword list keywords and textarea.
    $('.ajaxNegKeywordsOfCampaign').click(function() {
        var type       = $(this).data('type');
        var confirmstatus = $(this).data('confirmstatus');
        
        // Fetch the single display
        var display = $(this).siblings('.' + type);
        var displays = new Array(display);

        for (var i = 0; i < displays.length; i++) {
            var display = $(displays[i]);
            var requestUri = $(this).attr('href');
            
            if (display.css('display') != 'none' && display.css('display') != undefined) {
                display.css({
                    display: 'block'
                });
                display.slideUp('fast');
            }
            else {
                display.css({
                    display: 'none'
                });
                if ($(this).data('all') != 1) {
                    $('.display, .editKwOfCampaigns').slideUp('fast');
                }

                $.ajax({
                    type: 'POST',
                    url: requestUri,
                    data: 'ajaxRequest=1',
                    dataType: 'json',
                    async: false,
                    success: function(data, status, jqXHR) {
                        var text = '';
                        
                        if (type == 'display') {
                            for (index in data) {
                                text = text + data[index] + '<br />';
                            }

                            display.find('p').html(text);
                        }
                        if (type == 'editKwOfCampaigns') {
                            for (index in data) {
                                text = text + data[index] + "\n";
                            }
                            
                            if (index == undefined) {
                                var index = 0;
                            }

                            text = text.replace(new RegExp('<br>', 'g'), "\n");
                            display.find('textarea').attr('rows', (parseInt(index) + 2));

                            display.find('textarea').val(text);
                            
                            display.find('input').data('confirmstatus', confirmstatus);
                        }
                        
                        display.slideDown('fast');
                    },
                    error: function(data) {

                    }
                });
            }
        }

        return false;
    });
    
 // Send updated neg keywords to server
    $('.ajaxDisplay.editKwOfCampaigns').find('input[type="submit"]').on('click', function() {
        var keywords         = $(this).prevAll('textarea').val();
        var awcampaignIds 	 = $(this).data('awcampaignids');
        var campaignId	 	 = $(this).data('campaignid');
        var confirmstatus	 = $(this).data('confirmstatus');
        
        $.ajax({
            type: 'POST',
            url: 'http://' + window.location.hostname + '/semmanagement/updateNegKeywordsOfCampaign/',
            data: {
                'ajaxRequest': 1,
                'keywords'		: keywords,
                'awcampaignIds' : awcampaignIds,
                'campaignId'	: campaignId,
                'confirmstatus' : confirmstatus
            },
            dataType: 'json',
            success: function(data) {
            	location.reload();
            	
//                messageDisplay = $(document).messageDisplay();
//                for (key in data.success) {
//                    messageDisplay.messageDisplay('add', 'success', key, data.success[key]);
//                }
            },
            error: function(data) {
                
            }
        });
    });
    
    
    
 // Display and hide anti neg keyword list keywords and textarea.
    $('.ajaxAntiNegKeywordsOfCampaign').click(function() {
        var type       = $(this).data('type');
        
        // Fetch the single display
        var display = $(this).siblings('.' + type);
        var displays = new Array(display);

        for (var i = 0; i < displays.length; i++) {
            var display = $(displays[i]);
            var requestUri = $(this).attr('href');
            
            if (display.css('display') != 'none' && display.css('display') != undefined) {
                display.css({
                    display: 'block'
                });
                display.slideUp('fast');
            }
            else {
                display.css({
                    display: 'none'
                });
                if ($(this).data('all') != 1) {
                    $('.display, .editAntiNegKwOfCampaigns').slideUp('fast');
                }

                $.ajax({
                    type: 'POST',
                    url: requestUri,
                    data: 'ajaxRequest=1',
                    dataType: 'json',
                    async: false,
                    success: function(data, status, jqXHR) {
                        var text = '';
                        
                        if (type == 'display') {
                            for (index in data) {
                                text = text + data[index] + '<br />';
                            }

                            display.find('p').html(text);
                        }
                        if (type == 'editAntiNegKwOfCampaigns') {
                            for (index in data) {
                                text = text + data[index] + "\n";
                            }
                            
                            if (index == undefined) {
                                var index = 0;
                            }

                            text = text.replace(new RegExp('<br>', 'g'), "\n");
                            display.find('textarea').attr('rows', (parseInt(index) + 2));

                            display.find('textarea').val(text);
                        }
                        
                        display.slideDown('fast');
                    },
                    error: function(data) {

                    }
                });
            }
        }

        return false;
    });
    
    // Send updated anti negative keywords to server
    $('.ajaxDisplay.editAntiNegKwOfCampaigns').find('input[type="submit"]').on('click', function() {
        var keywords         = $(this).prevAll('textarea').val();
        var awcampaignIds 	 = $(this).data('awcampaignids');
        var campaignId	 	 = $(this).data('campaignid');
        
        
        $.ajax({
            type: 'POST',
            url: 'http://' + window.location.hostname + '/semmanagement/updateAntiNegKeywordsOfCampaign/',
            data: {
                'ajaxRequest': 1,
                'keywords'		: keywords,
                'awcampaignIds' : awcampaignIds,
                'campaignId'	: campaignId
            },
            dataType: 'json',
            success: function(data) {
            	location.reload();
//                messageDisplay = $(document).messageDisplay();
//                
//                for (key in data.success) {
//                    messageDisplay.messageDisplay('add', 'success', key, data.success[key]);
//                }
            },
            error: function(data) {
                
            }
        });
    });
  
    /**
     * <p>
     * Ajax content loader<br>
     * <br>
     * The ajax content loader reloads contents depending on definitions made in the html and replaces the definition with the content.
     * </p>
     */
    $('.ajaxContentLoader').each(function(index, element) {
        var element = $(element);
        var msgDisplay  = $(document).messageDisplay();
        
        var requestData = $(this).data('data');
        requestData.HTTP_ADZLOCAL_AJAX = true;

        // Add loader gif to loader definition
        $(this).append(
            $('<p>')
                .css({'text-align': 'center'})
                .append(
                    $('<img>')
                        .attr('src', 'http://' + window.location.hostname + '/images/ajax-loader-indicator-big.gif')
                )
        );
            
        $.ajax({
            type: 'POST',
            url: 'http://' + window.location.hostname + '/ajax/contentLoader/',
            data: requestData,
            dataType: 'json'
        }).done(function(data, status, jqXHR) {
            // If the request was successfull, but the new value could not be set, reset the old value.
            if (data.error != undefined && data.error.length > 0) {
                if (data.error != undefined) {
                    for (key in data.error) {
                        msgDisplay.messageDisplay('add', 'error', key, data.error[key]);
                    }
                }
            }
            else {
                element.replaceWith(data.content);
                
                loadAjaxStatusUpdateElements($('body'), msgDisplay);
                loadAjaxNumberFields($('body'), msgDisplay);

                //check if url contains anchor target; if yes, scroll to anchor tag
                var hash = window.location.hash.substring(1);
                if (hash != '') {
                	scrollToAnchor(hash);
                }

                if (data.success != undefined) {
                    for (key in data.success) {
                        msgDisplay.messageDisplay('add', 'error', key, data.success[key]);
                    }
                }
            }
        }).fail(function(jqXHR, status, errorThrown) {
            msgDisplay.messageDisplay('add', 'error', 'requestError', 'Während der Kommunikation mit dem Server ist ein Fehler aufgetreten. Bitte laden die Seite neu. Sollte der Fehler wieder auftreten, wende Dich bitte an die IT (' + errorThrown + ').');
            
            element.empty();
            element.append(
                $('<span>')
                    .addClass('icon')
                    .addClass('iconExclamation')
            );
        });
        
        
        
    });
    
    
    /**
     * <p>
     * AJAX FORM POSTER
     * <br>
     * <br>
     * The ajax form poster sends forms via ajax request to the server.
     * </p>
     */
    $('body').on('submit', '.ajaxForm', function(event) {
        event.preventDefault();
        var msgDisplay      = $(document).messageDisplay();
        var requestData     = $(this).serialize();
        var successCallback = $(this).data('successcallback');
        var errorCallback   = $(this).data('errorcallback');

        msgDisplay.messageDisplay('reset');

        $.ajax({
            type: 'POST',
            url: $(this).attr('action'),
            data: requestData,
            dataType: 'json'
        }).done(function(data, status, jqXHR) {
            // Error case
            if (data.error != undefined) {
                showMessages(msgDisplay, 'error', data.error);
                
                if (errorCallback != undefined) {
                    setTimeout(errorCallback + '()', 1);
                }
            }
            // Success case
            else {
                if (data.success != undefined) {
                    showMessages(msgDisplay, 'success', data.success);
                }
                if (data.info != undefined) {
                    showMessages(msgDisplay, 'info', data.info);
                }
                
                if (successCallback != undefined) {
                    setTimeout(successCallback + '()', 1);
                }
            }
        });
    });
});