    const alert = document.querySelector('.alert');
    (async function () {
    let res =  await new Promise(function(resolve, reject) {
    alert.addEventListener('animationend', (e)=> {
    resolve('end');
} )
});
    alert.remove();
})();


        const myform = document.querySelector('.myform');
        const linkDelete = document.querySelector('.js-pins-delete');
        // window.myform = myform;

        linkDelete.onclick = function(e) {
        e.preventDefault();
        window.confirm('are you sure') && myform.submit();
    }
