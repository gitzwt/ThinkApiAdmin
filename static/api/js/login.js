/*API login页js*/

$("body").keyup(function () {
    if (event.which === 13){
        $(".submit").trigger("click");
    }
});

$(".submit").click(function() {
    $('.ui.form').form({
        fields: {
            password: {
                identifier: 'key',
                rules: [
                    {
                        type: 'empty',
                        prompt: '密钥key不能为空'
                    }
                ]
            }
        }
    });
    var key = $("input[name ='key']").val();
    $.ajax({
        url: "#",
        type: "post",
        data: {key: key},
        dataType: "json",
        success: function (data) {
            if (data.code === 301) {
                layer.msg(data.msg);
            } else if (data.code === 302) {
                layer.msg(data.msg);
            } else if (data.code === 303) {
                layer.msg(data.msg);
            } else if (data.code === 200) {
                layer.msg(data.msg);
                //登录成功跳转
                setTimeout(function(){
                    window.location.href = data.url;
                },2000);
            }
        }
    });
});
