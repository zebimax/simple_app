$(document).ready(function() {
    $('.translate_product_name').each(
        function(i, item) {
            var id = $(item).data('product_id'),
                firstDescription = $('.translate_product_description[data-product_id="' + id + '"]').first(),
                firstTagLine = $('.translate_product_tagline[data-product_id="' + id + '"]').first();
            $.data(document.body, 'product_id_' + id, {
                name: $(item).val(),
                description: firstDescription ? firstDescription.val() : '',
                tagline : firstTagLine ? firstTagLine.val() : ''
            });
        }
    );
    $('#info_message').hide();
    $('#error_message').hide();
    $.data( document.body, 'base_lang_select', $('.base-language-select[data-base_language_selected="1"]').data('base_language_id'));
    $.data( document.body, 'translate_lang_select', $('.translate-language-select[data-translate_language_selected="1"]').data('translate_language_id'));
    var active = $('.pagination.active');
    $.data(document.body, 'current_page', active ? active.data('page') : 1);

    $('.base-language-select').bind('click', function(){
        $.data(document.body, 'base_lang_select', $(this).data('base_language_id'));
        loadProducts(collectParams(), function(){
            $('.base-language-select').each(function(i, item) {
                $(item).removeClass('btn-primary');
            });
            $(this).addClass('btn-primary');
        }, this);
    });

    $('.translate-language-select').bind('click', function(){
        $.data(document.body, 'translate_lang_select', $(this).data('translate_language_id'));
        loadProducts(collectParams(), function(){
            $('.translate-language-select').each(function(i, item) {
                $(item).removeClass('btn-primary');
            });
            $(this).addClass('btn-primary');
        }, this);
    });

    $('#products_brand_select').bind('change', function(){
        loadProducts(collectParams());
    });

    $('#load_products_btn').bind('click', function(){
        loadProducts(collectParams());
    });

    $('#products_limit_select').bind('change', function(){
        loadProducts(collectParams());
    });

    $('#products_category_select').bind('change', function(){
        loadProducts(collectParams());
    });
    productsBinds();
    bindPaginationLinks();

    function saveProduct(product_id, item) {
        var names = $.find('.translate_product_name[data-product_id="' + product_id + '"]'),
            descriptions = $.find('.translate_product_description[data-product_id="' + product_id + '"]'),
            taglines = $.find('.translate_product_tagline[data-product_id="' + product_id + '"]'),
            modifiebles = $.find('.modifiable[data-product_id="' + product_id + '"]'),
            name = names ? $(names).first().val() : '',
            description = descriptions ? $(descriptions).first().val() : '',
            tagline = taglines ? $(taglines).first().val() : '';
        if (
            name === $.data(document.body, 'product_id_' + product_id).name &&
            description === $.data(document.body, 'product_id_' + product_id).description &&
            tagline === $.data(document.body, 'product_id_' + product_id).tagline
        ) {
            return log('Content are not changed', true);
        }
        $.ajax({
            type: "POST",
            url: '/productadmin/app.php/translate/save',
            data: {
                id: product_id,
                translate_language_id: $.data(document.body, 'translate_lang_select'),
                name: name,
                description: description,
                tagline: tagline
            },
            success: function(data) {
                log(data.message, data.success);
                $(modifiebles).each(function(i, item){
                    $(item).removeClass('modified');
                });
            },
            failure: function (data) {

            },
            dataType: 'json'
        });
    }

    function log(message, success) {
        var info_element = (success) ? $('#info_message') : $('#error_message');
        info_element.html(message);
        info_element.show();

        setTimeout(function () {
            info_element.html('');
            info_element.hide();
        }, 2500);
    }

    function loadProducts(params, callback, scope) {
        $.ajax({
            type: "POST",
            url: '/productadmin/app.php/translate',
            data: {
                base_language_id: params.baseLanguageId,
                translate_language_id: params.translateLanguageId,
                category_id: params.categoryId,
                brand_id: params.brandId,
                limit: params.limit,
                page: params.page,
                not_translated_name:params.notTranslatedName,
                not_translated_description: params.notTranslatedDescription,
                not_translated_tagline: params.notTranslatedTagLine,
                check_translated: params.checkTranslated
            },
            success: function (data) {
                !callback || callback.call(scope, data);
                if (
                   typeof data.data.list !== 'undefined' &&
                   typeof data.data.pagination !== 'undefined'
                ) {
                    fillProducts(data.data.list);
                    reRenderPagination(data.data.pagination);
                    bindPaginationLinks();
                    productsBinds();
                    saveNotChanged(data.data.list);
                }
                !data.message || log(data.message, data.success)
            },
            dataType: 'json'
        });
    }

    function collectParams()
    {
        return {
            baseLanguageId: $.data(document.body, 'base_lang_select'),
            translateLanguageId: $.data(document.body, 'translate_lang_select'),
            categoryId: $('#products_category_select').val(),
            brandId: $('#products_brand_select').val(),
            limit: $('#products_limit_select').val(),
            page: $.data(document.body, 'current_page'),
            notTranslatedName: $('#not_translated_name').prop('checked') === true ? 1 : 0,
            notTranslatedDescription: $('#not_translated_description').prop('checked') === true ? 1 : 0,
            notTranslatedTagLine: $('#not_translated_tagline').prop('checked') === true ? 1 : 0,
            checkTranslated: $('input[name="check_translated"]:checked').val()
        }
    }

    function fillProducts(products)
    {
        var productsList = $('#products_list'),
            list = products.list || [],
            count = products.count || 0;
        productsList.html('');
        $('#found_translate_products').html(count);
        $('#not_translated_products').html(products.not_exist_translate);
        $('#names_not_translated').html(products.names_not_translated);
        $('#descriptions_not_translated').html(products.descriptions_not_translated);
        $('#taglines_not_translated').html(products.taglines_not_translated);
        $(list).each(function(){
            var productBlock = $('<div class="panel panel-default" />')
                .append($('<div class="panel-heading" />')
                    .append($('<h3 class="panel-title" />')
                        .append($('<span class="label label-default">Product id' + this.id + '</span>'))))
                .append($('<div class="panel-body" />')
                    .append($('<div class="row" />')
                        .append($('<div class="col-xs-6" />')
                            .append($('<div class="alert alert-info" role="alert">Product name</div>')
                                .append('<button type="button" class="btn btn-success copy-item pull-right" data-copy-item="name" data-product-id="' +
                                this.id + '">Copy &gt;&gt;</button>'))
                            .append($('<div data-item="name" data-product-id="' + this.id + '">' + this.name + '</div>'))
                            .append($('<div class="alert alert-info" role="alert">Product description</div>')
                                .append('<button type="button" class="btn btn-success copy-item pull-right" data-copy-item="description" data-product-id="' +
                            this.id + '">Copy &gt;&gt;</button>'))
                            .append($('<div data-item="description" data-product-id="' + this.id + '">' + this.description + '</div>'))
                            .append($('<div class="alert alert-info" role="alert">Product tagline</div>')
                                .append('<button type="button" class="btn btn-success copy-item pull-right" data-copy-item="tagline" data-product-id="' +
                            this.id + '">Copy &gt;&gt;</button>'))
                            .append($('<div data-item="tagline" data-product-id="' + this.id + '">' + this.tagline + '</div>')))
                        .append($('<div class="col-xs-6">')
                            .append($('<div class="input-group">'))
                            .append($('<input data-product_id="'
                            + this.id + '" type="text" class="form-control modifiable translate_product_name" value="'
                            + this.translate_name + '">'))
                            .append($('<textarea data-product_id="'
                            + this.id + '" rows="20" class="form-control modifiable translate_product_description" >' + this.translate_description + '</textarea>'))
                            .append($('<input data-product_id="'
                            + this.id + '" type="text" class="form-control modifiable translate_product_tagline" value="' + this.translate_tagline + '">')))))
                .append($('<div class="btn-group" />')
                    .append($('<button type="button" class="btn btn-success save_product_translate" data-product-id="' + this.id + '">Save</button>')));
            productsList.append(productBlock);
        });
    }

    function reRenderPagination(links)
    {
        var paginations = $('.pagination');
        $(paginations).each(function(i, item) {
            $(item).html('');
            $(links).each(function (y, link) {
                $(item)
                    .append($('<li class="' + link.active + link.disabled + link.additional +'" />')
                        .append('<a class="products_pagination" href="#" data-page="' + link.link_id + '">' + link.link_show + '</a>'));
            });
        });
    }

    function bindPaginationLinks() {
        $('.products_pagination').bind('click', function (e) {
            e.preventDefault();
            $.data(document.body, 'current_page', $(this).data('page'));
            loadProducts(collectParams(), function () {
                $.data(document.body, 'current_page', $(this).data('page'));
            });
        });
    }

    function productsBinds() {
        $('.modifiable').bind('change keyup paste', function () {
            $(this).addClass('modified');
        });

        $('.save_product_translate').bind('click', function () {
            saveProduct(
                $(this).data('product-id'),
                $(this)
            );
        });

        $('.copy-item').bind('click', function() {
            var copyItem = $(this).data('copy-item'),
                productId = $(this).data('product-id'),
                sourceVal = $('[data-item="' + copyItem + '"][data-product-id="' + productId + '"]').first().html(),
                targetItem = $('.translate_product_' + copyItem + '[data-product_id="' + productId + '"]').first();
            var tag = targetItem.prop("tagName");
            if (tag === 'INPUT') {
                targetItem.val(sourceVal);
            } else if (tag === 'TEXTAREA') {
                targetItem.val(sourceVal);
            }

        });
    }

    function saveNotChanged(list)
    {
        $(list).each(function (i, item) {
            $.data(document.body, 'product_id_' + item.id, item);
        });
    }
});

