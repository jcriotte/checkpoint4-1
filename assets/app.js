/* eslint-disable no-unused-expressions */
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

function handleLoginModal() {
    const loginModal = document.getElementById('login-modal');
    const loginModalHide = document.getElementById('login-modal-hide');
    const loginIcon = document.getElementById('login-icon');

    if (loginIcon) {
        loginIcon.addEventListener('click', () => {
            if (loginModal.classList.contains('hidden')) {
                loginModal.classList.remove('hidden');
            }
        });

        loginModalHide.addEventListener('click', () => {
            if (!loginModal.classList.contains('hidden')) {
                loginModal.classList.add('hidden');
            }
        });
    }
}

function fadeOut(el) {
    // eslint-disable-next-line no-param-reassign
    el.style.opacity = 1;
    // eslint-disable-next-line wrap-iife
    (function fade() {
        // eslint-disable-next-line no-param-reassign
        el.style.opacity -= 0.01;
        if (el.style.opacity < 0) {
            // eslint-disable-next-line no-param-reassign
            el.style.display = 'none';
        } else {
            requestAnimationFrame(fade);
        }
    })();
}

function handleFlashMessagesDisplay() {
    if (document.getElementsByClassName('flash').length > 0) {
        const flash = document.getElementsByClassName('flash');
        for (let i = 0; i < flash.length; i += 1) {
            flash[i].opacity = 1;
            setTimeout(() => {
                fadeOut(flash[i]);
            }, 4000);
        }
    }
}

window.addEventListener('DOMContentLoaded', (event) => {
    handleLoginModal();
    handleFlashMessagesDisplay();

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

    if (document.getElementsByClassName('court-div')) {
        const courts = document.getElementsByClassName('court-div');
        const arrows = document.getElementsByClassName('arrow');
        const slots = document.getElementsByClassName('slots');

        const hours = document.getElementsByClassName('slot-hour');

        for (let i = 0; i < courts.length; i += 1) {
            courts[i].addEventListener('click', () => {
                const slot = courts[i].nextElementSibling;
                const arrow = courts[i].getElementsByClassName('arrow')[0];
                if (arrow.classList.contains('up')) {
                    if (
                        !slot.getElementsByClassName('hour-selected').length > 0
                    ) {
                        slot.classList.add('hidden');
                        arrow.classList.remove('up');
                        arrow.classList.add('down');
                        return;
                    }
                    return;
                }

                for (let j = 0; j < arrows.length; j += 1) {
                    if (
                        !arrows[
                            j
                        ].parentNode.nextElementSibling.getElementsByClassName(
                            'hour-selected',
                        ).length > 0
                    ) {
                        if (arrows[j].classList.contains('up')) {
                            arrows[j].classList.remove('up');
                        }
                        if (!arrows[j].classList.contains('down')) {
                            arrows[j].classList.add('down');
                        }
                    }
                }

                arrow.classList.remove('down');
                arrow.classList.add('up');

                for (let j = 0; j < slots.length; j += 1) {
                    if (
                        !slots[j].getElementsByClassName('hour-selected')
                            .length > 0
                    ) {
                        if (!slots[j].classList.contains('hidden')) {
                            slots[j].classList.add('hidden');
                        }
                    }
                }

                slot.classList.remove('hidden');
            });
        }

        for (let i = 0; i < hours.length; i += 1) {
            hours[i].addEventListener('click', () => {
                if (hours[i].classList.contains('hour-selected')) {
                    hours[i].classList.remove('hour-selected');
                    hours[i].classList.add('hour-non-selected');
                    return;
                }

                for (let j = 0; j < hours.length; j += 1) {
                    if (hours[j].classList.contains('hour-selected')) {
                        hours[j].classList.remove('hour-selected');
                        hours[j].classList.add('hour-non-selected');
                    }
                }

                hours[i].classList.remove('hour-non-selected');
                hours[i].classList.add('hour-selected');
            });
        }
    }

    if (document.getElementById('reserve-slot')) {
        const reserveButton = document.getElementById('reserve-slot');
        const reserveHint = document.getElementById('reserve-hint');

        reserveButton.addEventListener('click', () => {
            if (!document.getElementsByClassName('hour-selected').length > 0) {
                if (reserveHint.classList.contains('hidden')) {
                    reserveHint.classList.remove('hidden');
                    return;
                }
                return;
            }

            // eslint-disable-next-line operator-linebreak
            const date = document.getElementById('date-booking').dataset.value;
            // eslint-disable-next-line operator-linebreak
            const slot =
                document.getElementsByClassName('hour-selected')[0].dataset
                    .value;

            const dateSplit = date.split('/');
            const slotSplit = slot.split('-');

            const year = dateSplit[2];
            const month = dateSplit[1];
            const day = dateSplit[0];

            const court = slotSplit[0];
            const hour = slotSplit[1];

            const data = {
                year,
                month,
                day,
                court,
                hour,
            };

            redirectPost('/booking/new', data);
        });
    }
});
