/**
 * Hostel Complaint Management System - Client Side JavaScript
 * Handles form validation, interactive quick-fills, live table search, and notifications
 */

document.addEventListener("DOMContentLoaded", function () {
    // -------------------------------------------------------------
    // 1. Auto-dismiss Flash Alerts
    // -------------------------------------------------------------
    const alerts = document.querySelectorAll(".alert");
    alerts.forEach(function (alert) {
        // Allow manual close
        const closeBtn = alert.querySelector(".alert-close");
        if (closeBtn) {
            closeBtn.addEventListener("click", function () {
                alert.style.transition = "opacity 0.3s ease";
                alert.style.opacity = "0";
                setTimeout(() => alert.remove(), 300);
            });
        }

        // Auto close after 5 seconds if not error
        if (!alert.classList.contains("alert-danger")) {
            setTimeout(function () {
                alert.style.transition = "opacity 0.4s ease";
                alert.style.opacity = "0";
                setTimeout(() => alert.remove(), 400);
            }, 5000);
        }
    });

    // -------------------------------------------------------------
    // 2. Login Form Validation & Quick Fill Demo Buttons
    // -------------------------------------------------------------
    const loginForm = document.getElementById("loginForm");
    if (loginForm) {
        loginForm.addEventListener("submit", function (e) {
            const usernameInput = document.getElementById("username");
            const passwordInput = document.getElementById("password");

            if (!usernameInput.value.trim()) {
                e.preventDefault();
                alert("Please enter your username.");
                usernameInput.focus();
                return false;
            }

            if (!passwordInput.value.trim()) {
                e.preventDefault();
                alert("Please enter your password.");
                passwordInput.focus();
                return false;
            }
        });
    }

    // Quick-Fill Demo buttons
    const btnFillStudent = document.getElementById("fillStudent");
    const btnFillWarden = document.getElementById("fillWarden");
    const usernameField = document.getElementById("username");
    const passwordField = document.getElementById("password");

    if (btnFillStudent && usernameField && passwordField) {
        btnFillStudent.addEventListener("click", function () {
            usernameField.value = "student1";
            passwordField.value = "student123";
            usernameField.focus();
        });
    }

    if (btnFillWarden && usernameField && passwordField) {
        btnFillWarden.addEventListener("click", function () {
            usernameField.value = "warden1";
            passwordField.value = "warden123";
            usernameField.focus();
        });
    }

    // -------------------------------------------------------------
    // 3. Complaint Form Validation & Character Counter (Student)
    // -------------------------------------------------------------
    const complaintForm = document.getElementById("complaintForm");
    const descTextarea = document.getElementById("description");
    const charCountDisplay = document.getElementById("charCount");

    // Live character counter
    if (descTextarea && charCountDisplay) {
        descTextarea.addEventListener("input", function () {
            const currentLength = this.value.length;
            charCountDisplay.textContent = currentLength;
            if (currentLength > 500) {
                charCountDisplay.style.color = "#ef4444";
            } else {
                charCountDisplay.style.color = "#94a3b8";
            }
        });
    }

    // Validation on submit
    if (complaintForm) {
        complaintForm.addEventListener("submit", function (e) {
            const categorySelect = document.getElementById("category");
            const roomNumberInput = document.getElementById("room_number");
            const description = descTextarea ? descTextarea.value.trim() : "";

            if (!categorySelect.value) {
                e.preventDefault();
                alert("Please select a complaint category.");
                categorySelect.focus();
                return false;
            }

            if (roomNumberInput && !roomNumberInput.value.trim()) {
                e.preventDefault();
                alert("Please enter your room number.");
                roomNumberInput.focus();
                return false;
            }

            if (description.length < 10) {
                e.preventDefault();
                alert("Please provide a detailed description (at least 10 characters).");
                descTextarea.focus();
                return false;
            }

            if (description.length > 500) {
                e.preventDefault();
                alert("Complaint description must not exceed 500 characters.");
                descTextarea.focus();
                return false;
            }
        });
    }

    // -------------------------------------------------------------
    // 4. Live Table Search & Filter (Warden Dashboard)
    // -------------------------------------------------------------
    const searchInput = document.getElementById("searchInput");
    const complaintsTable = document.getElementById("complaintsTable");
    const noResultsRow = document.getElementById("noSearchResults");

    if (searchInput && complaintsTable) {
        searchInput.addEventListener("keyup", function () {
            const filterText = this.value.toLowerCase().trim();
            const tableRows = complaintsTable.querySelectorAll("tbody tr.complaint-row");
            let visibleCount = 0;

            tableRows.forEach(function (row) {
                // Check all cells text in the row
                const rowText = row.textContent.toLowerCase();
                if (rowText.includes(filterText)) {
                    row.style.display = "";
                    visibleCount++;
                } else {
                    row.style.display = "none";
                }
            });

            // Toggle "No results" row if everything is filtered out
            if (noResultsRow) {
                noResultsRow.style.display = (visibleCount === 0) ? "" : "none";
            }
        });
    }

    // -------------------------------------------------------------
    // 5. Confirm Status Change in Warden Dashboard
    // -------------------------------------------------------------
    const statusForms = document.querySelectorAll(".status-update-form");
    statusForms.forEach(function (form) {
        form.addEventListener("submit", function (e) {
            const selectEl = form.querySelector("select[name='status']");
            const selectedStatus = selectEl ? selectEl.value : "";
            const confirmChange = confirm("Are you sure you want to update this complaint status to '" + selectedStatus + "'?");
            if (!confirmChange) {
                e.preventDefault();
                return false;
            }
        });
    });

    // -------------------------------------------------------------
    // 6. Registration Form Validation
    // -------------------------------------------------------------
    const registerForm = document.getElementById("registerForm");
    if (registerForm) {
        registerForm.addEventListener("submit", function (e) {
            const pwd = document.getElementById("password").value;
            const cpwd = document.getElementById("confirm_password").value;

            if (pwd.length < 6) {
                e.preventDefault();
                alert("Password must be at least 6 characters long.");
                document.getElementById("password").focus();
                return false;
            }

            if (pwd !== cpwd) {
                e.preventDefault();
                alert("Passwords do not match. Please verify both fields.");
                document.getElementById("confirm_password").focus();
                return false;
            }
        });
    }
});

