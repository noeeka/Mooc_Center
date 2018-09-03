function createCodeEditor(id, language){
    //当前textarea对象属性
    var width = $(id).width()+'px';
    var height = $(id).height()+'px';
    var eid = $(id).attr('id');
    var name = $(id).attr('name');
    var val  = $(id).val();

    //生成新对象
    var html = '<input type="hidden" name="'+name+'" value="'+val+'"><div id="'+eid+'" style="width: '+width+';height: '+height+'"></div>';

    //多实例容器
    var objectMap = {};

    //替换元素
    $(id).replaceWith(html);

    //monaco实例化
    require.config({
        paths: { 'vs': '/static/js/monaco-editor/min/vs' }, 'vs/nls': {
            availableLanguages: {
                '*': 'zh-cn'
            }
        }
    });
    require(['vs/editor/editor.main'], function () {
        objectMap[name] = monaco.editor.create(document.getElementById(eid), {
            value: val,
            language: language,
            wordWrap: "on",   //自动换行，注意大小写
            wrappingIndent: "indent",
        });
        //监听变更事件
        objectMap[name].onDidChangeModelContent(function(){
            $('[name='+name+']').val(objectMap[name].getValue());
        });
        monaco.editor.setTheme('vs');//hc-black vs vs-dark
    });
}