class Map {
    constructor() {
        this.maps = null;
        this.key = '7cbdb5a9-7fba-4d82-9c6c-dfd9fe13b599';
    }

    load(readyAction) {
        if (this.maps){
            this.maps.ready(() => {
                readyAction(this.maps);
            });
            return false;
        }

        return new Promise((resolve, reject) => {
            let script = document.createElement('script');
            script.type = 'text/javascript';
            script.src = `https://api-maps.yandex.ru/2.1/?apikey=${this.key}&lang=ru_RU`;
            script.onload = resolve;
            script.onerror = e => reject(e);

            document.body.appendChild(script);
        }).then(() => {
            this.maps = window.ymaps;
            this.maps.ready(() => {
                readyAction(this.maps);
            });
        });
    }
}

module.exports = new Map();
