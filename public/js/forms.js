var forms = {
	Validation : {
	// function to check whether the form has empty input fields which are mandatory
	    isformValid : function(form)
	    {
	        var textareas = form.find('textarea');
	        var textfields = form.find('input[type=text]');
	        var selectfields = form.find('select');
	        var radiofields = form.find('.radio');

	        var errorsPresent = false;

	        textfields.each(function () {
	            if($(this).hasClass('mandatory'))
	            {
	                if(forms.Validation.isTextFieldEmpty($(this)))
	                {
	                    errorsPresent = true;
	                    $(this).addClass('empty');
	                    $(this).closest('.form-field').find('.errorMsg').show();
	                }
	            }
	        });

	        textareas.each(function () {
	            if($(this).hasClass('mandatory'))
	            {
	                if(forms.Validation.isTextareaEmpty($(this)))
	                {
	                    errorsPresent = true;
	                    $(this).addClass('empty');
	                    $(this).closest('.form-field').find('.errorMsg').show();
	                }
	            }
	        });

	        selectfields.each(function () {
	            if($(this).hasClass('mandatory'))
	            {
	                if(forms.Validation.isSelectFieldEmpty($(this)))
	                {
	                    errorsPresent = true;
	                    $(this).addClass('empty');
	                }
	            }
	        });

	        radiofields.each(function() {
	        	if($(this).hasClass('mandatory'))
	        	{
	        		if(forms.Validation.isRadioFieldEmpty($(this)))
	                {
	                    errorsPresent = true;
	                    $(this).addClass('empty');
	                    $(this).closest('.form-field').find('.errorMsg').show();
	                }
	        	}
	        });

	        if(errorsPresent)
	        {
	            return false;
	        }
	        else
	        {
	            return true;
	        }
	    },

	    isNumeric: function (elem) {
            var pattern = /^\d+(\.\d{1,2})?$/;
            return pattern.test(elem.val().replace(/\s+/gm, ''));
        },

	    isTextFieldEmpty : function(selector)
	    {
	        if(selector.val() == '')
	        {
	            return true;
	        }
	        return false;
	    },

	    isTextareaEmpty : function(selector)
	    {
	        if(selector.val() == '')
	        {
	            return true;
	        }
	        return false;
	    },

	    isSelectFieldEmpty : function(selector)
	    {
	        if(selector.val() == 'default')
	        {
	            return true;
	        }
	        return false;
	    },

	    isRadioFieldEmpty : function(selector)
	    {
	    	return (selector.find('input[type="radio"]:checked').length == 0);
	    },

		isInputFieldEmpty : function(selector)
	    {
	        if(selector.val() == '')
	        {
	            return true;
	        }
	        return false;
	    }	    
	},
	validateForms : {
		isAddMovieFormValid : function(form)
		{
			var errorsPresent = (0 !== form.find('.error').length);

			var urlParams = Miscellaneous.getParamsFromUrl(location.search.replace('?', ''));
			if((!urlParams.hasOwnProperty('source')) || (urlParams.source !== 'addMovie'))
			{
				if(forms.Validation.isInputFieldEmpty(form.find('input[name="movie-name"]')))
				{
					form.find('input[name="movie-name"]').closest('.form-field').addClass('error');
					errorsPresent = true;
				}
				if(forms.Validation.isInputFieldEmpty(form.find('input[name="movie-size"]')))
				{
					form.find('input[name="movie-size"]').closest('.form-field').addClass('error');
					errorsPresent = true;
				}

				if(forms.Validation.isSelectFieldEmpty(form.find('select[name="movie-language"]')))
				{
					form.find('select[name="movie-language"]').closest('.form-field').addClass('error');
					errorsPresent = true;
				}
				if(forms.Validation.isSelectFieldEmpty(form.find('select[name="movie-format"]')))
				{
					form.find('select[name="movie-format"]').closest('.form-field').addClass('error');
					errorsPresent = true;
				}
				if(forms.Validation.isInputFieldEmpty(form.find('input[name="movie-year"]')))
				{
					form.find('input[name="movie-year"]').closest('.form-field').addClass('error');
					errorsPresent = true;
				}
				if(form.find('input[name="directors[]"]').length == 0)
				{
					form.find('.picked-item-list[data-type="director"]').closest('.form-field').addClass('error');
					errorsPresent = true;
				}
				if(form.find('input[name="actors[]"]').length == 0)
				{
					form.find('.picked-item-list[data-type="actor"]').closest('.jsSuggestionWrap').find('.form-field').addClass('error');
					errorsPresent = true;
				}
			}

			if(0 !== form.find('input[name="movie-image"]').length)
			{
				var formField = form.find('input[name="movie-image"]').closest('.form-field');
				var maxSize = formField.find('input[name="maxSize"]').val();
				var allowedExt = formField.find('input[name="allowedExt"]').val().split(", ");
				var file = form.find('input[name="movie-image"]')[0].files[0];
				if(file)
				{
					var size = file.size;
					var name = file.name;
					var matches = name.match(/(.*)\.(.*)/);
					var ext = matches[2];
					if(-1 === allowedExt.indexOf(ext))
					{
						formField.addClass('error');
						errorsPresent = true;
					}
				}
			}

			if(errorsPresent)
			{
				$('html, body').animate({
						scrollTop : $(form.find('.error')[0]).offset().top
					}, 300
				);
			}

			return !errorsPresent;
		},

		isAddSongFormValid : function(form)
		{
			var errorsPresent = false;

			if(forms.Validation.isInputFieldEmpty(form.find('input[name="name"]')))
			{
				form.find('input[name="name"]').closest('.form-field').addClass('error');
				errorsPresent = true;
			}

			if(forms.Validation.isInputFieldEmpty(form.find('input[name="size"]')))
			{
				form.find('input[name="size"]').closest('.form-field').addClass('error');
				errorsPresent = true;
			}

			if(form.find('input[name="movies[]"]').length == 0)
			{
				form.find('.picked-item-list[data-type="movie"]').closest('.form-field').addClass('error');
				errorsPresent = true;
			}

			if(form.find('input[name="singers[]"]').length == 0)
			{
				form.find('.picked-item-list[data-type="singer"]').closest('.form-field').addClass('error');
				errorsPresent = true;
			}

			if(form.find('input[name="composers[]"]').length == 0)
			{
				form.find('.picked-item-list[data-type="composer"]').closest('.form-field').addClass('error');
				errorsPresent = true;
			}

			if(form.find('input[name="lyricists[]"]').length == 0)
			{
				form.find('.picked-item-list[data-type="lyricist"]').closest('.form-field').addClass('error');
				errorsPresent = true;
			}

			if(errorsPresent)
			{
				$('html, body').animate({
						scrollTop : $(form.find('.error')[0]).offset().top
					}, 300
				);
			}

			return !errorsPresent;
		},

		isEditArtistFormValid : function(form)
		{
			var errorsPresent = false;

			if(forms.Validation.isInputFieldEmpty(form.find('input[name="artist-name"]')))
			{
				form.find('input[name="artist-name"]').closest('.form-field').addClass('error');
				errorsPresent = true;
			}

			if(errorsPresent)
			{
				$('html, body').animate({
						scrollTop : $(form.find('.error')[0]).offset().top
					}, 300
				);
			}

			return !errorsPresent;
		},

		isAddCardFormValid: function(form) {
			var errorsPresent = false;

			var cardTypeSelect = form.find('select[name="cardType"]');
			var cardTypeValue = cardTypeSelect.val();
			var cardTypeElement = cardTypeSelect.find('option[value="' + cardTypeValue + '"]');
			var cardType = cardTypeElement.text().trim();

			if(forms.Validation.isInputFieldEmpty(form.find('input[name="name"]')))
			{
				form.find('input[name="name"]').closest('.form-field').addClass('error');
				errorsPresent = true;
			}

			if(forms.Validation.isTextareaEmpty(form.find('textarea[name="description"]')))
			{
				form.find('textarea[name="description"]').closest('.form-field').addClass('error');
				errorsPresent = true;
			}

			if(forms.Validation.isSelectFieldEmpty(form.find('select[name="cardType"]')))
			{
				form.find('select[name="cardType"]').closest('.form-field').addClass('error');
				errorsPresent = true;
			}

			if ('MONSTER' === cardType) {
				if(forms.Validation.isInputFieldEmpty(form.find('input[name="level"]')))
				{
					form.find('input[name="level"]').closest('.form-field').addClass('error');
					errorsPresent = true;
				}

				if(forms.Validation.isSelectFieldEmpty(form.find('select[name="attribute"]')))
				{
					form.find('select[name="attribute"]').closest('.form-field').addClass('error');
					errorsPresent = true;
				}

				if(forms.Validation.isSelectFieldEmpty(form.find('select[name="type"]')))
				{
					form.find('select[name="type"]').closest('.form-field').addClass('error');
					errorsPresent = true;
				}

				if(forms.Validation.isInputFieldEmpty(form.find('input[name="attack"]')))
				{
					form.find('input[name="attack"]').closest('.form-field').addClass('error');
					errorsPresent = true;
				}

				if(forms.Validation.isInputFieldEmpty(form.find('input[name="defense"]')))
				{
					form.find('input[name="defense"]').closest('.form-field').addClass('error');
					errorsPresent = true;
				}
			}

			if(forms.Validation.isSelectFieldEmpty(form.find('select[name="cardSubType"]')))
			{
				form.find('select[name="cardSubType"]').closest('.form-field').addClass('error');
				errorsPresent = true;
			}

			if(forms.Validation.isSelectFieldEmpty(form.find('select[name="rarity"]')))
			{
				form.find('select[name="rarity"]').closest('.form-field').addClass('error');
				errorsPresent = true;
			}

			if(forms.Validation.isSelectFieldEmpty(form.find('select[name="limitType"]')))
			{
				form.find('select[name="limitType"]').closest('.form-field').addClass('error');
				errorsPresent = true;
			}

			if(errorsPresent)
			{
				$('html, body').animate({
						scrollTop : $(form.find('.error')[0]).offset().top
					}, 300
				);
			}

			return !errorsPresent;
		},

		isEditCardFormValid: function(form) {
			var errorsPresent = false;

			var cardTypeSelect = form.find('select[name="cardType"]');
			var cardTypeValue = cardTypeSelect.val();
			var cardTypeElement = cardTypeSelect.find('option[value="' + cardTypeValue + '"]');
			var cardType = cardTypeElement.text().trim();

			if(forms.Validation.isInputFieldEmpty(form.find('input[name="name"]')))
			{
				form.find('input[name="name"]').closest('.form-field').addClass('error');
				errorsPresent = true;
			}

			if(forms.Validation.isTextareaEmpty(form.find('textarea[name="description"]')))
			{
				form.find('textarea[name="description"]').closest('.form-field').addClass('error');
				errorsPresent = true;
			}

			if(forms.Validation.isSelectFieldEmpty(form.find('select[name="cardType"]')))
			{
				form.find('select[name="cardType"]').closest('.form-field').addClass('error');
				errorsPresent = true;
			}

			if ('MONSTER' === cardType) {
				if(forms.Validation.isInputFieldEmpty(form.find('input[name="level"]')))
				{
					form.find('input[name="level"]').closest('.form-field').addClass('error');
					errorsPresent = true;
				}

				if(forms.Validation.isSelectFieldEmpty(form.find('select[name="attribute"]')))
				{
					form.find('select[name="attribute"]').closest('.form-field').addClass('error');
					errorsPresent = true;
				}

				if(forms.Validation.isSelectFieldEmpty(form.find('select[name="type"]')))
				{
					form.find('select[name="type"]').closest('.form-field').addClass('error');
					errorsPresent = true;
				}

				if(forms.Validation.isInputFieldEmpty(form.find('input[name="attack"]')))
				{
					form.find('input[name="attack"]').closest('.form-field').addClass('error');
					errorsPresent = true;
				}

				if(forms.Validation.isInputFieldEmpty(form.find('input[name="defense"]')))
				{
					form.find('input[name="defense"]').closest('.form-field').addClass('error');
					errorsPresent = true;
				}
			}

			if(forms.Validation.isSelectFieldEmpty(form.find('select[name="cardSubType"]')))
			{
				form.find('select[name="cardSubType"]').closest('.form-field').addClass('error');
				errorsPresent = true;
			}

			if(forms.Validation.isSelectFieldEmpty(form.find('select[name="rarity"]')))
			{
				form.find('select[name="rarity"]').closest('.form-field').addClass('error');
				errorsPresent = true;
			}

			if(forms.Validation.isSelectFieldEmpty(form.find('select[name="limitType"]')))
			{
				form.find('select[name="limitType"]').closest('.form-field').addClass('error');
				errorsPresent = true;
			}

			if(errorsPresent)
			{
				$('html, body').animate({
						scrollTop : $(form.find('.error')[0]).offset().top
					}, 300
				);
			}

			return !errorsPresent;
		},

		isAddSourceFormValid: function(form) {
			var errorsPresent = false;

			return !errorsPresent;
		},

		isEditSourceFormValid: function(form) {
			var errorsPresent = false;

			return !errorsPresent;
		}
	}
};