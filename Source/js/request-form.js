const $ = require('jquery');

class Form {
    constructor() {
        this.form = $('[data-form=request]');
    }

    bind() {
        let o = this;

        o.form.submit(function() {
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serializeArray(),
                dataType: 'json',
                success: data => alert(data.message),
                error: o.error
            });

            return false;
        });
    }

    error() {
        alert('Ошибка сервера, обратитесь к администретору');
    }
}

module.exports = new Form;
