
// pristinejs
import Pristine from 'pristinejs';

export default function FormValidate(selector){
    let elm = document.querySelectorAll(selector);
    if( elm != 'undefined' && elm != null ){
      elm.forEach(item => {
        let itemId = item.id;
        var form = document.getElementById(itemId);

        var pristine = new Pristine(form, {
            classTo: 'form-group',
            errorClass: 'is-invalid',
            successClass: 'is-valid',
            errorTextParent: 'form-wrap',
            errorTextTag: 'div',
            errorTextClass: 'form-error' 
        });
        
        function onchange(newselector){
          let newelm = document.querySelectorAll(newselector);
          newelm.forEach(newitem => {
            newitem.addEventListener('change', function() { 
              if(form.classList.contains('validated')){
                var valid = pristine.validate();
              }
            });
          })
        }
        onchange('.js-select');

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var valid = pristine.validate();
            form.classList.add('validated');
        });
      })
    }
}