export default function extendObject(obj, ext) {
    Object.keys(ext).forEach(function (key) { obj[key] = ext[key]; });
    return obj;
}