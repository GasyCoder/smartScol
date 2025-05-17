export default function hexRGB(hex, op) {
    let color, colorRGB; var opc = (op) ? op : 1;
    if (/^#([A-Fa-f0-9]{3}){1,2}$/.test(hex)) {
        color = hex.substring(1).split('');
        if (color.length === 3) {
            color = [color[0], color[0], color[1], color[1], color[2], color[2]];
        }
        color = '0x' + color.join('');
        colorRGB = [(color >> 16) & 255, (color >> 8) & 255, color & 255].join(',');
        return (opc >= 1) ? 'rgba(' + colorRGB + ')' : 'rgba(' + colorRGB + ',' + opc + ')';
    }
    throw new Error('bad hex');
}