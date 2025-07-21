export default function Tab(el){
    const elm = document.querySelectorAll('.tab-toggle');
    elm.forEach(function(item){
        let panel = document.querySelector(item.dataset.target),
            panel_root = panel.parentElement,
            panel_items = panel_root.children;
            
        item.addEventListener("click", function(e){
            e.preventDefault();
            // tab toggle active
            let toggles = item.closest('.tab-nav').querySelectorAll('.tab-toggle');
            Array.from(toggles).forEach(item => {
                item.classList.remove('active');
            });
            item.classList.add('active');

            //tab panel active
            Array.from(panel_items).forEach(item => {
                item.classList.remove('active');
            });
            panel.classList.add('active');
        })
    })
}