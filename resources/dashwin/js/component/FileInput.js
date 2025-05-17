export default function FileInput(selector) {
    const elm = document.querySelectorAll(selector);
    elm.forEach(item => {
        let old = item.nextElementSibling.textContent, allFile = [];
        item.addEventListener("change", function(e){
            for (var i = 0; i < item.files.length; i++) { allFile[i] = item.files[i].name }
            old = (allFile) ? allFile.join(', ') : old;
            item.nextElementSibling.innerHTML = old;
        });
    })
};