<?php
if (!isset($user_name)) $user_name = 'Multimedia';
if (!isset($events)) $events = [];
if (!isset($msg)) $msg = '';
$user = $user ?? ['name' => $user_name, 'user_id' => 'N/A', 'department' => null, 'profile_picture' => null];
$department = $user['department'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multimedia Dashboard - EVENTIFY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard_multimedia.css">
</head>
<body>

<nav class="top-navbar">
    <div class="navbar-left">
        <div class="brand-logo">
            <i class="fas fa-camera"></i>
            <span>EVENTIFY</span>
        </div>
    </div>
    <div class="navbar-right">
        <div class="dropdown">
            <button class="profile-avatar profile-toggle dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="<?= htmlspecialchars($user_name) ?>">
                <?php if (!empty($user['profile_picture'])): ?>
                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($user['profile_picture']) ?>" alt="<?= htmlspecialchars($user_name) ?>" class="profile-avatar-img">
                <?php else: ?>
                    <?= strtoupper(substr($user_name, 0, 1)) ?>
                <?php endif; ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end profile-menu">
                <li class="px-3 py-2">
                    <div class="small text-muted">Signed in as</div>
                    <div class="fw-semibold"><?= htmlspecialchars($user_name) ?></div>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="#" onclick="openMmProfileModal(); return false;">
                        <i class="fas fa-user me-2"></i> Profile
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="<?= BASE_URL ?>/upcoming_events.php">
                        <i class="fas fa-calendar-check me-2"></i> Upcoming events
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-section">
            <div class="mm-user-card">
                <div class="mm-user-avatar">
                    <?php if (!empty($user['profile_picture'])): ?>
                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($user['profile_picture']) ?>" alt="<?= htmlspecialchars($user_name) ?>">
                    <?php else: ?>
                        <span><?= strtoupper(substr($user_name, 0, 1)) ?></span>
                    <?php endif; ?>
                </div>
                <h3 class="mm-user-name"><?= htmlspecialchars($user_name) ?></h3>
                <p class="mm-user-id">ID: <?= htmlspecialchars($user['user_id'] ?? 'N/A') ?></p>
                <?php if ($department): ?>
                    <span class="mm-user-dept"><?= htmlspecialchars($department) ?> Multimedia</span>
                <?php else: ?>
                    <span class="mm-user-dept muted">Department not set</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="sidebar-section">
            <h3 class="sidebar-title">QUICK ACTIONS</h3>
            <button class="action-btn" onclick="openMmProfileModal()">
                <i class="fas fa-user-edit"></i>
                <span>Edit profile</span>
            </button>
            <a href="<?= BASE_URL ?>/upcoming_events.php" class="action-btn">
                <i class="fas fa-calendar-check"></i>
                <span>Upcoming events</span>
            </a>
            <a href="#" class="action-btn" data-bs-toggle="modal" data-bs-target="#logoutModal">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
        <div class="sidebar-section">
            <h3 class="sidebar-title">YOUR ROLE</h3>
            <p class="role-desc">
                You are part of the multimedia team.
                Each department may have its own multimedia club – choose any event below and add photos for it.
            </p>
        </div>
    </aside>

    <main class="main-content">
        <div class="content-header">
            <h1>Event photos</h1>
            <p class="text-muted">Choose an event and upload photos.</p>
        </div>

        <?php if ($msg): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="events-list">
            <?php if (empty($events)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <p>No events yet. Events will appear here when organizers create them.</p>
                </div>
            <?php else: ?>
                <?php foreach ($events as $ev): ?>
                    <div class="event-card">
                        <div class="event-info">
                            <h3 class="event-title"><?= htmlspecialchars($ev['title']) ?></h3>
                            <p class="event-meta">
                                <i class="fas fa-calendar"></i> <?= date('M j, Y', strtotime($ev['date'])) ?>
                                &nbsp;·&nbsp;
                                <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($ev['location']) ?>
                            </p>
                            <?php if (!empty($ev['photo_count'])): ?>
                                <span class="photo-badge">
                                    <i class="fas fa-images"></i> <?= (int)$ev['photo_count'] ?> photo(s)
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="event-actions">
                            <button type="button" class="btn btn-upload" data-bs-toggle="modal" data-bs-target="#uploadModal"
                                    data-event-id="<?= (int)$ev['id'] ?>"
                                    data-event-title="<?= htmlspecialchars($ev['title']) ?>">
                                <i class="fas fa-cloud-upload-alt"></i> Upload photos
                            </button>
                            <?php if (!empty($ev['photo_count'])): ?>
                                <button type="button"
                                        class="btn btn-outline-secondary btn-gallery"
                                        data-bs-toggle="modal"
                                        data-bs-target="#galleryModal"
                                        data-event-id="<?= (int)$ev['id'] ?>"
                                        data-event-title="<?= htmlspecialchars($ev['title']) ?>">
                                    <i class="fas fa-folder-open"></i> View photos
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Profile Modal (Multimedia) -->
<div id="mmProfileModal" class="profile-modal">
    <div class="profile-modal-content">
        <span class="profile-close" onclick="closeMmProfileModal()">&times;</span>
        <h2>My Information</h2>
        <form id="mmProfileForm" action="<?= BASE_URL ?>/backend/auth/update_multimedia_profile.php" method="POST" enctype="multipart/form-data" onsubmit="event.preventDefault(); confirmMmProfileChanges(this);">
            <div class="form-group">
                <label for="mmProfilePicture">Profile Picture</label>
                <div class="profile-picture-preview-container">
                    <?php if (!empty($user['profile_picture'])): ?>
                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Current profile picture" id="mmProfilePicturePreview" class="profile-picture-preview profile-picture-clickable" onclick="openMmProfilePicFullscreen(this.src)" title="Click to view full screen">
                    <?php else: ?>
                        <div class="profile-picture-placeholder" id="mmProfilePicturePreview">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <input
                    type="file"
                    id="mmProfilePicture"
                    name="profile_picture"
                    accept="image/jpeg,image/png,image/gif,image/webp"
                    class="form-control-file"
                    onchange="previewMmProfilePicture(this)"
                >
                <small class="text-muted">JPG, PNG, GIF, or WEBP (max 5MB)</small>
            </div>

            <div class="form-group">
                <label for="mmFullName">Full Name</label>
                <input
                    type="text"
                    id="mmFullName"
                    name="name"
                    value="<?= htmlspecialchars($user['name'] ?? $user_name) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label>Multimedia Club</label>
                <input
                    type="text"
                    value="<?= $department ? htmlspecialchars($department . ' Multimedia') : 'Not assigned' ?>"
                    readonly
                >
                <small class="text-muted d-block mt-1">Each department has its own multimedia club. This is based on your department.</small>
            </div>

            <button type="submit" class="btn btn-primary w-100">Save Info</button>
        </form>
    </div>
</div>

<!-- Confirm Multimedia Profile Save Modal -->
<div class="modal fade" id="confirmMmProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Save profile changes?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="confirmMmProfileMessage" class="mb-0"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmMmProfileBtn">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Profile picture fullscreen viewer (multimedia) -->
<div class="profile-pic-fullscreen" id="mmProfilePicFullscreen" style="display:none;">
    <div class="profile-pic-fullscreen-overlay" onclick="closeMmProfilePicFullscreen()"></div>
    <button class="profile-pic-fullscreen-close" onclick="closeMmProfilePicFullscreen()" aria-label="Close"><i class="fas fa-times"></i></button>
    <div class="profile-pic-fullscreen-content">
        <img id="mmProfilePicFullscreenImg" src="" alt="Profile picture">
    </div>
</div>

<!-- Upload modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload photos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>/backend/auth/upload_event_photo.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="event_id" id="uploadEventId" value="">
                <div class="modal-body">
                    <p class="mb-3 text-muted" id="uploadEventTitle"></p>
                    <label class="form-label">Select images (JPG, PNG, GIF — max 5MB each)</label>
                    <input type="file" name="photos[]" id="photosInput" class="form-control" accept="image/jpeg,image/png,image/gif" multiple required>
                    <small class="text-muted">You can select multiple files.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i> Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Logout modal -->
<div class="modal fade" id="logoutModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <p class="mb-0">Are you sure you want to log out?</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="<?= BASE_URL ?>/backend/auth/logout.php" class="btn btn-danger">Log out</a>
            </div>
        </div>
    </div>
</div>

<!-- Gallery modal -->
<div class="modal fade" id="galleryModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="galleryTitle">Event photos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="galleryGrid" class="gallery-grid"><!-- Thumbnails injected by JS --></div>
                <p id="galleryEmpty" class="text-muted small" style="display:none;">No photos uploaded yet for this event.</p>
            </div>
        </div>
    </div>
</div>

<!-- Delete Photo Confirmation Modal -->
<div class="modal fade" id="deletePhotoModal" tabindex="-1" aria-labelledby="deletePhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deletePhotoModalLabel"><i class="fas fa-trash-alt me-2"></i>Delete Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to delete this photo? This cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="deletePhotoConfirmBtn"><i class="fas fa-trash-alt me-1"></i> Yes, Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Photo Viewer Lightbox (Facebook-style) -->
<div class="photo-viewer" id="photoViewer" style="display:none;">
    <div class="photo-viewer-overlay" onclick="closePhotoViewer()"></div>
    <button class="photo-viewer-close" onclick="closePhotoViewer()" aria-label="Close">
        <i class="fas fa-times"></i>
    </button>
    <button class="photo-viewer-nav photo-viewer-prev" onclick="navigatePhoto(-1)" aria-label="Previous">
        <i class="fas fa-chevron-left"></i>
    </button>
    <button class="photo-viewer-nav photo-viewer-next" onclick="navigatePhoto(1)" aria-label="Next">
        <i class="fas fa-chevron-right"></i>
    </button>
    <div class="photo-viewer-content">
        <img id="viewerImage" src="" alt="Event photo" class="photo-viewer-img">
        <div class="photo-viewer-info">
            <span id="viewerPhotoCount" class="photo-count"></span>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Profile modal (multimedia)
function openMmProfileModal() {
    var el = document.getElementById('mmProfileModal');
    if (el) el.classList.add('show');
}
function closeMmProfileModal() {
    var el = document.getElementById('mmProfileModal');
    if (el) el.classList.remove('show');
}
function previewMmProfilePicture(input) {
    var preview = document.getElementById('mmProfilePicturePreview');
    if (!preview) return;
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            if (preview.tagName === 'IMG') {
                preview.src = e.target.result;
            } else {
                var img = document.createElement('img');
                img.id = 'mmProfilePicturePreview';
                img.className = 'profile-picture-preview profile-picture-clickable';
                img.alt = 'Preview';
                img.src = e.target.result;
                img.onclick = function() { openMmProfilePicFullscreen(img.src); };
                preview.parentNode.replaceChild(img, preview);
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}
var pendingMmProfileForm = null;
function confirmMmProfileChanges(form) {
    var nameEl = form.querySelector('input[name="name"]');
    var name = nameEl ? nameEl.value : '';
    var fileInput = form.querySelector('input[name="profile_picture"]');
    var hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
    var msg = 'Update your display name' + (name ? ' to "' + name + '"' : '') + '.';
    if (hasFile) msg += ' A new profile picture will be uploaded.';
    var messageEl = document.getElementById('confirmMmProfileMessage');
    if (messageEl) messageEl.textContent = msg;
    pendingMmProfileForm = form;
    var modalEl = document.getElementById('confirmMmProfileModal');
    if (!modalEl) { form.submit(); return; }
    var modal = new bootstrap.Modal(modalEl);
    modalEl.addEventListener('shown.bs.modal', function raiseConfirmZIndex() {
        modalEl.removeEventListener('shown.bs.modal', raiseConfirmZIndex);
        modalEl.style.zIndex = '1200';
        var backdrops = document.querySelectorAll('.modal-backdrop');
        for (var i = 0; i < backdrops.length; i++) { backdrops[i].style.zIndex = '1199'; }
    }, { once: true });
    modal.show();
    var btn = document.getElementById('confirmMmProfileBtn');
    if (btn) {
        btn.onclick = function() {
            modal.hide();
            if (pendingMmProfileForm) {
                pendingMmProfileForm.submit();
                pendingMmProfileForm = null;
            }
        };
    }
}
function openMmProfilePicFullscreen(src) {
    if (!src) return;
    var el = document.getElementById('mmProfilePicFullscreen');
    var img = document.getElementById('mmProfilePicFullscreenImg');
    if (el && img) {
        img.src = src;
        el.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}
function closeMmProfilePicFullscreen() {
    var el = document.getElementById('mmProfilePicFullscreen');
    if (el) {
        el.style.display = 'none';
        document.body.style.overflow = '';
    }
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeMmProfilePicFullscreen();
});

// Make photos data available to JS
window.photosByEvent = <?= json_encode($photosByEvent ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

document.addEventListener('DOMContentLoaded', function() {
    var uploadModal = document.getElementById('uploadModal');
    if (uploadModal) {
        uploadModal.addEventListener('show.bs.modal', function(e) {
            var btn = e.relatedTarget;
            if (btn && btn.dataset.eventId) {
                document.getElementById('uploadEventId').value = btn.dataset.eventId;
                document.getElementById('uploadEventTitle').textContent = 'Event: ' + (btn.dataset.eventTitle || '');
                document.getElementById('photosInput').value = '';
            }
        });
    }

    var galleryModal = document.getElementById('galleryModal');
    if (galleryModal) {
        galleryModal.addEventListener('show.bs.modal', function(e) {
            var btn = e.relatedTarget;
            var titleEl = document.getElementById('galleryTitle');
            var gridEl = document.getElementById('galleryGrid');
            var emptyEl = document.getElementById('galleryEmpty');
            if (!gridEl || !emptyEl) return;

            gridEl.innerHTML = '';
            emptyEl.style.display = 'none';

            if (btn && btn.dataset.eventId) {
                var eventId = btn.dataset.eventId;
                var eventTitle = btn.dataset.eventTitle || '';
                if (titleEl) {
                    titleEl.textContent = 'Photos for: ' + eventTitle;
                }

                var photos = (window.photosByEvent && window.photosByEvent[eventId]) || [];
                if (!photos.length) {
                    emptyEl.style.display = 'block';
                    return;
                }

                photos.forEach(function(photo, index) {
                    var wrapper = document.createElement('div');
                    wrapper.className = 'gallery-item';

                    var img = document.createElement('img');
                    img.src = '<?= BASE_URL ?>/' + photo.file_path;
                    img.alt = 'Event photo';
                    img.className = 'gallery-photo';
                    img.style.cursor = 'pointer';
                    img.onclick = function() {
                        openPhotoViewer(eventId, index);
                    };

                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '<?= BASE_URL ?>/backend/auth/delete_event_photo.php';
                    form.className = 'delete-photo-form';
                    form.onclick = function(ev) {
                        ev.stopPropagation();
                    };

                    var inputId = document.createElement('input');
                    inputId.type = 'hidden';
                    inputId.name = 'photo_id';
                    inputId.value = photo.id;

                    var inputEvent = document.createElement('input');
                    inputEvent.type = 'hidden';
                    inputEvent.name = 'event_id';
                    inputEvent.value = eventId;

                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'btn-delete-photo';
                    btn.innerHTML = '<i class="fas fa-trash-alt"></i>';
                    btn.onclick = function (ev) {
                        ev.stopPropagation();
                        window.pendingDeletePhotoForm = form;
                        var modal = new bootstrap.Modal(document.getElementById('deletePhotoModal'));
                        modal.show();
                    };

                    form.appendChild(inputId);
                    form.appendChild(inputEvent);
                    form.appendChild(btn);

                    wrapper.appendChild(img);
                    wrapper.appendChild(form);
                    gridEl.appendChild(wrapper);
                });
            }
        });
    }

    var deleteConfirmBtn = document.getElementById('deletePhotoConfirmBtn');
    if (deleteConfirmBtn) {
        deleteConfirmBtn.addEventListener('click', function() {
            if (window.pendingDeletePhotoForm) {
                var modal = bootstrap.Modal.getInstance(document.getElementById('deletePhotoModal'));
                if (modal) modal.hide();
                window.pendingDeletePhotoForm.submit();
                window.pendingDeletePhotoForm = null;
            }
        });
    }

    // Photo viewer (Facebook-style)
    var currentEventId = null;
    var currentPhotoIndex = 0;
    var currentPhotos = [];

    window.openPhotoViewer = function(eventId, photoIndex) {
        currentEventId = eventId;
        currentPhotoIndex = parseInt(photoIndex) || 0;
        currentPhotos = (window.photosByEvent && window.photosByEvent[eventId]) || [];
        
        if (!currentPhotos.length) return;
        
        var viewer = document.getElementById('photoViewer');
        if (viewer) {
            viewer.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            updateViewerImage();
        }
    };

    window.closePhotoViewer = function() {
        var viewer = document.getElementById('photoViewer');
        if (viewer) {
            viewer.style.display = 'none';
            document.body.style.overflow = '';
        }
    };

    window.navigatePhoto = function(direction) {
        if (!currentPhotos.length) return;
        currentPhotoIndex += direction;
        if (currentPhotoIndex < 0) currentPhotoIndex = currentPhotos.length - 1;
        if (currentPhotoIndex >= currentPhotos.length) currentPhotoIndex = 0;
        updateViewerImage();
    };

    function updateViewerImage() {
        if (!currentPhotos.length || currentPhotoIndex < 0 || currentPhotoIndex >= currentPhotos.length) return;
        var photo = currentPhotos[currentPhotoIndex];
        var img = document.getElementById('viewerImage');
        var countEl = document.getElementById('viewerPhotoCount');
        
        if (img) {
            img.src = '<?= BASE_URL ?>/' + photo.file_path;
        }
        if (countEl) {
            countEl.textContent = (currentPhotoIndex + 1) + ' / ' + currentPhotos.length;
        }
    }

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        var viewer = document.getElementById('photoViewer');
        if (viewer && viewer.style.display === 'flex') {
            if (e.key === 'Escape') {
                closePhotoViewer();
            } else if (e.key === 'ArrowLeft') {
                navigatePhoto(-1);
            } else if (e.key === 'ArrowRight') {
                navigatePhoto(1);
            }
        }
    });
});
</script>
</body>
</html>
