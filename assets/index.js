    const alert = document.querySelector('.alert');
    (async function () {
        try {
            let res = await new Promise(function (resolve, reject) {
                alert.addEventListener('animationend', (e) => {
                    resolve('end');
                })
            }).catch((err) => console.log(err.message));

            alert.remove();
        }catch(err) {
            console.log(err.message);
        }
})();

try {
    const myform = document.querySelector('.myform');
    const linkDelete = document.querySelector('.js-pins-delete');
    // window.myform = myform;

    linkDelete.onclick = function (e) {
        e.preventDefault();
        window.confirm('are you sure') && myform.submit();
    }
}catch(err) {
    console.log('error: '+ err.message);
}


    // const error_login = document.querySelector('.error_login');
    // new Promise(function (resolve, reject) {
    //     error_login.addEventListener('animationend', (e) => {
    //         resolve('end');
    //     })
    // })
    //     .then((res) => {
    //         error_login.remove();
    //     }, (err) => (console.log(err.message)))


