var notifyActions = {
    success: function(message) {
        notifyActions.setMessage('Success', message, 'alert-success');
        notifyActions.showNotify();
    },

    info: function(message) {
        notifyActions.setMessage('Info', message, 'alert-info');
        notifyActions.showNotify();
    },

    danger: function(message) {
        notifyActions.setMessage('Danger', message, 'alert-danger');
        notifyActions.showNotify();
    },

    warning: function(message) {
        notifyActions.setMessage('Warning', message, 'alert-warning');
        notifyActions.showNotify();
    },

    setMessage: function(title, message, alertClass) {
        var modal = $("#notify_modal");
        var container = modal.find('#notify_content');
        var markup = '<div class="alert ' + alertClass + '" align="center"><strong>' + title + '!</strong> ' + message + '</div>';
        container.html(markup);
    },

    showNotify: function() {
        $("#notify_modal").modal('show');
        setTimeout(function() {
            notifyActions.hideNotify();
        }, 1000);
    },

    hideNotify: function() {
        $("#notify_modal").modal('hide')
    }
};