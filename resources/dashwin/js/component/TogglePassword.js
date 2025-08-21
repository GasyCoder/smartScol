export default function TogglePassword(selector) {
    let elem = document.querySelectorAll(selector);
    if(elem.length > 0){
      elem.forEach(item => {
        item.addEventListener("click", function(e){
          e.preventDefault();
          let target = document.querySelector(item.getAttribute("href"));
          if(target.type == "password") {
            target.type = "text";
            item.classList.add("is-shown");
          }else{
            target.type = "password";
            item.classList.remove("is-shown");
          }
        });
      });
    }
};