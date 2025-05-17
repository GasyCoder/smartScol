
export const ShowModal = function (dialog) {
    let getAll = document.querySelectorAll('.modal');
    dialog.classList.add('show');
    document.body.classList.add('overflow-hidden');
    getAll.forEach(function(getItem){
        getItem !== dialog && getItem.classList.remove('show');
    })
}

export const HideModal = function (dialog) {
    dialog.classList.remove('show');
    document.body.classList.remove('overflow-hidden');
}

export default function Modal(el){
    let ele = el ? el : '.modal-toggle';
    let elm = document.querySelectorAll(ele);
    elm.forEach(function(item){
        let dialog = document.querySelector(item.dataset.target),
            close = dialog?.querySelectorAll('.modal-close');
        item.addEventListener("click", function(e){
            ShowModal(dialog); 
        })
        close?.forEach(function(item){
            item.addEventListener("click", function(e){
                e.preventDefault();
                HideModal(dialog);
            })
        })
    })
}
