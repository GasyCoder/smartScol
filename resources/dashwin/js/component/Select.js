import Choices from 'choices.js';

export default function Select(selector,options){
    let elm = document.querySelectorAll(selector);
    if( elm.length > 0 ){
      elm.forEach(item => {
        let search = item.dataset.search ? JSON.parse(item.dataset.search) : false;
        let sort = item.dataset.sort ? JSON.parse(item.dataset.sort) : false;
        let cross = item.dataset.cross ? JSON.parse(item.dataset.cross) : true;
        let placeholderValue = item.dataset.placeholder ? item.dataset.placeholder : null;
        const choices = new Choices(item, {
          silent: true,
          allowHTML: false,
          searchEnabled: search,
          placeholder: true,
          placeholderValue: placeholderValue,
          searchPlaceholderValue: '',
          shouldSort: sort,
          removeItemButton: cross,
          itemSelectText: '',
          noResultsText: 'No results',
        });
      })
    }
}
