let refinfo = document.referrer;
if (!refinfo) {
    alert('直リンクはご遠慮下さいませ(;・∀・)');
}

let chkAdminOperation = setInterval(chkGameState, 1000);

let data = {
    'fnc_name' : '',
};

let bakResTxt = '';
function chkGameState() {
    data.fnc_name = 'chkNextGame';

    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = () => {
        if (xhr.readyState == 4 && xhr.status == 200) {
            // 通信の完了時 かつ 通信成功時
            if (xhr.responseText !== bakResTxt && bakResTxt !== '') {
                submitPage(xhr.responseText);
            } else {
                bakResTxt = xhr.responseText;
            }
        }
    }

    xhr.open('POST', './Ajax.php');
    xhr.setRequestHeader('content-type', 'application/x-www-form-urlencoded;charset=UTF-8');
    xhr.send(encodeHtmlForm(data));
}

function encodeHtmlForm(data)
{
    let params = [];

    for (let name in data) {
        let value = data[name];
        let param = encodeURIComponent(name) + '=' + encodeURIComponent(value);

        params.push(param);
    }

    return params.join( '&' ).replace( /%20/g, '+' );
}

function submitPage(res) {
    let state = res.slice(-1);
    if (state === '0') {
        document.finishGame.submit();
    } else {
        document.nextGame.submit();
    }
}
