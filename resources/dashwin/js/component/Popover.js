import { createPopper } from '@popperjs/core';
import {isRTL} from "../function";
export default function Popover(el){
    const elm = document.querySelectorAll('.popover-toggle');
    const class_top = `[&[data-popper-placement^='top']_.popover-arrow]:h-2 [&[data-popper-placement^='top']_.popover-arrow]:w-4 [&[data-popper-placement^='top']_.popover-arrow]:-bottom-2 [&[data-popper-placement^='top']_.popover-arrow]:left-1/2 [&[data-popper-placement^='top']_.popover-arrow]:-translate-x-1/2 [&[data-popper-placement^='top']_.popover-arrow]:before:border-t-gray-300 [&[data-popper-placement^='top']_.popover-arrow]:before:dark:border-t-gray-900 [&[data-popper-placement^='top']_.popover-arrow]:before:border-t-8 [&[data-popper-placement^='top']_.popover-arrow]:before:border-r-8 [&[data-popper-placement^='top']_.popover-arrow]:before:border-b-0 [&[data-popper-placement^='top']_.popover-arrow]:before:border-l-8 [&[data-popper-placement^='top']_.popover-arrow]:after:bottom-px [&[data-popper-placement^='top']_.popover-arrow]:after:left-0 [&[data-popper-placement^='top']_.popover-arrow]:after:border-t-white [&[data-popper-placement^='top']_.popover-arrow]:after:dark:border-t-gray-950 [&[data-popper-placement^='top']_.popover-arrow]:after:border-t-8 [&[data-popper-placement^='top']_.popover-arrow]:after:border-r-8 [&[data-popper-placement^='top']_.popover-arrow]:after:border-b-0 [&[data-popper-placement^='top']_.popover-arrow]:after:border-l-8`,
    class_right = `[&[data-popper-placement^='right']_.popover-arrow]:h-4 [&[data-popper-placement^='right']_.popover-arrow]:w-2 [&[data-popper-placement^='right']_.popover-arrow]:-left-2 [&[data-popper-placement^='right']_.popover-arrow]:top-1/2 [&[data-popper-placement^='right']_.popover-arrow]:-translate-y-1/2 [&[data-popper-placement^='right']_.popover-arrow]:before:border-r-gray-300 [&[data-popper-placement^='right']_.popover-arrow]:before:dark:border-r-gray-900 [&[data-popper-placement^='right']_.popover-arrow]:before:border-t-8 [&[data-popper-placement^='right']_.popover-arrow]:before:border-r-8 [&[data-popper-placement^='right']_.popover-arrow]:before:border-b-8 [&[data-popper-placement^='right']_.popover-arrow]:before:border-l-0 [&[data-popper-placement^='right']_.popover-arrow]:after:left-px [&[data-popper-placement^='right']_.popover-arrow]:after:top-0 [&[data-popper-placement^='right']_.popover-arrow]:after:border-r-white [&[data-popper-placement^='right']_.popover-arrow]:after:dark:border-r-gray-950 [&[data-popper-placement^='right']_.popover-arrow]:after:border-t-8 [&[data-popper-placement^='right']_.popover-arrow]:after:border-r-8 [&[data-popper-placement^='right']_.popover-arrow]:after:border-b-8 [&[data-popper-placement^='right']_.popover-arrow]:after:border-l-0`,
    class_bottom = `[&[data-popper-placement^='bottom']_.popover-arrow]:h-2 [&[data-popper-placement^='bottom']_.popover-arrow]:w-4 [&[data-popper-placement^='bottom']_.popover-arrow]:-top-2 [&[data-popper-placement^='bottom']_.popover-arrow]:left-1/2 [&[data-popper-placement^='bottom']_.popover-arrow]:-translate-x-1/2 [&[data-popper-placement^='bottom']_.popover-arrow]:before:border-b-gray-300 [&[data-popper-placement^='bottom']_.popover-arrow]:before:dark:border-b-gray-900 [&[data-popper-placement^='bottom']_.popover-arrow]:before:border-t-0 [&[data-popper-placement^='bottom']_.popover-arrow]:before:border-r-8 [&[data-popper-placement^='bottom']_.popover-arrow]:before:border-b-8 [&[data-popper-placement^='bottom']_.popover-arrow]:before:border-l-8 [&[data-popper-placement^='bottom']_.popover-arrow]:after:top-px [&[data-popper-placement^='bottom']_.popover-arrow]:after:left-0 [&[data-popper-placement^='bottom']_.popover-arrow]:after:border-b-white [&[data-popper-placement^='bottom']_.popover-arrow]:after:dark:border-b-gray-950 [&[data-popper-placement^='bottom']_.popover-arrow]:after:border-t-0 [&[data-popper-placement^='bottom']_.popover-arrow]:after:border-r-8 [&[data-popper-placement^='bottom']_.popover-arrow]:after:border-b-8 [&[data-popper-placement^='bottom']_.popover-arrow]:after:border-l-8`,
    class_left = `[&[data-popper-placement^='left']_.popover-arrow]:h-4 [&[data-popper-placement^='left']_.popover-arrow]:w-2 [&[data-popper-placement^='left']_.popover-arrow]:-right-2 [&[data-popper-placement^='left']_.popover-arrow]:top-1/2 [&[data-popper-placement^='left']_.popover-arrow]:-translate-y-1/2 [&[data-popper-placement^='left']_.popover-arrow]:before:border-l-gray-300 [&[data-popper-placement^='left']_.popover-arrow]:before:dark:border-l-gray-900 [&[data-popper-placement^='left']_.popover-arrow]:before:border-t-8 [&[data-popper-placement^='left']_.popover-arrow]:before:border-r-0 [&[data-popper-placement^='left']_.popover-arrow]:before:border-b-8 [&[data-popper-placement^='left']_.popover-arrow]:before:border-l-8 [&[data-popper-placement^='left']_.popover-arrow]:after:right-px [&[data-popper-placement^='left']_.popover-arrow]:after:top-0 [&[data-popper-placement^='left']_.popover-arrow]:after:border-l-white [&[data-popper-placement^='left']_.popover-arrow]:after:dark:border-l-gray-950 [&[data-popper-placement^='left']_.popover-arrow]:after:border-t-8 [&[data-popper-placement^='left']_.popover-arrow]:after:border-r-0 [&[data-popper-placement^='left']_.popover-arrow]:after:border-b-8 [&[data-popper-placement^='left']_.popover-arrow]:after:border-l-8`;

    elm.forEach(function(item){
        const id = "id" + Math.random().toString(16).slice(2);
        item.setAttribute("data-target", id);
        const title = item.dataset.title ? item.dataset.title : false;
        const content = item.dataset.content ? item.dataset.content : 'placeholder';
        const markup = `<div tabindex="0" id="${id}" class="popover-content absolute w-screen max-w-[276px] border border-gray-300 dark:border-gray-900 bg-white dark:bg-gray-950 rounded-md shadow-md z-[1070] hidden [&.show]:block ${class_top} ${class_right} ${class_bottom} ${class_left}">
            <div class="popover-arrow absolute before:block before:h-0 before:w-0 before:border-transparent after:absolute after:block after:h-0 after:w-0 after:border-transparent"></div>
            ${title ? `<h3 class="px-3 py-2 border-b border-gray-300 dark:border-gray-900 bg-gray-100 dark:bg-gray-900 text-base leading-5 font-bold font-heading text-slate-700 dark:text-white rounded-t-[inherit]">${title}</h3>`: ''}
            <div class="px-3 py-2 text-sm leading-6">${content}</div>
        </div>`
        document.body.insertAdjacentHTML('beforeend', markup);
        item.addEventListener("click", function(e){
            e.preventDefault();
            const offset = item.dataset.offset ? item.dataset.offset : [0, 10];
            const placement = item.dataset.placement ? item.dataset.placement : 'right';
            const rtlPlacement = item.dataset.rtlPlacement ? item.dataset.rtlPlacement : placement;
            const popover = document.getElementById(item.dataset.target);
            popover.classList.contains("show") ? popover.classList.remove("show") : popover.classList.add("show");
            createPopper(item, popover, {
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
    })
}