/*!
  * NioApp v1.0.0 (https://softnio.com/)
  * Developed by Softnio Team.
  * Copyright by Softnio.
*/

import attr from "./attr.js";
import extendObject from "./extendObject.js";
import getParents from "./getParents.js";
import hexRGB from "./hexRGB.js";
import randomId from "./randomId.js";
import slideDown from "./slideDown.js";
import slideUp from "./slideUp.js";
import slideToggle from "./slideToggle.js";
import toMin from "./toMin.js";
import toTime from "./toTime.js";
import toTwelve from "./toTwelve.js";
import isDark from "./isDark.js";
import isRTL from "./isRTL.js";
import isTouch from "./isTouch.js";
import isMobile from "./isMobile.js";
import asMobile from "./asMobile.js";

import today from "./today.js";
import yesterday from "./yesterday.js";
import currentMonth from "./currentMonth.js";

const config = {
    app : { name: "NioApp", version: "1.0.0", author: "Softnio"},
    package: { name: "DashWind Tailwind", version: "1.0"},
    break : { sm: 640, md: 768, lg: 1024, xl: 1280, xl2: 1536, any: Infinity },
    win : { height: window.innerHeight, width: window.innerWidth },
    monthList : ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
    theme : {
        sidebar: "dark", //other value can be passed "light"
        header: "light", //other value can be passed "dark"
        skin: "light", //other value can be passed "dark"
        direction : 'ltr'
    }
}

function docReady(callback){
    document.addEventListener('DOMContentLoaded', callback, false);
}

function winLoad(callback){
    window.addEventListener('load', callback, false);
}

function onResize(callback,selector){
    selector = (typeof selector === typeof undefined) ? window : selector;
    selector.addEventListener('resize', callback)
}

export { docReady, winLoad, onResize, config, attr, extendObject, getParents, hexRGB, isDark, isMobile, isRTL, isTouch, randomId, slideDown, slideUp, slideToggle, toMin, toTime, toTwelve, asMobile, today, yesterday, currentMonth }