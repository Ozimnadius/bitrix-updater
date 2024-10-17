/*document.querySelector('#modules').addEventListener('change', function (e) {
    e.preventDefault();

    const form = this.closest('form');
    const versionInput = form.querySelector('[name="version"]');
    const latestTimestampInput = form.querySelector('[name="latestTimestamp"]');
    const searchBtn = form.querySelector('#searchBtn');


    fetch('ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({action: 'version', module: this.value})
    }).then(response => response.json()).then(function (data) {
        versionInput.value = data.VERSION;
        // latestTimestampInput.value = new Date(data.VERSION_DATE).getTime();
        latestTimestampInput.value = data.VERSION_DATE;
        searchBtn.disabled = false;
    }).catch(function (err) {
        console.log('Fetch Error :-S', err);
    });
});

document.querySelector('#searchBtn').addEventListener('click', function (e) {
    e.preventDefault();

    const form = this.closest('form');
    const versionInput = form.querySelector('[name="version"]');
    const latestTimestampInput = form.querySelector('[name="latestTimestamp"]');
    const searchBtn = form.querySelector('#searchBtn');


    fetch('ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({action: 'version', module: this.value})
    }).then(response => response.json()).then(function (data) {
        versionInput.value = data.VERSION;
        // latestTimestampInput.value = new Date(data.VERSION_DATE).getTime();
        latestTimestampInput.value = data.VERSION_DATE;
        searchBtn.disabled = false;
    }).catch(function (err) {
        console.log('Fetch Error :-S', err);
    });
});*/


class Upgrader {

    constructor(formId) {
        this.form = document.querySelector(`#${formId}`);
        this.inputAction = this.form.querySelector('[name="action"]');
        this.table = this.form.querySelector('#table');
        this.tableContent = this.table.querySelector('tbody');

        this.events = new Set();

        this.form.querySelectorAll(`[data-event]`).forEach(i => {
            i.dataset.event.split(',').forEach((event) => {
                let [eventType, eventName] = event.split('.');

                if (!this[eventName]) return;

                this.events.add(eventType);
            });
        });

        document.addEventListener("DOMContentLoaded", () => {
            this.init();
        });
    }

    init() {

        this.events.forEach((type) => {

            this.form.addEventListener(type, (e) => {
                const target = e.target.closest(`[data-event]`);
                if (!target) return;

                target.dataset.event.split(',').forEach((event) => {
                    let [eventType, eventName] = event.split('.');

                    if (type !== eventType || !this[eventName]) return;

                    this[eventName].call(this, e, target);
                });
            });
        });

    }

    get Data() {
        return new FormData(this.form);
    }

    getModule(e, elem) {
        e.preventDefault();
        this.inputAction.value = 'version';

        fetch('ajax.php', {
            method: 'POST',
            body: this.Data
        }).then(response => response.json()).then((data)=> {
            const versionInput = this.form.querySelector('[name="version"]');
            const versionNewInput = this.form.querySelector('[name="newVersion"]');
            const dateInput = this.form.querySelector('[name="date"]');
            const searchBtn = this.form.querySelector('#searchBtn');

            versionInput.value = data.VERSION;
            dateInput.value = data.VERSION_DATE;
            searchBtn.disabled = false;
            let newVersion = data.VERSION.split( '.');
            newVersion[2] = parseInt(newVersion[2]) + 1;
            versionNewInput.value = newVersion.join('.');
        }).catch(function (err) {
            console.log('Fetch Error :-S', err);
        });
    }

    searchAndCopy(e, elem) {
        e.preventDefault();
        this.inputAction.value = 'searchAndCopy';

        fetch('ajax.php', {
            method: 'POST',
            body: this.Data
        }).then(response => response.json()).then(data =>
        {
            const prepareBtn = this.form.querySelector('#prepareArchive');
            prepareBtn.disabled = false;
            this.renderRows(data);

        }).catch(function (err)
        {
            console.log('Fetch Error :-S', err);
        });
    }

    renderRows(data) {
        const items = data.map((i, x) => {
            return `<tr>
                <th scope="row">${x + 1}</th>
                <td>${i}</td>
            </tr>`;
        });

        this.tableContent.innerHTML = items.join('');
    }

    prepareArchive(e, elem) {
        e.preventDefault();
        this.inputAction.value = 'prepareArchive';

        fetch('ajax.php', {
            method: 'POST',
            body: this.Data
        }).then(response => response.json()).then(data =>
        {
            this.form.querySelector('#archiveLink').href = data;
            this.form.querySelector('#archiveLink').hidden = false;
        }).catch(function (err)
        {
            console.log('Fetch Error :-S', err);
        });
    }

}

new Upgrader('form');



