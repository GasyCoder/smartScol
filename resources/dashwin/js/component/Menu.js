import {slideUp,slideDown} from '../function'

let Menu = {};

export default Menu = {
    load: function(elm,subparent){
      let parent = elm.parentElement;
      if(!parent.classList.contains(subparent)){
        parent.classList.add(subparent);
      }
    },
    toggle: function(elm,active){
      let parent = elm.parentElement;
      let nextelm = elm.nextElementSibling;
      let speed = nextelm.children.length > 5 ? 400 + nextelm.children.length * 10 : 400;
      if(!parent.classList.contains(active)){
        parent.classList.add(active);
        slideDown(nextelm,speed);
      }else{
        parent.classList.remove(active);
        slideUp(nextelm,speed);
      }
    },
    closeSiblings: function(elm,active,subparent,submenu){
      let parent = elm.parentElement;
      let siblings = parent.parentElement.children;
      Array.from(siblings).forEach(item => {
        if(item !== parent){
          item.classList.remove(active);
          if(item.classList.contains(subparent)){
            let subitem = item.querySelectorAll(`.${submenu}`);
            subitem.forEach(child => {
              child.parentElement.classList.remove(active);
              slideUp(child,400);
            })
          }
        }
      });
    }
}