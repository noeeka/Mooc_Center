import Cookies from 'js-cookie';
import env from '../../build/env';
import semver from 'semver';
import packjson from '../../package.json';

function request(){
    var ajaxData = {};
    ajaxData.timestamp = ((new Data()).getTime())/1000;
    ajaxData.center_token = Cookies.get('center_token');
    ajaxData.salt = Cookies.get('salt');
    ajaxData.sign =encrypt_key([request.url,ajaxData.timestamp,ajaxData.center_token,ajaxData.salt],'');
}

