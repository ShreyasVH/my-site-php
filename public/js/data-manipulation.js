/**
 * Author: shreyas.hande
 * Date: 2/10/18
 * Time: 1:26 PM
 */

var dataManipulation = {

    primaryFunctions : {
        updateData : function(url, parentElementSelector, type)
        {
            Miscellaneous.showLoader();
            var loadType = 'replace';
            var nextOffset = 0;
            if(type == 'scroll')
            {
                loadType = 'append';
                nextOffset = offset;
            }
            else
            {
                $(window).scrollTop(0);
            }
            var params = dataManipulation.filterFunctions.getCurrentFiltersData();
            params.append('order', dataManipulation.sortFunctions.getSortQueryString(dataManipulation.sortFunctions.getCurrentSortData()));
            params.append('offset', nextOffset);
            $.ajax({
                type : 'POST',
                url : url,
                data : params,
                processData : false,
                contentType: false,
                cache : false,
                success : function(data)
                {
                    dataManipulation.primaryFunctions.loadData(loadType, data.view, parentElementSelector);
                    dataManipulation.primaryFunctions.updateCount(data.count);
                    dataManipulation.primaryFunctions.toggleResetOptionDisplay(data.filters, data.sortMap);
                    dataManipulation.primaryFunctions.updateUrl(url, data.filters, data.sortMap);
                    dataManipulation.primaryFunctions.updateJsVariables(data);
                    Miscellaneous.hideLoader();
                }
            });
        },

        loadData : function(loadType, data, parentElementSelector)
        {
            var newData = $(data).find(parentElementSelector).html();
            if(loadType == 'replace')
            {
                $(parentElementSelector).html(newData);
            }
            else
            {
                $(parentElementSelector).append(newData);
            }
        },

        updateCount : function(count)
        {
            $('.jsTotalCount').html(count);
        },

        updateJsVariables : function(variables)
        {
            totalCount = variables.count;
            offset = variables.offset;
        },

        toggleResetOptionDisplay : function(filters, sortMap)
        {
            $.each(filters, function(key, value) {
                var parentDiv = $('input[name="' + key + '[]"]').closest('.panel');
                parentDiv.find('.clear-filter').show();
                parentDiv.find('.applied-filter').show();
            });

            var unselectedFilters = $('.jsFilterDiv').filter(function(index, element) {
                return ((Object.keys(filters)).indexOf($(element).attr('data-filter')) == -1);
            });

            unselectedFilters.each(function() {
                $(this).find('.clear-filter').hide();
                $(this).find('.applied-filter').hide();
            });

            if(Object.keys(filters).length == 0)
            {
                $('.jsClearAllFilters').hide();
            }
            else {
                $('.jsClearAllFilters').show();
            }
        },

        updateUrl : function(url, filters, sortMap)
        {
            var sort = {
                'order': Object.keys(sortMap)[0] + ' ' + sortMap[Object.keys(sortMap)[0]].toUpperCase()
            };
            var params = $.extend({}, filters, sort);
            Miscellaneous.replaceUrl(url, params);
        }
    },

    filterFunctions : {
        applyFilters : function()
        {
            dataManipulation.primaryFunctions.updateData(location.pathname, '.jsMovieContainer', 'filter');
        },

        updateFilterDisplays : function()
        {
            // $('input[type="checkbox"]:checked').add('.picked-item').add('')
        },

        clearFilter : function(context, loadDataRequired = true)
        {
            context.find('input[type="checkbox"]:checked').prop('checked', false);

            var slider = context.find('.range-slider');
            slider.slider('values', 0, slider.attr('data-min'));
            slider.slider('values', 1, slider.attr('data-min'));

            context.find('input.min').val('');
            context.find('input.max').val('');

            context.find('input[type="text"]').val('');

            context.find('.picked-item').remove();

            if(loadDataRequired)
            {
                dataManipulation.filterFunctions.applyFilters();
            }
            context.find('.applied-filter').hide();
            context.find('.clear-filter').hide();
        },

        clearAllFilters : function()
        {
            $('.filters .panel').each(function() {
                dataManipulation.filterFunctions.clearFilter($(this), false);
            });
            dataManipulation.filterFunctions.applyFilters();
        },
        
        getCurrentFiltersData : function()
        {
            return new FormData($('form[name="apply-filters-form"]')[0]);
        }
    },

    sortFunctions :
    {
        applySort : function(sortType, sortOrder)
        {
            var currentSort = dataManipulation.sortFunctions.getCurrentSortData();
            if((sortType != Object.keys(currentSort)[0]) || (sortOrder != currentSort[Object.keys(currentSort)[0]]))
            {
                dataManipulation.sortFunctions.updateDisplay(sortType, sortOrder);
                dataManipulation.primaryFunctions.updateData(location.pathname, '.jsMovieContainer', 'sort');
            }

        },
        
        getCurrentSortData : function()
        {
            return {
                [$('.jsSortDisplay').attr('data-sortType')] : $('.jsSortDisplay').attr('data-sortOrder')
            };
        },

        getSortQueryString : function(sortMap)
        {
            return Object.keys(sortMap)[0] + ' ' + sortMap[Object.keys(sortMap)[0]];
        },

        updateDisplay : function(sortType, sortOrder)
        {
            var sortLabel = $('.jsSort[data-sortType="' + sortType + '"]').closest('li').find('.jsSortLabel').text();
            var sortText = sortLabel + ' ' + sortOrder;
            $('.jsSortDisplay').text(sortText);
            $('.jsSortDisplay').attr('data-sortType', sortType);
            $('.jsSortDisplay').attr('data-sortOrder', sortOrder);
        }
    }
};

$(document).ready(function() {

    $(document).on('submit', 'form[name="apply-filters-form"]', function(e) {
        e.preventDefault();
        dataManipulation.filterFunctions.applyFilters();
    });

    $(document).on('click', '.jsSort', function(e) {
        dataManipulation.sortFunctions.applySort($(this).attr('data-sortType'), $(this).attr('data-sortOrder'));;
    });

    $(window).scroll(function() {
        if(($(window).scrollTop() + $(window).height() >= $(document).height()) && (offset < totalCount))
        {
            dataManipulation.primaryFunctions.updateData(location.pathname, '.jsMovieContainer', 'scroll');
        }
    });

    $(document).on('keyup', '.filters input.min', function() {
        $(this).closest('.panel-body').find('.range-slider').slider('values', 0, $(this).val());
    });

    $(document).on('keyup', '.filters input.max', function() {
        $(this).closest('.panel-body').find('.range-slider').slider('values', 1, $(this).val());
    });

    $('.filters .range-slider').each(function() {
        var defaultMin = parseInt($(this).attr('data-min'));
        var defaultMax = parseInt($(this).attr('data-max'));
        var min = ((!forms.Validation.isInputFieldEmpty($(this).closest('.jsFilterDiv').find('input.min'))) ? parseInt($(this).closest('.jsFilterDiv').find('input.min').val()) : defaultMin);
        var max = ((!forms.Validation.isInputFieldEmpty($(this).closest('.jsFilterDiv').find('input.max'))) ? parseInt($(this).closest('.jsFilterDiv').find('input.max').val()) : defaultMin);
        $(this).slider({
            range : true,
            min : defaultMin,
            max : defaultMax,
            step : parseInt($(this).attr('data-step')),
            values : [
                min,
                max
            ],
            slide : function(event, ui)
            {
                var handle = $(ui.handle);
                handle.closest('.panel-body').find('input.min').val(ui.values[0]);
                handle.closest('.panel-body').find('input.max').val(ui.values[1]);
            }
        });
    });

    $(document).on('click', '.jsClearFilter', function() {
        dataManipulation.filterFunctions.clearFilter($(this).closest('.panel'));
    });

    $(document).on('click', '.jsClearAllFilters', function() {
        dataManipulation.filterFunctions.clearAllFilters();
    });
});