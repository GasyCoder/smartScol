import { config } from "../function";

let Sidebar = {};
export default Sidebar = {
    compact : function(){
      let toggle = document.querySelectorAll('.sidebar-compact-toggle');
      let parent = document.querySelector('.nk-sidebar');
      let body = parent && parent.querySelector('.nk-sidebar-body');
      toggle && toggle.forEach(item => {
        item.addEventListener("click", function(e){
          e.preventDefault();
          item.classList.toggle('compact-active');
          parent.classList.toggle('is-compact');
          !parent.classList.contains('is-compact') && parent.classList.remove('has-hover');
        });
      })
      body && body.addEventListener('mouseenter', function (e) {
          parent.classList.contains('is-compact') && parent.classList.add('has-hover');
      });
      body && body.addEventListener('mouseleave', function (e) {
          parent.classList.contains('is-compact') && parent.classList.remove('has-hover');
      });
    },
    
    toggle: function(){
      let toggle = document.querySelectorAll('.sidebar-toggle');
      let parent = document.querySelector('.nk-sidebar');
      toggle.forEach(item => {
        item.addEventListener("click", function(e){
          e.preventDefault();
          item.classList.toggle('active');
          parent && parent.classList.toggle('sidebar-visible');
          document.body.classList.toggle('overflow-hidden')
        });
      })
    },

    page_resize: function(){
      let toggle = document.querySelectorAll('.sidebar-toggle');
      let parent = document.querySelector('.nk-sidebar');
      if(config.win.width > config.break.xl) {
        toggle.forEach(item => {
          item.classList.remove('active');
        })
        parent && parent.classList.remove('sidebar-visible');
        document.body.classList.remove('overflow-hidden');
      }
    }
}