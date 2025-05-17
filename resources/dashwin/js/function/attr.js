export default function attr(selector,property,value) {
    const att = document.createAttribute(property);
    att.value = value;
    selector.setAttributeNode(att);
}