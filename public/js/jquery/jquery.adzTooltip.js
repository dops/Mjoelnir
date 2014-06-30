/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function() {
    $('.tooltip').mouseover(function() {
        var tooltipIconId       = $(this).attr('id');
        var tooltipIconPosition = $(this).position();
        var tooltipIconWidth = $(this).width();
        var tolltipContentId    = tooltipIconId + 'Content';
        
        $('#' + tolltipContentId).css({
            'position': 'absolute',
            'top': tooltipIconPosition.top,
            'left': tooltipIconPosition.left + tooltipIconWidth + 10
        });
        
        $('#' + tolltipContentId).show();
    })
    .mouseout(function() {
        var tooltipIconId       = this.id;
        var tolltipContentId    = tooltipIconId + 'Content';
        
        $('#' + tolltipContentId).hide();
    });
});