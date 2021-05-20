(function($) {
    'use strict';

    let property_city = "";
    let defaultList = "";
    let defaultArchiveType = "venta";
    let optionsSelected = new Array();
    let tagsWithTaxonomies = new Array();

    function addOption(option, text) {
        if (!optionsSelected.includes(option)) {
            optionsSelected.push(option)
        }
        if (!getTermSlug(option).includes('feature')) {
            let cssClass = '.' + getTermSlug(option);
            $(cssClass).hide();
        }
        if (!option.includes('ciudad'))
            showOptionSelected(text, option)
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
        //es menor o igual a 1 porque el array optionsSelected siempre tiene al menos una ciudad
        if (optionsSelected.length <= 1)
            showItemsDefault();
        else
            callWPAjax()
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

    function callWPAjax() {
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
                $('div.listing-tabs').text('Cargando resultados...')
                    //$('div.pagination-wrap').empty();
            },
            success: function(response) {
                $('.listing-view').empty();
                $('.listing-view').append(response.query);
                console.log(response.count_posts);
                if (response.count_posts === undefined || response.count_posts == 0) {
                    $('div.listing-tabs').hide();
                    $('div.sort-by').hide();
                    //$('div.pagination-wrap').hide();
                } else {
                    if (response.count_posts > 0) {
                        $('div.listing-tabs').show();
                        $('div.sort-by').show();
                        $('div.pagination-wrap').show();
                        $('div.pagination-wrap').append(response.paginacion);
                        if (defaultArchiveType != 'proyecto') {
                            if (response.count_posts > 1)
                                $('div.listing-tabs').text(response.count_posts + ' Propiedades')
                            else
                                $('div.listing-tabs').text(response.count_posts + ' Propiedad')
                        } else {
                            if (response.count_posts > 1)
                                $('div.listing-tabs').text(response.count_posts + ' Proyectos')
                            else
                                $('div.listing-tabs').text(response.count_posts + ' Proyecto')
                        }
                    }
                }

            }
        })
    }

    $(document).ready(function() {
        let pathname = window.location.pathname;
        if (pathname.includes('montevideo')) {
            property_city = "property_city|ciudad-de-montevideo";
        } else {
            property_city = "property_city|ciudad-de-punta-del-este";
        }
        addOption(property_city, property_city);
        defaultArchiveType = $('div.advanced-search-module').data('type');
        defaultList = $('div.item-listing-wrap').clone();
        let defaultFilter = $('li.filter-default > a');
        $.each(defaultFilter, function() {
            addOption($(this).attr('href'), $(this).text());
        });

    })

    $(document).on('click', '.close', function(event) {
        event.preventDefault();
        addOption(property_city, property_city);
        removeOption($(this).data('option'))
    });

    $(document).on('click', '.filter-options a', function(event) {
        event.preventDefault();
        addOption(property_city, property_city);
        addOption($(this).attr('href'), $(this).text());
        console.log(optionsSelected);
        callWPAjax();

    })


})(jQuery);