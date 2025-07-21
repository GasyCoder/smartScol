import Tagify from '@yaireo/tagify'

export default function Tags(selector) {
    let elm = document.querySelectorAll(selector);
    if( elm.length > 0 ){
      elm.forEach(item => {
        let tagify = new Tagify (item);
      })
    }
};