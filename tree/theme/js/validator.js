function baseValidator() {
    return {
        child: function(ctrl, field) {
            return ctrl.elem.find('.' + field);
        },
        val: function(ctrl, field) {
            return this.child(field).val().trim();
        }        
    }
}

function validatorFIO(msg) {
    return jQuery.extend(baseValidator(), {
        validate: function(ctrl) {
            for (var n in msg) {
                var input = this.child(ctrl, n);
                var r = /[^A-Za-z-А-Яа-я`]+/;
                var v = input.val();        
                
                if (!v || r.test(input.val())) {
                    return {
                        input: input,
                        position: (new Vector(input.position())).add(new Vector(10 + input.width() / 2, 73)),
                        text: msg[n]
                    }
                }
            }
            return false;
        }
    })
}

function validatorBDAY(msg) {
    return {
        validate: function(ctrl) {
            var input = ctrl.elem.find('input');
            var r = /^(\d{1,2})\.(\d{1,2})\.(\d{4})$/;
            var v = input.val();        
            
            if (!v || !r.test(input.val())) {
                return {
                    input: input,
                    position: (new Vector(input.position())).add(new Vector(10 + input.width() / 2, 73)),
                    text: msg
                }
            }
            return false;
        }
    }
}

function validatorName(msg) {
    return {
        validate: function(ctrl) {
            var input = ctrl.elem.find('input');
            var r = /[^A-Za-z-А-Яа-я`\s\!_]+/;
            var v = input.val();        
            
            if (!v || r.test(input.val())) {
                return {
                    input: input,
                    position: (new Vector(input.position())).add(new Vector(10 + input.width() / 2, 73)),
                    text: msg
                }
            }
            return false;
        }
    }
}

function validatorIMAGE(msg) {
    return {
        validate: function(ctrl) {
            var input = ctrl.elem.find('img');
            if (input.attr('src') == ctrl.defaultImage()) {
                return {
                    input: input,
                    position: (new Vector(input.position())).add(new Vector(10 + input.width() / 2, input.outerHeight())),
                    text: msg
                }
            }
            return false;
        }
    }
}