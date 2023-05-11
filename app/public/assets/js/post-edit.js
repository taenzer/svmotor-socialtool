document.addEventListener("DOMContentLoaded", function(){
    let newTrigger = document.getElementById("add-event");
    let eventWrap = document.getElementById("eventWrap");

    // Add Eventlisteners to all existing Positions
    var existingEvent = eventWrap.querySelectorAll(".event");
    for (var i = 0; i < existingEvent.length; i++) {
        addListerToEvent(existingEvent[i]);
    }

    function newEvent(){
        $.ajax({
        url: "/ajax.php",
        dataType: "json",
        data: {
            action: "postNewEvent",
            nonce: "nonce" // TODO: NOCE
        },
        success: function( data ) {
            
            var temp = document.createElement("div");
            temp.innerHTML = data.trim();
            var newEvent = temp.firstChild;
            addListerToEvent(newEvent);
            eventWrap.appendChild(newEvent);
        },
        });
    }

    function changeEventType(e){
        let target = e.currentTarget;
        let event = getEventByListenerTarget(target);
        event.setAttribute("data-eventType", target.value);        
    }

    function addListerToEvent(eventObj){
        eventObj.querySelector(".eventDel").addEventListener("click", delEvent);
        eventObj.querySelector(".eventType").addEventListener("change", changeEventType);
    }

    function delEvent(e){
        let target = e.currentTarget;
        let event = getEventByListenerTarget(target);
        event.remove();
    }

    function getEventByListenerTarget(target, sec = 10){
        var el = target;
        while (!el.classList.contains("event") && sec > 0) {
        el = el.parentElement;
        sec--;
        }
        return sec == 0 ? false : el;
    }

    newTrigger.addEventListener("click", newEvent);
});