var Evozon = Evozon || {};

Evozon.Restrictions = (function ($) {

    this.toggleMessageBlock = function(event) {
        var params      = arguments[1],
            element     = event.element()
            displayList = false;

        element.up('li').select('.restriction-message-block').first().hide();

        if (params.expected == undefined) {
            return;
        }

        if (params.expected == element.value) {
            element.up('li').select('.restriction-message-block').first().show();
        }
    };

    return {
        toggleMessageBlock : toggleMessageBlock
    };
})($$);

