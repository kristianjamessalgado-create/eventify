// Global calendar instance
let calendar = null;
let currentDate = new Date();
let selectedDate = new Date(); // highlighted day in mini calendar
let selectedDepartment = 'ALL';
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

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    initMiniCalendar();
    initFullCalendar();
    initDepartmentFilter();
    initViewButtons();
    initCalendarNavigation();
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

    // Filter events by selected department
    function getFilteredEvents() {
        if (!window.eventsData) return [];
        if (selectedDepartment === 'ALL') {
            return window.eventsData;
        }
        return window.eventsData.filter(event => {
            const dept = event.extendedProps?.department || 'ALL';
            return dept === selectedDepartment || dept === 'ALL';
        });
    }

    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        initialDate: currentDate,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        headerToolbar: false, // We use custom controls
        events: getFilteredEvents(),
        eventDisplay: 'block',
        height: 'auto',
        dayHeaderFormat: { weekday: 'long' },
        firstDay: 0,
        weekends: true,
        nowIndicator: true,
        eventTimeFormat: {
            hour: 'numeric',
            minute: '2-digit',
            omitZeroMinute: false,
            meridiem: 'short'
        },

        // Click empty date -> create event
        dateClick: function(info) {
            window.location.href = BASE_URL + "/backend/auth/createevent.php?date=" + info.dateStr;
        },

        // Click existing event -> show details modal
        eventClick: function(info) {
            const event = info.event;
            const props = event.extendedProps || {};

            // Fill modal content
            document.getElementById('eventTitle').textContent = event.title || 'Untitled event';

            // Format date
            let dateStr = '';
            if (event.start) {
                const opts = { year: 'numeric', month: 'short', day: 'numeric' };
                dateStr = event.start.toLocaleDateString(undefined, opts);
            }
            document.getElementById('eventDate').textContent = dateStr || (event.startStr || '');

            document.getElementById('eventLocation').textContent = props.location || 'N/A';
            document.getElementById('eventDescription').textContent = props.description || 'No description provided.';

            // Department
            const dept = (props.department || 'ALL');
            document.getElementById('eventDepartment').textContent = (dept === 'ALL')
                ? 'All Departments'
                : dept;

            // Organizer
            document.getElementById('eventOrganizer').textContent = props.organizer || 'N/A';

            // Status
            const statusEl = document.getElementById('eventStatus');
            const status = (props.status || 'active').toLowerCase();
            statusEl.textContent = status.charAt(0).toUpperCase() + status.slice(1);
            statusEl.className = 'badge ' + (status === 'active' ? 'bg-success' : 'bg-secondary');

            // Created at
            document.getElementById('eventCreatedAt').textContent = props.created_at || 'N/A';

            // Edit link
            const editLink = document.getElementById('eventEditLink');
            if (props.editUrl) {
                editLink.href = props.editUrl;
                editLink.style.display = 'inline-block';
            } else {
                editLink.style.display = 'none';
            }

            // Show modal
            const modalEl = document.getElementById('eventDetailsModal');
            const eventModal = new bootstrap.Modal(modalEl);
            eventModal.show();

            info.jsEvent.preventDefault();
        },

        // Custom event rendering to add department data attribute
        eventDidMount: function(info) {
            const dept = info.event.extendedProps?.department || 'ALL';
            info.el.setAttribute('data-dept', dept);
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
    let msg = 'Update your display name' + (name ? ' to “‘ + name + ’”' : '') + '.';
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
