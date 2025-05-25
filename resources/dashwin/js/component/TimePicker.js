import {TimePicker} from "../addon";
import {extendObject} from "../function";

export default function TimePickerElement(selector,opt){
    let elm = document.querySelectorAll(selector);
    if( elm.length > 0 ){
      elm.forEach(item => {
        let format = item.dataset.format ? parseInt(item.dataset.format) : 12;
        let interval = item.dataset.interval ? parseInt(item.dataset.interval) : 30;
        let startTime = item.dataset.startTime ? item.dataset.startTime : '12:00';
        let endTime = item.dataset.endTime ? item.dataset.endTime : '23:00';
        let placement = item.dataset.placement ? item.dataset.placement : 'bottom-start';
        let def = {
          format: format,
          interval : interval,
          start : startTime,
          placement : placement
        }, 
        attr = (opt) ? extendObject(def, opt) : def;
        TimePicker(item,attr)
      })
    }
}