$(function() {
var id = getUrl('id');
            request({
                type: 'post',
                url: '/api/sysmessage/read',
                dataType: 'json',
                data: {
                    id:id
                },
                success: function(res) {
                    console.log(res)
                    console.log(res.data.content)
                    if (res.status == 1) {
                        var content = JSON.parse(res.data.content);
                        // console.log(content)
                        $('.my p').text(content.my);
                        $('.admin p').text(content.reply);
                        $('.replyContent h5').text(res.data.user_nickname);
                        $('.more').attr('href',res.data.mb_url +'&from=reply');
                        if(!res.data.avatar==''){
                            $('.my .photo').attr('src',res.data.avatar);
                        }
                    }
                }
            },true);




})
