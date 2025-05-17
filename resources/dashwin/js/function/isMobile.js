
export default function isMobile() {
    let value = (navigator.userAgent.match(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Windows Phone|/i)) ? true : false;
    return value;
}