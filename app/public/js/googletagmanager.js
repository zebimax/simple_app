$(document).ready(function(){
    onAddToCartClick();
    onRemoveFromCartClick();
    onProductLinkClick();
    setTimeout('addImpressions()', 2000);
    setTimeout('addPromoImpressions()', 2000);
    onPromoBannerLinkClick();
    onCheckOutClick();
    onAddPurchasedProducts();
});

function addImpressions(){
    var impressions = [];
    var price, list;
    list = $('#prod-results').data('product_list');
    $('input.products_info_data').each(function(i, item){
        impressions.push({
            'name': $(item).data('name'),
            'id': $(item).data('id'),
            'price': $(item).data('price'),
            'brand': $(item).data('brand'),
            'category': $(item).data('category'),
            'list': list,
            'position': $(item).data('position')
        });
    });
    if (impressions.length > 0) {
        gtmPush({'impressions': impressions});
    }
}

function addPromoImpressions(){
    var impressions = [];
    $('input.banners_info_data').each(function(i, item){
        impressions.push({
            'id': $(item).data('id'),
            'name': $(item).data('name'),
            'position': $(item).data('position')
        });
    });
    if (impressions.length > 0) {
        gtmPush(
            {
                'promoView': {
                    'promotions': impressions
                }
            }
        );
    }
}

function onAddToCartClick(){
    $('.addToCart').click(function() {
        addToCartClick($(this).data('id'));
    });
}

function onRemoveFromCartClick(){
    $('.removeFromCart').click(function() {
        removeFromCartClick($(this).data('id'));
    });
}

function removeQuantity(id, quantity){
    var item = $('input.products_info_data[data-id="'+ id +'"]').first();
    if (item.length > 0) {
        gtmPush(
            {
                'remove': {
                    'products': [{
                        'name': item.data('name'),
                        'id': id,
                        'price': item.data('price'),
                        'brand': item.data('brand'),
                        'category': item.data('category'),
                        'quantity': quantity
                    }]
                }
            },
            'removeFromCart'
        );
    }
}

function addQuantity(id, quantity){
    var item = $('input.products_info_data[data-id="'+ id +'"]').first();
    if (item.length > 0) {
        gtmPush(
            {
                'add': {
                    'products': [{
                        'name': item.data('name'),
                        'id': id,
                        'price': item.data('price'),
                        'brand': item.data('brand'),
                        'category': item.data('category'),
                        'quantity': quantity
                    }]
                }
            },
            'addToCart'
        );
    }
}

function addToCartClick(id){
    var item = $('input.products_info_data[data-id="'+ id +'"]').first();

    if (item.length > 0) {
        onAddIntoCart(
            id,
            item.data('name'),
            item.data('price'),
            item.data('brand'),
            item.data('category'),
            $('#products_quantity_' + id).val()
        );
    }
}

function removeFromCartClick(id){
    var item = $('input.products_info_data[data-id="'+ id +'"]').first();

    if (item.length > 0) {
        removeFromCart(
            id,
            item.data('name'),
            item.data('price'),
            item.data('brand'),
            item.data('category'),
            $('#products_quantity_' + id).val()
        );
    }
}

function onProductLinkClick(){
    $('.productlink').click(function(event){
        event.preventDefault();
        var id = $(this).data('id'),
            location = $(this).attr('href'),
            product_info = $('.products_info_data[data-id="'+ id +'"]').first();
        gtmPush(
            {
                'click': {
                    'actionField': {'list': $('#prod-results').data('product_list')},
                    'products': [{
                        'name': product_info.data('name'),
                        'id': id,
                        'price': product_info.data('price'),
                        'brand': product_info.data('brand'),
                        'category': product_info.data('category')
                    }]
                }
            },
            'productClick',
            function() {
                document.location = location;
            }
        );
    });
}

function onPromoBannerLinkClick(){
    $('.banners_promo_link').click(function(event){
        event.preventDefault();
        var id = $(this).data('id'),
            location = $(this).attr('href'),
            banner_info = $('.banners_info_data[data-id="'+ id +'"]').first();
        var data = {
            'promoClick': {
                'promotions': [
                    {
                        'id': id,
                        'name': banner_info.data('name'),
                        'position': banner_info.data('position')
                    }]
            }
        };
        gtmPush(data, 'promotionClick', function() {
            document.location = location;
        });
    });
}

function removeFromCart(id, name, price, brand, category, quantity) {
    gtmPush(
        {
            'remove': {
                'products': [{
                    'name': name,
                    'id': id,
                    'price': price,
                    'brand': brand,
                    'category': category,
                    'quantity': quantity
                }]
            }
        },
        'removeFromCart',
        function() {
            document.location = 'shopping_cart.php?action=remove_product&products_id=' + id;
        }
    );
}

function onAddIntoCart(id, name, price, brand, category, quantity) {
    gtmPush(
        {
            'add': {
                'products': [{
                    'name': name,
                    'id': id,
                    'price': price,
                    'brand': brand,
                    'category': category,
                    'quantity': quantity
                }]
            }
        },
        'addToCart',
        function() {
            addProductToCart(id, quantity);
        }
    );
}

function onCheckOutClick(){
    $('#checkout_payment_submit').click(function(){
        var option = $('input[name="payment"]:checked').val();
        var products = [];
        $('.products_info_data').each(function(i, item) {
            products.push({
                'name': $(item).data('name'),
                'id': $(item).data('name'),
                'price': $(item).data('price'),
                'brand': $(item).data('brand'),
                'category': $(item).data('category'),
                'quantity': $(item).data('quantity')
            });
        });
        gtmPush(
            {
                'checkout': {
                    'actionField': {'step': 1, 'option': option},
                    'products': products
                }
            },
            'checkout',
            function() {
                $('form[name="checkout_payment"]').submit();
            }
        );
    });
}

function onAddPurchasedProducts(){
    var id, tax, revenue, shipping, coupon,
        order_info = $('#purchase_products_info'),
        products = [];
    if (order_info.length > 0) {
        id = order_info.data('id');
        tax = order_info.data('tax');
        revenue = order_info.data('revenue');
        shipping = order_info.data('shipping');
        coupon = order_info.data('coupon');
        $('.purchased_products_info_data').each(function(i, item) {
            products.push({
                'id': $(item).data('id'),
                'name': $(item).data('name'),
                'price': $(item).data('price'),
                'quantity': $(item).data('quantity')
            });
        });
        if (products.length > 0) {
            gtmPush(
                {
                    'purchase': {
                        'actionField': {
                            'id': id,
                            'tax': tax,
                            'revenue': revenue,
                            'shipping': shipping,
                            'coupon': coupon
                        },
                        'products': products
                    }
                }
            );
        }
    }
}
function gtmPush(data, event, callback){
    var item = {
        'ecommerce': data
    };
    if (typeof  event !== 'undefined') {
        item.event = event;
    }
    if (typeof  callback !== 'undefined') {
        item.eventCallback = callback;
    }
    dataLayer.push(item);
}