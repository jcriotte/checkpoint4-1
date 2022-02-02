/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

// start the Stimulus application
import './bootstrap';

import Calendar from './Calendar';

function redirectPost(url, data) {
    const form = document.createElement('form');
    document.body.appendChild(form);
    form.method = 'post';
    form.action = url;

    Object.entries(data).forEach(([key, value]) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
    });

    form.submit();
}
window.addEventListener('DOMContentLoaded', (event) => {
    if (document.getElementById('days')) {
        const calendar = new Calendar();

        const submit = document.getElementById('submit');
        submit.addEventListener('click', (e) => {
            e.preventDefault();

            const data = {
                year: calendar.getYear(),
                month: calendar.getMonth(),
                day: calendar.getDaySelected(),
            };

            redirectPost('/booking/search', data);
        });
    }
});
