import { config } from "./index.js";
export default function asMobile() {
    let value = (config.win.width < config.break.md) ? true : false;
    return value;
}