// Global calendar instance
let calendar = null;
let currentDate = new Date();
let renderMiniCalendar = null; // Will be set by initMiniCalendar

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    initMiniCalendar();
    initFullCalendar();
    initViewButtons();
    initCalendarNavigation();
    initMobileSidebar();
    initScanQRModal();
    initStudentUpcomingEventClicks();
});

// ===============================
// SCAN QR FOR ATTENDANCE
// ===============================
function initScanQRModal() {
    const modalEl = document.getElementById('scanQRModal');
    const videoEl = document.getElementById('scanQRVideo');
    const canvasEl = document.getElementById('scanQRCanvas');
    const placeholderEl = document.getElementById('scanQRPlaceholder');
    const statusEl = document.getElementById('scanQRStatus');
    if (!modalEl || !videoEl || !canvasEl) return;

    let stream = null;
    let scanAnimationId = null;

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(function(t) { t.stop(); });
            stream = null;
        }
        if (scanAnimationId != null) {
            cancelAnimationFrame(scanAnimationId);
            scanAnimationId = null;
        }
        if (videoEl.srcObject) {
            videoEl.srcObject = null;
        }
    }

    function parseCheckinTokenFromUrl(urlString) {
        try {
            var url = new URL(urlString);
            return url.searchParams.get('t') || null;
        } catch (e) {
            return null;
        }
    }

    function tick() {
        if (!videoEl || !videoEl.srcObject || videoEl.readyState !== videoEl.HAVE_ENOUGH_DATA) {
            scanAnimationId = requestAnimationFrame(tick);
            return;
        }
        var w = videoEl.videoWidth;
        var h = videoEl.videoHeight;
        if (!w || !h) {
            scanAnimationId = requestAnimationFrame(tick);
            return;
        }
        canvasEl.width = w;
        canvasEl.height = h;
        var ctx = canvasEl.getContext('2d');
        ctx.drawImage(videoEl, 0, 0, w, h);
        var imageData = ctx.getImageData(0, 0, w, h);
        if (typeof jsQR !== 'undefined') {
            var code = jsQR(imageData.data, imageData.width, imageData.height);
            if (code && code.data) {
                var token = parseCheckinTokenFromUrl(code.data);
                if (token) {
                    stopCamera();
                    var base = (window.BASE_URL || '').replace(/\/$/, '');
                    window.location.href = base + '/checkin.php?t=' + encodeURIComponent(token);
                    return;
                }
            }
        }
        scanAnimationId = requestAnimationFrame(tick);
    }

    function getCameraStream(constraints) {
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            return navigator.mediaDevices.getUserMedia(constraints);
        }
        var legacy = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia;
        if (legacy) {
            return new Promise(function(resolve, reject) {
                legacy.call(navigator, constraints, resolve, reject);
            });
        }
        return Promise.reject(new Error('Not supported'));
    }

    modalEl.addEventListener('shown.bs.modal', function() {
        placeholderEl.innerHTML = '<span><i class="fas fa-camera fa-2x mb-2 d-block"></i>Starting camera…</span>';
        placeholderEl.style.display = 'flex';
        videoEl.style.display = 'none';
        var constraints = { video: { facingMode: 'environment', width: { ideal: 640 }, height: { ideal: 480 } } };
        getCameraStream(constraints).then(function(mediaStream) {
            stream = mediaStream;
            videoEl.srcObject = stream;
            videoEl.setAttribute('playsinline', true);
            videoEl.play().then(function() {
                placeholderEl.style.display = 'none';
                videoEl.style.display = 'block';
                statusEl.textContent = 'Position the event QR code within the frame.';
                tick();
            }).catch(function() {
                statusEl.textContent = 'Could not start video.';
                placeholderEl.style.display = 'none';
            });
        }).catch(function(err) {
            placeholderEl.innerHTML = '<span><i class="fas fa-video-slash fa-2x mb-2 d-block"></i>Camera not available here</span>';
            placeholderEl.style.display = 'flex';
            statusEl.innerHTML = 'Camera access needs <strong>HTTPS</strong> or is blocked in this browser. <br class="d-none d-md-inline">' +
                '<strong>Workaround:</strong> Open your phone’s <strong>Camera</strong> or <strong>QR scanner</strong> app, scan the event QR code, then open the link to check in.';
        });
    });

    modalEl.addEventListener('hidden.bs.modal', function() {
        stopCamera();
        placeholderEl.style.display = 'flex';
        placeholderEl.innerHTML = '<span><i class="fas fa-camera fa-2x mb-2 d-block"></i>Starting camera…</span>';
        statusEl.textContent = 'Position the event QR code within the frame.';
    });
}

// ===============================
// MOBILE SIDEBAR DRAWER
// ===============================
function initMobileSidebar() {
    const toggle = document.getElementById('sidebarToggleMobile');
    const closeBtn = document.getElementById('sidebarCloseMobile');
    const backdrop = document.getElementById('sidebarBackdrop');
    const sidebar = document.getElementById('studentSidebar');

    function openSidebar() {
        document.body.classList.add('student-sidebar-open');
    }

    function closeSidebar() {
        document.body.classList.remove('student-sidebar-open');
    }

    if (toggle) toggle.addEventListener('click', openSidebar);
    if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
    if (backdrop) backdrop.addEventListener('click', closeSidebar);

    // Close drawer when a quick action or modal trigger is clicked
    if (sidebar) {
        sidebar.addEventListener('click', function(e) {
            var target = e.target.closest('.action-btn, .logout-btn, [data-bs-toggle="modal"]');
            if (target && window.matchMedia('(max-width: 768px)').matches) {
                closeSidebar();
            }
        });
    }
}

// ===============================
// MINI CALENDAR
// ===============================
function initMiniCalendar() {
    const miniCalEl = document.getElementById('miniCalendar');
    const monthEl = document.getElementById('miniCalMonth');
    const prevBtn = document.getElementById('miniCalPrev');
    const nextBtn = document.getElementById('miniCalNext');

    if (!miniCalEl || !monthEl) return;

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
            miniCalEl.appendChild(dayEl);
        }

        // Current month days
        const today = new Date();
        for (let day = 1; day <= daysInMonth; day++) {
            const dayEl = document.createElement('div');
            dayEl.className = 'mini-cal-day';
            dayEl.textContent = day;

            // Highlight today only
            if (year === today.getFullYear() && month === today.getMonth() && day === today.getDate()) {
                dayEl.classList.add('today');
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
            miniCalEl.appendChild(dayEl);
        }
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            currentDate.setMonth(currentDate.getMonth() - 1);
            if (calendar) {
                calendar.prev();
                // Sync with calendar focus date
                const focus = calendar.getDate ? calendar.getDate() : new Date();
                currentDate = new Date(focus);
                selectedDate = new Date(focus);
            }
            renderMiniCalendar();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            currentDate.setMonth(currentDate.getMonth() + 1);
            if (calendar) {
                calendar.next();
                // Sync with calendar focus date
                const focus = calendar.getDate ? calendar.getDate() : new Date();
                currentDate = new Date(focus);
                selectedDate = new Date(focus);
            }
            renderMiniCalendar();
        });
    }

    // Initial render
    renderMiniCalendar();
}

// ===============================
// FULLCALENDAR INITIALIZATION
// ===============================
function initFullCalendar() {
    const calendarEl = document.getElementById('student-calendar');
    if (!calendarEl) return;

    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        initialDate: currentDate,
        selectable: false, // Students can't create events
        dayMaxEvents: true,
        headerToolbar: false, // We use custom controls
        events: window.studentEvents || [],
        eventDisplay: 'block',
        height: '100%',
        contentHeight: 'auto',
        dayHeaderFormat: window.matchMedia('(max-width: 768px)').matches ? { weekday: 'short' } : { weekday: 'long' },
        firstDay: 0,
        weekends: true,
        nowIndicator: true,
        eventTimeFormat: {
            hour: 'numeric',
            minute: '2-digit',
            omitZeroMinute: false,
            meridiem: 'short'
        },

        // Click event -> show details in modal (read-only for students)
        eventClick: function(info) {
            showStudentEventDetails(info.event);
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
            // Use the calendar focus date, not the visible-range start.
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

    // On resize (e.g. rotate phone), switch day headers between short (mobile) and long (desktop)
    window.addEventListener('resize', function() {
        if (!calendar) return;
        var isMobile = window.matchMedia('(max-width: 768px)').matches;
        calendar.setOption('dayHeaderFormat', isMobile ? { weekday: 'short' } : { weekday: 'long' });
    });

    // Force initial sync (removes the hardcoded placeholder month in sidebar)
    const focus = calendar.getDate ? calendar.getDate() : new Date();
    currentDate = new Date(focus);
    selectedDate = new Date(focus);
    if (renderMiniCalendar) renderMiniCalendar();
}

// ===============================
// STUDENT EVENT DETAILS (SHARED)
// ===============================
function showStudentEventDetails(eventLike) {
    if (!eventLike) return;
    const props = eventLike.extendedProps || {};
    const dept = (props.department || 'ALL');
    const deptText = (dept === 'ALL') ? 'All Departments' : dept;
    let startDate = null;
    let endDate = null;

    // eventLike may be a FullCalendar Event or a plain object from window.studentEvents
    if (eventLike.start instanceof Date) {
        startDate = eventLike.start;
        endDate = eventLike.end instanceof Date ? eventLike.end : null;
    } else if (eventLike.start) {
        const s = new Date(eventLike.start);
        if (!isNaN(s.getTime())) {
            startDate = s;
        }
        if (eventLike.end) {
            const e = new Date(eventLike.end);
            if (!isNaN(e.getTime())) {
                endDate = e;
            }
        }
    }

    let dateStr = '';
    if (startDate) {
        const dOpts = { year: 'numeric', month: 'long', day: 'numeric' };
        dateStr = startDate.toLocaleDateString(undefined, dOpts);
        const tOpts = { hour: 'numeric', minute: '2-digit', hour12: true };
        const startTime = startDate.toLocaleTimeString(undefined, tOpts);
        let range = startTime;
        if (endDate) {
            const endTime = endDate.toLocaleTimeString(undefined, tOpts);
            range = startTime + ' – ' + endTime;
        }
        dateStr += ' · ' + range;
    }

    const bodyEl = document.getElementById('eventDetailsModalBody');
    if (bodyEl) {
        bodyEl.innerHTML = '<p class="mb-2"><strong>Event:</strong> ' + (eventLike.title || 'Untitled') + '</p>' +
            '<p class="mb-2"><strong>Date &amp; Time:</strong> ' + (dateStr || 'TBA') + '</p>' +
            '<p class="mb-2"><strong>Location:</strong> ' + (props.location || 'N/A') + '</p>' +
            '<p class="mb-2"><strong>Department:</strong> ' + deptText + '</p>' +
            '<p class="mb-0"><strong>Description:</strong> ' + (props.description || 'No description provided.') + '</p>';
    }
    const modalEl = document.getElementById('eventDetailsModal');
    if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
}

function initStudentUpcomingEventClicks() {
    const links = document.querySelectorAll('.student-event-link[data-event-id]');
    if (!links.length || !window.studentEvents) return;

    links.forEach(function(el) {
        el.addEventListener('click', function() {
            const id = this.getAttribute('data-event-id');
            if (!id) return;
            const events = Array.isArray(window.studentEvents) ? window.studentEvents : [];
            const match = events.find(function(e) {
                return String(e.id) === String(id);
            });
            if (match) {
                showStudentEventDetails(match);
            }
        });
    });
}

// ===============================
// CALENDAR TITLE UPDATE
// ===============================
function updateCalendarTitle(info) {
    const titleEl = document.getElementById('calendarTitle');
    if (!titleEl || !calendar) return;

    // Always use FullCalendar's own computed title (prevents wrong month)
    titleEl.textContent = calendar.view?.title || '';
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

// ===============================
// PROFILE MODAL FUNCTIONS
// ===============================
function openProfileModal() {
    const modal = document.getElementById('profileModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeProfileModal() {
    const modal = document.getElementById('profileModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('profileModal');
    if (event.target === modal) {
        closeProfileModal();
    }
});

// Get BASE_URL from window or set default
const BASE_URL = window.BASE_URL || '/school_events';
