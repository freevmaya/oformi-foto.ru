var pay_support;
var WININDSPEED = 3000;

function controlPay(a_data) {
    return {
        elem: null,
        create: function(elem) {
            this.elem = elem;
            
            elem.find('.button').each(function(i, itm) {
                $(itm).button(a_data, $(itm));
            });                
            return this;
        }
    }
} 

var PaySupport = function(app, payed, balance, prices, wins) {
    var menuItem = $('.money');
    var This = this;         
    $.extend(This, new Events());
     
     
    if (payed) {
        menuItem.css('display', 'block');
    }                                              
    
    this.menu = [
        {id: 1, name: pay_locale.MENU_ADDBALANCE},
        //{id: 2, name: 'Список операций'},
        {id: 3, name: pay_locale.MENU_FREEMONEY},
        {id: 4, name: locale.USERSUPPORT}
    ]
    
    
    this.menuRelease = function(mitem) {
        switch (mitem.id) {
            case 1: This.addBalance();
                break;
            case 3: winsShow();
                break; 
            case 4: app.showSupport();
                break;
        }
    }
    
    this.refreshBalance = function() {
        var text = pay_locale.money.replace('%s', Utils.money(balance));
        
        menuItem.html('<span>' + balance + '</span>');
        if (menuItem.attr('data-title'))
            menuItem.attr('data-title', text);
        else locale.buttons.money = text; 
    }
    
    this.requestBalance = function(params) {
        params = $.extend({
            uid: app.user.uid
        }, params);
    
        $.main.query('getBalanceValue', params, function(a_data) {
            if (a_data) {
                var nb = parseInt(a_data.balance);
                if (nb && (balance != nb)) {
                    var sum = nb - balance;
                    setTimeout(function() {
                        This.priceAnimate(sum);
                    }, 3000);
                    balance = nb;
                    This.refreshBalance();
                    This.trigger($.Event('CHANGEBALANCE', balance));
                }
            }
        });
    }
    
// PRIVATE

    function winsShow() {
        var text = pay_locale.WINMONEYDESC;
        $.each(wins, function(i, w) {
            if (w.price) text += w.desc + ' - ' + Utils.money(w.price) + '<br>';
        });
        app.alert(text);
    }
    
    function payDialog(priceItem, onComplete) {
        var text = pay_locale.PAYOPERATIONDESC
        text = text.replace('%s1', priceItem.desc);
        text = text.replace('%s2', Utils.money(priceItem.price));
        app.alert(text, function() {
            This.payProcess(priceItem, onComplete);
        });
    }
    
    this.addBalance = function() {
        var dlg = $('.content').dialog({
            payment: controlPay(function(price, data) {
                dlg.close();
                This.payProcess($.extend(wins.ADDBALANCE, {price: price}));
            })            
        }, null, {
        }, 'dlg_addBalance').toCenter().show();
    }

    this.payProcess = function(payitem, onComplete) {
        console.log(payitem);        
    }
    
// DECLARE

    this.priceAnimate = function(price, pos) {
        if (!pos) {
            pos = menuItem.offset();
            pos.top += menuItem.height();         
        }
        var isWin = price>0; 
        win_ind = $('#templates .' + (isWin?'win_ind':'pay_ind')).clone();
        $('body').append(win_ind);
        win_ind.css($.extend(pos, {opacity: 1}));
        win_ind.text((isWin?'+':'') + price);
        win_ind.animate({top: pos.top + (80 * (isWin?-1:1)), opacity: 0}, WININDSPEED);
        
        setTimeout(function() {win_ind.remove();}, WININDSPEED);
    }
    
    this.pind = function(type, pos) {
        var itm = wins[type] || prices[type];
        if (itm) {
            isWin = itm.id > 99;
            this.priceAnimate(isWin?itm.price:(-itm.price), pos);
//            this.transaction(type, price);
        }        
    }          
    
    this.possibly = function(type, onComplete, params, serviceProcess) {
        var pc;    
        function lcomplete() {
            if (onComplete) onComplete(params);
        }
        
        function trans() {
            This.transaction(type, -pc.price, params, function() {
                lcomplete();
                This.pind(type);
            });        
        }
        if (payed) {
            if (prices[type]) {
                pc = prices[type];
                if (balance - pc.price >= 0) {
                    if (serviceProcess) serviceProcess(trans);
                    else trans();
                    
                } else payDialog(pc, function() {
                    This.possibly(type, onComplete, params, serviceProcess);
                });
            } else console.log('Unknown operation - "' + type + '"" requested');
        } else if (serviceProcess) serviceProcess(lcomplete)
                else lcomplete();
    }
    
    this.transaction = function(type, amount, params, onComplete) {
        if (payed && (amount > 0)) {
            if (!params) params = {};
            var pi = prices[type] || wins[type];
            var isWin = pi.id > 99;
            amount = amount?amount:(isWin?pi.price:-pi.price);
                    
            params = $.extend({
                uid: app.user.uid,
                service: pi.id,
                amount: amount 
            }, params);
            
            $.main.query('addTransaction', params, function(a_data) {
                if (a_data) {
                    balance += amount;
                    This.refreshBalance();
                    if (onComplete) onComplete();
                }
            });
        } else if (onComplete) onComplete();
    }
    
    this.getWinds = function() {return wins};
    this.getPrices = function() {return prices};
    this.getWind = function(type) {return wins[type]};
    this.getPrice = function(type) {return prices[type]}; 
    
    if (payed) this.refreshBalance();
}