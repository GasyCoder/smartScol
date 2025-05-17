import { createPopper } from '@popperjs/core';
import { toMin, toTime, toTwelve, randomId, attr } from '../function'
export default function TimePicker(selector,opt) {

    let options = {
        format: opt.format ? opt.format : 24,
        interval : opt.interval ? opt.interval : 30,
        start : opt.start ? opt.start : '00:00',
        end : opt.end ? opt.end : '23:59',
        placement : opt.placement ? opt.placement : 'bottom-start',
        class: {
            dropdown: 'nk-timepicker-dropdown',
            dropdownItem: 'nk-timepicker-time',
        }
    }

    let timeInterval = options.interval;
    let timeFormat = options.format;
    let timeStart = options.start;
    let timeEnd = options.end;
    let totalTime = toMin(timeEnd) - toMin(timeStart);
    let timeSlot = Math.floor(totalTime / timeInterval);
    let items = []

    let startTime = toMin(timeStart);
  
    for (let i = 0; i < timeSlot+1; i++) {
        let currentTime = startTime;
        let timeString = function(){
            if(timeFormat == 12){
                return toTwelve(toTime(currentTime));
            }else{
                return toTime(currentTime)
            }
        };
        items.push(`<li><button class="relative w-full px-5 py-1.5 flex items-center rounded-[inherit] text-xs leading-5 font-normal text-base-600 hover:text-primary-600 hover:bg-base-50 transition-all duration-[400ms] ${options.class.dropdownItem}" data-picker-text="${timeString()}" type="button">
            ${timeString()}
        </button></li>`
        )
        startTime = currentTime + timeInterval;
    }

    let itemsMarkups = items.join('');
    selector.classList.add('peer');
    selector.addEventListener("click", function(e){
        e.preventDefault();
        selector.classList.contains("show") ? selector.classList.remove("show") : selector.classList.add("show");
        createPopper(selector, selector.nextElementSibling, {
            placement: options.placement,
        });
        selector.nextElementSibling.style.display = 'block';
    })

    document.addEventListener("mouseup", function(e){
        e.preventDefault();
        if(selector !== e.target){
            selector.classList.remove("show");
            selector.nextElementSibling.style.display = 'none';
        }
    })

    let id = selector.id ? selector.id : randomId(8);
    
    if(!selector.id){
        attr(selector,'id',id);
    }

    let dropdownTemplate = `
    <ul class="nk-timepicker-dropdown absolute min-w-[180px] border border-gray-300 dark:border-gray-900 bg-white dark:bg-gray-950 rounded-md shadow-md z-[1000] ${options.class.dropdown}" data-picker-id="${id}" style="max-height:320px;overflow:auto;display:none;">
        ${itemsMarkups}
    </ul>
    `
    selector.insertAdjacentHTML('afterend', dropdownTemplate);

    let timeSelector = document.querySelectorAll(`.${options.class.dropdownItem}`);
    timeSelector.forEach(item => {
        item.addEventListener("click", function(e){
            e.preventDefault();
            let itemtext = item.dataset.pickerText;
            let input = document.getElementById(item.closest(`.${options.class.dropdown}`).dataset.pickerId);
            input.value = itemtext;
            //set active slot
            let allItems = item.closest(`.${options.class.dropdown}`).querySelectorAll(`.${options.class.dropdownItem}`);
            allItems.forEach(otherItem=>{
                otherItem.classList.remove('active');
            })
            item.classList.add('active');
            selector.classList.remove("show");
          });
    })
}