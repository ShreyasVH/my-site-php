$(document).ready(function() {

    $(document).on('change', 'select[name="cardType"]', function() {
        var cardTypeId = $(this).val();

        var cardTypeElement = $(this).find('option[value="' + cardTypeId + '"]')[0];

        var cardType = $(cardTypeElement).text().trim();
        cardActions.toggleAdditionalFields(cardType);
    });

    $(document).on('click', '.jsObtainCard', function(e) {
        var cardId = $(this).attr('data-cardId');
        var modal = $('#obtain_card');
        var cardIdInput = modal.find('input[name="cardId"]');
        cardIdInput.val(cardId);
        modal.modal('show');
    });

    $(document).on('click', '.jsAddVersion', function(e) {
        var cardName = $(this).attr('data-cardName');
        var cardId = $(this).attr('data-cardId');
        var modal = $('#version_card');
        var cardNameInput = modal.find('input[name="cardName"]');
        var cardIdInput = modal.find('input[name="cardId"]');
        cardNameInput.val(cardName);
        cardIdInput.val(cardId);
        modal.modal('show');
    });

    $(document).on('submit', 'form[name="obtain-card-form"]', function(e) {
        e.preventDefault();
        if($(this).find('.error').length == 0)
        {
            var formdata = new FormData($(this)[0]);
            $.ajax({
                type : 'POST',
                url : '/cards/obtain',
                data : formdata,
                cache: false,
                processData: false,
                contentType: false,
                success : function(data)
                {
                    if(data.success) {
                        notifyActions.success('Card Obtained Successfully');
                        $('#obtain_card').modal('hide');
                        cardActions.obtainActions.resetObtainPopup();
                    } else {
                        notifyActions.danger('Error obtaining card. Message: ' + data.response);
                    }
                }
            });
        }
    });

    $(document).on('submit', 'form[name="add-version-form"]', function(e) {
        e.preventDefault();
        var form = $(this);
        if($(this).find('.empty').length == 0)
        {
            var formdata = new FormData($(this)[0]);
            $.ajax({
                type : 'POST',
                url : '/cards/version',
                data : formdata,
                cache: false,
                processData: false,
                contentType: false,
                success : function(data)
                {
                    if(data.success) {
                        notifyActions.success('Updated card version Successfully');
                        $('#version_card').modal('hide');
                        cardActions.versionActions.resetVersionPopup();
                        var cardIdElement = form.find('input[name="cardId"]');
                        var cardId = cardIdElement.val();
                        var previousCardElement = $('.jsCard[data-id="' + cardId + '"]');
                        previousCardElement.after(data.cardHtml);
                    } else {
                        notifyActions.danger('Error updating card version. Message: ' + data.response);
                    }
                }
            });
        }
    });
});

var cardActions = {
    toggleAdditionalFields: function(cardType) {
        if ('MONSTER' === cardType) {
            $('input[name="level"]').closest('.form-field').show();
            $('select[name="attribute"]').closest('.form-field').show();
            $('select[name="type"]').closest('.form-field').show();
            $('input[name="attack"]').closest('.form-field').show();
            $('input[name="defense"]').closest('.form-field').show();
        } else {
            $('input[name="level"]').closest('.form-field').hide();
            $('select[name="attribute"]').closest('.form-field').hide();
            $('select[name="type"]').closest('.form-field').hide();
            $('input[name="attack"]').closest('.form-field').hide();
            $('input[name="defense"]').closest('.form-field').hide();
        }
    },

    getMyCards: function(cardId, imageUrl) {
        $.ajax({
            url: '/cards/myCards',
            type: 'POST',
            data: {
                cardId: cardId,
                imageUrl: imageUrl
            },
            cache: false,
            success: function(result) {
                if (result.view) {
                    $('#my_cards').html(result.view);
                }

            }
        });
    },

    getSourcesByCard: function(cardId) {
        $.ajax({
            url: '/cards/sourcesByCard?cardId=' + cardId,
            type: 'GET',
            cache: false,
            success: function(result) {
                if (result.view) {
                    $('#sources').html(result.view);
                }

            }
        });
    },

    obtainActions: {
        resetObtainPopup: function() {
            var modal = $('#obtain_card');
            var selectElement = modal.find('select');
            selectElement.val('default');
        }
    },

    versionActions: {
        resetVersionPopup: function() {
            var modal = $('#version_card');
            var fileElement = modal.find('input[type="file"]');
            fileElement.val('');
            modal.find('.jsFileText').html('No file chosen');
            modal.find('.jsUploadText').html('Upload');
        }
    },
};