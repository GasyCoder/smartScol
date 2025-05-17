import { createPopper } from '@popperjs/core';
import { isRTL } from "../function";

export default function Dropdown(el){
    const elm = document.querySelectorAll('.dropdown-toggle');
    elm.forEach(function(item){
        item.addEventListener("click", function(e){
            e.preventDefault();
             const offset = item.dataset.offset ? [parseInt(item.dataset.offset.split(',')[0]), parseInt(item.dataset.offset.split(',')[1])] : [0, 0];
             const rtlOffset = item.dataset.rtlOffset ? [parseInt(item.dataset.rtlOffset.split(',')[0]), parseInt(item.dataset.rtlOffset.split(',')[1])] : offset;
             let placement = item.dataset.placement ? item.dataset.placement : 'bottom-start';
             let rtlPlacement = item.dataset.rtlPlacement ? item.dataset.rtlPlacement : 'bottom-end';

              var getNextSibling = function (elem, selector) {

                 var sibling = elem.nextElementSibling;
         
                 while (sibling) {
                     if (sibling.matches(selector)) return sibling;
                     sibling = sibling.nextElementSibling
                 }
             
             };
             createPopper(item, getNextSibling(item, '.dropdown-menu'), {
                 placement : isRTL() ? rtlPlacement :placement,
                 modifiers: [
                     {
                       name: 'offset',
                       options: {
                         offset: isRTL() ? rtlOffset : offset,
                       },
                     },
                     {
                         name: 'preventOverflow',
                         options: {
                           padding: 8,
                           altAxis: true,
                           boundary: '#pagecontent',
                         },
                     },
                 ],
             });
             item.classList.contains("show") ? item.classList.remove("show") : item.classList.add("show");
        })
        document.addEventListener("click", function(e){
            let clickable = e.target.closest('.clickable'),
                choiceItem = e.target.classList.contains('choices__item');
            if(item !== e.target && !clickable && !e.target.classList.contains('clickable') && !choiceItem ){
                item.classList.remove("show");
            }
        })
     })
 }