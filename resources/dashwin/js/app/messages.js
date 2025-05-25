import { config } from "../function";

let Message = {};

export let conversation_toggle = document.querySelectorAll('.profile-toggle'),
    conversation_item = document.querySelectorAll('.conversation-item'),
    conversation_close = document.querySelectorAll('.conversation-close'),
    conversation_body = document.querySelector('.conversation-body'),
    conversation_profile = document.querySelector('.conversation-profile'),
    shown_conversation  = 'conversation-shown',
    shown_profile  = 'profile-shown',
    auto_hide  = 'conversation-profile-autohide',
    current_conversation  = 'current',
    visible_profile  = 'visible',
    flat_break = config.break.lg,
    info_break = document.body.classList.contains('has-apps-sidebar') ? config.break.xl : config.break.xl2;

export default Message = {
    autohide : function(flat_break,auto_hide){
        if(config.win.width >= flat_break){
            !document.body.classList.contains(auto_hide) && document.body.classList.add(auto_hide);
        }else{
            document.body.classList.contains(auto_hide) && document.body.classList.remove(auto_hide);
        }
    },
    profile_show : function(){
        conversation_profile?.classList.add(visible_profile);
        conversation_body?.classList.add(shown_profile); 
        document.body.classList.add(`conversation-${shown_profile}`);
    },
    profile_hide : function(){
        conversation_profile?.classList.remove(visible_profile);
        conversation_body?.classList.remove(shown_profile); 
        document.body.classList.remove(`conversation-${shown_profile}`);
    },
    profile_toggle : function(){
        conversation_toggle.forEach(item => {
            item.addEventListener('click', function(){
                conversation_profile?.classList.toggle(visible_profile);
                conversation_body?.classList.toggle(shown_profile);

                if(conversation_profile?.classList.contains(visible_profile) && !document.body.classList.contains(`conversation-${shown_profile}`)) {
                    document.body.classList.add(`conversation-${shown_profile}`);
                } else {
                    document.body.classList.remove(`conversation-${shown_profile}`);
                }

                if(config.win.width >= flat_break) {
                    if(document.body.classList.contains(auto_hide)) {
                        document.body.classList.remove(auto_hide);
                    } else if(config.win.width < info_break && !conversation_profile?.classList.contains(visible_profile)) {
                        document.body.classList.add(auto_hide);
                    }
                }

            })
        })
    },
    page_resize : function(){
        if(document.body.classList.contains(auto_hide)){
            config.win.width >= info_break ? Message.profile_show() : Message.profile_hide();
        }
        if(config.win.width >= flat_break && config.win.width < info_break) {
            if(document.body.classList.contains(`conversation-${shown_profile}`)) {
                document.body.classList.remove(`conversation-${shown_profile}`);
                Message.profile_hide();
            }
        }
    },
    conversation_show : function() {
        conversation_item.forEach(item => {
            item.addEventListener('click', function(event){
                event.preventDefault();
                conversation_item.forEach(aitem => {
                    aitem.classList.remove(current_conversation);
                });
                conversation_body?.classList.add(shown_conversation);
                item.classList.add(current_conversation);
            })
        })
    },
    conversation_hide : function() {
        conversation_close.forEach(item => {
            item.addEventListener('click', function(event){
                event.preventDefault();
                conversation_body?.classList.remove(shown_conversation);
            })
        })
    }
}