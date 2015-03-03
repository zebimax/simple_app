function checkBrandSpecailText(id){
    if($("#brands_"+id).prop("checked")==true){
        $("#brands_"+id).prop("checked", false);
    }else{
        $("#brands_"+id).prop("checked", true);
    }
    checkBrandSpecail(id);
}

function checkBrandSpecail(id){
    if($("#brands_"+id).prop("checked")==true){
        var brand = $("#brands_"+id).val();
        var brandFilter = $("#brands_filter").val();
        $("#brands_filter").val(brandFilter + "," + brand)
        loadProducts();
    }else{
        removeBrandSpecail(id);
    }
}
function removeBrandSpecail(id){
    var brand = $("#brands_"+id).val();
    var brandFilter = $("#brands_filter").val();
    var brandArray = brandFilter.split(",");
    var newBrandFilter = "";
    for (var i=0; i<brandArray.length; i++) {
        if(brandArray[i] == brand || brandArray[i] == "")
            continue;
        newBrandFilter = newBrandFilter + "," + brandArray[i];
    }
    $("#brands_filter").val(newBrandFilter)
    $("#brands_"+id).prop("checked",false);
    loadProducts();
}
function closeAllBrandsSpecail(){
    $("[name='brands']").prop("checked",false);
    $("#brands_filter").val("")
    loadProducts();
}
function checkKortsByText(id){
    if($("#korts_"+id).prop("checked")==true){
        $("#korts_"+id).prop("checked",false);
    }else{
        $("#korts_"+id).prop("checked",true);
    }
    checkKorts(id);
}

function checkKorts(id){
    if($("#korts_"+id).prop("checked")==true){
        var korts = $("#korts_"+id).val();
        var kortsFilter = $("#discounts_filter").val();
        $("#discounts_filter").val(kortsFilter+korts + ",")
        loadProducts();
    }else{
        removeKorts(id);
    }
}

function removeKorts(id){
    var korts = $("#korts_"+id).val();
    var kortsFilter = $("#discounts_filter").val();
    var kortsArray = kortsFilter.split(",");
    var newKortsFilter = "";
    for (var i=0; i<kortsArray.length; i++) {
        if(parseInt(kortsArray[i]) === parseInt(korts) || kortsArray[i] == "")
            continue;
        newKortsFilter = newKortsFilter + kortsArray[i] + ",";
    }
    $("#discounts_filter").val(newKortsFilter)
    $("#korts_"+id).prop("checked",false);
    loadProducts();
}

function closeAllKorts(){
    $("[name='korts']").prop("checked",false);
    $("#discounts_filter").val("")
    loadProducts();
}
$(function(){
    $('.toggle-options').click( function () {
        $(this).toggleClass("closed");
        $(this).toggleClass("open");
        $(".options-box").slideToggle(500);
        if($("#brand_open").val() == "1")
            $("#brand_open").val(0);
        else
            $("#brand_open").val(1);
    });
    $('.toggle-korting').click( function () {
        $(this).toggleClass("closed");
        $(this).toggleClass("open");
        $(".korting-box").slideToggle(500);
        if($("#discounts_open").val() == "1")
            $("#discounts_open").val(0);
        else
            $("#discounts_open").val(1);
    });
    $(".prod-box-min").mouseover(function(){
        $(this).find(".leverbaar").removeClass("hide");}).mouseout(function(){
        $(this).find(".leverbaar").addClass("hide");
    })
});
