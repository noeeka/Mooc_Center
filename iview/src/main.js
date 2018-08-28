import Vue from 'vue';
import iView from 'iview';
import {router} from './router/index';
import {appRouter} from './router/router';
import store from './store';
import App from './app.vue';
import '@/locale';
import 'iview/dist/styles/iview.css';
import VueI18n from 'vue-i18n';
import util from './libs/util';
import jq from 'jquery';
import axios from 'axios';

Vue.use(VueI18n);
Vue.use(iView);

Vue.prototype.axios_request = function (param, callback) {
    // 发送请求
    axios.get(
        'http://mooc.com/v1/proxy/index',
        {
            params: param
        }).then(callback);
};
Vue.prototype.request = function (param, callback, async) {
    if (async !== false) {
        async = true;
    }
    // 发送请求
    jq.ajax({
        url: 'http://mooc.com/v1/proxy/index',
        data: param,
        dataType: 'json',
        async: async,
        success: callback
    });
};
Vue.prototype.array_map = function (array, key1, key2) {
    let obj = {};
    for (let i in array) {
        let item = array[i];
        obj[item[key1]] = item[key2];
    }
    return obj;
};
Vue.prototype.dateFormat = function (timestamp) {
    let date = new Date();
    date.setTime(timestamp * 1000);
    let year = date.getFullYear();
    let month = date.getMonth() + 1;
    let day = date.getDate();
    let hour = date.getHours();
    let minute = date.getMinutes();
    return year + '-' + month + '-' + day + ' ' + hour + ':' + minute;
};

new Vue({
    el: '#app',
    router: router,
    store: store,
    render: h => h(App),
    data: {
        currentPageName: ''
    },
    mounted () {
        this.currentPageName = this.$route.name;
        // 显示打开的页面的列表
        this.$store.commit('setOpenedList');
        this.$store.commit('initCachepage');
        // 权限菜单过滤相关
        this.$store.commit('updateMenulist');
        // iview-admin检查更新
        util.checkUpdate(this);
    },
    created () {
        let tagsList = [];
        appRouter.map((item) => {
            if (item.children.length <= 1) {
                tagsList.push(item.children[0]);
            } else {
                tagsList.push(...item.children);
            }
        });
        this.$store.commit('setTagsList', tagsList);
    }
});
