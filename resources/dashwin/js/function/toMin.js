export default function toMin(input) {
    let value = input.split(':');
    let hour = parseInt(value[0]);
    let min = (value[1] !== undefined) ? parseInt(value[1]) : 0;
    let time = (hour*60) + min
    return time;
}