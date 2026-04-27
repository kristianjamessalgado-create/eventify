// Global calendar instance
let calendar = null;
const EVENTIFY_ROLE = (window.currentRole || 'organizer').toLowerCase();

/** Sidebar filter: event row matches selected department (supports JSON multi-audience). */
function eventifyEventDeptMatchesFilter(eventDept, filterDept) {
    const f = String(filterDept || 'ALL').trim();
    const ev = String(eventDept || 'ALL').trim();
    if (f === 'ALL' || f === '') {
        return true;
    }
    if (ev === '' || ev === 'ALL') {
        return true;
    }
    if (ev === f) {
        return true;
    }
    if (ev.charAt(0) === '[') {
        try {
            const arr = JSON.parse(ev);
            if (Array.isArray(arr)) {
                return arr.indexOf(f) !== -1;
            }
        } catch (e) {
            /* ignore */
        }
    }
    return false;
}

function eventifyOrganizerTodayYmd() {
    const d = new Date();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return d.getFullYear() + '-' + m + '-' + day;
}

function eventifyFormatStoredDeptForModal(stored) {
    const d = String(stored || 'ALL').trim();
    if (d === '' || d === 'ALL') {
        return 'All Departments';
    }
    if (d.charAt(0) === '[') {
        try {
            const arr = JSON.parse(d);
            if (Array.isArray(arr) && arr.length) {
                return arr.join(' · ');
            }
        } catch (e) {
            /* ignore */
        }
    }
    return d;
}

/**
 * Fill event details modal from a FullCalendar EventApi (shared: calendar click, admin upcoming list).
 */
function eventifyFillAndShowEventDetails(event) {
    if (!event) {
        return;
    }
    const props = event.extendedProps || {};

    const titleEl = document.getElementById('eventTitle');
    if (titleEl) {
        titleEl.textContent = event.title || 'Untitled event';
    }

    let dateStr = '';
    if (event.start) {
        const dOpts = { year: 'numeric', month: 'short', day: 'numeric' };
        dateStr = event.start.toLocaleDateString(undefined, dOpts);
        const tOpts = { hour: 'numeric', minute: '2-digit', hour12: true };
        const startTime = event.start.toLocaleTimeString(undefined, tOpts);
        let range = startTime;
        if (event.end) {
            const endTime = event.end.toLocaleTimeString(undefined, tOpts);
            range = startTime + ' – ' + endTime;
        }
        dateStr += ' · ' + range;
    }
    const dateCell = document.getElementById('eventDate');
    if (dateCell) {
        dateCell.textContent = dateStr || (event.startStr || '');
    }

    const locEl = document.getElementById('eventLocation');
    if (locEl) {
        locEl.textContent = props.location || 'N/A';
    }
    const descEl = document.getElementById('eventDescription');
    if (descEl) {
        descEl.textContent = props.description || 'No description provided.';
    }

    const deptEl = document.getElementById('eventDepartment');
    if (deptEl) {
        const label = String(props.department_display || '').trim();
        deptEl.textContent = label || eventifyFormatStoredDeptForModal(props.department);
    }

    const orgEl = document.getElementById('eventOrganizer');
    if (orgEl) {
        orgEl.textContent = props.organizer || 'N/A';
    }

    const statusEl = document.getElementById('eventStatus');
    const status = (props.status || 'active').toLowerCase();
    if (statusEl) {
        statusEl.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        if (status === 'active') {
            statusEl.className = 'badge bg-success';
        } else if (status === 'rejected') {
            statusEl.className = 'badge bg-danger';
        } else if (status === 'pending') {
            statusEl.className = 'badge bg-warning text-dark';
        } else {
            statusEl.className = 'badge bg-secondary';
        }
    }

    const rejectWrap = document.getElementById('eventRejectReasonWrap');
    const rejectReasonEl = document.getElementById('eventRejectReason');
    if (rejectWrap && rejectReasonEl) {
        const reason = (props.reject_reason || '').trim();
        if (status === 'rejected' && reason) {
            rejectReasonEl.textContent = reason;
            rejectWrap.style.display = 'block';
        } else {
            rejectWrap.style.display = 'none';
        }
    }

    const createdEl = document.getElementById('eventCreatedAt');
    if (createdEl) {
        createdEl.textContent = props.created_at || 'N/A';
    }

    const editLink = document.getElementById('eventEditLink');
    if (editLink) {
        if (props.editUrl) {
            editLink.href = props.editUrl;
            editLink.style.display = 'inline-block';
        } else {
            editLink.style.display = 'none';
        }
    }

    const qrLink = document.getElementById('eventQrLink');
    if (qrLink) {
        if (event.id) {
            qrLink.href = BASE_URL + '/event_qr.php?id=' + event.id;
            qrLink.style.display = 'inline-block';
        } else {
            qrLink.style.display = 'none';
        }
    }

    const attendanceLink = document.getElementById('eventAttendanceLink');
    if (attendanceLink) {
        if (event.id) {
            attendanceLink.href = BASE_URL + '/event_attendance.php?id=' + event.id;
            attendanceLink.style.display = 'inline-block';
        } else {
            attendanceLink.style.display = 'none';
        }
    }

    const markBtn = document.getElementById('organizerMarkEndedBtn');
    if (markBtn) {
        const ymd = String(props.event_date_ymd || '').trim();
        const canMarkEnded =
            EVENTIFY_ROLE === 'organizer' &&
            status === 'active' &&
            ymd !== '' &&
            ymd <= eventifyOrganizerTodayYmd();
        if (canMarkEnded && event.id) {
            markBtn.style.display = 'inline-block';
            markBtn.setAttribute('data-eventify-event-id', String(event.id));
        } else {
            markBtn.style.display = 'none';
            markBtn.setAttribute('data-eventify-event-id', '');
        }
    }

    const modalEl = document.getElementById('eventDetailsModal');
    if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const eventModal = bootstrap.Modal.getOrCreateInstance(modalEl);
        eventModal.show();
    }
}

/**
 * Open event details from calendar by id (e.g. admin upcoming modal).
 */
function eventifyOpenEventDetailsById(eventId) {
    if (!calendar || eventId == null || eventId === '') {
        return false;
    }
    const ev = calendar.getEventById(String(eventId));
    if (ev) {
        eventifyFillAndShowEventDetails(ev);
        return true;
    }
    return false;
}

window.eventifyFillAndShowEventDetails = eventifyFillAndShowEventDetails;
window.eventifyOpenEventDetailsById = eventifyOpenEventDetailsById;
let currentDate = new Date();
let selectedDate = new Date(); // highlighted day in mini calendar
let selectedDepartment = (function () {
    var os = (typeof window !== 'undefined' && window.__organizerSettings) ? window.__organizerSettings : {};
    var d = String(os.default_department_filter != null ? os.default_department_filter : 'ALL').trim();
    if (!d) {
        d = 'ALL';
    }
    return d;
})();
let renderMiniCalendar = null; // Will be set by initMiniCalendar

function isSameDay(a, b) {
    return (
        a &&
        b &&
        a.getFullYear() === b.getFullYear() &&
        a.getMonth() === b.getMonth() &&
        a.getDate() === b.getDate()
    );
}

function initOrganizerSidebarToggle() {
    const toggle = document.getElementById('organizerSidebarToggle');
    const closeBtn = document.getElementById('organizerSidebarClose');
    const backdrop = document.getElementById('organizerSidebarBackdrop');
    const sidebar = document.getElementById('organizerSidebar');
    const isMobileView = () => window.matchMedia('(max-width: 768px)').matches;

    const refreshCalendarLayout = () => {
        if (!calendar) return;
        if (typeof calendar.updateSize === 'function') {
            calendar.updateSize();
        }
    };

    const refreshCalendarLayoutSmooth = () => {
        [0, 90, 180, 280, 360].forEach(function (ms) {
            setTimeout(refreshCalendarLayout, ms);
        });
    };

    const closeMobileSidebar = () => document.body.classList.remove('organizer-sidebar-open');

    if (toggle) {
        toggle.addEventListener('click', function () {
            if (isMobileView()) {
                document.body.classList.add('organizer-sidebar-open');
                return;
            }
            document.body.classList.toggle('organizer-sidebar-collapsed');
            refreshCalendarLayoutSmooth();
        });
    }
    if (closeBtn) closeBtn.addEventListener('click', closeMobileSidebar);
    if (backdrop) backdrop.addEventListener('click', closeMobileSidebar);

    if (sidebar) {
        sidebar.addEventListener('transitionend', function (e) {
            if (e.propertyName === 'width' || e.propertyName === 'padding-left' || e.propertyName === 'padding-right') {
                refreshCalendarLayout();
            }
        });
        sidebar.addEventListener('click', function (e) {
            const target = e.target.closest('.action-btn, [data-bs-toggle="modal"]');
            if (target && isMobileView()) closeMobileSidebar();
        });
    }

    window.addEventListener('resize', function () {
        if (!isMobileView()) closeMobileSidebar();
        refreshCalendarLayoutSmooth();
    });
}

// Initialize on DOM ready
function initCreateEventDeptAudience() {
    const form = document.getElementById('createEventModalForm');
    if (!form) {
        return;
    }
    const allCb = document.getElementById('ceDeptAll');
    const specifics = form.querySelectorAll('.ce-dept-specific');
    if (allCb) {
        allCb.addEventListener('change', function () {
            if (allCb.checked) {
                specifics.forEach(function (cb) {
                    cb.checked = false;
                });
            }
        });
    }
    specifics.forEach(function (cb) {
        cb.addEventListener('change', function () {
            if (cb.checked && allCb) {
                allCb.checked = false;
            }
        });
    });
    form.addEventListener('submit', function (e) {
        const anySpecific = Array.from(specifics).some(function (c) {
            return c.checked;
        });
        const allOn = allCb && allCb.checked;
        if (!allOn && !anySpecific) {
            e.preventDefault();
            alert('Please choose "All departments" or select at least one department.');
        }
    });
}

function eventifyOrganizerStatusUpdateUrl() {
    const b = String(typeof window.BASE_URL !== 'undefined' ? window.BASE_URL : '/school_events').replace(/\/+$/, '');
    return b + '/backend/auth/update_organizer_event_status.php';
}

let eventifyOrganizerStatusPending = null;

function eventifyOpenOrganizerEventStatusModal(opts) {
    eventifyOrganizerStatusPending = {
        action: opts.action,
        eventId: String(opts.eventId)
    };
    const titleEl = document.getElementById('organizerEventStatusConfirmTitle');
    const bodyEl = document.getElementById('organizerEventStatusConfirmBody');
    if (titleEl) {
        titleEl.textContent = opts.title || 'Confirm';
    }
    if (bodyEl) {
        bodyEl.textContent = opts.body || '';
    }
    const modalEl = document.getElementById('organizerEventStatusConfirmModal');
    if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

function initOrganizerEventStatusModal() {
    const statusModal = document.getElementById('organizerEventStatusConfirmModal');
    if (statusModal && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        statusModal.addEventListener('shown.bs.modal', function () {
            statusModal.style.zIndex = '2000';
            const backs = document.querySelectorAll('.modal-backdrop');
            backs.forEach(function (b, i) {
                if (i === backs.length - 1) {
                    b.style.zIndex = '1990';
                }
            });
        });
        statusModal.addEventListener('hidden.bs.modal', function () {
            statusModal.style.zIndex = '';
            document.querySelectorAll('.modal-backdrop').forEach(function (b) {
                b.style.zIndex = '';
            });
        });
    }

    const yesBtn = document.getElementById('organizerEventStatusConfirmYes');
    const form = document.getElementById('organizerEventStatusHiddenForm');
    if (yesBtn && form) {
        yesBtn.addEventListener('click', function () {
            if (!eventifyOrganizerStatusPending) {
                return;
            }
            const evIdInput = document.getElementById('organizerEventStatusHiddenEventId');
            const actInput = document.getElementById('organizerEventStatusHiddenAction');
            if (evIdInput) {
                evIdInput.value = eventifyOrganizerStatusPending.eventId;
            }
            if (actInput) {
                actInput.value = eventifyOrganizerStatusPending.action;
            }
            form.action = eventifyOrganizerStatusUpdateUrl();
            const confirmModal = document.getElementById('organizerEventStatusConfirmModal');
            if (confirmModal && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const inst = bootstrap.Modal.getInstance(confirmModal);
                if (inst) {
                    inst.hide();
                }
            }
            form.submit();
        });
    }

    document.body.addEventListener('click', function (e) {
        const btn = e.target.closest('.js-organizer-event-status-btn');
        if (!btn) {
            return;
        }
        const action = btn.getAttribute('data-eventify-action') || '';
        const eventId = btn.getAttribute('data-eventify-event-id') || '';
        if (!eventId || (action !== 'close' && action !== 'cancel')) {
            return;
        }
        if (action === 'cancel') {
            eventifyOpenOrganizerEventStatusModal({
                action: 'cancel',
                eventId: eventId,
                title: 'Withdraw submission?',
                body: 'This event will no longer be pending approval.'
            });
        } else {
            eventifyOpenOrganizerEventStatusModal({
                action: 'close',
                eventId: eventId,
                title: 'Mark event as ended?',
                body: 'Students will no longer be able to check in for this event.'
            });
        }
    });

    const markEndedBtn = document.getElementById('organizerMarkEndedBtn');
    if (markEndedBtn) {
        markEndedBtn.addEventListener('click', function () {
            const eventId = markEndedBtn.getAttribute('data-eventify-event-id') || '';
            if (!eventId) {
                return;
            }
            eventifyOpenOrganizerEventStatusModal({
                action: 'close',
                eventId: eventId,
                title: 'Mark event as ended?',
                body: 'Students will no longer be able to check in for this event.'
            });
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    initOrganizerSidebarToggle();
    initMiniCalendar();
    initFullCalendar();
    initDepartmentFilter();
    initViewButtons();
    initCalendarNavigation();
    initCreateEventDeptAudience();
    initOrganizerEventStatusModal();

    var orgSettingsForm = document.getElementById('organizerSettingsForm');
    var orgSettingsBtn = document.getElementById('organizerSettingsUpdateBtn');
    var orgSettingsConfirmEl = document.getElementById('confirmOrganizerSettingsModal');
    var orgSettingsConfirmYes = document.getElementById('confirmOrganizerSettingsYes');
    if (orgSettingsForm && orgSettingsBtn && orgSettingsConfirmEl && orgSettingsConfirmYes && typeof bootstrap !== 'undefined') {
        var orgSettingsConfirmModal = bootstrap.Modal.getOrCreateInstance(orgSettingsConfirmEl);
        orgSettingsBtn.addEventListener('click', function () {
            orgSettingsConfirmModal.show();
        });
        orgSettingsConfirmYes.addEventListener('click', function () {
            orgSettingsConfirmModal.hide();
            orgSettingsForm.submit();
        });
    }

    var clearNotifModal = document.getElementById('organizerClearNotifsModal');
    if (clearNotifModal && typeof bootstrap !== 'undefined') {
        clearNotifModal.addEventListener('show.bs.modal', function () {
            document.querySelectorAll('.top-navbar .dropdown-menu.show').forEach(function (menu) {
                var toggle = menu.previousElementSibling;
                if (toggle && toggle.getAttribute('data-bs-toggle') === 'dropdown') {
                    var inst = bootstrap.Dropdown.getInstance(toggle);
                    if (inst) {
                        inst.hide();
                    }
                }
            });
        });
    }
});

// ===============================
// MINI CALENDAR
// ===============================
function initMiniCalendar() {
    const miniCalEl = document.getElementById('miniCalendar');
    const monthEl = document.getElementById('miniCalMonth');
    const prevBtn = document.getElementById('miniCalPrev');
    const nextBtn = document.getElementById('miniCalNext');

    if (!miniCalEl || !monthEl || !prevBtn || !nextBtn) return;

    renderMiniCalendar = function() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        
        // Update month display
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'];
        monthEl.textContent = `${monthNames[month]} ${year}`;

        // Clear previous content
        miniCalEl.innerHTML = '';

        // Day headers
        const dayHeaders = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
        dayHeaders.forEach(day => {
            const header = document.createElement('div');
            header.className = 'mini-cal-day-header';
            header.textContent = day;
            miniCalEl.appendChild(header);
        });

        // Get first day of month and number of days
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const daysInMonth = lastDay.getDate();
        const startingDayOfWeek = firstDay.getDay();

        // Previous month days
        const prevMonthLastDay = new Date(year, month, 0).getDate();
        for (let i = startingDayOfWeek - 1; i >= 0; i--) {
            const day = prevMonthLastDay - i;
            const dayEl = document.createElement('div');
            dayEl.className = 'mini-cal-day other-month';
            dayEl.textContent = day;
            
            // Make previous month days clickable
            dayEl.addEventListener('click', function() {
                const clickedDate = new Date(year, month - 1, day);
                currentDate = clickedDate;
                selectedDate = clickedDate;
                if (calendar) {
                    calendar.gotoDate(clickedDate);
                }
                renderMiniCalendar();
            });

            if (isSameDay(new Date(year, month - 1, day), selectedDate)) {
                dayEl.classList.add('selected');
            }
            
            miniCalEl.appendChild(dayEl);
        }

        // Current month days
        const today = new Date();
        for (let day = 1; day <= daysInMonth; day++) {
            const dayEl = document.createElement('div');
            dayEl.className = 'mini-cal-day';
            dayEl.textContent = day;

            // Check if today
            if (year === today.getFullYear() && month === today.getMonth() && day === today.getDate()) {
                dayEl.classList.add('today');
            }

            // Click handler - navigate main calendar to this date
            dayEl.addEventListener('click', function() {
                const clickedDate = new Date(year, month, day);
                currentDate = clickedDate;
                selectedDate = clickedDate;
                if (calendar) {
                    calendar.gotoDate(clickedDate);
                }
                renderMiniCalendar();
            });

            if (isSameDay(new Date(year, month, day), selectedDate)) {
                dayEl.classList.add('selected');
            }

            miniCalEl.appendChild(dayEl);
        }

        // Next month days
        const totalCells = 42; // 6 rows × 7 days
        const remainingCells = totalCells - (startingDayOfWeek + daysInMonth);
        for (let day = 1; day <= remainingCells; day++) {
            const dayEl = document.createElement('div');
            dayEl.className = 'mini-cal-day other-month';
            dayEl.textContent = day;
            
            // Make next month days clickable
            dayEl.addEventListener('click', function() {
                const clickedDate = new Date(year, month + 1, day);
                currentDate = clickedDate;
                selectedDate = clickedDate;
                if (calendar) {
                    calendar.gotoDate(clickedDate);
                }
                renderMiniCalendar();
            });

            if (isSameDay(new Date(year, month + 1, day), selectedDate)) {
                dayEl.classList.add('selected');
            }
            
            miniCalEl.appendChild(dayEl);
        }
    }

    prevBtn.addEventListener('click', function() {
        currentDate.setMonth(currentDate.getMonth() - 1);
        if (calendar) {
            calendar.prev();
            // Sync with calendar's focus date (not range start)
            const focus = calendar.getDate ? calendar.getDate() : new Date();
            currentDate = new Date(focus);
            selectedDate = new Date(focus);
        }
        renderMiniCalendar();
    });

    nextBtn.addEventListener('click', function() {
        currentDate.setMonth(currentDate.getMonth() + 1);
        if (calendar) {
            calendar.next();
            // Sync with calendar's focus date (not range start)
            const focus = calendar.getDate ? calendar.getDate() : new Date();
            currentDate = new Date(focus);
            selectedDate = new Date(focus);
        }
        renderMiniCalendar();
    });

    // Initial render
    renderMiniCalendar();
}

// ===============================
// FULLCALENDAR INITIALIZATION
// ===============================
function initFullCalendar() {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    const os = window.__organizerSettings || {};
    const allowedViews = ['dayGridMonth', 'timeGridWeek', 'timeGridDay'];
    let initView = String(os.default_calendar_view || '').trim();
    if (!allowedViews.includes(initView)) {
        initView = 'dayGridMonth';
    }
    const deptPref = String(os.default_department_filter || '').trim();
    if (deptPref) {
        const matchEl = Array.from(document.querySelectorAll('.calendar-item[data-dept]')).find(function (el) {
            return (el.getAttribute('data-dept') || '') === deptPref;
        });
        if (matchEl) {
            selectedDepartment = deptPref;
        }
    }
    const showWeekends = !(os.show_weekends === 0 || os.show_weekends === false || String(os.show_weekends) === '0');
    const weekStartsOn = parseInt(os.week_starts_on, 10) === 1 ? 1 : 0;

    // Filter events by selected department
    function getFilteredEvents() {
        if (!window.eventsData) return [];
        if (selectedDepartment === 'ALL') {
            return window.eventsData;
        }
        return window.eventsData.filter(event => {
            const dept = event.extendedProps?.department || 'ALL';
            return eventifyEventDeptMatchesFilter(dept, selectedDepartment);
        });
    }

    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: initView,
        initialDate: currentDate,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        headerToolbar: false, // We use custom controls
        events: getFilteredEvents(),
        eventDisplay: 'block',
        height: 'auto',
        dayHeaderFormat: { weekday: 'long' },
        firstDay: weekStartsOn,
        weekends: showWeekends,
        nowIndicator: true,
        eventTimeFormat: {
            hour: 'numeric',
            minute: '2-digit',
            omitZeroMinute: false,
            meridiem: 'short'
        },

        // Click empty date -> create event (organizer only)
        dateClick: function(info) {
            if (EVENTIFY_ROLE === 'organizer') {
                window.location.href = BASE_URL + "/backend/auth/createevent.php?date=" + info.dateStr;
            }
        },

        // Click existing event -> show details modal
        eventClick: function(info) {
            eventifyFillAndShowEventDetails(info.event);
            info.jsEvent.preventDefault();
        },

        // Custom event rendering to add department + state attributes
        eventDidMount: function(info) {
            const dept = info.event.extendedProps?.department || 'ALL';
            info.el.setAttribute('data-dept', dept);
            const status = String(info.event.extendedProps?.status || '').toLowerCase();
            const start = info.event.start instanceof Date ? info.event.start : null;
            const now = new Date();
            let state = 'active';

            if (status === 'closed' || status === 'completed') {
                state = 'closed';
            } else if (status === 'rejected') {
                state = 'rejected';
            } else if (start && start > now) {
                state = 'upcoming';
            } else {
                state = 'active';
            }
            info.el.setAttribute('data-event-state', state);

            // Force color at runtime to avoid CSS/cache conflicts.
            let bg = '#16a34a'; // active
            if (state === 'upcoming') bg = '#f59e0b';
            if (state === 'closed') bg = '#6b7280';
            if (state === 'rejected') bg = '#dc2626';
            info.el.style.backgroundColor = bg;
            info.el.style.borderColor = bg;
            info.el.style.color = '#ffffff';
        },

        // Update title when view changes and sync mini calendar
        datesSet: function(info) {
            updateCalendarTitle(info);
            // IMPORTANT: FullCalendar's info.start is the start of the visible range
            // (can be previous month). Use the calendar "focus" date instead.
            const focus = calendar.getDate ? calendar.getDate() : new Date();
            currentDate = new Date(focus);
            selectedDate = new Date(focus);
            // Update mini calendar to match main calendar focus date
            if (renderMiniCalendar) {
                renderMiniCalendar();
            }
        }
    });

    calendar.render();
    window.eventifyCalendar = calendar;

    document.querySelectorAll('.calendar-item').forEach(function (i) {
        i.classList.toggle('active', (i.getAttribute('data-dept') || '') === selectedDepartment);
    });
    document.querySelectorAll('.view-btn').forEach(function (b) {
        const v = b.getAttribute('data-view');
        b.classList.toggle('active', v === initView && v !== 'today');
    });

    // Force initial sync (removes the hardcoded placeholder "September 2026")
    const focus = calendar.getDate ? calendar.getDate() : new Date();
    currentDate = new Date(focus);
    selectedDate = new Date(focus);
    if (renderMiniCalendar) renderMiniCalendar();

    // Update events when department filter changes
    window.updateCalendarEvents = function() {
        calendar.removeAllEvents();
        calendar.addEventSource(getFilteredEvents());
    };
}

// (Modal calendar removed; main calendar stays in dashboard)

// ===============================
// CALENDAR TITLE UPDATE
// ===============================
function updateCalendarTitle(info) {
    const titleEl = document.getElementById('calendarTitle');
    if (!titleEl || !calendar) return;

    // Always use FullCalendar's own computed title (prevents off-by-one / range-start issues)
    titleEl.textContent = calendar.view?.title || '';
}

// ===============================
// DEPARTMENT FILTER
// ===============================
function initDepartmentFilter() {
    const calendarItems = document.querySelectorAll('.calendar-item');
    
    calendarItems.forEach(item => {
        item.addEventListener('click', function() {
            // Remove active class from all
            calendarItems.forEach(i => i.classList.remove('active'));
            
            // Add active to clicked
            this.classList.add('active');
            
            // Update selected department
            selectedDepartment = this.getAttribute('data-dept') || 'ALL';
            
            // Update calendar events
            if (window.updateCalendarEvents) {
                window.updateCalendarEvents();
            }
        });
    });
}

// ===============================
// VIEW BUTTONS
// ===============================
function initViewButtons() {
    const viewButtons = document.querySelectorAll('.view-btn');
    
    viewButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const view = this.getAttribute('data-view');
            
            // Remove active from all
            viewButtons.forEach(b => b.classList.remove('active'));
            
            // Add active to clicked
            this.classList.add('active');
            
            // Handle "Today" button
            if (view === 'today') {
                calendar.today();
                const focus = calendar.getDate ? calendar.getDate() : new Date();
                currentDate = new Date(focus);
                selectedDate = new Date(focus);
                if (renderMiniCalendar) renderMiniCalendar();
            } else {
                // Change view
                calendar.changeView(view);
            }
        });
    });
}

// ===============================
// CALENDAR NAVIGATION
// ===============================
function initCalendarNavigation() {
    const prevBtn = document.getElementById('calPrev');
    const nextBtn = document.getElementById('calNext');
    
    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            calendar.prev();
            const focus = calendar.getDate ? calendar.getDate() : new Date();
            currentDate = new Date(focus);
            selectedDate = new Date(focus);
            // Update mini calendar
            if (renderMiniCalendar) {
                renderMiniCalendar();
            }
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            calendar.next();
            const focus = calendar.getDate ? calendar.getDate() : new Date();
            currentDate = new Date(focus);
            selectedDate = new Date(focus);
            // Update mini calendar
            if (renderMiniCalendar) {
                renderMiniCalendar();
            }
        });
    }
}

// Get BASE_URL from window or set default
const BASE_URL = window.BASE_URL || '/school_events';

// ===============================
// ORGANIZER PROFILE
// ===============================
function previewOrganizerProfilePicture(input) {
    const preview = document.getElementById('organizerProfilePicturePreview');
    if (!preview) return;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            if (preview.tagName === 'IMG') {
                preview.src = e.target.result;
            } else {
                const img = document.createElement('img');
                img.id = 'organizerProfilePicturePreview';
                img.className = 'organizer-profile-picture-preview';
                img.alt = 'Preview';
                img.src = e.target.result;
                preview.parentNode.replaceChild(img, preview);
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function confirmOrganizerProfileChanges(form) {
    const name = (form.querySelector('input[name="name"]') || {}).value || '';
    const fileInput = form.querySelector('input[name="profile_picture"]');
    const hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
    let msg = 'Update your display name' + (name ? ` to "${name}"` : '') + '.';
    if (hasFile) msg += ' A new profile picture will be uploaded.';
    const messageEl = document.getElementById('confirmOrganizerProfileMessage');
    if (messageEl) messageEl.textContent = msg;
    const modalEl = document.getElementById('confirmOrganizerProfileModal');
    if (!modalEl) {
        form.submit();
        return;
    }
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
    const confirmBtn = document.getElementById('confirmOrganizerProfileBtn');
    if (confirmBtn) {
        confirmBtn.onclick = function () {
            modal.hide();
            form.submit();
        };
    }
}
