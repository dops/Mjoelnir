$(document).ready(function() {
    // Display and hide neg keyword list keywords and textarea.
    var ajaxNegKeywordListFunction = function() {
        var type       = $(this).data('type');

        // Fetch the single display
        var display = $(this).siblings('.' + type);
        var displays = new Array(display);
        
        // If the shoe all button has been clicked, select all available displays
        if ($(this).data('all') == 1) {
            var displays = $(this).closest('table').find('.' + type);
        }

        for (var i = 0; i < displays.length; i++) {
            var display = $(displays[i]);
            var requestUri = display.siblings('.ajaxNegKeywordList[data-type="' + type + '"]').attr('href');

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
                    $('.display, .edit').slideUp('fast');
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
                            for (index in data.rows) {
                                bgColorClass = '';
                                suffix       = '';
                                if (data['rows'][index]['match_type'] == 'exact') {
                                    bgColorClass = 'bgYellow';
                                }
                                if (data['rows'][index]['match_type'] == 'broad') {
                                    bgColorClass = 'bgRed';
                                }

                                text = text + '<span class="' + bgColorClass + '">' + data['rows'][index]['keyphrase'] + '</span><br>';
                            }

                            display.find('p').html(text);
                        }

                        if (type == 'edit') {
                            for (index in data.rows) {
                            	suffix       = '';
                                if (data['rows'][index]['match_type'] == 'exact') {
                                    suffix       = '::e';
                                }
                                if (data['rows'][index]['match_type'] == 'broad') {
                                    suffix       = '::b';
                                }

                                text = text + data['rows'][index]['keyphrase'] + suffix + "\n";
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
    }
    $('.ajaxNegKeywordList').click(ajaxNegKeywordListFunction);
    
    function addRowToCampaignNegKwListTable(data, msgDisplay) {
        var dataData = {
            'sCallback': 'SemmanagementController::negKeywordListRemove',
            'aData':{
                'iCampaignId': data.data.iCampaignId,
                'iAdwordsCampaignId': data.data.iAdwordsCampaignId,
                'iAwKwNegListId': data.data.iAwKwNegListId
            }
        };

        var newRow = $('<tr>')
            .append(
                $('<td>').html(data.responseData.sNegKwListLabel)
            )
            .append(
                $('<td>')
                    .append('<a href="/semmanagement/getNegKeywordsForList/negKeywordsListId/' + data.data.iAwKwNegListId + '/" class="ajaxNegKeywordList" data-type="display">' + data.responseData.iNumKws + '</a>')
                    .append('<div class="ajaxDisplay display ' + data.data.iAwKwNegListId + '"><p></p></div>')
            )
            .append($('<td>')
                .append($('<div>')
                    .attr({'id': 'formElementWrapperAwkwneglistremove' + data.data.iAdwordsCampaignId + data.data.iAwKwNegListId})
                    .addClass('field fieldAjaxuniversal clear')
                    .data('key', 'awKwNegListRemove')
                    .data('data', dataData)
                    .data('confirm-message', 'Bitte bestätige, dass Du diese Negative Keyword Liste von dieser Kategorie entfernen willst.')
                    .append($('<div>')
                        .addClass('labelWrapper')
                        .append($('<label>')
                            .attr('for', 'formElementAwkwneglistremove')
                            .append($('<span>')
                                .addClass('icon iconDelete')
                            )
                        )
                    )
                )
            );

        newRow.css({ background: '#2DD700' });
        if (data.data.iAdwordsCampaignId != undefined) {
            $('#negKwList_awcp' + data.data.iAdwordsCampaignId).find('tbody').append(newRow);
        }
        else {
            $('#negKwList_awcp0').find('tbody').append(newRow);
        }

        newRow.find('.ajaxNegKeywordList').on('click', ajaxNegKeywordListFunction);

        var result = $('#formElementWrapperAwkwneglistremove' + data.data.iAdwordsCampaignId + data.data.iAwKwNegListId).ajaxFormUniversal({
            messageDisplay: msgDisplay,
            success: function(event, data) {
                $(event.target).closest('tr').remove();
            }
        });

        return true;
    }

    // Create datepicker fields
    if ($('.fieldDatepicker input').length > 0) {
        $('.fieldDatepicker input').datepicker({
            dateFormat: "yy-mm-dd",
            dayNames: ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'],
            dayNamesMin: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
            dayNamesShort: ['Son', 'Mon', 'Die', 'Mit', 'Don', 'Fre', 'Sam'],
            changeYear: true,
            changeMonth: true,
            showAnim: 'slideDown',
            showButtonPanel: true,
            showOn: 'focus',
            prevText: 'zur&uuml;ck',
            nextText: 'vor',
            currentText: 'jetzt',
            closeText: 'schlie&szlig;en'
        });
    }
    
    // Create datetimepicker fields
    if ($('.fieldDatetimepicker input').length > 0) {
        $('.fieldDatetimepicker input').datetimepicker({
            dateFormat: "yy-mm-dd",
            dayNames: ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'],
            dayNamesMin: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
            dayNamesShort: ['Son', 'Mon', 'Die', 'Mit', 'Don', 'Fre', 'Sam'],
            changeYear: true,
            changeMonth: true,
            showAnim: 'slideDown',
            showButtonPanel: true,
            showOn: 'focus',
            prevText: 'zur&uuml;ck',
            nextText: 'vor',
            timeFormat: "HH:mm:ss",
            addSliderAccess: true,
            sliderAccessArgs: {
                touchonly: false
            },
            currentText: 'jetzt',
            closeText: 'schlie&szlig;en',
            timeText: 'Zeit',
            hourText: 'Stunde',
            minuteText: 'Minute',
            secondText: 'Sekunde'
        });
    }
    
    // Create datepicker fields
    if ($('.fieldTimepicker input').length > 0) {
        $('.fieldTimepicker input').timepicker({
            timeFormat: "HH:mm:ss",
            addSliderAccess: true,
            sliderAccessArgs: {
                touchonly: false
            },
            showAnim: 'slideDown',
            showButtonPanel: true,
            showOn: 'focus',
            prevText: 'zur&uuml;ck',
            nextText: 'vor',
            currentText: 'jetzt',
            closeText: 'schlie&szlig;en',
            timeText: 'Zeit',
            hourText: 'Stunde',
            minuteText: 'Minute',
            secondText: 'Sekunde',
            timeOnlyTitle: 'Zeit w&auml;hlen'
        });
    }
    
    // Display and hide fields for special user type information
    var customInfos = {};
    var placeholder = '<div id="customInfoPlaceholder"></div>';
    function toggleSpecialInformationFields(type, firstExec) {
        if (firstExec == true) {
            $('.specialUserInfo').each(function(e) {
                if (e == 0) {
                    $(this).before(placeholder);
                }
                customInfos[this.id]    = this;
                $(this).remove();
            })
        }

        if (typeof(type) != 'undefined') {
            // Set placeholder and remove current fieldset
            $('.specialUserInfo').before(placeholder);
            $('.specialUserInfo').remove();
            
            // Set new fieldset and remove placeholder
            var sFieldsetName   = type.toLowerCase() + 'Info';
            $('#customInfoPlaceholder').after(customInfos[sFieldsetName])
            $('#customInfoPlaceholder').remove();
        }
    }
    toggleSpecialInformationFields($('#formElementUsertype option:selected').val(), true);
    $('#formElementUsertype').change(function(e) {
        toggleSpecialInformationFields($('#formElementUsertype option:selected').val());
    });
    
    // Loading ajax form fields
    var msgDisplay           = $(document).messageDisplay();

    loadAjaxStatusUpdateElements($('body'), msgDisplay);
    
    // Negative keyword list selector for adding a list to a adwords campaign
    var ajaxFormSelect = $('#campaignNeggKwMngmnt')
        .find('.fieldAjaxselect')
        .ajaxFormSelect({
            messageDisplay: msgDisplay,
            itemSelect: function(event, data) {
                //availability of awkwneg2awcp id used as flag to decide if a neg kw has to be added to a list OR if a list has to be added to an aw campaign
                if(typeof(data.data.iAwKwNeg2AwcpId) != "undefined" && data.data.iAwKwNeg2AwcpId !== null) { 
                	//add info about neg kw which has been added to a neg kw list in rightmost table cell (last column) belonging to the neg kw
                	var infoCell = $('#infoCell_' + data.data.iAwKwNeg2AwcpId);
                	if (infoCell.text() != '') {
                		infoCell.append('<br />');
                	}
                	infoCell.append('zu Liste mit ID ' + data.data.iAwKwNegListId + ' hinzugefügt');
                }
                else {
                	addRowToCampaignNegKwListTable(data, msgDisplay);
                }
            }
        });
    
    // Negative keyword list selector for adding a list to a category
    var ajaxFormSelect = $('#categoryWrapper')
        .find('.fieldAjaxselect')
        .ajaxFormSelect({
            messageDisplay: msgDisplay,
            itemSelect: function(event, data) {
                if ($('#negKwListTable').find('tbody').find('tr:last').hasClass('odd')) {
                    var sOddEven = 'even';
                }
                else {
                    var sOddEven = 'odd';
                }

                var dataData = {
                    'sCallback': "CategoryController::negKeywordListRemove",
                    "aData":{
                        "iCategoryId": data.data.iCategoryId,
                        "iNegKwListId": data.data.iAwKwNegListId
                    }
                };
                var newRow = $('<tr>')
                    .addClass(sOddEven)
                    .append(
                        $('<td>').html(data.data.sNegKwListLabel)
                    )
                    .append(
                        $('<td>')
                            .append('<a href="/semmanagement/getNegKeywordsForList/negKeywordsListId/' + data.data.iAwKwNegListId + '/" class="ajaxNegKeywordList" data-type="display">' + data.responseData.iNumKws + '</a>')
                            .append('<div class="ajaxDisplay display ' + data.data.iAwKwNegListId + '"><p></p></div>')
                    )
                    .append($('<td>')
                        .append($('<div>')
                            .attr({'id': 'formElementWrapperAwkwneglistremove'})
                            .addClass('field fieldAjaxuniversal clear')
                            .data('key', 'awKwNegListRemove')
                            .data('data', dataData)
                            .data('confirm-message', 'Bitte bestätige, dass Du diese Negative Keyword Liste von dieser Kategorie entfernen willst.')
                            .append($('<div>')
                                .addClass('labelWrapper')
                                .append($('<label>')
                                    .attr('for', 'formElementAwkwneglistremove')
                                    .append($('<span>')
                                        .addClass('icon iconDelete')
                                    )
                                )
                            )
                        )
                    );

                newRow.css({ background: '#2DD700' });
                $('#negKwListTable').find('tbody').append(newRow);
                newRow.find('.ajaxNegKeywordList').on('click', ajaxNegKeywordListFunction);
                
                newRow.find('#formElementWrapperAwkwneglistremove').ajaxFormUniversal({
                    messageDisplay: msgDisplay,
                    success: function(event, data) {
                        $(event.target).closest('tr').remove();
                    }
                });
            }
        });
    
    // Ajax number fields
    loadAjaxNumberFields($('body'), msgDisplay);
    
    // Ajax text fields
    $('.fieldAjaxtext').ajaxFormText({messageDisplay: msgDisplay});
    $('body').on('click', '.fieldAjaxtext', function() {
        $(this).ajaxFormText({messageDisplay: msgDisplay});
    });
    
    // Ajax textarea fields
    $('body').on('click', '.fieldAjaxtextarea', function() {
        $(this).ajaxFormTextarea({messageDisplay: msgDisplay});
    })
    
    // Ajax universal fields
    var ajaxFormUniversal = $('#categoryEdit').find('.fieldAjaxuniversal').ajaxFormUniversal({
        messageDisplay: msgDisplay,
        success: function(event, data) {
            $(event.target).closest('tr').remove();
        },
        error: function(event, data) {
            $(event.target).closest('tr').addClass('error');
        }
    });
    var ajaxFormUniversal = $('body').on('click', '#categoryEdit', function() {
        $(this).find('.fieldAjaxuniversal').ajaxFormUniversal({
            messageDisplay: msgDisplay,
            success: function(event, data) {
                $(event.target).closest('tr').remove();
            },
            error: function(event, data) {
                $(event.target).closest('tr').addClass('error');
            }
        });
    });
    
    // Remove neg kw list from campaign
    var ajaxFormUniversal = $('.negKwListRemoveFromAwcp, .antiNegKwRemoveFromAwcp').ajaxFormUniversal({
        messageDisplay: msgDisplay,
        success: function(event, data) {
            $(event.target).closest('tr').remove();
        },
        error: function(event, data) {
            $(event.target).closest('tr').addClass('error');
        }
    });
    
    // Adding the category default neg kw list set to the campaign.
    var ajaxFormUniversal = $('#campaignNeggKwMngmnt').find('.addCategoryDefaultSet').ajaxFormUniversal({
        messageDisplay: msgDisplay,
        success: function(event, data) {
            if (data.responseData.aListData != undefined) {
                for (i = 0; i < data.responseData.aListData.length; i++) {
                    var aData = {
                        'data': data.data,
                        'responseData': data.responseData.aListData[i]
                    };
                    aData.data.iAwKwNegListId = data.responseData.aListData[i].iAwKwNegListId;
                    addRowToCampaignNegKwListTable(aData, msgDisplay);
                }
            }
        }
    });
   
});