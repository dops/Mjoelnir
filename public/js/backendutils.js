function setFormValue(frmName, fldName, value, submitFrm, event, hash, frmAction) {
	// Check weather fldName and value are arrays or not
    if (
        (typeof fldName == 'object' && typeof value != 'object')
        || (typeof fldName != 'object' && typeof value == 'object')
        || (typeof fldName == 'object' && typeof value == 'object' && fldName.length != value.length)
    ) {
//    if (typeof fldName != typeof value || (typeof fldName == 'object' && fldName.length != value.length)) {
        console.error('Filter field and filter value are not from the same type or have different numbers of entities.');
    }
    else if (typeof fldName != 'object' && typeof value != 'object') {
        tmp = String(fldName);
        fldName = new Array(tmp);
        tmp = String(value);
        value = new Array(tmp);
    }
    
    var numFields = fldName.length;

    //check if given form already exists - if not, create it now
    if (document.getElementById(frmName) === null) {
    	var form = document.createElement('form');
    	form.setAttribute('id', frmName);
    	form.setAttribute('name', frmName);
    	form.setAttribute('method', 'post');
    	form.setAttribute('action', document.URL); //document.referrer?
    	document.getElementsByTagName('body')[0].appendChild(form);
    	
    	fld = document.createElement('input');
        fld.setAttribute('type', 'hidden');
        fld.setAttribute('name', 'filterForm');
        fld.setAttribute('value', frmName);
        form.appendChild(fld);
    }

    var frm = document.forms[frmName];
    
    if (frmAction != undefined) {
    	frm.setAttribute('action', frmAction); //custom action given
	}
    
    
    for (i = 0; i < numFields; i++) {
        // Remove page parameter on filter submit
        if (fldName[i] == '_submitFilters') {
            if (frm.elements['p']) {
                frm.elements['p'].setAttribute('value', '1');
            }
        }

        var fld = frm.elements[fldName[i]];
        
        if (fld == undefined && value[i] != '') {
    		var fld = document.createElement('input');
            fld.setAttribute('type', 'hidden');
            fld.setAttribute('name', fldName[i]);
            fld.setAttribute('value', value[i]);
            
            frm.appendChild(fld);
            
            if (fldName[i] == 'order') {
                orderDirFld = document.createElement('input');
                orderDirFld.setAttribute('type', 'hidden');
                orderDirFld.setAttribute('name', 'orderDir');
                orderDirFld.setAttribute('value', '');
                
                frm.appendChild(orderDirFld);
            }
        }
        else {
            // Remove field if value is empty
        	if (fld != undefined) {
        		if (value[i] == '') {
		        	console.log('löschen');
		        	frm.removeChild(fld);
		        }
		        else {
		            fld.setAttribute('value', value[i]);
		        }
        	}
        }
        
        // On order, update direction
        if (fldName[i] == 'order' && value[i] != '') {
            if (fld.value == value[i]) {
                frm.elements['orderDir'].value = (frm.elements['orderDir'].value == 'ASC' ? 'DESC' : 'ASC');
            }
        }
    }
    
    // Submit the form
    if (submitFrm == true) {
    	
    	 if (typeof event !== 'undefined' && event.ctrlKey == true) {
    		 frm.setAttribute('target', '_blank');
    		 frm.submit();
    		 return false; //prevents aditionally opening empty javascript:void(0) tab 
         }
    	 else {
    		 if (frm.hasAttribute('target')) {
        		 frm.removeAttribute('target');
    		 }
    	 }
    	
        // Add hash if given
        if (hash != undefined) {
            frm.action = frm.action + '#' + hash;
        }
        
        frm.submit();
        return false;
    }
    return false;
}

function removeElement(elementId){
	
    if (document.getElementById(elementId)) {     
    	var parent = document.getElementById(elementId).parentNode;
        parent.removeChild(document.getElementById(elementId));
    }
    return true;
}

function scrollToAnchor(anchor){
    var aTag = $("a[name='"+ anchor +"']");
    $('html,body').scrollTop(aTag.offset().top); 
    //to animate, use sth like this:  $('html,body').animate({scrollTop: aTag.offset().top},0);
}

$(document).ready(function() {
    // Content area resize function.
    $.fn.resizePage = function() {
        // Get sidebar widths
        var sidebarLeftWidth = 0;
        if ($('#sidebarLeft').children().length > 0) {
            sidebarLeftWidth    = parseInt($('#sidebarLeft').css('width').replace('px', ''));
        }
        var sidebarRightWidth = 0;
        if ($('#sidebarRight').children().length > 0) {
            sidbarRightWidth    = parseInt($('#sidebarRight').css('width').replace('px', ''));
        }

        $('#main').width($('#main').width() - sidebarLeftWidth - sidebarRightWidth);
    }
    
    // Automatic fix of content width when sidebars occure on load and on resize.
    $(document).resizePage();
    
    // Branchenbuch Monitoring div switch
    $('ul.compareSelect a').click(function(event) {
        // Fetch parent li and its index
        var parentLi    = $(this).closest('li');
        var pos         = parentLi.index();
        
        // Remove all active classes from all li and div elements
        parentLi.parent().parent().find('li, div').removeClass('active');

        // Add active class to current li and corresponding div
        parentLi.addClass('active');
        parentLi.parent().siblings('.tabContent').find('div:nth-of-type(' + (pos + 1) + ')').addClass('active');
    });

    // Enter key observation for filterform submit
    $('.filter').keydown(function(event) {
        if (event.keyCode == 13) {
            var formName = $(this).data('filterform');
            var form = document.forms[formName];
            if (form != undefined) {
                form.submit();
            }
        }
    });
    
    // Select all checkbox
    $('input.selectAll').on('click', function(event) {
        $(document)
            .find('.' + $(this).data('selectallidentifier'))
            .prop('checked', $(this).prop('checked'));
    });
    
    // Extend subnavigation hight when sub nav appears
    $('#subNav').find('ul li').on('mouseenter', function(event) {
        if ($(this).find('ul').length > 0) {
            $('#subNav').css({
                'height': '40px'
            });
        }
    }).on('mouseleave', function(event) {
        if ($('#subNav').find('ul li li.active').length == 0) {
            $('#subNav').css({
                'height': '20px'
            });
            $('#subNav').find('ul li').each(function() {
                if (($(this).hasClass('active') && $(this).find('ul').length > 0) || $(this).find('ul').css('display') == 'block') {
                    $('#subNav').css({
                        'height': '40px'
                    });
                }
            });
        }
    });
    $('#subNav').find('ul li').each(function() {
        if (($(this).hasClass('active') && $(this).find('ul').length > 0) || $(this).find('ul li.active').length > 0) {
            $('#subNav').css({
                'height': '40px'
            });
        }
    });
    
});