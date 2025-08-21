import ClipboardJS from "clipboard";

export default function Clipboard(selector){
    let clipboardTrigger = document.querySelectorAll(selector);
    clipboardTrigger.forEach(item => {
      //init clipboard
      let clipboard = new ClipboardJS(item);
      //set markup
      let initMarkup = item.innerHTML;
      let successMarkup = item.dataset.markupSuccess;
      //on-sucess
      clipboard.on("success", function(e){
        let target = e.trigger;
        target.classList.add('success');
        target.innerHTML = successMarkup;
        setTimeout(function(){
          target.innerHTML = initMarkup;
          target.classList.remove('success');
        }, 1000)
      });
    });
}