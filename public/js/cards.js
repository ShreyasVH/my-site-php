$(document).ready(function() {

    $(document).on('change', 'select[name="cardType"]', function() {
        var cardTypeId = $(this).val();

        var cardTypeElement = $(this).find('option[value="' + cardTypeId + '"]')[0];

        var cardType = $(cardTypeElement).text().trim();
        console.log(cardType);

        // if()
    });

});