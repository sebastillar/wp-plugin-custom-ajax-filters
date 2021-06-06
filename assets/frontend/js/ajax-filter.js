(function($) {
    'use strict';

const location = window.location.href;
const isProject = () => {
    if(defaultArchiveType.includes('proyectos'))
        return true
    else
        return false
};
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
var preciosArr = new Array();
var currency = "";
var project_str = ""; 



function addOption(optionObj) {
    if (optionObj !== null && !isSelected(optionObj)) {
        optionsSelected.push(optionObj)
    }

    if (!optionObj.slug.includes('type')){
        showTaxonomyGroup(optionObj, false);
    }
    else {

        if (defaultArchiveType.includes(projectSlug)) {
            /*
            if (countPropertyTypeInSelected() > 1) {
                showTaxonomyGroup(optionObj, false);
            }
            */
                showTaxonomyGroup(optionObj, false);            
        } else {  
            showTaxonomyGroup(optionObj, false);
        }
    }

    if (optionObj.slug.includes(projectSlug))
        removeItemFromTaxGroup(optionObj)
    
    showOptionSelected(optionObj);
}

function showTaxonomyGroup(optionObj, mostrar) {
    let cssClass = '.' + getTermSlug(optionObj);
    if (isValidCss(cssClass) && isSelected(optionObj)){            
        if(mostrar) 
            $(cssClass).show() 
        else{
            $(cssClass).hide();
        } 
    }

}

function showPrecio(){
    if(!optionsSelected.length){
        $('#precio').hide();
    }

    $.each(optionsSelected, function(i) {
        if (!location.includes(projectSlug)){
            $('#precio').show();
            if(optionsSelected[i].slug.includes('alquiler')){
                $('.alquiler').show();
                $('.venta').hide();
            }
            else{
                $('.alquiler').hide();
                $('.venta').show();
            }
        }
        else{
            $('#precio').hide();
        }
    });
}

function getPrecios(){
    $.each(optionsSelected, function(i) {
        if(optionsSelected[i].text.includes('USD') || optionsSelected[i].text.includes('$')){
            preciosArr[0] = optionsSelected[i].slug.split('-')[0]
            preciosArr[1] = optionsSelected[i].slug.split('-')[1]
            currency = optionsSelected[i].moneda
            optionsSelected.splice(i,1)

        }        
    });
}

function countPropertyTypeInSelected() {
    const storage = optionsSelected;
    storage.filter(function(item) {
        if (item.slug.includes(propertyType)) {
            return true;
        } else {
            return false;
        }
    });
    return storage.length;
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
    //addProjectFixedToFilter();
    if('moneda' in optionObj){
        preciosArr = new Array()
        currency = ''
    }
    
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
    if('moneda' in optionObj){
        html += '<button type="button" class="close" data-option="'+optionObj.slug+'" data-moneda="'+optionObj.moneda+'" data-dismiss="alert" aria-label="Close">';    
    }
    else{
        html += '<button type="button" class="close" data-option="'+optionObj.slug+'" data-dismiss="alert" aria-label="Close">';        
    }

    html += '<span aria-hidden="true">&times;</span></button></div>';
    return html;
}

function getTermSlug(optionObj) {
    return (optionObj !== null) ? optionObj.slug.split("|")[0] : ""
}

function showOptionSelected(optionObj) {
    $('#filtros-seleccionados').empty()
    /*
    let showingArr = []
    if (optionsSelected.length >= 1) {
        isSelected(optionObj) ? showingArr.push(optionObj.slug) : ''
    }
    */
    $.each(optionsSelected, function(i) {
        if (optionsSelected[i].mostrar)
            $('#filtros-seleccionados').append(writeAlertHtml(optionsSelected[i]));        
        /*
        if (!optionsSelected[i].slug.includes(projectSlug))
            $('#filtros-seleccionados').append(writeAlertHtml(optionsSelected[i]));
        */
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
            optionObj.text = $('a[data-term="' + optionObj.slug + '"]').text()
            if (location.includes(projectSlug)){
                optionObj.mostrar = false                
            }
            else{
                optionObj.mostrar = true
            }

        } else {
            optionObj.slug = propertyCity + "punta-del-este";
            optionObj.text = $('a[data-term="' + optionObj.slug + '"]').text()
            if (location.includes(projectSlug)){
                optionObj.mostrar = false                
            }
            else{
                optionObj.mostrar = true
            }
        }
        addOption(optionObj);
    }
}

function addProjectFixedToFilter() {
    let optionObj = {}
    if (location.includes(projectSlug)) {
        optionObj.slug = propertyType;
        optionObj.text = $('a[data-term="' + optionObj.slug + '"]').text();
        optionObj.mostrar = false;
        addOption(optionObj);
    }
}

function loadOptionsDefault(defaultFilter){
    $.each(defaultFilter, function() {
        let optionObj = {
            'slug': $(this).data('term'),
            'text': $(this).text(),
            'mostrar': true
        };
        if (!optionObj.slug.includes(propertyCity) && !optionObj.slug.includes(propertyType))
            addOption(optionObj);
    });
}

function successResponse(response) {
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


function callWPAjax() {
    $.ajax({
        url: ajaxfilter.ajaxurl,
        type: 'post',
        data: {
            action: 'ajax_filter',
            archiveType: defaultArchiveType,
            tags: JSON.stringify(optionsSelected),
            prices:JSON.stringify(preciosArr), 
            project_str: project_str,
            currency: currency,
            is_project: isProject
        },
        beforeSend: function() {
            $('.listing-view').find('.card').remove();
            $(document).scrollTop();
            $('.spinner-border').show();
            //$('div.pagination-wrap').empty();
        },
        error: function(e) {
            console.log("error")
            console.log(e.responseText)
        },
        success: function(response){
            $('.spinner-border').hide();            
            successResponse(response)
        }
    })
}



    $(document).ready(function() {
        $('.spinner-border').hide();
        defaultArchiveType = $('div.advanced-search-module').data('type');
        addCityToFilter();
        //addProjectFixedToFilter();
        defaultList = $('div.item-listing-wrap').clone();
        let defaultFilter = $('li.filter-default > a');
        loadOptionsDefault(defaultFilter);
        showPrecio();
    })

    $(document).on('click', '#btn-buscador', function(event) {
        event.preventDefault();
        project_str = $("#buscador-proyectos").val();
        callWPAjax();
    });

    $(document).on('click', '.close', function(event) {
        event.preventDefault();
        if($(this).data('moneda')){
            removeOption({ 
                'slug': $(this).data('option'),
                'moneda': $(this).data('moneda')                 
            })            
        }
        else{
            removeOption({ 
                'slug': $(this).data('option')
            });            
        }

        showPrecio();        
    });

    $(document).on('click', '.filter-options a', function(event) {
        event.preventDefault();
        let optionObj = Object;
        if($(this).data('term') !== undefined){
            optionObj = {
                'slug': $(this).data('term'),
                'text': $(this).text(),
                'mostrar': true
            };
        }
        else{
            optionObj = {
                'slug': $(this).data('price'),
                'text': $(this).text(),
                'moneda': $(this).data('currency'),
                'mostrar': true                
            };
        }
        addOption(optionObj);
        showPrecio();
        getPrecios();
        callWPAjax();
    })


})(jQuery);