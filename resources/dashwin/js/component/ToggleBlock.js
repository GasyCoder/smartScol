import { slideUp, slideDown } from '../function';

export default function ToggleBlock(el){
    const elm = el ? document.querySelectorAll(el) : document.querySelectorAll('.block-toggle');
    elm.forEach(function(item){
        let activeclass = item.dataset.activeClass ? item.dataset.activeClass : 'active';
        item.addEventListener("click", function(e){
            e.preventDefault();
            let target = document.querySelector(item.dataset.target);
            if (window.getComputedStyle(target).display === 'none') {
                item.classList.add(activeclass)
                slideDown(target, 400);
                target.classList.add('hidden')
                target.classList.remove('block')
            } else {
                item.classList.remove(activeclass)
                slideUp(target, 400);
                target.classList.remove('hidden')
                target.classList.add('block')
            }
        })
    })
}