window.addEvent('domready', function() {
    function afertSubmit() {
        formTime.set('text', 'submit');
        clearInterval(timerID);
        cycle.checked = false;
    }
    forms = document.getElements('form');
    if (forms.length > 0) {
        var form = forms[0], formTime;
        var cycle = (function() {
            if ($('cycle-checkbox').checked) {
                sec--;
                if (sec > 0)                
                    formTime.set('text', sec);
                else {
                    afertSubmit();
                    form.submit();
                }                
            }
        });
        
        form.innerHTML += '<input type="checkbox" checked value="1" name="cycle" id="cycle-checkbox">Отправить через <span id="formTime"></span> сек.';
        form.addEvent('submit', afertSubmit);
        var sec = 10;
        formTime = $('formTime');
        formTime.set('text', sec);
        var timerID = cycle.periodical(1000, formTime);
    }
});