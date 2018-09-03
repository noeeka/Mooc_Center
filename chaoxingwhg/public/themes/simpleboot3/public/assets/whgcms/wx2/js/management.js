//选择性别
var nameEl = document.getElementById('sex');
var data1 = [{
    text: '男',
    value: 1
}, {
    text: '女',
    value: 2
}];

var picker = new Picker({
    data: [data1],
    selectedIndex: [0, 1, 2]
    // title: '选择性别'
});

picker.on('picker.select', function(selectedVal, selectedIndex) {
    nameEl.innerText = data1[selectedIndex[0]].text;
})

// picker.on('picker.change', function (index, selectedIndex) {
//     console.log(index);
//     console.log(selectedIndex);
// });

picker.on('picker.valuechange', function(selectedVal, selectedIndex) {
    console.log(selectedVal);
    console.log(selectedIndex);
});

nameEl.addEventListener('click', function() {
    picker.show();
});
$('body').on('click', '.picker-mask', function() {
    picker.hide();
})
//调用日期插件
// $('#birthday').date();
var calendar = new datePicker();
calendar.init({
    'trigger': '#birthday',
    /*按钮选择器，用于触发弹出插件*/
    'type': 'date',
    /*模式：date日期；datetime日期时间；time时间；ym年月；*/
    // 'minDate': '1900-1-1',
    /*最小日期*/
    // 'maxDate': '2100-12-31',
    /*最大日期*/
    'onSubmit': function() { /*确认时触发事件*/
        var theSelectData = calendar.value;
    },
    'onClose': function() { /*取消时触发事件*/ }
});