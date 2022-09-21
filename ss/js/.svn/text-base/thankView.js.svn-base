var payThankForm=null;

function showThankView() {
    if (!payThankForm) {
        var table;                
        function addPayItem(item) {
            (new Element('tr', {
                html: '<td>' + item[0] + '</td>' + '<td class="icon_name_payment-default">' + item[1] + '</td>' + '<td class="money">' + item[2] + ' руб.</td>'
            })).inject(table);
        }
        
        payThankForm = new Element('div', {
            'id': 'payThankForm'
        });
        
        payThankForm.set('html', '<iframe style="margin:0px auto" frameborder="0" allowtransparency="true" scrolling="no" src="https://money.yandex.ru/embed/shop.xml?account=410011797400320&quickpay=shop&payment-type-choice=on&mobile-payment-type-choice=on&writer=seller&targets=%D0%9D%D0%B0+%D1%80%D0%B0%D0%B7%D0%B2%D0%B8%D1%82%D0%B8%D0%B5+%D1%81%D0%B0%D0%B9%D1%82%D0%B0+oformi-foto.ru&targets-hint=&default-sum=100&button-text=04&successURL=http%3A%2F%2Foformi-foto.ru" width="450" height="198"></iframe>');
        
        var pl = $('payList');
        table = pl.inject(payThankForm).setStyle('display', 'block').getElement('table');
        
        var h = pl.getElement('h3');
        
        var total = 0;
        pay_list.each(function(item) {
            addPayItem(item);
            total += item[2];
        });
        
        h.set('text', h.get('text').replace('%s', total));
    }    
    
    SqueezeBox.fromElement(payThankForm, {size: {x: 450, y: 454}}); //198
}