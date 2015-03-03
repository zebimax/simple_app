$(document).ready(function() {
    var active = $('.pagination.active');
    $.data(document.body, 'current_page', active ? active.data('page') : 1);

    $('#products_limit_select').bind('change', function(){
        $.data(document.body, 'current_page', 1);
        loadProducts(collectParams());
    });
    bindPaginationLinks();

    function loadProducts(params, callback, scope) {
        $.ajax({
            type: "POST",
            url: '/admin/app.php/expensive-unipharma',
            data: {
                limit: params.limit,
                page: params.page
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
                }
            },
            dataType: 'json'
        });
    }

    function collectParams()
    {
        return {
            limit: $('#products_limit_select').val(),
            page: $.data(document.body, 'current_page')
        }
    }

    function fillProducts(products)
    {
        var productsList = $('#products_list_table'),
            list = products.list || [],
            count = products.count || 0;
        $('#products_list_table .products_row').remove();
        $('#found_products').html(count);
        $(list).each(function(){
            var product = $('<tr class="products_row"></tr>')
                .append('<td>' + this.id + '<td>')
                .append('<td>' + this.definition + '<td>')
                .append('<td>' + this.article + '<td>')
                .append('<td>' + this.price + '<td>')
                .append('<td>' + this.advice_price + '<td>');
            productsList.append(product);
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
});

