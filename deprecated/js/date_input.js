/*
Date Input 1.1.7
Requires jQuery version: 1.2.6

Copyright (c) 2007-2008 Jonathan Leighton & Torchbox Ltd

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
*/

DateInput = (function($) { // Localise the $ function
    function DateInput(el, opts) {
        if (typeof(opts) != "object") opts = {};
        $.extend(this, DateInput.DEFAULT_OPTS, opts);

        this.input = $(el);
        this.bindMethodsToObj("show", "hide", "hideOnEsc", "hideIfClickOutside", "selectMonth", "selectDate", "prevMonth", "nextMonth");
        this.build();
        this.hide();
    };

    DateInput.DEFAULT_OPTS = {
        month_names: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
        short_day_names: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
        start_of_week: 1
    };

    DateInput.prototype = {
        build: function() {
            this.monthNameSpan = $(' <span class="month_name"></span> ');
            var monthNav = $('<p class="month_nav"></p>').append(
                $('<a href="#" class="prev">&laquo;</a>').click(this.prevMonth), this.monthNameSpan, $('<a href="#" class="next">&raquo;</a>').click(this.nextMonth)
            );

            var table = this.showTime() + "<table><thead><tr>";
            $(this.adjustDays(this.short_day_names)).each(function() {
                table += "<th>" + this + "</th>";
            });
            table += "</tr></thead><tbody></tbody></table>";
            this.dateSelector = this.rootLayers = $('<div class="date_selector"></div>').append(monthNav, table).insertAfter(this.input);
            this.tbody = $("tbody", this.dateSelector);
            this.input.click(this.bindToObj(function() {
                this.stringToDate();
            }));
        },

        selectMonth: function(date) {
            this.currentMonth = new Date(date.getFullYear(), date.getMonth(), 1);
            var rangeStart = this.rangeStart(date), rangeEnd = this.rangeEnd(date);
            var numDays = this.daysBetween(rangeStart, rangeEnd);
            var dayCells = "";

            for (var i = 0; i <= numDays; i++) {
                var currentDay = new Date(rangeStart.getFullYear(), rangeStart.getMonth(), rangeStart.getDate() + i, 12, 00);
                if (this.isFirstDayOfWeek(currentDay)) dayCells += "<tr>";
                if (currentDay.getMonth() == date.getMonth()) {
                    dayCells += '<td date="' + this.getYMD(currentDay) + '"><a href="#">' + currentDay.getDate() + '</a></td>';
                }
                else {
                    dayCells += '<td class="unselected_month" date="' + this.getYMD(currentDay) + '">' + currentDay.getDate() + '</td>';
                }
            };
            this.monthNameSpan.empty().append(this.monthName(date) + " " + date.getFullYear());
            this.tbody.empty().append(dayCells);

            $("a", this.tbody).click(this.bindToObj(function(event) {
                var parent = $(event.target).parent();
                var hour = parent.parent().parent().parent().parent().find(".date_hour").val();
                var minute = parent.parent().parent().parent().parent().find(".date_minute").val();
                var second = parent.parent().parent().parent().parent().find(".date_second").val();
                this.input.val(parent.attr("date") + " " + hour + ":" + minute + ":" + second);
                this.hide();
                return false;
            }));

            $("td[date=" + this.getYMD(new Date()) + "]", this.tbody).addClass("today");
        },

        stringToDate: function() {
            var input = this.input.val();
            matches = input.match(/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/);
            var currentDate = new Date(matches[1], matches[2] - 1, matches[3], matches[4], matches[5], matches[6]);
            this.selectMonth(currentDate);
        },

        getYMD: function(date) {
            var year = date.getFullYear();
            var month = (date.getMonth() + 1).toString();
            var day = date.getDate().toString();
            if (month.length == 1) month = "0" + month;
            if (day.length == 1) day = "0" + day;
            return year + "-" + month + "-" + day;
        },

        dateToString: function(date) {
            var ymd = this.getYMD(date);
            var hrs = date.getHours().toString();
            var mins = date.getMinutes().toString();
            var secs = date.getSeconds().toString();
            if (hrs.length == 1) hrs = "0" + hrs;
            if (mins.length == 1) mins = "0" + mins;
            if (secs.length == 1) secs = "0" + secs;
            return ymd + " " + hrs + ":" + mins + ":" + secs;
        },

        showTime: function() {
            var val = this.input.val().split(" ")[1].split(":");
            var active_hour = val[0].toString();
            var active_minute = val[1].toString();
            var active_second = val[2].toString();

            var output = "<select class='date_hour'>";
            for (var i = 0; i < 24; i++) {
                var hour = (10 > i) ? "0" + i : i;
                var hour_fmt = (0 == i) ? "12am" : (12 > i) ? i + "am" : (12 == i) ? "12pm" : (i - 12) + "pm";
                var selected = (active_hour == hour.toString()) ? " selected" : "";
                output += "<option rel='class' value='"+hour+"'"+selected+">"+hour_fmt+"</option>";
            }
            output += "</select> <select class='date_minute'>";
            for (i = 0; i < 60; i++) {
                var minute = (10 > i) ? "0" + i : i;
                var selected = (active_minute == minute.toString()) ? " selected" : "";
                output += "<option class='opt' value='"+minute+"'"+selected+">"+minute+"</option>";
            }
            output += "</select> <select class='date_second'>";
            for (i = 0; i < 60; i++) {
                var second = (10 > i) ? "0" + i : i;
                var selected = (active_second == second.toString()) ? " selected" : "";
                output += "<option class='opt' value='"+second+"'"+selected+">"+second+"</option>";
            }
            return output + "</select>";
        },

        show: function() {
            this.rootLayers.css("display", "block");
            this.setPosition();
            this.input.unbind("focus", this.show);
            $([window, document.body]).click(this.hideIfClickOutside).keyup(this.hideOnEsc);
        },

        hide: function() {
            this.rootLayers.css("display", "none");
            $([window, document.body]).unbind("click", this.hideIfClickOutside).unbind("keyup", this.hideOnEsc);
            this.input.focus(this.show);
        },

        hideIfClickOutside: function(event) {
            if (event.target != this.input[0] && !this.insideSelector(event) && "opt" != event.target.className) {
                this.hide();
            };
        },

        hideOnEsc: function(event) {
            if (event.keyCode == 27) this.hide();
        },

        setPosition: function() {
            var offset = this.input.position();
            this.rootLayers.css({
                top: offset.top + this.input.outerHeight(),
                left: offset.left
            });

            if (this.ieframe) {
                this.ieframe.css({
                    width: this.dateSelector.outerWidth(),
                    height: this.dateSelector.outerHeight()
                });
            };
        },

        moveMonthBy: function(amount) {
            this.selectMonth(new Date(this.currentMonth.setMonth(this.currentMonth.getMonth() + amount)));
        },

        prevMonth: function() {
            this.moveMonthBy(-1);
            return false;
        },

        nextMonth: function() {
            this.moveMonthBy(1);
            return false;
        },

        monthName: function(date) {
            return this.month_names[date.getMonth()];
        },

        insideSelector: function(event) {
            var offset = this.dateSelector.offset();
            offset.right = offset.left + this.dateSelector.outerWidth();
            offset.bottom = offset.top + this.dateSelector.outerHeight();

            return event.pageY < offset.bottom &&
            event.pageY > offset.top &&
            event.pageX < offset.right &&
            event.pageX > offset.left;
        },

        bindToObj: function(fn) {
            var self = this;
            return function() { return fn.apply(self, arguments) };
        },

        bindMethodsToObj: function() {
            for (var i = 0; i < arguments.length; i++) {
                this[arguments[i]] = this.bindToObj(this[arguments[i]]);
            };
        },

        indexFor: function(array, value) {
            for (var i = 0; i < array.length; i++) {
                if (value == array[i]) return i;
            };
        },

        monthNum: function(month_name) {
            return this.indexFor(this.month_names, month_name);
        },

        daysBetween: function(start, end) {
            start = Date.UTC(start.getFullYear(), start.getMonth(), start.getDate());
            end = Date.UTC(end.getFullYear(), end.getMonth(), end.getDate());
            return (end - start) / 86400000;
        },

        changeDayTo: function(to, date, direction) {
            var difference = direction * (Math.abs(date.getDay() - to - (direction * 7)) % 7);
            return new Date(date.getFullYear(), date.getMonth(), date.getDate() + difference);
        },

        rangeStart: function(date) {
            return this.changeDayTo(this.start_of_week, new Date(date.getFullYear(), date.getMonth()), -1);
        },

        rangeEnd: function(date) {
            return this.changeDayTo((this.start_of_week - 1) % 7, new Date(date.getFullYear(), date.getMonth() + 1, 0), 1);
        },

        isFirstDayOfWeek: function(date) {
            return date.getDay() == this.start_of_week;
        },

        isLastDayOfWeek: function(date) {
            return date.getDay() == (this.start_of_week - 1) % 7;
        },

        adjustDays: function(days) {
            var newDays = [];
            for (var i = 0; i < days.length; i++) {
                newDays[i] = days[(i + this.start_of_week) % 7];
            };
            return newDays;
        }
    };

    $.fn.date_input = function(opts) {
        return this.each(function() { new DateInput(this, opts); });
    };
})(jQuery); // End localisation of the $ function
