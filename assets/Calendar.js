export default class Calendar {
    year = document.getElementById('year');

    month = document.getElementById('month');

    calendar = document.getElementById('days');

    daySelected;

    days = document.getElementsByClassName('cal-day-filled');

    previousMonth = document.getElementById('previous-month');

    nextMonth = document.getElementById('next-month');

    constructor() {
        this.setUpButtonListeners();
        this.setUpDaysListener();
        [this.daySelected] = [
            document.getElementsByClassName('day-selected')[0],
        ];
    }

    setUpButtonListeners() {
        this.previousMonth.addEventListener('click', (e) => {
            e.preventDefault();
            this.handleButtonsClick('previous');
        });

        this.nextMonth.addEventListener('click', (e) => {
            e.preventDefault();
            this.handleButtonsClick('next');
        });
    }

    setUpDaysListener() {
        for (let i = 0; i < this.days.length; i += 1) {
            this.days[i].addEventListener('click', (e) => {
                const dayTarget = e.target;

                if (dayTarget.classList.contains('day-selected')) {
                    return;
                }

                // eslint-disable-next-line operator-linebreak
                const selected =
                    document.getElementsByClassName('day-selected');
                for (let j = 0; j < selected.length; j += 1) {
                    selected[j].classList.remove('day-selected');
                }

                dayTarget.classList.add('day-selected');
                this.daySelected = dayTarget;
            });
        }
    }

    handleButtonsClick(x) {
        const modifier = x === 'next' ? 1 : -1;

        let year = this.getYear();
        let month = this.getMonth() + modifier;

        if (month === 0) {
            month = 12;
            year -= 1;
        } else if (month === 13) {
            month += 1;
            year += 1;
        }

        const calendar = this.makeCalendar(month, year);

        this.updateCalendar(calendar);

        this.updateMonth(month, year);
        this.updateYear(year);

        this.days = document.getElementsByClassName('cal-day-filled');
        this.setUpDaysListener();
    }

    updateCalendar(calendar) {
        this.calendar.innerHTML = '';

        for (let i = 0; i < calendar.length; i += 1) {
            const tr = document.createElement('tr');
            this.calendar.appendChild(tr);

            for (let j = 0; j < calendar[i].length; j += 1) {
                const td = document.createElement('td');
                td.id = calendar[i][j];

                td.classList.add(
                    'w-10',
                    'h-10',
                    'text-center',
                    'cursor-default',
                );

                if (calendar[i][j]) {
                    td.classList.add('cal-day-filled');
                    td.innerHTML = String(calendar[i][j]).padStart(2, '0');
                } else {
                    td.classList.add('cal-day-empty');
                }

                this.calendar.lastChild.appendChild(td);
            }
        }
    }

    updateYear(year) {
        this.year.innerHTML = year;
        this.year.dataset.value = year;
    }

    updateMonth(month, year) {
        const date = new Date(year, month, 0);
        const monthString = date.toLocaleString('fr-FR', { month: 'long' });

        this.month.innerHTML = this.capitalizeFirstLetter(monthString);
        this.month.dataset.value = month;
    }

    makeCalendar(month, year) {
        let week = 0;
        const nbOfDays = this.numberOfDaysInMonth(month, year);

        const calendar = [['', '', '', '', '', '', '']];

        for (let i = 1; i <= nbOfDays; i += 1) {
            const dayIndex = this.getDayIndex(i, month, year);

            if (i > 1 && dayIndex === 0) {
                week += 1;
                calendar.push(['', '', '', '', '', '', '']);
            }

            calendar[week][dayIndex] = i;
        }

        return calendar;
    }

    static numberOfDaysInMonth(month, year) {
        return new Date(year, month, 0).getDate();
    }

    static getDayIndex(day, month, year) {
        const date = new Date(year, month - 1, day);
        const dayIndex = date.getDay();

        if (dayIndex === 0) {
            return 6;
        }
        return dayIndex - 1;
    }

    getPreviousMonthButton() {
        return this.previousMonth;
    }

    getNextMonthButton() {
        return this.nextMonth;
    }

    getDaySelected() {
        return +this.daySelected.id;
    }

    getMonth() {
        return +this.month.dataset.value;
    }

    getYear() {
        return +this.year.dataset.value;
    }

    static capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }
}
