document.addEventListener('DOMContentLoaded', function () {
    var settingsForm = document.getElementById('adminSettingsForm');
    var settingsUpdateBtn = document.getElementById('adminSettingsUpdateBtn');
    var confirmSettingsModalEl = document.getElementById('confirmAdminSettingsUpdateModal');
    var confirmSettingsYesBtn = document.getElementById('confirmAdminSettingsUpdateYes');
    var confirmSettingsModal = confirmSettingsModalEl ? bootstrap.Modal.getOrCreateInstance(confirmSettingsModalEl) : null;

    if (settingsUpdateBtn && settingsForm && confirmSettingsModal) {
        settingsUpdateBtn.addEventListener('click', function () {
            confirmSettingsModal.show();
        });
    }
    if (confirmSettingsYesBtn && settingsForm) {
        confirmSettingsYesBtn.addEventListener('click', function () {
            if (confirmSettingsModal) {
                confirmSettingsModal.hide();
            }
            settingsForm.submit();
        });
    }

    var otpReqModalEl = document.getElementById('otpRequestConfirmModal');
    var otpReqMsgEl = document.getElementById('otpRequestConfirmText');
    var otpReqConfirmBtn = document.getElementById('otpRequestConfirmBtn');
    var otpReqModal = otpReqModalEl ? bootstrap.Modal.getOrCreateInstance(otpReqModalEl) : null;
    var otpPendingForm = null;

    document.querySelectorAll('form.js-confirm-otp-request').forEach(function (f) {
        f.addEventListener('submit', function (e) {
            e.preventDefault();
            otpPendingForm = f;
            if (otpReqMsgEl) {
                otpReqMsgEl.textContent = f.getAttribute('data-confirm-message') || 'Are you sure you want to request OTP?';
            }
            if (otpReqModal) {
                otpReqModal.show();
            }
        });
    });
    if (otpReqConfirmBtn) {
        otpReqConfirmBtn.addEventListener('click', function () {
            if (!otpPendingForm) return;
            if (otpReqModal) otpReqModal.hide();
            otpPendingForm.submit();
            otpPendingForm = null;
        });
    }
    if (otpReqModalEl) {
        otpReqModalEl.addEventListener('hidden.bs.modal', function () {
            otpPendingForm = null;
        });
    }

    var deptData = window.__adminChartDept || { labels: [], counts: [] };
    var stData = window.__adminChartStatus || { labels: [], counts: [] };
    var deptLabels = deptData.labels && deptData.labels.length ? deptData.labels : ['No events'];
    var deptCounts = deptData.counts && deptData.counts.length ? deptData.counts : [0];
    function showEmptyChartMessage(canvasId, msg) {
        var el = document.getElementById(canvasId);
        if (!el || !el.parentElement) return;
        el.parentElement.innerHTML = '<div class="adm-chart-empty">' + msg + '</div>';
    }
    if (typeof Chart === 'undefined') {
        showEmptyChartMessage('adminChartDept', 'Charts unavailable (Chart.js failed to load).');
        showEmptyChartMessage('adminChartStatus', 'Charts unavailable (Chart.js failed to load).');
    }
    var cdept = document.getElementById('adminChartDept');
    if (cdept && typeof Chart !== 'undefined' && deptData.counts && deptData.counts.length) {
        new Chart(cdept, {
            type: 'bar',
            data: {
                labels: deptLabels,
                datasets: [{
                    label: 'Events',
                    data: deptCounts,
                    backgroundColor: 'rgba(14, 165, 233, 0.55)',
                    borderColor: 'rgba(14, 165, 233, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                    x: { ticks: { maxRotation: 45, minRotation: 0 } }
                }
            }
        });
    } else if (cdept && typeof Chart !== 'undefined') {
        showEmptyChartMessage('adminChartDept', 'No events yet for department chart.');
    }
    var cst = document.getElementById('adminChartStatus');
    if (cst && typeof Chart !== 'undefined' && stData.counts && stData.counts.length) {
        new Chart(cst, {
            type: 'doughnut',
            data: {
                labels: stData.labels || [],
                datasets: [{
                    data: stData.counts || [],
                    backgroundColor: [
                        'rgba(234, 179, 8, 0.85)',
                        'rgba(16, 185, 129, 0.85)',
                        'rgba(239, 68, 68, 0.85)',
                        'rgba(100, 116, 139, 0.85)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    } else if (cst && typeof Chart !== 'undefined') {
        showEmptyChartMessage('adminChartStatus', 'No events yet for status chart.');
    }
    var fData = window.__adminChartFeedback || { labels: ['1★', '2★', '3★', '4★', '5★'], counts: [0, 0, 0, 0, 0] };
    var cfb = document.getElementById('adminChartFeedback');
    if (cfb && typeof Chart !== 'undefined' && fData.counts && fData.counts.length) {
        new Chart(cfb, {
            type: 'bar',
            data: {
                labels: fData.labels || [],
                datasets: [{
                    label: 'Feedback',
                    data: fData.counts || [],
                    backgroundColor: 'rgba(99, 102, 241, 0.6)',
                    borderColor: 'rgba(99, 102, 241, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    var openPendingBtn = document.getElementById('admOpenPendingBtn');
    var eventModal = document.getElementById('eventDetailsModal');
    var pendingModal = document.getElementById('pendingEventsModal');
    if (openPendingBtn && eventModal && pendingModal) {
        openPendingBtn.addEventListener('click', function () {
            var eventModalInstance = bootstrap.Modal.getInstance(eventModal);
            if (eventModalInstance) eventModalInstance.hide();
            setTimeout(function () { bootstrap.Modal.getOrCreateInstance(pendingModal).show(); }, 300);
        });
    }

    var auditSearch = document.getElementById('auditLogSearch');
    if (auditSearch) {
        auditSearch.addEventListener('input', function () {
            var q = (auditSearch.value || '').toLowerCase().trim();
            document.querySelectorAll('#auditLogTableBody tr.audit-log-row').forEach(function (tr) {
                tr.style.display = !q || tr.innerText.toLowerCase().indexOf(q) !== -1 ? '' : 'none';
            });
        });
    }

    // Reject modal: set event_id and return_to from trigger button
    var rejectModal = document.getElementById('rejectEventModal');
    if (rejectModal) {
        rejectModal.addEventListener('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            if (btn && btn.getAttribute('data-event-id')) {
                document.getElementById('rejectEventId').value = btn.getAttribute('data-event-id');
                document.getElementById('rejectReturnTo').value = btn.getAttribute('data-return-to') || '';
                document.getElementById('rejectOpenModal').value = btn.getAttribute('data-open-modal') || 'pending';
                var title = btn.getAttribute('data-event-title') || 'this event';
                document.getElementById('rejectEventTitleText').textContent = 'Reject "' + title + '"? Optionally give a reason so the organizer knows what to fix.';
                document.getElementById('rejectReasonInput').value = '';
            }
        });
    }

    var headCheck = document.getElementById('pendingHeadCheck');
    var selectAllBtn = document.getElementById('bulkSelectAllPending');
    var bulkRejectBtn = document.getElementById('bulkRejectBtn');
    var bulkForm = document.getElementById('bulkEventStatusForm');
    function getPendingChecks() {
        return Array.prototype.slice.call(document.querySelectorAll('.pending-event-checkbox'));
    }
    function setAllPendingChecks(v) {
        getPendingChecks().forEach(function (c) { c.checked = !!v; });
        if (headCheck) headCheck.checked = !!v;
    }
    if (headCheck) {
        headCheck.addEventListener('change', function () { setAllPendingChecks(headCheck.checked); });
    }
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function () {
            var checks = getPendingChecks();
            var allChecked = checks.length > 0 && checks.every(function (c) { return c.checked; });
            setAllPendingChecks(!allChecked);
        });
    }
    if (bulkRejectBtn && bulkForm) {
        bulkRejectBtn.addEventListener('click', function () {
            var selected = getPendingChecks().some(function (c) { return c.checked; });
            if (!selected) {
                alert('Select at least one event first.');
                return;
            }
            var reason = prompt('Optional rejection reason for selected events:', '');
            var input = document.getElementById('bulkRejectReasonInput');
            if (input) input.value = reason || '';
            var hiddenAction = document.createElement('input');
            hiddenAction.type = 'hidden';
            hiddenAction.name = 'action';
            hiddenAction.value = 'reject';
            bulkForm.appendChild(hiddenAction);
            bulkForm.submit();
        });
    }

    var openModal = String(window.__adminOpenModal || '').toLowerCase();
    if (openModal === 'pending') {
        var pm = document.getElementById('pendingEventsModal');
        if (pm && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(pm).show();
        }
    } else if (openModal === 'settings') {
        var sm = document.getElementById('adminSettingsModal');
        if (sm && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(sm).show();
        }
    } else if (openModal === 'notifications') {
        var nm = document.getElementById('adminNotificationsModal');
        if (nm && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(nm).show();
        }
    }

    document.querySelectorAll('.admin-upcoming-event-link').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-event-id');
            var dateStr = this.getAttribute('data-event-date') || '';
            setTimeout(function () {
                var opened = typeof window.eventifyOpenEventDetailsById === 'function' && id && window.eventifyOpenEventDetailsById(id);
                if (!opened && window.eventifyCalendar && dateStr) {
                    try {
                        window.eventifyCalendar.gotoDate(dateStr);
                    } catch (e) { /* ignore */ }
                }
            }, 320);
        });
    });

    // Apply saved admin display preferences client-side.
    var settings = window.__adminSettings || {};
    var defaultView = String(settings.default_dashboard_view || '').toLowerCase();
    if (defaultView === 'pending') {
        var pendingModal = document.getElementById('pendingEventsModal');
        if (pendingModal && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            setTimeout(function () {
                bootstrap.Modal.getOrCreateInstance(pendingModal).show();
            }, 250);
        }
    } else if (defaultView === 'charts') {
        var chartsSection = document.querySelector('.adm-charts');
        if (chartsSection) {
            setTimeout(function () {
                chartsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 150);
        }
    }

    var legend = document.getElementById('adminCalendarLegend');
    if (legend && Number(settings.calendar_legend_visible || 0) !== 1) {
        legend.style.display = 'none';
    }

});
