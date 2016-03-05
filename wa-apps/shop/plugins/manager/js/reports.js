$(function () {
    $.reports.managersAction = function () {
        $("#reportscontent").load('?plugin=manager&module=reports'+this.getTimeframeParams());
    }
});