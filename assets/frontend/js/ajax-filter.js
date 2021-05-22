(function($) {
    'use strict';

    const location = window.location.href;
    const urlParams = new URLSearchParams(window.location.search);
    const projectSlug = "proyecto"
    const propertyCity = "property_city|ciudad-de-";
    const propertyType = "property_type|proyecto";
    const ciudadObj = {
        'value': urlParams.get('ciudad'),
        'key': 'ciudad'
    };

    var defaultList = "";
    var defaultArchiveType = "";
    var optionsSelected = new Array();
    var project_str = "";

    function addOption(optionObj) {
        if (optionObj !== null && !isSelected(optionObj)) {
            optionsSelected.push(optionObj)
        }
        if (!optionObj.slug.includes('type'))
            showTaxonomyGroup(optionObj, false);
        else {
            if (defaultArchiveType.includes(projectSlug)) {
                if (countPropertyTypeInSelected() > 1) {
                    showTaxonomyGroup(optionObj, false);
                }
            } else {
                showTaxonomyGroup(optionObj, false);
            }
        }

        if (optionObj.slug.includes(projectSlug))
            removeItemFromTaxGroup(optionObj)

        showOptionSelected(optionObj)
    }

    function showTaxonomyGroup(optionObj, mostrar) {
        let cssClass = '.' + getTermSlug(optionObj);
        if (isValidCss(cssClass) && isSelected(optionObj))
            mostrar ? $(cssClass).show() : $(cssClass).hide();
    }

    function countPropertyTypeInSelected() {
        const storage = optionsSelected;
        const count = storage.filter(function(item) {
            if (item.slug.includes(propertyType)) {
                return true;
            } else {
                return false;
            }
        }).length;
        console.log(count)
        return count;
    }

    function isValidCss(cssClass) {
        return cssClass != '.' ? true : false;
    }

    function isSelected(optionObj) {
        return optionsSelected.some(e => equals(e, optionObj));
    }

    function equals(optionA, optionB) {
        return (optionA.slug === optionB.slug) ? true : false;
    }

    function removeItemFromTaxGroup(optionObj) {
        $('a[href="' + optionObj.slug + '"]').parent().addClass('remove-item');
        $('.remove-item').hide()
    }

    function removeOption(optionObj) {
        showTaxonomyGroup(optionObj, true);
        if (optionsSelected.length && isSelected(optionObj)) {
            $.each(optionsSelected, function(i) {
                if (equals(optionsSelected[i], optionObj)) {
                    optionsSelected.splice(i, 1)
                    return false;
                }
            })
        }
        addProjectFixedToFilter();
        if (optionObj.slug && optionObj.slug.includes('proyecto') && optionsSelected) {
            if (optionsSelected.length <= 1) {
                showItemsDefault();
            } else
                callWPAjax()
        } else {
            if (optionsSelected.length === 0) {
                showItemsDefault();
            } else
                callWPAjax()
        }
    }

    function removeParentesis(text) {
        let retorno = "";
        if (text !== undefined)
            retorno = text.split("(");
        return retorno[0]
    }

    function writeAlertHtml(optionObj) {
        let html = '';
        html += '<div class="alert alert-secondary alert-dismissible fade show" role="alert">';
        html += removeParentesis(optionObj.text);
        html += '<button type="button" class="close" data-option="';
        html += optionObj.slug;
        html += '" data-dismiss="alert" aria-label="Close">';
        html += '<span aria-hidden="true">&times;</span></button></div>';
        return html;
    }

    function getTermSlug(optionObj) {
        return (optionObj !== null) ? optionObj.slug.split("|")[0] : ""
    }

    function showOptionSelected(optionObj) {
        $('#filtros-seleccionados').empty()
        let showingArr = []
        if (optionsSelected >= 1) {
            isSelected(optionObj) ? showingArr.push(optionObj.slug) : ''
        }
        $.each(optionsSelected, function(i) {
            if (!optionsSelected[i].slug.includes(projectSlug))
                $('#filtros-seleccionados').append(writeAlertHtml(optionsSelected[i]));
        });
    }

    function showItemsDefault() {
        $('div.item-listing-wrap').remove();
        $('.listing-view').append(defaultList);
    }

    function addCityToFilter() {
        let optionObj = {}
        if (location.includes(ciudadObj.key)) {
            if (ciudadObj.value.includes('montevideo')) {
                optionObj.slug = propertyCity + "montevideo";
                optionObj.text = $('a[href="' + optionObj.slug + '"]').text()
            } else {
                optionObj.slug = propertyCity + "punta-del-este";
                optionObj.text = $('a[href="' + optionObj.slug + '"]').text()
            }
            addOption(optionObj);
        }
    }

    function addProjectFixedToFilter() {
        let optionObj = {}
        if (location.includes(projectSlug)) {
            optionObj.slug = propertyType;
            optionObj.text = $('a[href="' + optionObj.slug + '"]').text();
            addOption(optionObj);
        }
    }

    function callWPAjax() {
        $.ajax({
            url: ajaxfilter.ajaxurl,
            type: 'post',
            data: {
                action: 'ajax_filter',
                archiveType: defaultArchiveType,
                tags: JSON.stringify(optionsSelected),
                project_str: project_str,
            },
            beforeSend: function() {
                $('.listing-view').find('.card').remove();
                $(document).scrollTop();
                $('div.listing-tabs').text('Cargando resultados...')
                $('div.pagination-wrap').empty();
            },
            error: function(e) {
                console.log("error")
                console.log(e.responseText)
            },
            success: function(response) {
                console.log("argumentos")
                console.log(response)
                $('.listing-view').empty();
                $('.listing-view').append(response.query);
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
                        if (defaultArchiveType != projectSlug) {
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

    function initSearchStr() {
        $("#buscador-proyectos").keyup(function() {
            project_str = $("#buscador-proyectos").val();
        });
    }

    $(document).ready(function() {
        defaultArchiveType = $('div.advanced-search-module').data('type');
        addCityToFilter();
        addProjectFixedToFilter();
        initSearchStr();
        defaultList = $('div.item-listing-wrap').clone();
        let defaultFilter = $('li.filter-default > a');
        $.each(defaultFilter, function() {
            let optionObj = {
                'slug': $(this).attr('href'),
                'text': $(this).text()
            };
            if (!optionObj.slug.includes(propertyCity) && !optionObj.slug.includes(propertyType))
                addOption(optionObj);
        });
    })

    $(document).on('click', '#btn-buscador', function(event) {
        event.preventDefault();
        console.log("request")
        callWPAjax();
    });

    $(document).on('click', '.close', function(event) {
        event.preventDefault();
        removeOption({ 'slug': $(this).data('option') })
    });

    $(document).on('click', '.filter-options a', function(event) {
        event.preventDefault();
        let optionObj = {
            'slug': $(this).attr('href'),
            'text': $(this).text()
        };
        addOption(optionObj);
        callWPAjax();
    })


})(jQuery);