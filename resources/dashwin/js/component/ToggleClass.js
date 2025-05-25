export default function ToggleClass(el){
    const elm = document.querySelectorAll('.class-toggle');
    elm.forEach(function(item){
        let target = document.querySelector(item.dataset.target);
        let activeclass = item.dataset.activeClass ? item.dataset.activeClass : 'active';
        let bodyScroll = item.dataset.bodyScroll === "off" ? true : false;
        item.addEventListener("click", function(e){
            let items = document.querySelectorAll(`[data-target="${item.dataset.target}"]`);
            items.forEach(function(single){
                single.classList.contains(activeclass) ? single.classList.remove(activeclass) : single.classList.add(activeclass);
            })
            target.classList.contains(activeclass) ? target.classList.remove(activeclass) : target.classList.add(activeclass);
            bodyScroll && document.body.classList.toggle('overflow-hidden');
        })
    })
}