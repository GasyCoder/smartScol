import { createPopper } from '@popperjs/core';
import {isRTL} from "../function";

export default function Tooltip(el){
    const elm = document.querySelectorAll('.tooltip-toggle');
    const class_top = `[&[data-popper-placement^='top']]:after:top-full [&[data-popper-placement^='top']]:after:left-1/2 [&[data-popper-placement^='top']]:after:-translate-x-1/2 [&[data-popper-placement^='top']]:after:border-t-gray-800 [&[data-popper-placement^='top']]:after:border-t-6 [&[data-popper-placement^='top']]:after:border-r-6 [&[data-popper-placement^='top']]:after:border-b-0 [&[data-popper-placement^='top']]:after:border-l-6`,
    class_right = `[&[data-popper-placement^='right']]:after:right-full [&[data-popper-placement^='right']]:after:top-1/2 [&[data-popper-placement^='right']]:after:-translate-y-1/2 [&[data-popper-placement^='right']]:after:border-r-gray-800 [&[data-popper-placement^='right']]:after:border-t-6 [&[data-popper-placement^='right']]:after:border-r-6 [&[data-popper-placement^='right']]:after:border-b-6 [&[data-popper-placement^='right']]:after:border-l-0`,
    class_bottom = `[&[data-popper-placement^='bottom']]:after:bottom-full [&[data-popper-placement^='bottom']]:after:left-1/2 [&[data-popper-placement^='bottom']]:after:-translate-x-1/2 [&[data-popper-placement^='bottom']]:after:border-b-gray-800 [&[data-popper-placement^='bottom']]:after:border-t-0 [&[data-popper-placement^='bottom']]:after:border-r-6 [&[data-popper-placement^='bottom']]:after:border-b-6 [&[data-popper-placement^='bottom']]:after:border-l-6`,
    class_left =`[&[data-popper-placement^='left']]:after:left-full [&[data-popper-placement^='left']]:after:top-1/2 [&[data-popper-placement^='left']]:after:-translate-y-1/2 [&[data-popper-placement^='left']]:after:border-l-gray-800 [&[data-popper-placement^='left']]:after:border-t-6 [&[data-popper-placement^='left']]:after:border-r-0 [&[data-popper-placement^='left']]:after:border-b-6 [&[data-popper-placement^='left']]:after:border-l-6`;
    elm.forEach(function(item){
        const id = "id" + Math.random().toString(16).slice(2);
        item.setAttribute("data-target", id);
        const title = item.dataset.title ? item.dataset.title : false;
        const markup = `<div tabindex="0" id="${id}" class="text-white bg-gray-800 text-xs px-3 py-1.5 rounded-sm z-[10000] max-w-[200px] w-max bock break-words font-normal after:absolute after:block after:h-0 after:w-0 after:border-transparent after:z-[10000] [&[data-popper-reference-hidden]]:opacity-0 ${class_top} ${class_right} ${class_bottom} ${class_left}" >
        ${title}
        </div>`
        item.addEventListener("mouseenter", function(e){
            e.preventDefault();
            
            const offset = item.dataset.offset ? item.dataset.offset : [0, 10];
            const placement = item.dataset.placement ? item.dataset.placement : 'right';
            const rtlPlacement = item.dataset.rtlPlacement ? item.dataset.rtlPlacement : placement;
            
            document.body.insertAdjacentHTML('beforeend', markup);
            const tooltip = document.getElementById(item.dataset.target);
            createPopper(item, tooltip, {
                placement : isRTL() ? rtlPlacement :placement,
                modifiers: [
                    {
                      name: 'offset',
                      options: {
                        offset: offset,
                      },
                    },
                    {
                        name: 'flip',
                        options: {
                          fallbackPlacements: ['top', 'bottom'],
                        },
                    },
                ],
            });
        })
        item.addEventListener("mouseleave", function(e){
            e.preventDefault();
            const tooltip = document.getElementById(item.dataset.target);
            tooltip.remove();
        })
    })
}