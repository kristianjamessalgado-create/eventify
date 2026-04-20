// ===============================
// SECTION NAVIGATION
// ===============================
let currentSection = document.querySelector('section.active');

function isMobileView() {
    return typeof window !== 'undefined' && window.innerWidth <= 768;
}

function goToSection(id) {
    const nextSection = document.getElementById(id);
    if (!nextSection) return;

    // On mobile: sections stack; scroll to the section
    if (isMobileView()) {
        nextSection.classList.add('active');
        if (currentSection && currentSection !== nextSection) {
            currentSection.classList.remove('active');
        }
        currentSection = nextSection;
        nextSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        return;
    }

    if (currentSection === nextSection) return;

    // Exit current section
    if (currentSection) {
        currentSection.classList.remove('active');
        currentSection.classList.add('exit-left');
    }

    // Prepare next section
    nextSection.classList.remove('exit-left');
    nextSection.classList.add('enter-left');

    // Force browser reflow
    nextSection.offsetHeight;

    // Activate next
    nextSection.classList.remove('enter-left');
    nextSection.classList.add('active');

    currentSection = nextSection;

    // Smooth scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ===============================
// MOBILE MENU
// ===============================
function closeMobileNav() {
    const nav = document.getElementById('mobileNav');
    const btn = document.getElementById('hamburgerBtn');
    const overlay = document.getElementById('mobileNavOverlay');
    if (nav) nav.classList.remove('open');
    if (btn) btn.classList.remove('active');
    if (overlay) overlay.classList.remove('show');
    document.body.classList.remove('mobile-menu-open');
    document.body.style.overflow = '';
}

function toggleMobileNav() {
    const nav = document.getElementById('mobileNav');
    const btn = document.getElementById('hamburgerBtn');
    const overlay = document.getElementById('mobileNavOverlay');
    if (!nav || !btn) return;
    const isOpen = nav.classList.toggle('open');
    btn.classList.toggle('active', isOpen);
    if (overlay) overlay.classList.toggle('show', isOpen);
    document.body.classList.toggle('mobile-menu-open', isOpen);
    document.body.style.overflow = '';
}

document.addEventListener('DOMContentLoaded', function () {
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const registerRoleSelectModal = document.getElementById('registerRoleSelectModal');
    const registerDepartmentWrapModal = document.getElementById('registerDepartmentWrapModal');
    const loginModalPassword = document.getElementById('loginModalPassword');
    const toggleLoginModalPassword = document.getElementById('toggleLoginModalPassword');
    const registerModalPassword = document.getElementById('registerModalPassword');
    const registerModalConfirmPassword = document.getElementById('registerModalConfirmPassword');
    const toggleRegisterModalPassword = document.getElementById('toggleRegisterModalPassword');
    const toggleRegisterModalConfirmPassword = document.getElementById('toggleRegisterModalConfirmPassword');
    const loginModalForm = document.getElementById('loginModalForm');
    const registerModalForm = document.getElementById('registerModalForm');
    const verifyModalForm = document.getElementById('verifyModalForm');
    const verifyModalEmail = document.getElementById('verifyModalEmail');
    const loginModalMessage = document.getElementById('loginModalMessage');
    const registerModalMessage = document.getElementById('registerModalMessage');
    const verifyModalMessage = document.getElementById('verifyModalMessage');
    if (hamburgerBtn) {
        hamburgerBtn.addEventListener('click', toggleMobileNav);
    }

    if (registerRoleSelectModal && registerDepartmentWrapModal) {
        const toggleDeptField = function () {
            registerDepartmentWrapModal.style.display = registerRoleSelectModal.value === 'student' ? 'block' : 'none';
        };
        registerRoleSelectModal.addEventListener('change', toggleDeptField);
        toggleDeptField();
    }

    function bindEyeToggle(buttonEl, inputEl) {
        if (!buttonEl || !inputEl) return;
        buttonEl.addEventListener('click', function () {
            const show = inputEl.type === 'password';
            inputEl.type = show ? 'text' : 'password';
            buttonEl.setAttribute('aria-pressed', show ? 'true' : 'false');
            buttonEl.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            var icon = buttonEl.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye', !show);
                icon.classList.toggle('fa-eye-slash', show);
            }
            inputEl.focus();
        });
    }

    bindEyeToggle(toggleLoginModalPassword, loginModalPassword);
    bindEyeToggle(toggleRegisterModalPassword, registerModalPassword);
    bindEyeToggle(toggleRegisterModalConfirmPassword, registerModalConfirmPassword);

    function setInlineMessage(el, type, text) {
        if (!el) return;
        if (!text) {
            el.textContent = '';
            el.style.display = 'none';
            el.classList.remove('error', 'success');
            return;
        }
        el.textContent = text;
        el.classList.remove('error', 'success');
        el.classList.add(type === 'success' ? 'success' : 'error');
        el.style.display = 'block';
    }

    function clearAllModalMessages() {
        setInlineMessage(loginModalMessage, '', '');
        setInlineMessage(registerModalMessage, '', '');
        setInlineMessage(verifyModalMessage, '', '');
    }

    function resolveFinalUrl(rawUrl) {
        try {
            return new URL(rawUrl, window.location.origin);
        } catch (e) {
            return null;
        }
    }

    function getRoleHomeUrl() {
        var base = window.EVENTIFY_BASE_URL || '/school_events';
        return base + '/index.php';
    }

    function safeNavigate(urlLike) {
        var target = (typeof urlLike === 'string') ? urlLike : '';
        if (!target || target.indexOf('[object') !== -1) {
            window.location.href = getRoleHomeUrl();
            return;
        }
        window.location.href = target;
    }

    function setFormSubmitting(formEl, submitting, label) {
        if (!formEl) return;
        var btn = formEl.querySelector('button[type="submit"]');
        if (!btn) return;
        if (submitting) {
            if (!btn.dataset.originalText) btn.dataset.originalText = btn.textContent || 'Submit';
            btn.disabled = true;
            btn.textContent = label || 'Please wait...';
        } else {
            btn.disabled = false;
            if (btn.dataset.originalText) btn.textContent = btn.dataset.originalText;
        }
    }

    function extractRedirectFromAuthHtml(htmlText) {
        if (typeof htmlText !== 'string' || htmlText === '') return '';
        var m = htmlText.match(/window\.top\.location\.href\s*=\s*([^;]+);/i);
        if (!m || !m[1]) return '';
        var rhs = m[1].trim();
        // Backend writes: window.top.location.href = "<url>";
        // Try JSON parsing first so escaped slashes are decoded properly.
        try {
            var parsed = JSON.parse(rhs);
            if (typeof parsed === 'string' && parsed) return parsed;
        } catch (e) {
            // ignore and try fallback below
        }
        // Fallback: strip quotes and unescape common slash escaping.
        rhs = rhs.replace(/^['"]|['"]$/g, '').replace(/\\\//g, '/');
        return rhs;
    }

    async function submitModalForm(formEl, onHandled) {
        if (!formEl) return;
        const body = new FormData(formEl);
        const response = await fetch(formEl.action, {
            method: 'POST',
            body: body,
            credentials: 'same-origin',
            redirect: 'follow'
        });
        const finalUrl = resolveFinalUrl(response.url);
        const responseText = await response.text();
        if (onHandled) onHandled(finalUrl, response, responseText);
    }

    // Hard reliability fix: do NOT AJAX-submit login.
    // Normal form submit ensures server session + role redirects always work.
    if (loginModalForm) {
        loginModalForm.addEventListener('submit', function () {
            clearAllModalMessages();
            setFormSubmitting(loginModalForm, true, 'Logging in...');
        });
    }

    // Hard reliability fix for registration too: do normal submit.
    // Backend already handles validation + redirects with clear error messages.
    if (registerModalForm) {
        registerModalForm.addEventListener('submit', function () {
            clearAllModalMessages();
            setFormSubmitting(registerModalForm, true, 'Registering...');
        });
    }

    if (verifyModalForm) {
        verifyModalForm.addEventListener('submit', function (e) {
            e.preventDefault();
            clearAllModalMessages();
            setFormSubmitting(verifyModalForm, true, 'Verifying...');
            submitModalForm(verifyModalForm, function (finalUrl) {
                if (!finalUrl) {
                    setFormSubmitting(verifyModalForm, false);
                    setInlineMessage(verifyModalMessage, 'error', 'Verification failed. Please try again.');
                    return;
                }
                const p = finalUrl.pathname || '';
                const q = finalUrl.searchParams;
                if (p.indexOf('/views/verify_account_otp.php') !== -1 && q.get('error')) {
                    setFormSubmitting(verifyModalForm, false);
                    setInlineMessage(verifyModalMessage, 'error', q.get('error'));
                    openVerifyModal();
                    return;
                }
                if (p.indexOf('/views/login.php') !== -1) {
                    setFormSubmitting(verifyModalForm, false);
                    setInlineMessage(loginModalMessage, q.get('error') ? 'error' : 'success', q.get('error') || q.get('success') || 'Done.');
                    openLoginModal();
                    return;
                }
                safeNavigate(finalUrl.href);
            }).catch(function () {
                setFormSubmitting(verifyModalForm, false);
                setInlineMessage(verifyModalMessage, 'error', 'Unable to connect. Please try again.');
            });
        });
    }

    // Re-open specific auth modal after backend redirect from form validation.
    if (window.AUTH_MODAL === 'register') {
        openRegisterModal();
        var serverErrEl = document.getElementById('registerModalMessageServer');
        if (window.AUTH_ERROR && registerModalMessage && !serverErrEl) {
            setInlineMessage(registerModalMessage, 'error', window.AUTH_ERROR);
        }
        if (window.AUTH_ERROR && /password/i.test(window.AUTH_ERROR)) {
            if (registerModalPassword) registerModalPassword.value = '';
            if (registerModalConfirmPassword) registerModalConfirmPassword.value = '';
        }
    } else if (window.AUTH_MODAL === 'verify') {
        openVerifyModal();
        if (window.AUTH_ERROR && verifyModalMessage) {
            setInlineMessage(verifyModalMessage, 'error', window.AUTH_ERROR);
        }
    } else if (window.AUTH_MODAL === 'login') {
        openLoginModal();
        var loginServerEl = document.getElementById('loginModalMessageServer');
        if (!loginServerEl && window.AUTH_ERROR && loginModalMessage) {
            setInlineMessage(loginModalMessage, 'error', window.AUTH_ERROR);
        } else if (!loginServerEl && window.AUTH_SUCCESS && loginModalMessage) {
            setInlineMessage(loginModalMessage, 'success', window.AUTH_SUCCESS);
        }
    }

    function promptLogin(loginUrl) {
        if (!loginUrl) return;
        if (window.innerWidth > 768) {
            openLoginModal();
        } else {
            window.location.href = loginUrl;
        }
    }

    // Login: on desktop open modal, on mobile go to full login page
    document.querySelectorAll('.login-trigger').forEach(function (el) {
        el.addEventListener('click', function (e) {
            var loginUrl = el.getAttribute('data-login-url');
            if (window.innerWidth > 768) {
                // Desktop: show modal, do not leave landing page
                e.preventDefault();
                openLoginModal();
            } else if (loginUrl) {
                // Mobile: navigate to full login/register page
                e.preventDefault();
                window.location.href = loginUrl;
            }
        });
    });


    // Public calendar: show events but require login on interaction
    try {
        var calEl = document.getElementById('publicCalendar');
        var monthEl = document.getElementById('publicCalendarMonth');
        if (calEl && window.FullCalendar) {
            var events = Array.isArray(window.PUBLIC_CALENDAR_EVENTS) ? window.PUBLIC_CALENDAR_EVENTS : [];
            var loginUrl = window.PUBLIC_LOGIN_URL || '';
            var syncMonth = function (cal) {
                if (!monthEl || !cal) return;
                monthEl.textContent = (cal.view && cal.view.title) ? cal.view.title : monthEl.textContent;
            };

            var calendar = new FullCalendar.Calendar(calEl, {
                initialView: 'dayGridMonth',
                height: 'auto',
                fixedWeekCount: false,
                showNonCurrentDates: true,
                navLinks: false,
                selectable: false,
                nowIndicator: true,
                events: events,
                dateClick: function () {
                    promptLogin(loginUrl);
                },
                eventClick: function (info) {
                    if (info && info.jsEvent) info.jsEvent.preventDefault();
                    promptLogin(loginUrl);
                },
                datesSet: function () {
                    syncMonth(calendar);
                }
            });
            calendar.render();
            syncMonth(calendar);
        }
    } catch (err) {
        // ignore calendar init failures on landing
    }
});

// ===============================
// MODAL LOGIC
// ===============================
function openLoginModal() {
    var modal = document.getElementById('loginModal');
    var registerModal = document.getElementById('registerModal');
    var verifyModal = document.getElementById('verifyModal');
    if (registerModal) registerModal.style.display = 'none';
    if (verifyModal) verifyModal.style.display = 'none';
    if (modal) modal.style.display = 'flex';
}

function closeLoginModal() {
    document.getElementById('loginModal').style.display = 'none';
}

function openRegisterModal() {
    var modal = document.getElementById('loginModal');
    var registerModal = document.getElementById('registerModal');
    var verifyModal = document.getElementById('verifyModal');
    if (modal) modal.style.display = 'none';
    if (verifyModal) verifyModal.style.display = 'none';
    if (registerModal) registerModal.style.display = 'flex';
}

function closeRegisterModal() {
    var registerModal = document.getElementById('registerModal');
    if (registerModal) registerModal.style.display = 'none';
}

function openVerifyModal() {
    var modal = document.getElementById('loginModal');
    var registerModal = document.getElementById('registerModal');
    var verifyModal = document.getElementById('verifyModal');
    if (modal) modal.style.display = 'none';
    if (registerModal) registerModal.style.display = 'none';
    if (verifyModal) verifyModal.style.display = 'flex';
}

function closeVerifyModal() {
    var verifyModal = document.getElementById('verifyModal');
    if (verifyModal) verifyModal.style.display = 'none';
}

window.onclick = function(e) {
    const modal = document.getElementById('loginModal');
    const registerModal = document.getElementById('registerModal');
    const verifyModal = document.getElementById('verifyModal');
    if (e.target === modal) closeLoginModal();
    if (e.target === registerModal) closeRegisterModal();
    if (e.target === verifyModal) closeVerifyModal();
}

// ===============================
// SPLINE EYES FOLLOW MOUSE
// ===============================
const spline = document.querySelector('spline-viewer');

if (spline) {
    spline.addEventListener('load', async (e) => {
        try {
            // Wait for the scene to fully initialize
            await new Promise(resolve => setTimeout(resolve, 1000));
            
            const app = e.target;
            
            // Try multiple ways to access the Spline scene
            let scene = null;
            let eyes = null;
            
            // Method 1: Try app.spline
            if (app.spline) {
                scene = app.spline;
            }
            // Method 2: Try app.application
            else if (app.application && app.application.scene) {
                scene = app.application.scene;
            }
            // Method 3: Try accessing through the event detail
            else if (e.detail && e.detail.scene) {
                scene = e.detail.scene;
            }
            
            if (!scene) {
                console.warn("Spline scene not found. Trying alternative access...");
                await new Promise(resolve => setTimeout(resolve, 500));
                if (spline.application) {
                    scene = spline.application.scene;
                }
            }
            
            if (!scene) {
                console.warn("Could not access Spline scene. Eye tracking disabled.");
                return;
            }

            // Try different possible names for the eyes object
            const possibleNames = ['Eyes', 'eyes', 'Eye', 'eye', 'EyesGroup', 'eyesGroup', 'Eyes_Group', 'EyesGroup1'];
            
            for (const name of possibleNames) {
                try {
                    if (scene.findObjectByName) {
                        eyes = scene.findObjectByName(name);
                    } else if (scene.getObjectByName) {
                        eyes = scene.getObjectByName(name);
                    }
                    if (eyes) {
                        console.log(`✓ Found eyes object: ${name}`);
                        break;
                    }
                } catch (err) {
                    // Continue searching
                }
            }

            // If not found by name, try recursive search
            if (!eyes) {
                const searchForEyes = (obj, depth = 0) => {
                    if (depth > 10 || !obj) return null;
                    
                    try {
                        if (obj.name && (obj.name.toLowerCase().includes('eye') || obj.name.toLowerCase().includes('pupil'))) {
                            return obj;
                        }
                        
                        if (obj.children && obj.children.length > 0) {
                            for (const child of obj.children) {
                                const found = searchForEyes(child, depth + 1);
                                if (found) return found;
                            }
                        }
                    } catch (err) {
                        // Skip objects that can't be accessed
                    }
                    return null;
                };
                
                eyes = searchForEyes(scene);
                if (eyes) {
                    console.log(`✓ Found eyes object recursively: ${eyes.name}`);
                }
            }

            if (!eyes) {
                console.warn("⚠ Could not find eyes object. Please check the object name in Spline.");
                return;
            }

            // Mouse tracking with smooth movement
            let targetRotationX = 0;
            let targetRotationY = 0;
            let currentRotationX = 0;
            let currentRotationY = 0;

            const handleMouseMove = (e) => {
                const x = (e.clientX / window.innerWidth - 0.5) * 2;
                const y = (0.5 - e.clientY / window.innerHeight) * 2;

                targetRotationY = x * 0.4;
                targetRotationX = y * 0.4;
            };

            document.addEventListener('mousemove', handleMouseMove);

            // Smooth animation loop
            function animateEyes() {
                currentRotationX += (targetRotationX - currentRotationX) * 0.15;
                currentRotationY += (targetRotationY - currentRotationY) * 0.15;

                try {
                    if (eyes) {
                        if (eyes.rotation !== undefined) {
                            eyes.rotation.y = currentRotationY;
                            eyes.rotation.x = currentRotationX;
                        } else if (eyes.rotationY !== undefined) {
                            eyes.rotationY = currentRotationY;
                            eyes.rotationX = currentRotationX;
                        }
                    }
                } catch (err) {
                    console.warn("Error updating eye rotation:", err);
                }

                requestAnimationFrame(animateEyes);
            }

            animateEyes();
            console.log("✓ Eye tracking initialized successfully");

        } catch (error) {
            console.error("✗ Error initializing eye tracking:", error);
        }
    });
}
