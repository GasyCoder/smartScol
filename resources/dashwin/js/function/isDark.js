export default function isDark() {
    let value = (document.querySelector('body').classList.contains('dark')) ? true : false;
    return value;
}