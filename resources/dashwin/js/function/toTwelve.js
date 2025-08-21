export default function toTwelve(time) {
    time = time.toString ().match (/^([01]\d|2[0-3])(:)([0-5]\d)(:[0-5]\d)?$/) || [time];
    if (time.length > 1) { 
        time = time.slice (1);
        time.pop();
        time[5] = +time[0] < 12 ? ' AM' : ' PM'; // Set AM/PM
        time[0] = +time[0] % 12 || 12;
    }
    time = time.join ('');
    return time;
}