(function($) {
    'use strict';

    let defaultList = "";
    let defaultFilter = "venta";
    let optionsSelected = new Array();
    let tagsWithTaxonomies = new Array();

    function addOption(option) {
        if (!optionsSelected.includes(option)) {
            optionsSelected.push(option)
        }
        if (!getTermSlug(option).includes('feature')) {
            let cssClass = '.' + getTermSlug(option);
            $(cssClass).hide();
        }
    }

    function removeOption(option) {
        if (optionsSelected.includes(option)) {
            const index = optionsSelected.indexOf(option);
            if (index > -1) {
                optionsSelected.splice(index, 1);
            }
        }
        if (getTermSlug(option) != 'property_feature') {
            let cssClass = '.' + getTermSlug(option);
            $(cssClass).show();
        }
        if (!optionsSelected.length)
            showItemsDefault();
    }

    function removeParentesis(text) {
        let retorno = text.split("(");
        return retorno[0]
    }

    function getTermSlug(option) {
        return option.split("|")[0];
    }

    function showOptionSelected(option, slug) {
        $('#filtros-seleccionados').append(
            '<div class="alert alert-secondary alert-dismissible fade show" role="alert">' +
            removeParentesis(option) +
            '<button type="button" class="close" data-option="' + slug + '" data-dismiss="alert" aria-label="Close">' +
            '<span aria-hidden="true">&times;</span>' +
            '</button>' +
            '</div>'
        )
    }

    function iterateTags() {
        optionsSelected.forEach(element => {
            let res = element.split("|");
            if (!tagsWithTaxonomies[res[0]])
                tagsWithTaxonomies[res[0]] = new Array()
            if (!tagsWithTaxonomies[res[0]].includes(res[1]))
                tagsWithTaxonomies[res[0]].push(res[1])
        })
    }

    function showItemsDefault() {
        $('div.item-listing-wrap').remove();
        $('.listing-view').append(defaultList);
    }

    $(document).ready(function() {
        defaultList = $('div.item-listing-wrap').clone();
        defaultFilter = $('h5').data('filtro');
    })

    $(document).on('click', '.close', function(event) {
        event.preventDefault();
        removeOption($(this).data('option'))
    });

    $(document).on('click', '.filter-options a', function(event) {
        event.preventDefault();
        addOption($(this).attr('href'));
        showOptionSelected($(this).text(), $(this).attr('href'))

        $.ajax({
            url: ajaxfilter.ajaxurl,
            type: 'post',
            data: {
                action: 'ajax_filter',
                query_vars: ajaxfilter.query_vars,
                tags: JSON.stringify(optionsSelected)
            },
            beforeSend: function() {
                $('.listing-view').find('.card').remove();
                $(document).scrollTop();
                $('.listing-view').append('p').text("Cargando");
            },
            success: function(response) {
                $('.listing-view').empty();
                $('.listing-view').append(response.query);
            }
        })
    })


})(jQuery);