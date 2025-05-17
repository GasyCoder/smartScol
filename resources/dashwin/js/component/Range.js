import noUiSlider from 'nouislider';
import {extendObject, isRTL} from "../function";

export default function Range(selector,opt){
    let elm = document.querySelectorAll(selector);
    if( elm != 'undefined' && elm != null ){
      elm.forEach(item => {
        let itemId = item.id;

        let start = item.dataset.start
            start = /\s/g.test(start) ? start.split(' ') : start
            start = start ? start : 0;
        let connect = item.dataset.connect;
            connect = /\s/g.test(connect) ? connect.split(' ') : connect;
            connect = typeof connect == 'undefined' ? 'lower' : connect;
            connect = connect == 'true' || connect == 'false' ? JSON.parse(connect) : connect;
        let min = item.dataset.min ? parseInt(item.dataset.min) : 0;
        let max = item.dataset.max ? parseInt(item.dataset.max) : 100;
        let minDistance = item.dataset.minDistance ? parseInt(item.dataset.minDistance) : null;
        let maxDistance = item.dataset.maxDistance ? parseInt(item.dataset.maxDistance) : null;
        let step = item.dataset.step ? parseInt(item.dataset.step) : 1;
        let orientation = item.dataset.orientation ? item.dataset.orientation : 'horizontal';
        let tooltip = item.dataset.tooltip ? JSON.parse(item.dataset.tooltip) : false;
        var def = {
          start: start,
          connect: connect,
          direction: isRTL() ? "rtl" : "ltr",
          range: {
              'min': min,
              'max': max
          },
          margin: minDistance,
          limit: maxDistance,
          step: step,
          orientation: orientation,
          tooltips: tooltip
        },
        attr = (opt) ? extendObject(def, opt) : def;
        noUiSlider.create(item, attr);
      })
    }
}