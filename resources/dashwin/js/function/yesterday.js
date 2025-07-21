export default function yesterday() {
    let today = new Date();
    let yesterday = new Date(today);
    yesterday.setDate(today.getDate() - 1);
    let y_dd = String(yesterday.getDate()).padStart(2, '0');
    let y_mm = String(yesterday.getMonth() + 1).padStart(2, '0');
    let y_yyyy = yesterday.getFullYear();

    return  y_yyyy + '-' + y_mm + '-' + y_dd;
}