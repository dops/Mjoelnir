/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function() {
    
    /*
     * This widget display messages for ajax responses.
     */
    (function( $ ) {
        $.widget('adzlocal.messageDisplay', {
            options: {
                'messages': {
                    'success': {},
                    'error': {},
                    'info': {}
                },
                'append': $('#messages'),
                'prepend': null,
                'bevore': null,
                'after': null
            },
            
            _create: function() {},
            
            
            _refresh: function() {
                for (keyType in this.options.messages) {
                    var num = Object.keys(this.options.messages[keyType]).length;
                    if (num > 0) {
                        if ($('.' + keyType + 'Msg').length == 0) {
                            var msgDiv = $('<div>')
                                .addClass(keyType + 'Msg');
                        }
                        else {
                            var msgDiv = $('.' + keyType + 'Msg');
                            msgDiv.children().remove();
                        }
                        
                        for (keyMsg in this.options.messages[keyType]) {
                            msgDiv.append($('<p>').text(this.options.messages[keyType][keyMsg]));
                        }
                        
                        this.options.append.append(msgDiv);
                    }
                    else {
                        if ($('.' + keyType + 'Msg').length > 0) {
                            $('.' + keyType + 'Msg').remove();
                        }
                    }
                }
            },
            
            
            add: function(type, key, msg) {
                this.options.messages[type][key] = msg;
                this._refresh();
            },
            
            reset: function() {
                for (key in this.options.messages) {
                    this.options.messages[key] = {};
                }
                this._refresh();
            },
            
            // Use the _setOption method to respond to changes to options
            _setOption: function( key, value ) {
                switch( key ) {
                    case "clear":
                        // handle changes to clear option
                        break;
                }

                if (this.options[key] != undefined) {
                    this.options[key] = value;
                }

                // In jQuery UI 1.8, you have to manually invoke the _setOption method from the base widget
                //              $.Widget.prototype._setOption.apply( this, arguments );
                // In jQuery UI 1.9 and above, you use the _super method instead
                this._super( "_setOption", key, value );
            },

            // Use the destroy method to clean up any modifications your widget has made to the DOM
            destroy: function() {
            // In jQuery UI 1.8, you must invoke the destroy method from the base widget
            //              $.Widget.prototype.destroy.call( this );
            // In jQuery UI 1.9 and above, you would define _destroy instead of destroy and not call the base method
            }
        });
    }( jQuery ) );

    /*
     * This widget provides the client side functionality for status update form fields.
     */
    (function( $ ) {
        $.widget( "adzlocal.ajaxFormStatusUpdate", {

            // These options will be used as defaults
            options: { 
                clear: null,
                elementWrapper: null,
                dialogId: null,
                dialogWrapper: null,
                dialogWrapperHover: false,
                messageDisplay: null
            },
            
            // Set up the widget
            _create: function() {
                if (this.options.dialogId == null) {
                    this.options.dialogId = 'statusUpdate' + this.element.data('key');
                }
                
                var element                 = this.element;
                this.options.elementWrapper = element.find('.elementWrapper');
                var wrapperPosition         = this.options.elementWrapper.position();

                // Set input styles
                this.element.css({
                    'borderWidth': '1px',
                    'borderColor': 'transparent',
                    'borderStyle': 'solid'
                });
                
                // Build dialog wrapper
                dialogWrapper = $('<div></div>')
                .attr({
                    'id': this.options.dialogId,
                    'class': 'formElementDialog ajaxUpdateStatusSelect'
                })
                .html(
                    $('<h6></h6>')
                    .text('Status wechseln zu:')
                    .append($('<ul>'))
                    )
                .css({
                    'display': 'none',
                    'position': 'absolute',
                    'top': wrapperPosition.top,
                    'left': wrapperPosition.left + this.options.elementWrapper.width() + 5,
                    'width': 'auto'
                });
                
                // Save dialog wrapper for further use and insert it after the currene element.
                this.options.dialogWrapper = dialogWrapper;
                this.options.dialogWrapper.insertAfter(element);
                
                // Refresh selectable list
                this._refresh();
            },
            
            // Refresh list of selectable values
            _refresh: function() {
                // Create status list
                var statusList = $('<ul>');
                this.options.elementWrapper.find('a').each(function() {
                    if (!$(this).hasClass('ajaxSelected')) {
                        selectable = $(this).clone();
                        selectable.append(' ' + $(this).data('label'))
                        var li = $('<li>')
                            .data('status', $(this).data('status'))
                            .data('label', $(this).data('label'))
                            .html(selectable);
                        statusList.append(li);
                    }
                });
                
                this.options.dialogWrapper.find('ul').replaceWith(statusList);
                
                // Bind events.
                this._bindEventHandlers();
            },
            
            // Bind event handlers to the element
            _bindEventHandlers: function() {
                elementWrapper = this.element.find('.elementWrapper');
                // Element wrapper events.
                this._on(this.options.elementWrapper, {
                    mouseenter: '_enterElementWrapper',
                    mouseleave: '_leaveElementWrapper'
                });
                
                // Dialogbox events
                this._on(this.options.dialogWrapper, {
                    mouseenter: '_enterDialogWrapper',
                    mouseleave: '_leaveDialogWrapper'
                });
                
                // Add click event to current element. Its removed first, to prevent double binding.
                this.element.unbind('click');
                this._on(this.element, {
                    click: '_selectNextItem'
                });
                
                // Add click events to selectable items
                this._on(this.options.dialogWrapper.find('li'), {
                    click: '_selectItem'
                });
                
                // Highlighting element as editable when its in a table row.
                this._on(this.element.closest('td'), {
                    mouseenter: function() {
                        this.element.find('span').closest('#' + this.element.attr('id')).css({
                            'borderColor': '#aaa'
                        });
                    },
                    mouseleave: function() {
                        this.element.find('span').closest('#' + this.element.attr('id')).css({
                            'borderColor': 'transparent'
                        });
                    }
                });
            },
            
            
            _selectNextItem: function(event) {
                var currentSelected = this.element.find('.ajaxSelected');
                
                if (currentSelected.index() == (this.options.elementWrapper.children().length - 1)) {
                    var newElement = this.options.elementWrapper.find(':first');
                }
                else {
                    var newElement = currentSelected.next();
                }
                
                this._submit(newElement);
            },
            
            // Change the selected item and execute ajax request.
            _selectItem: function(event) {

            	var selectedElement = $(event.target);
                var newElement      = this.options.elementWrapper.find("[data-status='" + selectedElement.data('status') + "']");
                
                this._submit(newElement);
            },
            
            // Sends the new value to the server.
            _submit: function(newElement) {
                var oldElement      = this.options.elementWrapper.find('.ajaxSelected');
                
                // Switch element display
                oldElement.removeClass('ajaxSelected');
                newElement.addClass('ajaxSelected');
                
                // Fetch the pre-defined request data and add additional data.
                var requestData            = this.element.data('data');
                requestData.aData.sParam    = this.element.data('key');
                requestData.aData.sNewValue = newElement.data('status');
                requestData.aData.sOldValue = oldElement.data('status');
                
                var self = this;
                self.options.messageDisplay.messageDisplay('reset');
                
                $.ajax({
                    type: 'POST',
                    url: 'http://' + window.location.hostname + '/ajax/updateModel/',
                    data: requestData,
                    dataType: 'json'
                }).done(function(data, status, jqXHR) {
                    // If the request was successfull, but the new value could not be set, reset the old value.
                    if (data.error != undefined) {
                        self._resetItem(oldElement);
                    }
                    
                    self._displayReturnMessages(data);
                }).fail(function(jqXHR, status, errorThrown) {
                    self._resetItem(oldElement);
                    
                    self._displayReturnMessages({
                        'error': {
                            'requestError': 'Während der Kommunikation mit dem Server ist ein Fehler aufgetreten. Bitte wiederhole Deine Aktion. Sollte der Fehler wieder auftreten, wende Dich bitte an die IT.'
                        }
                    });
                });
                
                // Hide the dialog and refresh the list of selectables.
                this.options.dialogWrapperHover = false;
                this._hideDialog();
                this._refresh();
                this._trigger('itemSelect', null, { status: requestData.aData.sNewValue, sModelId: requestData.aData.sModelId });
            },
            
            /*
             * Adds messages to the message display.
             */
            _displayReturnMessages: function(data) {
                // Display error messages
                if (data.error != undefined) {
                    for (key in data.error) {
                        this.options.messageDisplay.messageDisplay('add', 'error', key, data.error[key]);
                    }
                }
                // Display success messages
                if (data.success != undefined) {
                    for (key in data.success) {
                        this.options.messageDisplay.messageDisplay('add', 'success', key, data.success[key]);
                    }
                }
                // Display info messages
                if (data.info != undefined) {
                    for (key in data.info) {
                        this.options.messageDisplay.messageDisplay('add', 'info', key, data.info[key]);
                    }
                }
            },
            
            /*
             * Resets the previously selected item.
             */
            _resetItem: function(oldElement) {
                // Find new element.
                var newElement      = this.options.elementWrapper.find('.ajaxSelected');
                
                // Switch element display
                oldElement.addClass('ajaxSelected');
                newElement.removeClass('ajaxSelected');
                
                this._refresh();
            },
            
            // Displays the dialog box
            _enterElementWrapper: function(event) {
            	
            	var showSelectDialog = true;
            	if (this.options.elementWrapper.find('a').attr('showSelectDialog')) {
            		if (this.options.elementWrapper.find('a').attr('showSelectDialog') == 'dontShow') {
            			showSelectDialog = false;	
            		}
            	}
            	var wrapperPosition = this.options.elementWrapper.position();
            	this.options.dialogWrapper.css({
                    'top': wrapperPosition.top,
                    'left': wrapperPosition.left + this.options.elementWrapper.width() + 5,
            	});
                if (this.options.elementWrapper.find('a').length > 2 && showSelectDialog) {
                    this._showDialog(event);
                }
            },
            
            // Hide the dialog box
            _leaveElementWrapper: function(event) {
                self = this;
                setTimeout(function() {
                    self._hideDialog(event);
                }, 100);
            },
            
            // Actions on mouse enter event for dialog box.
            _enterDialogWrapper: function(event) {
                this.options.dialogWrapperHover = true;
            },
            
            // Actions on mouse leave event for dialog box.
            _leaveDialogWrapper: function(event) {
                this.options.dialogWrapperHover = false;
                this._hideDialog();
            },
            
            // Display the select dialog.
            _showDialog: function(event) {
                this.options.dialogWrapper.show();
                this._trigger('showDialog', event);
            },
            
            // Hide the select dialog.
            _hideDialog: function(event) {
                if (this.options.dialogWrapperHover == false) {
                    this.options.dialogWrapper.hide();
                }
                this._trigger('hideDialog', event);
            },
            
            // Use the _setOption method to respond to changes to options
            _setOption: function( key, value ) {
                switch( key ) {
                    case "clear":
                        // handle changes to clear option
                        break;
                }

                // In jQuery UI 1.8, you have to manually invoke the _setOption method from the base widget
                //              $.Widget.prototype._setOption.apply( this, arguments );
                // In jQuery UI 1.9 and above, you use the _super method instead
                this._super( "_setOption", key, value );
            },

            // Use the destroy method to clean up any modifications your widget has made to the DOM
            destroy: function() {
            // In jQuery UI 1.8, you must invoke the destroy method from the base widget
            //              $.Widget.prototype.destroy.call( this );
            // In jQuery UI 1.9 and above, you would define _destroy instead of destroy and not call the base method
            }
        });
    }( jQuery ) );
    
    /*
     * This widget provides the client side functionality for status update form fields.
     */
    (function( $ ) {
        $.widget( "adzlocal.ajaxFormSelect", {

            // These options will be used as defaults
            options: { 
                clear: null,
                elementWrapper: null,
                dialogId: null,
                dialogWrapper: null,
                dialogWrapperHover: false,
                messageDisplay: null,
                dialogWrapperTitle: 'Bitte wählen:'
            },
            
            // Set up the widget
            _create: function() {
                if (this.options.dialogId == null) {
                    this.options.dialogId = 'select' + this.element.data('key');
                }
                
                this.options.data = this.element.data('data');
                if (this.options.data['dialogWrapperTitle'] != undefined) {
                    this.options.dialogWrapperTitle = this.options.data['dialogWrapperTitle'];
                }
                
                // Put the label into an anchor tag to let it occure linke a link
                var label = this.element.find('label');
                label.replaceWith($('<a>').append(label.clone()));
                
                // Remove field background color
                this.element.closest('.field').css({
                    'display': 'inline',
                    'margin': '0',
                    'padding': '0',
                    'background': 'transparent none'
                });
                
                var element                 = this.element;
                this.options.labelWrapper   = element.find('.labelWrapper');
                this.options.elementWrapper = element.find('.elementWrapper');

                // Remove padding
                this.options.labelWrapper.css({
                    'margin': '0 10px',
                    'padding': '0'
                });
                this.options.labelWrapper.find('label').css({
                    'margin': '0',
                    'padding': '0',
                    'font-weight': 'normal',
                    'text-decoration': 'underline'
                });

                // Hide element wrapper
                this.options.elementWrapper.css({
                    'display': 'none'
                });
                
                // Build dialog wrapper
                dialogWrapper = $('<div></div>')
                .attr({
                    'id': this.options.dialogId,
                    'class': 'formElementDialog ajaxUpdateStatusSelect'
                })
                .html(
                    $('<h6>')
                    .text(this.options.dialogWrapperTitle)
                    .add($('<ul>'))
                    )
                .css({
                    'display': 'none',
                    'position': 'absolute'
//                    'width': 'auto'
                });
                
                // Save dialog wrapper for further use and insert it after the currene element.
                this.options.dialogWrapper = dialogWrapper;
                this.options.dialogWrapper.insertAfter(element);
                
                // Refresh selectable list
                this._refresh();
            },
            
            // Refresh list of selectable values
            _refresh: function() {
                // Create status list
                var statusList = $('<ul>');
                this.options.elementWrapper.find('a').each(function() {
                    selectable = $(this).clone();
                    selectable.append(' ' + $(this).data('label'))
                    var li = $('<li>')
                        .data('status', $(this).data('status'))
                        .data('label', $(this).data('label'))
                        .html(selectable);
                    statusList.append(li);
                });
                
                this.options.dialogWrapper.find('ul').replaceWith(statusList);
                
                // Bind events.
                this._bindEventHandlers();
            },
            
            // Bind event handlers to the element
            _bindEventHandlers: function() {
                // Element wrapper events.
                this._on(this.options.labelWrapper, {
                    mouseenter: '_enterElementWrapper',
                    mouseleave: '_leaveElementWrapper'
                });
                
                // Dialogbox events
                this._on(this.options.dialogWrapper, {
                    mouseenter: '_enterDialogWrapper',
                    mouseleave: '_leaveDialogWrapper'
                });
                
                // Add click events to selectable items
                this._on(this.options.dialogWrapper.find('li'), {
                    click: '_selectItem'
                });
                
                // Highlighting element as editable when its in a table row.
                this._on(this.element.closest('td'), {
                    mouseenter: function() {
                        this.element.find('span').closest('#' + this.element.attr('id')).css({
                            'borderColor': '#aaa'
                        });
                    },
                    mouseleave: function() {
                        this.element.find('span').closest('#' + this.element.attr('id')).css({
                            'borderColor': 'transparent'
                        });
                    }
                });
            },
            
            // Change the selected item and execute ajax request.
            _selectItem: function(event) {
            	var selectedElement = $(event.target);
                var newElement      = $(selectedElement);
                
                this._submit(newElement);
            },
            
            // Sends the new value to the server.
            _submit: function(newElement) {
                // Fetch the pre-defined request data and add additional data.
                var requestData                   = this.element.data('data');
                requestData.aData.sNegKwListLabel = newElement.data('label');
                requestData.aData.iAwKwNegListId  = newElement.data('value');
                
                var self = this;
                self.options.messageDisplay.messageDisplay('reset');
                
                $.ajax({
                    type: 'POST',
                    url: 'http://' + window.location.hostname + '/ajax/updateModel/',
                    data: requestData,
                    dataType: 'json'
                }).success(function(data, status, jqXHR) {
                    // Hide the dialog and refresh the list of selectables.
                    self.options.dialogWrapperHover = false;
                    self._hideDialog();
                    self._refresh();
                    self._trigger('itemSelect', null, { data: requestData.aData, responseData: data });
                    
                    self._displayReturnMessages(data);
                }).fail(function(jqXHR, status, errorThrown) {
                    self._displayReturnMessages({
                        'error': {
                            'requestError': 'Während der Kommunikation mit dem Server ist ein Fehler aufgetreten. Bitte wiederhole Deine Aktion. Sollte der Fehler wieder auftreten, wende Dich bitte an die IT.'
                        }
                    });
                });
            },
            
            /*
             * Adds messages to the message display.
             */
            _displayReturnMessages: function(data) {
                // Display error messages
                if (data.error != undefined) {
                    for (key in data.error) {
                        this.options.messageDisplay.messageDisplay('add', 'error', key, data.error[key]);
                    }
                }
                // Display success messages
                if (data.success != undefined) {
                    for (key in data.success) {
                        this.options.messageDisplay.messageDisplay('add', 'success', key, data.success[key]);
                    }
                }
                // Display info messages
                if (data.info != undefined) {
                    for (key in data.info) {
                        this.options.messageDisplay.messageDisplay('add', 'info', key, data.info[key]);
                    }
                }
            },
            
            // Displays the dialog box
            _enterElementWrapper: function(event) {
            	var wrapperPosition = this.options.labelWrapper.position();
            	this.options.dialogWrapper.css({
                    'top': wrapperPosition.top,
                    'left': wrapperPosition.left + this.options.labelWrapper.width() + 5,
            	});
                this._showDialog(event);
            },
            
            // Hide the dialog box
            _leaveElementWrapper: function(event) {
                self = this;
                setTimeout(function() {
                    self._hideDialog(event);
                }, 100);
            },
            
            // Actions on mouse enter event for dialog box.
            _enterDialogWrapper: function(event) {
                this.options.dialogWrapperHover = true;
            },
            
            // Actions on mouse leave event for dialog box.
            _leaveDialogWrapper: function(event) {
                this.options.dialogWrapperHover = false;
                this._hideDialog();
            },
            
            // Display the select dialog.
            _showDialog: function(event) {
                $('body').find('.formElementDialog').hide();
                
                this.options.dialogWrapper.show();
                this._trigger('showDialog', event);
            },
            
            // Hide the select dialog.
            _hideDialog: function(event) {
                if (this.options.dialogWrapperHover == false) {
                    this.options.dialogWrapper.hide();
                }
                this._trigger('hideDialog', event);
            },
            
            // Use the _setOption method to respond to changes to options
            _setOption: function( key, value ) {
                switch( key ) {
                    case "clear":
                        // handle changes to clear option
                        break;
                }

                // In jQuery UI 1.8, you have to manually invoke the _setOption method from the base widget
                //              $.Widget.prototype._setOption.apply( this, arguments );
                // In jQuery UI 1.9 and above, you use the _super method instead
                this._super( "_setOption", key, value );
            },

            // Use the destroy method to clean up any modifications your widget has made to the DOM
            destroy: function() {
            // In jQuery UI 1.8, you must invoke the destroy method from the base widget
            //              $.Widget.prototype.destroy.call( this );
            // In jQuery UI 1.9 and above, you would define _destroy instead of destroy and not call the base method
            }
        });
    }( jQuery ) );
      
    /*
     * This widget provides the functionality for the form field "ajaxnumber".
     */
    (function( $ ) {
        $.widget( "adzlocal.ajaxFormNumber", {

            // These options will be used as defaults
            options: { 
                clear: null,
                border: null,
                background: null,
                inputElem: null,
                iconUp: '<span class="icon iconBulletArrowUp arrowUp"></span>',
                iconDown: '<span class="icon iconBulletArrowDown arrowDown"></span>',
                iconAccept: '<span class="icon iconAccept accept"></span>',
                iconCancel: '<span class="icon iconCancel cancel"></span>',
                step: 1,
                oldValue: null,
                valuePrecision: 2,
                messageDisplay: null,
                timer: null
            },
            
            // Set up the widget
            _create: function() {
                this.options.inputElem = this.element.find('input');
                this.options.step      = this.options.inputElem.data('step');
                
                // Fetch input border and background styles
                this.options.border     = this.options.inputElem.css('border');
                this.options.background = this.options.inputElem.css('background');
                
                // Add up and down arrow and accpet and cancel button to the input element
                var iconUp     = $(this.options.iconUp);
                var iconDown   = $(this.options.iconDown);
//                var iconAccept = $(this.options.iconAccept);
//                var iconCancel = $(this.options.iconCancel);
                
                // First remove all icons to not add them twice, and then add them again.
                iconUp.insertAfter(this.element.find('.elementWrapper'));
                iconDown.insertAfter(iconUp);
//                iconAccept.insertAfter(iconDown);
//                iconCancel.insertAfter(iconAccept);
                
                // Set input styles
                this.element.css({
                    'width': this.element.find('.elementWrapper').width() + 36,
                    'borderWidth': '1px',
                    'borderColor': 'transparent',
                    'borderStyle': 'solid'
                });
                
                this.options.inputElem.css({
                    'height': (16 - parseInt(this.options.inputElem.css('paddingTop')) - parseInt(this.options.inputElem.css('paddingBottom')))
                });
                
                // Set icon styles
                this.element.find('.elementWrapper').siblings('.icon').each(function() {
                    var backgroundImage = $(this).css('backgroundImage');
                    var backgroundPosition = $(this).css('backgroundPosition');
                    var backgroundRepeat = $(this).css('backgroundRepeat');
                    $(this)
                    .attr('style', 'background: ' + backgroundImage + ' ' + backgroundRepeat + ' ' + backgroundPosition + ', -webkit-linear-gradient(top, white, #9FBFD2); '
                        + 'background:' + backgroundImage + ' ' + backgroundRepeat + ' ' + backgroundPosition + ', -moz-linear-gradient(top, white, #9FBFD2); '
                        + 'background:' + backgroundImage + ' ' + backgroundRepeat + ' ' + backgroundPosition + ', -o-linear-gradient(top, white, #9FBFD2); '
                        + 'background:' + backgroundImage + ' ' + backgroundRepeat + ' ' + backgroundPosition + ', -ms-linear-gradient(top, white, #9FBFD2); '
                        + 'background:' + backgroundImage + ' ' + backgroundRepeat + ' ' + backgroundPosition + ', linear-gradient(top, white, #9FBFD2); '
                    );
                });
                this.element.find('.elementWrapper').siblings('.icon').css({
                    'position': 'relative',
                    'left': '2px',
                    'margin': '2px 0',
                    'width': '16px',
                    'height': '15px',
                    'border': this.options.border,
                    'border-left-width': '0px'
                });
                this.element.find('.elementWrapper').siblings('.icon').first().css({
                    'border-left-width': '1px'
                });
                this.element.find('.elementWrapper').css({
                    'margin': '0',
                    'paddingRight': 0
                })
                
                // First disable the input element and reemove all borders and background color, to let it look like normal content.
                this._blur();
                
                this._bindEventHandlers();
                
                // Save old value
                this.options.oldValue = parseFloat(this.options.inputElem.val().replace(/,/, '.'));
                
                // Create timer
                var self = this;
                this.options.timer = $.timer(function() {
                    self._accept();
                    self.options.timer.stop();
                }, 1000, false);
            },
            
            // Bind event handlers to the element
            _bindEventHandlers: function() {
                // Element events.
                this._on(this.element.find('input'), {
                    click: '_focus'
                });
                
                // Accept and cancel events
                this._on(this.element.find('.accept'), {
                    click: '_accept'
                });
                this._on(this.element.find('.cancel'), {
                    click: '_cancel'
                });
                
                // Keydown event
                this._on(this.element.find('input'), {
                    keydown: function(event) {
                        var code = (event.keyCode ? event.keyCode : event.which);
                        if (code == 13) { this._accept(event); }
                        if (code == 27) { this._cancel(event); }
                    }
                });
                
                // Arrow event
                this._on(this.element.find('.arrowUp'), {
                    click: '_increase'
                });
                this._on(this.element.find('.arrowDown'), {
                    click: '_decrease'
                });
                
                // Highlighting element as editable when its in a table row.
                this._on(this.element.closest('tr'), {
                    mouseenter: function() {
                        this.options.inputElem.not(':focus').closest('#' + this.element.attr('id')).css({
                            'borderColor': '#aaa'
                        });
                    },
                    mouseleave: function() {
                        this.options.inputElem.not(':focus').closest('#' + this.element.attr('id')).css({
                            'borderColor': 'transparent'
                        });
                    }
                });
            },
            
            
            _focus: function(event) {
                // Save current value
                this.options.oldValue = this.options.inputElem.val();
                
                this.element.css({
                    'borderColor': 'transparent'
                }).find('.iconScriptEdit').remove();
                
                this.options.inputElem.css({
                    'border': this.options.border,
                    'background': this.options.background,
                    'cursor': 'text'
                });
                
                this.element.find('.elementWrapper').siblings('.icon').css({
                    'display': 'inline-block'
                });
                
                this.options.inputElem.prop('readonly', false);
                this.options.inputElem.focus();
                
                this._trigger('focus', event);
            },
            
            
            _blur: function(event) {
                // Validate if the inserted number fits to the defined step.
                step = true;
                if (step) {
                    this.options.inputElem.prop('readonly', true);
//                    this.element.find('.elementWrapper').siblings('.icon').css({
//                        'display': 'none'
//                    });
                    
                    this.options.inputElem.css({
                        'borderColor': 'transparent',
                        'background': 'transparent none',
                        'cursor': 'default'
                    });
                    
                    this.options.inputElem.blur();
                }
                else {
                    this._focus();
                }
                
                this._trigger('blur', event);
            },
            
            /*
             * Increases the field value by the given step.
             */
            _increase: function(event) {
                this.options.timer.reset().play();
                
                var newValue = (parseFloat(this.options.inputElem.val().replace(/,/, '.')) + parseFloat(this.options.step)).toFixed(this.options.valuePrecision);
                this.options.inputElem.val(newValue.replace(/\./, ','));
                
                this._trigger('increase', event);
            },
            
            /*
             * Decreases the field value by the given step.
             */
            _decrease: function(event) {
                this.options.timer.reset().play();
                
                var newValue = (parseFloat(this.options.inputElem.val().replace(/,/, '.')) - parseFloat(this.options.step)).toFixed(this.options.valuePrecision);
                this.options.inputElem.val(newValue.replace(/\./, ','));
                
                this._trigger('decrease', event);
            },
            
            /*
             * Accept the new value and send it to the server. If the server returns an error, the field will be canceled.
             */
            _accept: function(event) {
                this._blur();
                
                // Fetch the pre-defined request data and add additional data.
                var requestData             = this.options.inputElem.data('data');
                requestData.aData.sParam    = this.element.data('key');
                requestData.aData.sNewValue = parseFloat(this.options.inputElem.val().replace(/,/, '.'));
                requestData.aData.sOldValue = this.options.oldValue;
                
                var self = this;
                
                self.options.messageDisplay.messageDisplay('reset');
                
                $.ajax({
                    type: 'POST',
                    url: 'http://' + window.location.hostname + '/ajax/updateModel/',
                    data: requestData,
                    dataType: 'json'
                }).done(function(data, status, jqXHR) {
                    // If the request was successfull, but the new value could not be set, reset the old value.
                    if (data.error != undefined) {
                        self._cancel();
                    }
                    
                    // Save new value as old value
                    self.options.oldValue = requestData.aData.sNewValue;
                    
                    self._displayReturnMessages(data);
                }).fail(function(jqXHR, status, errorThrown) {
                    self._cancel();
                    
                    self._displayReturnMessages({
                        'error': {
                            'requestError': 'Während der Kommunikation mit dem Server ist ein Fehler aufgetreten. Bitte wiederhole Deine Aktion. Sollte der Fehler wieder auftreten, wende Dich bitte an die IT.'
                        }
                    });
                });
                
                this._trigger('accept', event);
            },
            
            /*
             * Canceling the input field, and reset the old value.
             */
            _cancel: function(event) {
                this.options.inputElem.val(this.options.oldValue);
                this._trigger('cancel', event);
                this._blur();
            },
            
            /*
             * Adds messages to the message display.
             */
            _displayReturnMessages: function(data) {
                // Display error messages
                if (data.error != undefined) {
                    for (key in data.error) {
                        this.options.messageDisplay.messageDisplay('add', 'error', key, data.error[key]);
                    }
                }
                // Display success messages
                if (data.success != undefined) {
                    for (key in data.success) {
                        this.options.messageDisplay.messageDisplay('add', 'success', key, data.success[key]);
                    }
                }
                // Display info messages
                if (data.info != undefined) {
                    for (key in data.info) {
                        this.options.messageDisplay.messageDisplay('add', 'info', key, data.info[key]);
                    }
                }
            },
            
            // Use the _setOption method to respond to changes to options
            _setOption: function( key, value ) {
                switch( key ) {
                    case "clear":
                        // handle changes to clear option
                        break;
                }

                // In jQuery UI 1.8, you have to manually invoke the _setOption method from the base widget
                //              $.Widget.prototype._setOption.apply( this, arguments );
                // In jQuery UI 1.9 and above, you use the _super method instead
                this._super( "_setOption", key, value );
            },

            // Use the destroy method to clean up any modifications your widget has made to the DOM
            destroy: function() {
            // In jQuery UI 1.8, you must invoke the destroy method from the base widget
            //              $.Widget.prototype.destroy.call( this );
            // In jQuery UI 1.9 and above, you would define _destroy instead of destroy and not call the base method
            }
        });
    }( jQuery ) );
    
    
    /*
     * This widget provides the functionality for the form field "ajaxtext".
     */
    (function( $ ) {
        $.widget( "adzlocal.ajaxFormText", {

            // These options will be used as defaults
            options: { 
                clear: null,
                border: null,
                background: null,
                inputElem: null,
                oldValue: null,
                messageDisplay: null,
                timer: null
            },
            
            // Set up the widget
            _create: function() {
                this.options.inputElem = this.element.find('input');
                this.options.step      = this.options.inputElem.data('step');
                
                // Fetch input border and background styles
                this.options.border     = this.options.inputElem.css('border');
                this.options.background = this.options.inputElem.css('background');
                
                // Set input styles
                this.element.css({
                    'width': this.element.find('.elementWrapper').width() + 36,
                    'borderWidth': '1px',
                    'borderColor': 'transparent',
                    'borderStyle': 'solid'
                });
                
                this.options.inputElem.css({
                    'height': (19 - parseInt(this.options.inputElem.css('paddingTop')) - parseInt(this.options.inputElem.css('paddingBottom')))
                });
                
                // Set icon styles
                this.element.find('.elementWrapper').siblings('.icon').each(function() {
                    var backgroundImage = $(this).css('backgroundImage');
                    var backgroundPosition = $(this).css('backgroundPosition');
                    var backgroundRepeat = $(this).css('backgroundRepeat');
                    $(this)
                    .attr('style', 'background: ' + backgroundImage + ' ' + backgroundRepeat + ' ' + backgroundPosition + ', -webkit-linear-gradient(top, white, #9FBFD2); '
                        + 'background:' + backgroundImage + ' ' + backgroundRepeat + ' ' + backgroundPosition + ', -moz-linear-gradient(top, white, #9FBFD2); '
                        + 'background:' + backgroundImage + ' ' + backgroundRepeat + ' ' + backgroundPosition + ', -o-linear-gradient(top, white, #9FBFD2); '
                        + 'background:' + backgroundImage + ' ' + backgroundRepeat + ' ' + backgroundPosition + ', -ms-linear-gradient(top, white, #9FBFD2); '
                        + 'background:' + backgroundImage + ' ' + backgroundRepeat + ' ' + backgroundPosition + ', linear-gradient(top, white, #9FBFD2); '
                    );
                });
                this.element.find('.elementWrapper').siblings('.icon').css({
                    'position': 'relative',
                    'left': '2px',
                    'margin': '2px 0',
                    'width': '16px',
                    'height': '15px',
                    'border': this.options.border,
                    'border-left-width': '0px'
                });
                this.element.find('.elementWrapper').siblings('.icon').first().css({
                    'border-left-width': '1px'
                });
                this.element.find('.elementWrapper').css({
                    'margin': '0',
                    'paddingRight': 0
                })
                
                // First disable the input element and reemove all borders and background color, to let it look like normal content.
                this._blur();
                
                this._bindEventHandlers();
                
                // Save old value
                this.options.oldValue = this.options.inputElem.val();
                
                // Create timer
                var self = this;
                this.options.timer = $.timer(function() {
                    self._accept();
                    self.options.timer.stop();
                }, 1000, false);
            },
            
            // Bind event handlers to the element
            _bindEventHandlers: function() {
                // Element events.
                this._on(this.element.find('input'), {
                    click: '_focus'
                });
                
                // Accept and cancel events
                this._on(this.element.find('.accept'), {
                    click: '_accept'
                });
                this._on(this.element.find('.cancel'), {
                    click: '_cancel'
                });
                
                // Keydown event
                this._on(this.element.find('input'), {
                    keydown: function(event) {
                    	this.options.timer.reset().play();
                        var code = (event.keyCode ? event.keyCode : event.which);
                        if (code == 13) { this._accept(event); }
                        if (code == 27) { this._cancel(event); }
                    }
                });
                
                // Highlighting element as editable when its in a table row.
                this._on(this.element.closest('td'), {
                    mouseenter: function() {
                        this.options.inputElem.not(':focus').closest('#' + this.element.attr('id')).css({
                            'borderColor': '#aaa'
                        });
                    },
                    mouseleave: function() {
                        this.options.inputElem.not(':focus').closest('#' + this.element.attr('id')).css({
                            'borderColor': 'transparent'
                        });
                    }
                });
            },
            
            
            _focus: function(event) {
                // Save current value
                this.options.oldValue = this.options.inputElem.val();
                
                this.element.css({
                    'borderColor': 'transparent'
                }).find('.iconScriptEdit').remove();
                
                this.options.inputElem.css({
                    'border': this.options.border,
                    'background': this.options.background,
                    'cursor': 'text'
                });
                
                this.element.find('.elementWrapper').siblings('.icon').css({
                    'display': 'inline-block'
                });
                
                this.options.inputElem.prop('readonly', false);
                this.options.inputElem.focus();
                
                this._trigger('focus', event);
            },
            
            
            _blur: function(event) {
                // Validate if the inserted number fits to the defined step.
                step = true;
                if (step) {
                    this.options.inputElem.prop('readonly', true);
                    
                    this.options.inputElem.css({
                        'borderColor': 'transparent',
                        'background': 'transparent none',
                        'cursor': 'default'
                    });
                    
                    this.options.inputElem.blur();
                }
                else {
                    this._focus();
                }
                
                this._trigger('blur', event);
            },
            
            /*
             * Accept the new value and send it to the server. If the server returns an error, the field will be canceled.
             */
            _accept: function(event) {
                this._blur();
                
                // Fetch the pre-defined request data and add additional data.
                var requestData             = this.options.inputElem.data('data');
                requestData.aData.sParam    = this.element.data('key');
                requestData.aData.sNewValue = this.options.inputElem.val();
                requestData.aData.sOldValue = this.options.oldValue;
                
                var self = this;
                
                self.options.messageDisplay.messageDisplay('reset');
                
                $.ajax({
                    type: 'POST',
                    url: 'http://' + window.location.hostname + '/ajax/updateModel/',
                    data: requestData,
                    dataType: 'json'
                }).done(function(data, status, jqXHR) {
                    // If the request was successfull, but the new value could not be set, reset the old value.
                    if (data.error != undefined) {
                        self._cancel();
                    }
                    
                    // Save new value as old value
                    self.options.oldValue = requestData.aData.sNewValue;
                    
                    self._displayReturnMessages(data);
                }).fail(function(jqXHR, status, errorThrown) {
                    self._cancel();
                    
                    self._displayReturnMessages({
                        'error': {
                            'requestError': 'Während der Änderung ist ein Fehler aufgetreten. Bitte wiederhole Deine Aktion. Sollte der Fehler wieder auftreten, wende Dich bitte an die IT.'
                        }
                    });
                });
                
                this._trigger('accept', event);
            },
            
            /*
             * Canceling the input field, and reset the old value.
             */
            _cancel: function(event) {
                this.options.inputElem.val(this.options.oldValue);
                this._trigger('cancel', event);
                this._blur();
            },
            
            /*
             * Adds messages to the message display.
             */
            _displayReturnMessages: function(data) {
                // Display error messages
                if (data.error != undefined) {
                    for (key in data.error) {
                        this.options.messageDisplay.messageDisplay('add', 'error', key, data.error[key]);
                    }
                }
                // Display success messages
                if (data.success != undefined) {
                    for (key in data.success) {
                        this.options.messageDisplay.messageDisplay('add', 'success', key, data.success[key]);
                    }
                }
                // Display info messages
                if (data.info != undefined) {
                    for (key in data.info) {
                        this.options.messageDisplay.messageDisplay('add', 'info', key, data.info[key]);
                    }
                }
            },
            
            // Use the _setOption method to respond to changes to options
            _setOption: function( key, value ) {
                switch( key ) {
                    case "clear":
                        // handle changes to clear option
                        break;
                }

                // In jQuery UI 1.8, you have to manually invoke the _setOption method from the base widget
                //              $.Widget.prototype._setOption.apply( this, arguments );
                // In jQuery UI 1.9 and above, you use the _super method instead
                this._super( "_setOption", key, value );
            },

            // Use the destroy method to clean up any modifications your widget has made to the DOM
            destroy: function() {
            // In jQuery UI 1.8, you must invoke the destroy method from the base widget
            //              $.Widget.prototype.destroy.call( this );
            // In jQuery UI 1.9 and above, you would define _destroy instead of destroy and not call the base method
            }
        });
    }( jQuery ) );
    
    /*
     * This widget provides the client side functionality for status update form fields.
     */
    (function( $ ) {
        $.widget( "adzlocal.ajaxFormUniversal", {

            // These options will be used as defaults
            options: {
                'confirmMessage': false
            },
            
            // Set up the widget
            _create: function() {
                if (this.element.data('confirm-message') != undefined) {
                    this.options.confirmMessage = this.element.data('confirm-message');
                }
                
                // Put the label into an anchor tag to let it occure linke a link
                var label = this.element.find('label');
                label.replaceWith($('<a>').append(label.clone()));
                
                this.options.labelWrapper = this.element.find('.labelWrapper');
                
                // Remove labelWrapper size
                this.options.labelWrapper.css({
                    'width': 'auto'
                });
                
                // Remove field background color
                this.element.closest('.field').css({
                    'display': 'inline-block',
                    'margin': '0',
                    'padding': '0',
                    'background': 'transparent none'
                });
                
                // Remove padding
                this.options.labelWrapper.css({
                    'margin': '0',
                    'padding': '0'
                });
                this.options.labelWrapper.find('label').css({
                    'margin': '0',
                    'padding': '0',
                    'font-weight': 'normal',
                    'text-decoration': 'underline',
                    'cursor': 'pointer'
                });
                
                this._bindEventHandlers();
            },
            
            // Bind event handlers to the element
            _bindEventHandlers: function() {
                // Add click events to selectable items
                this._on(this.element.find('label'), {
                    click: '_submit'
                });
            },
            
            // Sends the new value to the server.
            _submit: function(event) {
                // Fetch the pre-defined request data and add additional data.
                var requestData                   = this.element.data('data');
                var self = this;
                self.options.messageDisplay.messageDisplay('reset');
                
                // Check if the element is part of a form as a submit button. If yes, fetch all form values and add them to the request data.
                if (this.element.find('input')[0] != undefined && this.element.find('input')[0].form != undefined) {
                    var formData = $(this.element.find('input')[0].form).serializeArray();
                    for (i = 0; i < formData.length; i++) {
                        requestData[formData[i].name] = formData[i].value;
                    }
                }

                if (this.options.confirmMessage === false || confirm(this.options.confirmMessage)) {
                    $.ajax({
                        type: 'POST',
                        url: 'http://' + window.location.hostname + '/ajax/updateModel/',
                        data: requestData,
                        dataType: 'json'
                    }).success(function(data, status, jqXHR) {
                        if (data.error == undefined || data.error.length == 0) {
                            var triggerName = 'success';
                        }
                        else {
                            var triggerName = 'error';
                        }
                        
                        self._trigger(triggerName, event, { data: requestData.aData, responseData: data });
                        self._displayReturnMessages(data);
                    }).fail(function(jqXHR, status, errorThrown) {
                        self._displayReturnMessages({
                            'error': {
                                'requestError': 'Während der Kommunikation mit dem Server ist ein Fehler aufgetreten. Bitte wiederhole Deine Aktion. Sollte der Fehler wieder auftreten, wende Dich bitte an die IT.'
                            }
                        });
                    });
                }
            },
            
            /*
             * Adds messages to the message display.
             */
            _displayReturnMessages: function(data) {
                // Display error messages
                if (data.error != undefined) {
                    for (key in data.error) {
                        this.options.messageDisplay.messageDisplay('add', 'error', key, data.error[key]);
                    }
                }
                // Display success messages
                if (data.success != undefined) {
                    for (key in data.success) {
                        this.options.messageDisplay.messageDisplay('add', 'success', key, data.success[key]);
                    }
                }
                // Display info messages
                if (data.info != undefined) {
                    for (key in data.info) {
                        this.options.messageDisplay.messageDisplay('add', 'info', key, data.info[key]);
                    }
                }
            },
            
            // Use the _setOption method to respond to changes to options
            _setOption: function( key, value ) {
                switch( key ) {
                    case "clear":
                        // handle changes to clear option
                        break;
                }

                // In jQuery UI 1.8, you have to manually invoke the _setOption method from the base widget
                //              $.Widget.prototype._setOption.apply( this, arguments );
                // In jQuery UI 1.9 and above, you use the _super method instead
                this._super( "_setOption", key, value );
            },

            // Use the destroy method to clean up any modifications your widget has made to the DOM
            destroy: function() {
            // In jQuery UI 1.8, you must invoke the destroy method from the base widget
            //              $.Widget.prototype.destroy.call( this );
            // In jQuery UI 1.9 and above, you would define _destroy instead of destroy and not call the base method
            }
        });
    }( jQuery ) );
    
    /**
     * <p>This widget gives the ability to edit text on page.</p>
     */
    (function( $ ) {
        $.widget("adzlocal.ajaxFormTextarea", {
        
            options: {
                messageDisplay: null,
                confirmMessage: false
            },
            
            _create: function() {
                // Mark element as loaded
                this.element.addClass('active');
                this.canceled = false;
                
                this.elementWrapper = this.element.find('.elementWrapper');
                
                // Fetch data from paragraph and remove paragraph
                var paragraph = this.elementWrapper.find('p');
                var width     = this.element.closest('.field').width();
                var height    = paragraph.height();
                this.id       = paragraph.attr('id');
                this.oldText  = paragraph.text();
                this.data     = paragraph.data();
                this.elementWrapper.find('p').remove();
                
                // Create textare and add data to it
                var textarea = $('<textarea>')
                    .attr('id', this.id)
                    .data(this.data)
                    .css({ 'width': width - 2, 'height': height + 30, 'border-width': '1px' })
                    .val(this.oldText);
                
                this.elementWrapper.append(textarea);
                //move cursor to end of text
                textarea.focus();
                tmpStr = textarea.val();
                textarea.val('').val(tmpStr);

                var textareaPosition = textarea.position();
                
                // Add confirm and cancel buttons
                this.elementButtons = $('<div>').addClass('elementButtons');
                var confirmButton  = $('<span>').addClass('icon iconTick');
                var cancelButton   = $('<span>').addClass('icon iconCancel');
                this.elementButtons.append(confirmButton);
                this.elementButtons.append(cancelButton);
                
                this.elementButtons
                    .css({
                        'position': 'absolute',
                        'top': textareaPosition.top + height + 31,
                        'left': textareaPosition.left + width - (this.elementButtons.children().length * 18),
                        'width': (this.elementButtons.children().length * 18)
                    });
                
                this.elementButtons.insertAfter(textarea);
                textarea.focus();
                
                this._bindEventhandlers();
                
                this._trigger('create', this);
            },
            
            _destroy: function(event) {
                // Fetch data from paragraph and remove paragraph
                var textarea = this.element.find('textarea');
                var newText  = textarea.val();

                // If the text has change, send it to the server
                if (this.canceled == false && this.oldText != newText) {
                    this._submit(event, newText);
                }
                
                // Remove textarea and add icons
                textarea.remove();
                this.element.find('.icon').remove();
                
                // Create and add paragraph
                var paragraph = $('<p>')
                    .attr('id', this.id)
                    .data(this.data)
                    .text(newText);
                this.elementWrapper.append(paragraph);
                
                // Remove active flag
                this.element.removeClass('active');
                
                this._trigger('destroy', this);
            },
            
            _submit: function(event, newText) {
                // Fetch the pre-defined request data and add additional data.
                var requestData           = this.data.data;
                requestData.aData.newText = newText;
                var self                  = this;
                self.options.messageDisplay.messageDisplay('reset');
                
                if (this.options.confirmMessage === false || confirm(this.options.confirmMessage)) {
                    $.ajax({
                        type: 'POST',
                        url: 'http://' + window.location.hostname + '/ajax/updateModel/',
                        data: requestData,
                        dataType: 'json'
                    }).success(function(data, status, jqXHR) {
                        if (data.error == undefined || data.error.length == 0) {
                            var triggerName = 'success';
                        }
                        else {
                            var triggerName = 'error';
                            // Restore old text
                            self.element.find('p').text(self.oldText);
                        }
                        
                        self._trigger(triggerName, event, { data: requestData.aData, responseData: data });
                        self._displayReturnMessages(data);
                    }).fail(function(jqXHR, status, errorThrown) {
                        // Restore old text
                        self.element.find('p').text(self.oldText);
                        
                        self._displayReturnMessages({
                            'error': {
                                'requestError': 'Während der Kommunikation mit dem Server ist ein Fehler aufgetreten. Bitte wiederhole Deine Aktion. Sollte der Fehler wieder auftreten, wende Dich bitte an die IT.'
                            }
                        });
                    });
                }
                
                this._trigger('submit', this);
            },
            
            _bindEventhandlers: function() {
                // Destroy widget on click in body
                this._on(this.element.closest('body'), {
                    click: function() {
                        this.destroy();
                    }
                });
                
                // Stop propagation of click when it is within the element
                this._on(this.element, {
                    click: function(event) {
                        event.stopPropagation();
                    }
                });
                
                // Cancel button event
                this._on(this.element.find('.iconCancel'), {
                    click: function(event) {
                        this.canceled = true;
                        this.destroy(event);
                    }
                });
                
                // Accept button event
                this._on(this.element.find('.iconTick'), {
                    click: function(event) {
                        this.destroy(event);
                    }
                });
                
                // Reposition eleent buttons on textarea resize
                this._on(this.element.find('textarea'), {
                	mousedown: '_initiateTexareaResize',
                	mouseup: '_validateTextareaResize'
                });
            },
            
            _initiateTexareaResize: function(event) {
            	this.textareaWidth = $(event.target).width();
            	this.textareaHeight = $(event.target).height();
            	this.elementButtons.fadeOut('fast');
            },
            
            _validateTextareaResize: function(event) {
            	var textarea = $(event.target);
            	newWidth = textarea.width();
            	newHeight = textarea.height();
            	
            	if (newWidth != this.textareaWidth || newHeight != this.textareaHeight) {
            		var textareaPosition = textarea.position();
                    
            		this.elementButtons
                        .css({
                            'top': textareaPosition.top + textarea.height() + 1,
                            'left': textareaPosition.left + textarea.width() - (this.elementButtons.children().length * 18) + 2,
                        });
            	}
            	
            	this.elementButtons.fadeIn('fast');
            },
            
            /*
             * Adds messages to the message display.
             */
            _displayReturnMessages: function(data) {
                // Display error messages
                if (data.error != undefined) {
                    for (key in data.error) {
                        this.options.messageDisplay.messageDisplay('add', 'error', key, data.error[key]);
                    }
                }
                // Display success messages
                if (data.success != undefined) {
                    for (key in data.success) {
                        this.options.messageDisplay.messageDisplay('add', 'success', key, data.success[key]);
                    }
                }
                // Display info messages
                if (data.info != undefined) {
                    for (key in data.info) {
                        this.options.messageDisplay.messageDisplay('add', 'info', key, data.info[key]);
                    }
                }
            },
            
            // Use the _setOption method to respond to changes to options
            _setOption: function( key, value ) {
                switch( key ) {
                    case "clear":
                        // handle changes to clear option
                        break;
                }

                this._super( "_setOption", key, value );
            }
        });
    }( jQuery ) );
    
});