import { Datepicker, DateRangePicker } from 'vanillajs-datepicker';

export default function DatePickerElement(selector,opt){
    let elm = document.querySelectorAll(selector);
    if( elm.length > 0 ){
      elm.forEach(item => {
        let autohide = item.dataset.autoHide ? JSON.parse(item.dataset.autoHide) : true;
        let clearBtn = item.dataset.clearBtn ? JSON.parse(item.dataset.clearBtn) : false;
        let format = item.dataset.format ? item.dataset.format : 'mm/dd/yyyy';
        let maxView = item.dataset.maxView ? parseInt(item.dataset.maxView) : 3;
        let pickLevel = item.dataset.pickLevel ? parseInt(item.dataset.pickLevel) : 0;
        let startView = item.dataset.startView ? parseInt(item.dataset.startView) : 0;
        let title = item.dataset.title ? item.dataset.title : '';
        let todayBtn = item.dataset.todayBtn ? JSON.parse(item.dataset.todayBtn) : false;
        let todayBtnMode = item.dataset.todayBtnMode ? parseInt(item.dataset.todayBtnMode) : 0;
        let weekStart = item.dataset.weekStart ? parseInt(item.dataset.weekStart) : 0;
        let rangePicker = item.dataset.range ? true : false;
        let def = {
          autohide: autohide,
          clearBtn: clearBtn,
          format: format,
          maxView: maxView,
          pickLevel: pickLevel,
          startView: startView,
          title: title,
          todayBtn: todayBtn,
          todayBtnMode: todayBtnMode,
          weekStart: weekStart
        },
        attr = opt ? opt : def;
        const datepicker = rangePicker ? new DateRangePicker(item, attr) : new Datepicker(item, attr);
      })
    }
}