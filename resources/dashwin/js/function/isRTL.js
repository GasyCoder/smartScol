export default function isRTL() {
    let value = (document.querySelector('body').classList.contains('rtl') || document.querySelector('body').getAttribute('dir') === 'rtl') ? true : false;
    return value;
}