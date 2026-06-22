// GlobeTrek Adventures Client-Side Scripting

document.addEventListener('DOMContentLoaded', () => {
    initLoadingButtons();
    initForms();
    initAdminForms();
});

// Toast System
function showToast(message, type = 'info') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    toast.innerHTML = `
        <span>${message}</span>
        <button class="toast-close">&times;</button>
    `;

    container.appendChild(toast);

    // Auto dismiss after 4 seconds
    const dismissTimeout = setTimeout(() => {
        dismissToast(toast);
    }, 4000);

    toast.querySelector('.toast-close').addEventListener('click', () => {
        clearTimeout(dismissTimeout);
        dismissToast(toast);
    });
}

function dismissToast(toast) {
    toast.classList.add('slide-out');
    toast.addEventListener('animationend', () => {
        toast.remove();
    });
}

// Button Active State (Loading Animation)
function initLoadingButtons() {
    document.querySelectorAll('.btn-loading-action').forEach(btn => {
        btn.addEventListener('click', (e) => {
            setButtonLoading(btn);
        });
    });
}

function setButtonLoading(btn, text = 'Loading...') {
    if (btn.dataset.originalText) return; // already loading
    btn.dataset.originalText = btn.innerHTML;
    btn.disabled = true;
    btn.style.width = btn.offsetWidth + 'px'; // maintain size
    btn.innerHTML = `<span style="display:inline-block; animation: pulse 1s infinite">${text}</span>`;
}

function resetButtonLoading(btn) {
    if (btn.dataset.originalText) {
        btn.innerHTML = btn.dataset.originalText;
        btn.removeAttribute('data-original-text');
        btn.disabled = false;
        btn.style.width = '';
    }
}

// Form Handlers
function initForms() {
    // Single-input Query form on package cards
    document.querySelectorAll('.package-query-form').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            const input = form.querySelector('.query-input');
            const packageId = form.dataset.packageId;
            const question = input.value.trim();

            if (!question) {
                showToast('Please type a question first.', 'warning');
                return;
            }

            setButtonLoading(btn, 'Sending...');

            try {
                const response = await fetch('api/submit-query.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ package_id: packageId, question_text: question })
                });
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message || 'Question submitted successfully!', 'success');
                    input.value = '';
                } else {
                    showToast(result.message || 'Failed to submit question.', 'error');
                }
            } catch (err) {
                showToast('Network error, please try again.', 'error');
            } finally {
                resetButtonLoading(btn);
            }
        });
    });
}

// Admin Panels Event Bindings
function initAdminForms() {
    const addStaffForm = document.getElementById('add-staff-form');
    if (addStaffForm) {
        addStaffForm.addEventListener('submit', handleAddStaff);
    }
    const addPackageForm = document.getElementById('add-package-form');
    if (addPackageForm) {
        addPackageForm.addEventListener('submit', handleAddPackage);
    }
}

// -------------------------------------------------------------
// Interactive AJAX API Calls
// -------------------------------------------------------------

// 1. Dynamic Like System
async function toggleLike(packageId, btn) {
    setButtonLoading(btn, '...');
    try {
        const response = await fetch('api/toggle-like.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ package_id: packageId })
        });
        const result = await response.json();

        if (result.success) {
            const countSpan = btn.querySelector('.like-count');
            if (countSpan) {
                countSpan.textContent = result.likes_count;
            }
            showToast(result.message || 'Liked!', 'success');
        } else {
            showToast(result.message || 'Failed to update like.', 'error');
        }
    } catch (err) {
        showToast('Network error, please try again.', 'error');
    } finally {
        resetButtonLoading(btn);
    }
}

// 2. Bookmark / Save System
async function toggleSave(packageId, btn) {
    const star = btn.querySelector('.star-icon');
    btn.style.opacity = '0.5';

    try {
        const response = await fetch('api/toggle-save.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ package_id: packageId })
        });
        const result = await response.json();

        if (result.success) {
            if (result.saved) {
                btn.classList.add('saved');
                btn.title = 'Saved to bookmarks';
                star.textContent = '★';
                showToast('Added to bookmarks!', 'success');
            } else {
                btn.classList.remove('saved');
                btn.title = 'Save for later';
                star.textContent = '☆';
                showToast('Removed from bookmarks.', 'info');
            }
        } else {
            showToast(result.message || 'Failed to save package.', 'error');
        }
    } catch (err) {
        showToast('Network error, please try again.', 'error');
    } finally {
        btn.style.opacity = '1';
    }
}

// 3. Secure Booking AJAX Call
async function bookPackage(packageId, btn) {
    setButtonLoading(btn, 'Booking...');
    try {
        const response = await fetch('api/book-package.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ package_id: packageId })
        });
        const result = await response.json();

        if (result.success) {
            showToast('Booking successful! View in your Dashboard.', 'success');
        } else {
            showToast(result.message || 'Failed to confirm booking.', 'error');
        }
    } catch (err) {
        showToast('Network error, please try again.', 'error');
    } finally {
        resetButtonLoading(btn);
    }
}

// 4. Staff Query Reply
async function replyQuery(queryId, btn) {
    const row = btn.closest('tr');
    const replyInput = row.querySelector('.reply-input');
    const replyText = replyInput.value.trim();

    if (!replyText) {
        showToast('Please type a reply.', 'warning');
        return;
    }

    setButtonLoading(btn, 'Replying...');

    try {
        const response = await fetch('api/submit-query.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ query_id: queryId, answer_text: replyText })
        });
        const result = await response.json();

        if (result.success) {
            showToast('Reply submitted successfully!', 'success');
            const badge = row.querySelector('.badge');
            if (badge) {
                badge.className = 'badge badge-answered';
                badge.textContent = 'Answered';
            }
            const replyContainer = row.querySelector('.answer-container');
            if (replyContainer) {
                replyContainer.innerHTML = `<em>Replied:</em> ${replyText}`;
            }
            replyInput.parentElement.innerHTML = `<span style="color:var(--success); font-weight:600">Replied</span>`;
        } else {
            showToast(result.message || 'Failed to reply.', 'error');
        }
    } catch (err) {
        showToast('Network error, please try again.', 'error');
    } finally {
        resetButtonLoading(btn);
    }
}

// 5. Staff Booking Status Toggler
async function updateBookingStatus(bookingId, selectElement) {
    const originalValue = selectElement.dataset.originalVal || selectElement.value;
    selectElement.disabled = true;

    try {
        const response = await fetch('api/update-booking-status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ booking_id: bookingId, status: selectElement.value })
        });
        const result = await response.json();

        if (result.success) {
            showToast('Booking status updated successfully!', 'success');
            selectElement.dataset.originalVal = selectElement.value;
            const badge = selectElement.closest('tr').querySelector('.badge');
            if (badge) {
                badge.className = `badge badge-${selectElement.value.toLowerCase()}`;
                badge.textContent = selectElement.value;
            }
        } else {
            showToast(result.message || 'Failed to update booking status.', 'error');
            selectElement.value = originalValue;
        }
    } catch (err) {
        showToast('Network error, please try again.', 'error');
        selectElement.value = originalValue;
    } finally {
        selectElement.disabled = false;
    }
}

// 6. Staff Package Editor
async function savePackageEdit(packageId, btn) {
    const card = btn.closest('.package-row-edit-card');
    const destination = card.querySelector('.edit-dest').value.trim();
    const price = card.querySelector('.edit-price').value.trim();
    const description = card.querySelector('.edit-desc').value.trim();

    if (!destination || !price || !description) {
        showToast('Please fill out all fields.', 'warning');
        return;
    }

    setButtonLoading(btn, 'Saving...');

    try {
        const response = await fetch('api/update-package.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                package_id: packageId,
                destination: destination,
                price: parseFloat(price),
                description: description
            })
        });
        const result = await response.json();

        if (result.success) {
            showToast('Package updated successfully!', 'success');
        } else {
            showToast(result.message || 'Failed to update package details.', 'error');
        }
    } catch (err) {
        showToast('Network error, please try again.', 'error');
    } finally {
        resetButtonLoading(btn);
    }
}

// -------------------------------------------------------------
// Admin Management Actions
// -------------------------------------------------------------

// Add Staff Account (Admin only)
async function handleAddStaff(e) {
    e.preventDefault();
    const form = e.target;
    const btn = document.getElementById('btn-add-staff');
    const usernameInput = document.getElementById('staff-username');
    const passwordInput = document.getElementById('staff-password');
    const username = usernameInput.value.trim();
    const password = passwordInput.value;

    setButtonLoading(btn, 'Creating...');

    try {
        const response = await fetch('api/add-staff.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        });
        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            usernameInput.value = '';
            passwordInput.value = '';
            setTimeout(() => { location.reload(); }, 1200);
        } else {
            showToast(result.message, 'error');
        }
    } catch (err) {
        showToast('Network error. Please try again.', 'error');
    } finally {
        resetButtonLoading(btn);
    }
}

// Delete Staff Account (Admin only)
async function deleteStaff(staffId, btn) {
    if (!confirm('Are you sure you want to delete this staff account?')) return;
    setButtonLoading(btn, '...');
    try {
        const response = await fetch('api/delete-staff.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ staff_id: staffId })
        });
        const result = await response.json();
        if (result.success) {
            showToast(result.message, 'success');
            const row = document.getElementById(`staff-row-${staffId}`);
            if (row) row.remove();
        } else {
            showToast(result.message, 'error');
        }
    } catch (err) {
        showToast('Network error.', 'error');
    } finally {
        resetButtonLoading(btn);
    }
}

// Add Tour Package (Admin only)
async function handleAddPackage(e) {
    e.preventDefault();
    const form = e.target;
    const btn = document.getElementById('btn-add-pkg');
    
    const destination = document.getElementById('pkg-destination').value.trim();
    const price = document.getElementById('pkg-price').value;
    const imageUrl = document.getElementById('pkg-image').value.trim();
    const description = document.getElementById('pkg-description').value.trim();

    setButtonLoading(btn, 'Creating...');

    try {
        const response = await fetch('api/add-package.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                destination,
                price: parseFloat(price),
                description,
                image_url: imageUrl
            })
        });
        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            form.reset();
            setTimeout(() => { location.reload(); }, 1200);
        } else {
            showToast(result.message, 'error');
        }
    } catch (err) {
        showToast('Network error. Please try again.', 'error');
    } finally {
        resetButtonLoading(btn);
    }
}

// Delete Tour Package (Admin only)
async function deletePackage(packageId, btn) {
    if (!confirm('Are you sure you want to remove this package from the catalogue? This will delete all queries and bookings associated with it!')) return;
    setButtonLoading(btn, '...');
    try {
        const response = await fetch('api/delete-package.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ package_id: packageId })
        });
        const result = await response.json();
        if (result.success) {
            showToast(result.message, 'success');
            const row = document.getElementById(`pkg-row-${packageId}`);
            if (row) row.remove();
        } else {
            showToast(result.message, 'error');
        }
    } catch (err) {
        showToast('Network error.', 'error');
    } finally {
        resetButtonLoading(btn);
    }
}

// Dynamic Pulse animation for loading text
const style = document.createElement('style');
style.textContent = `
@keyframes pulse {
    0% { opacity: 0.6; }
    50% { opacity: 1; }
    100% { opacity: 0.6; }
}
`;
document.head.appendChild(style);
