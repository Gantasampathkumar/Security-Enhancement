// js/script.js

function applyTheme(theme) {
    const body = document.body;
    const themeToggle = document.getElementById('themeToggle');

    if (theme === 'dark') {
        body.classList.add('dark-mode');
        if (themeToggle) {
            themeToggle.innerHTML = '<i class="bi bi-sun-fill"></i>';
            themeToggle.setAttribute('data-bs-original-title', 'Switch to light mode');
        }
    } else {
        body.classList.remove('dark-mode');
        if (themeToggle) {
            themeToggle.innerHTML = '<i class="bi bi-moon-stars"></i>';
            themeToggle.setAttribute('data-bs-original-title', 'Switch to dark mode');
        }
    }

    localStorage.setItem('preferredTheme', theme);
}

function passwordScore(value) {
    let score = 0;
    if (value.length >= 8) score++;
    if (/[A-Z]/.test(value)) score++;
    if (/[a-z]/.test(value)) score++;
    if (/\d/.test(value)) score++;
    if (/[^A-Za-z0-9]/.test(value)) score++;
    return score;
}

function setValidationState(field, message) {
    const feedback = field.parentElement.querySelector('.invalid-feedback');
    if (message) {
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');
        if (feedback) feedback.textContent = message;
        return false;
    }
    field.classList.remove('is-invalid');
    if (field.value.trim() !== '') field.classList.add('is-valid');
    if (feedback) feedback.textContent = '';
    return true;
}

function validateField(field) {
    const value = field.value.trim();
    const rule = field.dataset.validate || field.name;

    if (field.required && value === '') return setValidationState(field, 'This field is required.');
    if (rule === 'title' && (value.length < 3 || value.length > 255)) return setValidationState(field, 'Title must be between 3 and 255 characters.');
    if (rule === 'content' && value.length < 10) return setValidationState(field, 'Content must be at least 10 characters.');
    if (rule === 'author' && !/^[a-zA-Z\s]+$/.test(value)) return setValidationState(field, 'Author can contain letters and spaces only.');
    if (rule === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return setValidationState(field, 'Enter a valid email address.');
    if (rule === 'search' && value.length > 100) return setValidationState(field, 'Search must be 100 characters or fewer.');

    if (rule === 'password' && value !== '') {
        const score = passwordScore(value);
        const bar = field.parentElement.querySelector('.password-strength-bar');
        const text = field.parentElement.querySelector('.password-strength-text');
        if (bar) {
            bar.style.width = `${score * 20}%`;
            bar.className = 'password-strength-bar';
            bar.classList.add(score >= 5 ? 'strong' : score >= 3 ? 'medium' : 'weak');
        }
        if (text) text.textContent = score >= 5 ? 'Strong password.' : score >= 3 ? 'Medium password. Add more variety.' : 'Weak password.';
        if (field.required && score < 5) return setValidationState(field, 'Password needs uppercase, lowercase, number, special character, and 8+ characters.');
        if (!field.required && value !== '' && score < 5) return setValidationState(field, 'Optional password must still be strong if provided.');
    }

    if (rule === 'confirm_password') {
        const password = document.getElementById('password');
        if (password && value !== password.value) return setValidationState(field, 'Passwords do not match.');
    }

    return setValidationState(field, '');
}

document.addEventListener('DOMContentLoaded', function () {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });

    const themeToggle = document.getElementById('themeToggle');
    const savedTheme = localStorage.getItem('preferredTheme') || 'light';
    applyTheme(savedTheme);

    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            const currentTheme = document.body.classList.contains('dark-mode') ? 'dark' : 'light';
            applyTheme(currentTheme === 'dark' ? 'light' : 'dark');
        });
    }

    document.querySelectorAll('.secure-form input, .secure-form textarea').forEach((field) => {
        field.addEventListener('input', () => validateField(field));
        field.addEventListener('blur', () => validateField(field));
    });

    document.querySelectorAll('.secure-form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            let valid = true;
            form.querySelectorAll('input, textarea').forEach((field) => {
                if (!validateField(field)) valid = false;
            });
            if (!valid) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    });

    document.querySelectorAll('.alert-dismissible').forEach((alert) => {
        setTimeout(() => bootstrap.Alert.getOrCreateInstance(alert).close(), 6000);
    });

    const deleteModal = document.getElementById('deleteModal');
    const deleteModalTitle = document.getElementById('deleteModalTitle');
    const deletePostId = document.getElementById('deletePostId');
    const deleteButtons = document.querySelectorAll('.delete-button');

    if (deleteModal && deleteButtons.length > 0) {
        const bootstrapModal = new bootstrap.Modal(deleteModal);
        deleteButtons.forEach((button) => {
            button.addEventListener('click', function () {
                deletePostId.value = this.getAttribute('data-id');
                deleteModalTitle.textContent = this.getAttribute('data-title');
                bootstrapModal.show();
            });
        });
    }

    const loadingOverlay = document.getElementById('loadingOverlay');
    const showLoading = () => loadingOverlay && loadingOverlay.classList.remove('d-none');
    document.querySelectorAll('.needs-loading').forEach((trigger) => trigger.addEventListener('click', showLoading));

    const searchForm = document.querySelector('.search-form');
    const clearSearchButton = document.querySelector('.clear-search');
    if (searchForm) searchForm.addEventListener('submit', showLoading);
    if (clearSearchButton && searchForm) {
        clearSearchButton.addEventListener('click', function () {
            const searchInput = document.querySelector('.search-input');
            if (searchInput) searchInput.value = '';
            searchForm.submit();
        });
    }

    if (window.chartStats) {
        const authorChartCanvas = document.getElementById('authorChart');
        const recentChartCanvas = document.getElementById('recentChart');
        if (authorChartCanvas && window.chartStats.authorLabels.length > 0) {
            new Chart(authorChartCanvas, { type: 'doughnut', data: { labels: window.chartStats.authorLabels, datasets: [{ label: 'Posts by Author', data: window.chartStats.authorCounts, backgroundColor: ['#0d6efd', '#6610f2', '#0dcaf0', '#198754', '#fd7e14'], borderWidth: 1 }] }, options: { responsive: true, plugins: { legend: { position: 'bottom' } } } });
        }
        if (recentChartCanvas && window.chartStats.dateLabels.length > 0) {
            new Chart(recentChartCanvas, { type: 'bar', data: { labels: window.chartStats.dateLabels, datasets: [{ label: 'Posts Last 7 Days', data: window.chartStats.dateCounts, backgroundColor: '#0d6efd' }] }, options: { responsive: true, scales: { y: { precision: 0 } } } });
        }
    }
});
