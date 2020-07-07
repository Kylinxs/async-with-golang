
/**
 * Support JavaScript for FullCalendar Resource Views used by wikiplugin_trackercalendar
 */

$.fn.setupFullCalendar = function(data)
{
    this.each(function () {
        let cal = this;
        $(cal).tikiModal(tr("Loading..."));

        let storeEvent = function (eventInfo) {
            let event = eventInfo.event,
                end = event.end,
                start = event.start,
                request = {
                    itemId: event.id,
                    trackerId: data.trackerId,
                    ajax: true,
                };

            if (!end) {
                end = start;
            }

            request['fields~' + data.begin] = moment(start).unix() + (start.getTimezoneOffset() * 60);
            request['fields~' + data.end] = moment(end).unix() + (end.getTimezoneOffset() * 60);

            let resource = event.getResources();
            if (resource.length) {
                resource = resource[0];
            }
            request['fields~' + data.resource] = resource.title;

            $.post($.service('tracker', 'update_item'), request, null, 'json');
        };

        let slotLabelTimeFormat = {
            hour: 'numeric',
            minute: '2-digit',
            meridiem: data.timeFormat,
            hour12: data.timeFormat
        };
        var calendar = new FullCalendar.Calendar(cal, {
            themeSystem: 'bootstrap',
            schedulerLicenseKey: data.premiumLicense,
            initialDate: data.initialDate,
            eventTimeFormat: {
                hour: 'numeric',
                minute: '2-digit',
                meridiem: data.timeFormat,
                hour12: data.timeFormat,
            },
            views: {
                timeGrid: {
                    // options apply to timeGridWeek and timeGridDay views
                    slotLabelFormat: slotLabelTimeFormat,
                },
                resourceTimelineDay: {
                    // options apply to timeGridWeek and timeGridDay views
                    slotLabelFormat: slotLabelTimeFormat,
                },
                resourceTimelineWeek: {
                    slotLabelInterval: "24:00:00",
                    slotDuration: "06:00:00",
                    slotLabelFormat: [
                        // top level of text
                        {
                            month: 'long',
                            year: 'numeric'
                        },
                        // lower level of text
                        {
                            day: "numeric",
                            weekday: 'short',
                        },
                    ],
                }
            },
            viewClassNames: function (currentView) {
                $(cal).tikiModal();
                console.debug(currentView.view.type);    // useful for debugging
            },
            timeZone: data.display_timezone,
            headerToolbar: {
                left: 'prevYear,prev,next,nextYear today',
                center: 'title',
                right: data.views
            },
            editable: true,
            events: $.service('tracker_calendar', 'list', $.extend(data.filterValues, {
                trackerId: data.trackerId,
                colormap: data.colormap,
                beginField: data.begin,
                endField: data.end,
                resourceField: data.resource,
                coloringField: data.coloring,
                filters: data.body,
                maxRecords: data.maxEvents
            })),
            buttonText: {
                resourceTimelineDay: data.labelResDay,
                resourceTimelineWeek: data.labelResWeek,
                resourceTimelineMonth: data.labelResMonth,
                resourceTimelineYear: data.labelResYear,
                listDay: data.labelListDay,
                listWeek: data.labelListWeek,
                listMonth: data.llabelListMonth,
                listYear: data.labelListYear,
                today: data.labelToday,
                resourceTimeGridWeek: data.labelAgendaWeek,
                resourceTimeGridDay: data.labelAgendaDay,
            },
            resources: data.resourceList,
            allDayText: data.labelAllDay,
            firstDay: data.firstDayofWeek,
            slotDuration: data.slotDuration,
            slotMinTime: data.minHourOfDay,
            slotMaxTime: data.maxHourOfDay,
            initialView: data.dView,
            eventClick: function (event) {
                if (data.url) {
                    var actualURL = data.url;
                    actualURL += actualURL.indexOf("?") === -1 ? "?" : "&";

                    if (data.trkitemid === "y" && data.addAllFields === "n") {    // "simple" mode
                        actualURL += "itemId=" + event.id;
                    } else {
                        var lOp = '';
                        var html = $.parseHTML(event.description) || [];

                        // Store useful data values to the URL for Wiki Argument Variable
                        // use and to javascript session storage for JQuery use
                        actualURL += "trackerid=" + event.trackerId;
                        if (data.trkitemid == 'y') {
                            actualURL = actualURL + "&itemId=" + event.id;
                        } else {
                            actualURL = actualURL + "&itemid=" + event.id;
                        }
                        actualURL = actualURL + "&title=" + event.title;
                        actualURL = actualURL + "&end=" + event.end;
                        actualURL = actualURL + "&start=" + event.start;
                        if (data.useSessionStorage) {
                            sessionStorage.setItem("trackerid", event.trackerId);
                            sessionStorage.setItem("title", event.title);
                            sessionStorage.setItem("start", event.start);
                            sessionStorage.setItem("itemid", event.id);
                            sessionStorage.setItem("end", event.end);
                            sessionStorage.setItem("eventColor", event.color);
                        }

                        // Capture the description HTML as variables
                        // with the label being the variable name
                        $.each(html, function (i, el) {
                            if (isEven(i) == true) {
                                lOp = el.textContent.replace(' ', '_');
                            } else {
                                actualURL = actualURL + "&" + lOp + "=" + el.textContent;
                                if (data.useSessionStorage) {
                                    sessionStorage.setItem(lOp, el.textContent);
                                }
                            }
                        });
                    }

                    location.href = actualURL;
                    return false;

                } else {
                    // standard tracker item view/edit
                    let e = event.event;
                    event.jsEvent.preventDefault();

                    if (e.startEditable && e.extendedProps.trackerId) {
                        var info = {
                            trackerId: e.extendedProps.trackerId,
                            itemId: e.id
                        };
                        $.openModal({
                            remote: $.service('tracker', 'update_item', info),
                            size: "modal-lg",
                            title: e.title,
                            open: function () {
                                $('form:not(.no-ajax)', this)
                                    .addClass('no-ajax') // Remove default ajax handling, we replace it
                                    .submit(ajaxSubmitEventHandler(function (data) {
                                        $(this).parents(".modal").modal("hide");
                                        calendar.refetchEvents();
                                    }));
                            }
                        });
                        return false;
                    } else {
                        return true;
                    }
                }
            },
            eventDidMount: function(arg) {
                let event = arg.event;
                let element = $(arg.el);
                element.attr('title', event.title);
                element.popover({
                    trigger: 'hover',
                    html: true,
                    content: event.extendedProps.description,
                    container: 'body',
                    delay: { "show": 250, "hide": 500 },
                    customClass: "popover-sm",
                });
            },
            dateClick: function (date, jsEvent, view) {
                if (data.canInsert) {
                    var info = {
                        trackerId: data.trackerId
                    };
                    let momentDate = moment(date.date);
                    info[data.beginFieldName] = momentDate.unix();
                    info[data.endFieldName] = momentDate.add(1, 'h').unix();
                    if (data.url) {
                        $('<a href="#"/>').attr('href', data.url);
                    } else {
                        $.openModal({
                            remote: $.service('tracker', 'insert_item', info),
                            size: "modal-lg",
                            title: data.addTitle,
                            open: function () {
                                $('form:not(.no-ajax)', this)
                                    .addClass('no-ajax') // Remove default ajax handling, we replace it
                                    .submit(ajaxSubmitEventHandler(function (data) {
                                        $(this).parents(".modal").modal("hide");
                                        calendar.refetchEvents();
                                    }));
                            }
                        });
                    }
                }

                return false;
            },
            eventResize: storeEvent,
            eventDrop: storeEvent,
            height: 'auto',
            dayMinWidth: 150, // will cause horizontal scrollbars
        });
        calendar.render();

        if (jqueryTiki.print_pdf_from_url !== "none") {
            $(document).ready(function () {
                addFullCalendarPrint('#' + data.id, '#calendar-pdf-btn', calendar);
            });
        }
    });
};