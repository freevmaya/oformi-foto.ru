window.addEvent('userInfo', function(u) {
    if (u.birthday) {
        var bda = u.birthday.split('.')
        console.log(bda[2]);
        var age = (new Date()).getFullYear() - bda[2];
        if (age > 35) {
            var adv = (new Element('div', {'class': 'is-adv'})).inject($$('body')[0]);
        }
    }
});