import { config, today, yesterday, currentMonth } from "./function";
import Message, { info_break } from "./app/messages";
import Calendar from "./app/calendar";

document.addEventListener('DOMContentLoaded', 
    function(){
        Message.autohide();
        Message.profile_toggle();
        Message.conversation_show();
        Message.conversation_hide();
        config.win.width >= info_break ? Message.profile_show() : Message.profile_hide();
    }
);

window.addEventListener('resize', function(){
    Message.page_resize();
});


Calendar('.js-calendar',[
    {
        id: 'default-event-id-' + Math.floor(Math.random()*9999999),
        title: 'Reader will be distracted',
        start: currentMonth() + '-03T13:30:00',
        className: "fc-event-danger",
        description: "Use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden. 1",
    },
    {
        id: 'default-event-id-' + Math.floor(Math.random()*9999999),
        title: 'Rabfov va hezow.',
        start: currentMonth() + '-14T13:30:00',
        end: currentMonth() + '-14',
        className: "fc-event-success",
        description: "Use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden. 2",
    },
    {
        id: 'default-event-id-' + Math.floor(Math.random()*9999999),
        title: 'The leap into electronic',
        start: currentMonth() + '-05',
        end: currentMonth() + '-06',
        className: "fc-event-primary",
        description: "Use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden. 3",
    },
    {
        id: 'default-event-id-' + Math.floor(Math.random()*9999999),
        title: 'Lorem Ipsum passage - Product Release',
        start: currentMonth() + '-02',
        end: currentMonth() + '-04',
        className: "fc-event-primary",
        description: "Use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden. 4",
    },
    {
        title: 'Gibmuza viib hepobe.',
        start: currentMonth() + '-12',
        end: currentMonth() + '-10',
        className: "fc-event-pink-soft",
        description: "Use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden. 5",
    },
    {
        id: 'default-event-id-' + Math.floor(Math.random()*9999999),
        title: 'Jidehse gegoj fupelone.',
        start: currentMonth() + '-07T16:00:00',
        className: "fc-event-danger-soft",
        description: "Use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden. 6",
    },
    {
        id: 'default-event-id-' + Math.floor(Math.random()*9999999),
        title: 'Ke uzipiz zip.',
        start: currentMonth() + '-16T16:00:00',
        end: currentMonth() + '-14',
        className: "fc-event-info-soft",
        description: "Use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden. 7",
    },
    {
        id: 'default-event-id-' + Math.floor(Math.random()*9999999),
        title: 'Piece of classical Latin literature',
        start: today(),
        end: today() + '-01',
        className: "fc-event-primary",
        description: "Use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden. 8",
    },
    {
        id: 'default-event-id-' + Math.floor(Math.random()*9999999),
        title: 'Nogok kewwib ezidbi.',
        start: today() + 'T10:00:00',
        className: "fc-event-info",
        description: "Use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden. 9",
    },
    {
        id: 'default-event-id-' + Math.floor(Math.random()*9999999),
        title: 'Mifebi ik cumean.',
        start: today() + 'T14:30:00',
        className: "fc-event-warning-soft",
        description: "Use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden. 10",
    },
    {
        id: 'default-event-id-' + Math.floor(Math.random()*9999999),
        title: 'Play Time',
        start: today() + 'T17:30:00',
        className: "fc-event-info",
        description: "Use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden. 11",
    },
    {
        id: 'default-event-id-' + Math.floor(Math.random()*9999999),
        title: 'Rujfogve kabwih haznojuf.',
        start: yesterday() + 'T05:00:00',
        className: "fc-event-danger",
        description: "Use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden. 12",
    },
    {
        id: 'default-event-id-' + Math.floor(Math.random()*9999999),
        title: 'simply dummy text of the printing',
        start: yesterday() + 'T07:00:00',
        className: "fc-event-primary-soft",
        description: "Use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden. 13",
    },
]);

