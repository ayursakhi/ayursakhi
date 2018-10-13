define(['jquery','mage/url'],function($,url){
    var pincodeCheck = function(){
        $('#pin-chek').click(function(){
            var pincode = $('#pincode-val').val();
            var baseUrl = url; 
            if(pincode){                
            $.ajax({
                data:"pincode="+pincode,
                url:baseUrl.build("pincode/pincode/validation"), //"http://local.ayursakhi.com/pincode/pincode/validation",
                type:"POST",
                success:function(response){
                    if(response){
                        $("#message").html(response);
                    }
                }
            })
        }
        else{
            $("#message").html("Please enter the valid pincode.");
        }
        })
    }
    return pincodeCheck;
})