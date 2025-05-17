import {slideUp, slideDown} from '../function'

export default function Accordion(el){
    const elm = document.querySelectorAll('.accordion-toggle');
    elm.forEach(function(item){
        let parent = item.parentElement, 
            accordion_body = item.nextElementSibling, 
            accordion_root = parent.parentElement, 
            accordion_items = accordion_root.children, 
            speed = 400;

        //on load
        parent.classList.contains("active") ? accordion_body.style.display = 'block' : accordion_body.style.display = 'none';
        
        item.addEventListener("click", function(e){
            e.preventDefault();
            Array.from(accordion_items).forEach(item => {
                if(item !== parent && !accordion_root.classList.contains('manual-close')){
                    slideUp(item.querySelector('.accordion-body'),speed);
                    item.classList.remove("active");
                }
            });
            if(!parent.classList.contains("active")){
                parent.classList.add("active");
                slideDown(accordion_body,speed);
            }else{
                slideUp(accordion_body,speed);
                parent.classList.remove("active");
            }
        })
    })
}