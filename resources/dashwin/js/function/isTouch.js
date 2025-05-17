export default function isTouch() {
    let value = (("ontouchstart" in document.documentElement)) ? true : false;
    return value;
}