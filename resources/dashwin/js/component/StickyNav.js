export default function StickyNav(selector) {
    let elem = document.querySelectorAll(selector);
    if(elem.length > 0){
      elem.forEach(item => {
        let _item_offset = item.offsetTop;
        console.log(_item_offset);
        window.addEventListener("scroll", function () {
          if (window.scrollY > _item_offset) {
            item.classList.add('has-fixed');
          } else {
            item.classList.remove('has-fixed');
          }
        });

      });
    }
};