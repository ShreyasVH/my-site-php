$(document).ready(function() {

	$(document).on('mouseenter', '.popup-close', function() {
		$(this).css('background', 'black');
		$(this).find('i').css('color', 'white');
	});

	$(document).on('mouseleave', '.popup-close', function() {
		$(this).css('background', 'white');
		$(this).find('i').css('color', 'black');

	});

	$(document).on("submit", "form", function(e){
	    if(!(forms.Validation.isformValid($(this))))
	    {
	    	e.preventDefault();
	    }
	});

	$(document).on('keydown', 'input.empty, textarea.empty', function() {
	    $(this).removeClass('empty');
	    $(this).closest('.form-field').find('.errorMsg').hide();
	});

	$(document).on('change', 'select.empty', function() {
		$(this).removeClass('empty');
	    $(this).closest('.form-field').find('.errorMsg').hide();
	});

	$(document).on('change', '.radio.empty input[type="radio"]', function() {
		$(this).closest('.radio').removeClass('empty');
	    $(this).closest('.form-field').find('.errorMsg').hide();
	});

	$(document).on('focus', '.form-field.error input, .form-field.error select', function(e) {
		$(this).closest('.form-field').removeClass('error');
	});

	$('.confirm').on('click', function(e) {
		if(!Miscellaneous.confirmMessage())
		{
			e.preventDefault();
		}
	});

	$(document).on('click', '.remove-movie', function(e) {
		e.preventDefault();

		if(Miscellaneous.confirmMessage())
		{
			var id = $(this).data('id');
			$.ajax({
				type : "GET",
				url : "/movies/removeMovie",
				data : {
					'id' : id
				},
				success : function(data) {
					if(data.success)
					{
						alert('Movie removed successfully');
						$('.jsMovieCard[data-id="' + id + '"]').remove();
					}
					else
					{
						alert('Failed to remove movie. Please try again');
					}
				}
			});
		}
	});

	$(document).on('click', '.jsRestoreMovie', function(e) {
		e.preventDefault();
		var movie_id = $(this).data('id');

		if(Miscellaneous.confirmMessage())
		{
			$.ajax({
				type : "POST",
				url : "/movies/restoreMovie",
				data : {
					'id' : movie_id
				},
				success : function (data)
				{
					if(data.success)
					{
						alert('Movie restored successfully');
						window.location.reload();
					}
					else
					{
						alert('Failed to restore movie. Please try again');
					}
				}
			});
		}
	});

	$(document).on('keydown', '#search_movie input[name="search"]', function(e) {
		if(e.which == 13)
		{
			$('#search_movie i.search-movie').trigger('click');
		}
	});

	$(document).on('click', 'a.smooth-scroll', function(e) {
		e.preventDefault();
		$('html, body').animate({
			scrollTop : $(this.hash).offset().top
			}, 800
		);
	});

	$(document).on('submit', 'form[name="add-artist-form"]', function(e) {
		e.preventDefault();
		if($(this).find('.error').length == 0)
		{
			var formdata = new FormData($(this)[0]);
			$.ajax({
				type : 'POST',
				url : '/artists/addArtist',
				data : formdata,
				cache: false,
				processData: false,
				contentType: false,
				success : function(data)
				{
					if(data.success)
					{
						Suggestions.pickSuggestion(data.artist.name, data.artist.id, data.context);
						$('#add_artist').modal('hide');
						resetAddArtistForm();
						$('.picked-item-list[data-type="' + data.context + '"]').closest('.jsSuggestionWrap').find('input.jsCustomDropdownInput').trigger('focus');
					}
				}
			});
		}
	});

	$(document).on('keyup', '.jsCustomDropdownInput', function(e) {
		if (27 === e.keyCode) {
			var dropdownMenu = $(this).closest('.jsCustomDropdown').find('.dropdown-menu');
			if (dropdownMenu.is(':visible')) {
				$(this).val('');
				dropdownMenu.hide();
			}
		} else if((e.which == 38) || (e.which == 40))
		{
			Suggestions.initiateNavigation($(this));
		}
		else
		{
			Suggestions.getSuggestions($(this));
		}
	});

	$(document).on('keyup', '.jsCustomDropdown .dropdown-menu li', function(e) {
		if((e.which == 38) || (e.which == 40))
		{
			Suggestions.navigateDropdown($(this), e)
		}
	});

	$(document).on('blur', '.jsCustomDropdownInput', function(e) {
		var currentTargetParent = $(this).closest('.jsCustomDropdown')[0];
		var nextTarget = e.relatedTarget;
		var nextTargetParent = $(nextTarget).closest('.jsCustomDropdown');
		if ((nextTargetParent.length === 0) || (nextTargetParent[0] !== currentTargetParent)) {
			var dropdownMenu = $(currentTargetParent).find('.dropdown-menu');
			$(this).val('');
			if (dropdownMenu.is(':visible')) {
				dropdownMenu.hide();
			}
		}
	});

	$(document).on('click', '.remove-item', function() {
		Miscellaneous.purge($(this).closest('.picked-item'));
	});

	$(document).on('click', '.add-new-artist', function(e) {
		e.preventDefault();
		Suggestions.add($.trim($(this).find('.new-artist').text()), $(this).attr('data-context'));
	});

	$(document).on('change', 'select[name="mode-select"]', function() {
		var newMode = $(this).val();
		$.ajax({
			type : "GET",
			url : '/index/changeMode',
			data : {
				'newMode' : newMode
			},
			success : function(data)
			{
				window.location.reload();
			}
		});
	});

	$(document).on('show.bs.collapse', '.collapse', function() {
		$(this).closest('.panel').find('.collapse-arrow').addClass('rotate');
	});

	$(document).on('hide.bs.collapse', '.collapse', function() {
		$(this).closest('.panel').find('.collapse-arrow').removeClass('rotate');
	});

	$(document).on('click', '.jsCustomFileUploadWrapper input[type="file"]', function(e) {
		$(this).val('');
		$(this).closest('.form-field').removeClass('error');
	});

	$(document).on('change', '.jsCustomFileUploadWrapper input[type="file"]', function(e) {
		var formField = $(this).closest('.form-field');
		var maxSize = parseInt(formField.find('input[name="maxSize"]').val(), 10);
		var allowedExt = formField.find('input[name="allowedExt"]').val().split(", ");
		var file = this.files[0];
		var size = file.size;
		var name = file.name;
		var matches = name.match(/(.*)\.(.*)/);
		var ext = matches[2];
		if(-1 === allowedExt.indexOf(ext) || (size > maxSize))
		{
			formField.addClass('error');
			return;
		}
		formField.find('.jsFileText').text(name);
		formField.find('.jsUploadText').text('Update');
	});

	$(document).on('click', '.jsUploadButton', function(e) {
		var wrapper = $(this).closest('.jsCustomFileUploadWrapper');
		wrapper.find('input[type="file"]').trigger('click');
	});
});


var Miscellaneous = {
	confirmMessage : function(question = 'Are you sure?')
	{
		return confirm(question);

	},

	purge : function(selector)
	{
		$(selector).remove();
	},

	showLoader : function()
	{
		$('.jsLoader').show();
		$('body').css('overflow', 'hidden');
	},

	hideLoader : function()
	{
		$('.jsLoader').hide();
		$('body').css('overflow', 'initial');
	},

	replaceUrl : function(url, params)
	{
		url = Miscellaneous.createUrlFromParams(url, params);
		window.history.replaceState({}, '', url);
	},

	createUrlFromParams : function(baseUrl, params)
	{
		var url = baseUrl;
		var queryString = '';
		var queryParams = [];
		$.each(params, function(key, value) {
			if(typeof value == 'object')
			{
				$.each(value, function(k, v) {
					if(queryString != '')
					{
						queryString += '&';
					}
					queryString += key + '[]=' + v;
				});
			}
			else
			{
				if(queryString != '')
				{
					queryString += '&';
				}

				queryString += key + '=' + value;
			}
		});

		if(queryString != '')
		{
			queryString = '?' + queryString;
		}

		return baseUrl + queryString;
	},

	ucFirst : function(str)
	{
		var firstChar = str[0];
		return firstChar.toUpperCase() + str.substr(1);
	},

	getParamsFromUrl: function(url)
	{
		var urlParams = [];
		var urlParts = url.split('&');
		for(var index in urlParts)
		{
			if(urlParts.hasOwnProperty(index))
			{
				var urlPart = urlParts[index];
				var parts = urlPart.split('=');
				var key = parts[0];
				var value = parts[1];
				urlParams[key] = value;
			}
		}
		return urlParams;
	},

	formatString: function(string)
	{
		return string.replace(/'/g, '\'', string).replace(/"/g, '\"');
	}
};

var Suggestions = {
	getSuggestions : function(input)
	{
		var keyword = input.val();
		var dropdown = input.closest('.dropdown').find('.dropdown-menu');
		var type = input.data('type');
		var context = input.closest('.jsSuggestionWrap').find('.picked-item-list').data('type');
		if(keyword.length < 3)
		{
			dropdown.hide();
			return;
		}

		$.ajax({
			type : "GET",
			url : '/suggestions/' + type + 'Suggestions',
			cache : false,
			data : {
				'keyword' : keyword,
				'context' : context
			},
			success : function(data)
			{
				dropdown.html(data);
				dropdown.show();

				// if(type != 'movies')
				// {
				// 	input.focus();
				// }
			}
		});
	},

	pickSuggestion : function(name, id, type)
	{
		var input = $('.picked-item-list[data-type="' + type + '"]').closest('.jsSuggestionWrap').find('input.jsCustomDropdownInput');
		if($('#' + type[0] + '_' + id).length == 0)
		{
			var markup = '<span class="picked-item"><input type="hidden" id="' + type[0] + '_' + id + '" value="' + id + '" name="' + type + 's[]">' + Miscellaneous.formatString(name) + ' <a href="javascript:void(0);" class="remove-item"><i class="glyphicon glyphicon-remove"></i></span>';
			input.closest('.jsSuggestionWrap').find('.picked-item-list').append(markup);
		}
		input.closest('.dropdown').find('.dropdown-menu').hide();
		input.val('');
		input.focus();
	},

	add : function(name, type)
	{
		var modal = $('#add_artist');
		modal.find('input[name="context"]').remove();
		modal.find('form').append($('<input />', {type : "hidden", name : "context", value : type}));
		modal.modal('show');
		modal.find('form input[name="name"]').val(name);
		modal.on('shown.bs.modal', function (e) {
			modal.find('form input[name="name"]').trigger('focus');
		});
	},

	remove : function()
	{

	},

	initiateNavigation : function(input)
	{
		input.closest('.dropdown').find('.dropdown-menu li').first().find('a').trigger('focus');
	},

	navigateDropdown : function(dropdown_item, event)
	{
		if(event.which == 38)
		{
			if(dropdown_item.is(':first-child'))
			{
				dropdown_item.closest('.dropdown').find('.jsCustomDropdownInput').focus();
			}
			else
			{
				dropdown_item.prev().find('a').trigger('focus');
			}
		}
		else if(event.which == 40)
		{
			dropdown_item.next().find('a').trigger('focus');
		}
	}
};

function resetAddArtistForm()
{
	var form = $('form[name="add-artist-form"]');
	form.find('input[name="name"]').val('');
	form.find('input[type="radio"]').attr('checked', false);
}