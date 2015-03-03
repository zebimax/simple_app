var loadingDiv = "<div id=\"loading\" style=\"z-index:999;position:absolute;left:45%;\"><img src=\"./img/loading.gif\"><\/div>";
$(document).ready(function(){
    //check the footer option, and adjust the footer postion
    if($("#footer").length>0){
        var windowHeight = $(window).height();
        var wrapperHeight = $("#wrapper").height();
        var diffHeight = windowHeight - wrapperHeight;
        if(diffHeight>0){
            var leftColHeight = $("#left_col").height() + diffHeight;
            $("#left_col").height(leftColHeight);
        }
    }
    var formSelector = $('form[name="quick_find3"]');
    if (formSelector.length > 0) {
        var form = formSelector.first();
        form.bind('submit', function(){
            $('input[name="brands"]').attr('checked', false);
        });
    }
});
function loadCategoryProduct() {
    var sTop = document.body.scrollTop+document.documentElement.scrollTop;
    $(document.body).append(loadingDiv);
    $("#loading").css("top",(sTop + document.documentElement.clientHeight/2 - 100) + "px");
    document.quick_find.submit();
    document.quick_find3.submit();
}

function AddQuantity(id) {
    var curent_value = parseInt($("#products_quantity_"+id).val());
    var minQty = parseInt($("#min_order_qty_"+id).val());
    var new_value =curent_value + minQty;
    document.getElementById("products_quantity_"+id).value = new_value;
}

function MiusQuantity(id) {
    var curent_value = parseInt($("#products_quantity_"+id).val());
    var minQty = parseInt($("#min_order_qty_"+id).val());
    var new_value = curent_value - minQty;
    if(new_value < minQty)new_value = minQty;
    document.getElementById("products_quantity_"+id).value = new_value;
}

function addIntoCart(id, name, price,brand,category) {
    confirmIntoCart(id, name, price,brand,category);
}

function addProductToCart(id, quantity) {
    $.ajax({
        type: "GET",
        url:"shopping_cart.php?action=addIntoCart&temp="+Math.random(),
        data:{
            products_id: id,
            value: quantity
        },
        success:function (data, status){
            var location = 'index.php';
            if (data.result === 'success') {
                location = "shopping_cart.php";
            } else if (data.result === 'need_enable_cookie'){
                location = "need_enable_cookie.php";
            }
            window.location.href = location;
        },
        dataType:'json'
    });
}

function confirmIntoCart(id) {

    $.ajax({
        type: "GET",
        url:"shopping_cart.php?action=addIntoCart&temp="+Math.random(),
        data:{
            products_id:id,
            value:$("#products_quantity").val()
        },
        success:function (data, status){
            var location = 'index.php';
            if (data.result === 'success') {
                location = "shopping_cart.php";
            } else if (data.result === 'need_enable_cookie'){
                location = "need_enable_cookie.php";
            }
            window.location.href = location;
        },
        dataType:'json'
    });
}

function closeIntoCart() {
    $("#msgDiv").dialog('destroy');
}

function openVoorraad(id) {
    $("#msgDiv").dialog({title:"VOORRAAD UPDATE", height:160,width:400});
    $.ajax({
        type: "GET",
        url:"load_forms_list.php?action=load_voorraad&temp="+Math.random(),
        data:{
            products_id:id
        },
        success:function (data, status){
            document.getElementById("msgDiv").innerHTML = data;
        }
    });
}

function sendVoorraad(id){
    if(!isMail($("#email_address_notify").val())){
        alert('Het lijkt erop dat uw e-mailadres onjuist is!');
        $("#email_address_notify").focus();
        return;
    }
    $.ajax({
        type: "GET",
        url:"./load_forms_list.php?action=send_voorraad&temp="+Math.random(),
        data:{
            emailadresse:$("#email_address_notify").val(),
            products_id:id
        },
        success:function (data, status){
            alert(data);
            $("#msgDiv").dialog('destroy');
        }
    });
}

function isMail(strText){
    var   strReg="";
    var   r;
    strReg=/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/i;
    r=strText.search(strReg);
    if(r==-1)
        return false;
    else
        return true;
}

function searchProducts() {
    var sortsCategory = $("#sortsCategory");
    if(sortsCategory.val()) {
        document.getElementById("sort").value = sortsCategory.val();
    }
    loadProducts();
}

function loadProducts() {
    var sTop = document.body.scrollTop+document.documentElement.scrollTop;
    $(document.body).append(loadingDiv);
    $("#loading").css("top",(sTop + document.documentElement.clientHeight/2 - 100) + "px");
    var formSelector = $('form[name="quick_find3"]');
    if (formSelector.length > 0) {
        var form = formSelector.first();
        form.submit();
    }
}

function clearKeyWordsInput() {
    if($("#ckeywords").val() == ""){
        loadProducts();
    }
}

$(function(){
    $(".prod-box-min-five").mouseover(function(){
        $(this).find(".leverbaar").removeClass("hide");}).mouseout(function(){
        $(this).find(".leverbaar").addClass("hide");
    })
    $("#tabs").tabs();
});
