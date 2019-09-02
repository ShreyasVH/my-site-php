$(document).ready(function() {

    $(document).on('change', 'select[name="cardType"]', function() {
        var cardTypeId = $(this).val();

        var cardTypeElement = $(this).find('option[value="' + cardTypeId + '"]')[0];

        var cardType = $(cardTypeElement).text().trim();
        cardActions.toggleAdditionalFields(cardType);
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
    }
};