export default function Progress(selector){
    let progressBar = document.querySelectorAll(selector);
    progressBar.forEach(item => {
        let amount = item.dataset.progress;
        item.style.width = amount;
    })
}