import jsVectorMap from 'jsvectormap'
import 'jsvectormap/dist/maps/world.js'

export default function WorldMap(selector,data){
    let elm = document.querySelectorAll(selector);
    elm.forEach(item => {
        const map = new jsVectorMap({
            selector: `#${item.id}`,
            map: 'world',
            showTooltip: true,
            zoomButtons:false,
            zoomOnScroll:false,
            visualizeData: data,
        })
    })
}