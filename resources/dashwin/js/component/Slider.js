import Swiper from 'swiper/bundle';
import {isRTL} from "../function"

export default function Slider(selector){
    let elm = document.querySelectorAll(selector);
    if( elm != 'undefined' && elm != null ){
      elm.forEach(item => {
        let _breakpoints = item.dataset.breakpoints ? JSON.parse(item.dataset.breakpoints) : null;
        let _autoplay = item.dataset.autoplay ? JSON.parse(item.dataset.autoplay) : false;
        let _loop = item.dataset.loop ? JSON.parse(item.dataset.loop) : false;
        let _centeredSlides = item.dataset.centeredslides ? JSON.parse(item.dataset.centeredslides) : false;
        let _speed = item.dataset.speed ? parseInt(item.dataset.speed) : 1000;
        let _effect = item.dataset.effect ? item.dataset.effect : '';
        let _parent = item.dataset.parent ? item.dataset.parent : false;
        
        var swiper = new Swiper(item, {
          // Optional parameters
          centeredSlides: _centeredSlides,
          loop: _loop,
          speed: _speed,
          autoplay:_autoplay,
          effect: _effect,
          // If we need pagination
          pagination: {
            el: ".swiper-pagination",
            type: 'bullets',
            clickable: true,
          },
          // Navigation arrows
          navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
            clickable: true,
          },
          breakpoints: _breakpoints,
          // thumbs: {
          //   swiper: swiper,
          // },
        });

        if(_parent){
          let item = document.querySelector(_parent);
          let _breakpoints = item.dataset.breakpoints ? JSON.parse(item.dataset.breakpoints) : null;
          let _autoplay = item.dataset.autoplay ? JSON.parse(item.dataset.autoplay) : false;
          let _loop = item.dataset.loop ? JSON.parse(item.dataset.loop) : false;
          let _centeredSlides = item.dataset.centeredslides ? JSON.parse(item.dataset.centeredslides) : false;
          let _speed = item.dataset.speed ? parseInt(item.dataset.speed) : 1000;
          let _effect = item.dataset.effect ? item.dataset.effect : '';
          var swiper_parent = new Swiper(item, {
            // Optional parameters
            centeredSlides: _centeredSlides,
            loop: _loop,
            speed: _speed,
            autoplay:_autoplay,
            effect: _effect,
            // If we need pagination
            pagination: {
              el: ".swiper-pagination",
              type: 'bullets',
              clickable: true,
            },
            // Navigation arrows
            navigation: {
              nextEl: '.swiper-button-next',
              prevEl: '.swiper-button-prev',
              clickable: true,
            },
            breakpoints: _breakpoints,
            thumbs: {
              swiper: swiper,
            },
          });
        }

        
      });
    }
}