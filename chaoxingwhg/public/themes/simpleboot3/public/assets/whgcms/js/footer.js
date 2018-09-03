$(function(){
    //header
    function headerModule(template){
        console.log(template);
        var templateTpl = $('#'+template).html();
        $('#footer').html(templateTpl)
        // var templateCmp = Handlebars.compile(templateTpl);
        // $('#header').html(templateCmp(data))

    }
    $.ajax({
        type: 'get',
        url: '/api/homepage/pc_global',
        dataType: 'json',
        async:false,
        success: function(res) {
            console.log(res)
            if(res.status==1){
                $('#global-css').append(res.data.css);
                headerModule(res.data.footer_alias);
                $.ajax({
                    url: '/api/baseinfo/read',
                    dataType: 'json',
                    async:false,
                    success: function(res) {
                        if (res.status == 1) {
                            $('.site_title').text(res.data.site_title);
                            $('.venue_tel').text(res.data.venue_tel);
                            $('.venue_addr').text(res.data.venue_addr);
                            $('.pcaccess_count').text(res.data.pcaccess_count);
                        }
                    }
                });
                //加载友链
                $.ajax({
                    url: '/api/link/index',
                    dataType: 'json',
                    async: false,
                    success: function(res) {
                        if (res.status == 1) {
                            var html = '';
                            for (var i in res.data) {
                                var item = res.data[i];
                                html += '<li><a href="' + item['url'] + '" target="' + item['target'] + '">' + item['name'] + '</a></li>';
                            }
                            $('.friendly').html(html);
                        }
                    }
                });
            }
        }
    })
})