<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - College Navigation System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="user_management.css">
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <button class="toggle-btn" id="toggle-sidebar" aria-label="Toggle sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <span class="logo-text">Campus Navigator</span>
        </div>
        <div class="user-info">
            <div class="user-avatar"></div>
            <div class="user-details">
                <div class="user-name"></div>
                <div class="user-role">Admin</div>
            </div>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="admin_dashboard.html" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <div class="section-title">Administration</div>
            <li class="nav-item">
                <a href="#" class="nav-link active">
                    <i class="fas fa-users-cog"></i>
                    <span>User Management</span>
                </a>
            </li>
            <div class="section-title">Services</div>
            <li class="nav-item">
                <a href="pending_requests.php" class="nav-link">
                    <i class="fas fa-tasks"></i>
                    <span>Pending Requests</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content" id="main-content">
        <div class="header">
            <button class="mobile-toggle-btn" id="mobile-toggle" aria-label="Toggle sidebar on mobile">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="page-title">User Management</h1>
        </div>

        <div class="dashboard-cards">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Total Users</div>
                    <div class="card-icon blue">
                        <i class="fas fa-users"></i>
                        <span id="total-users-count">0</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="filter-section">
            <h3 class="section-heading">Filter Users</h3>
            <div class="filter-controls">
                <div class="form-group">
                    <label for="filter-role">Role</label>
                    <select id="filter-role">
                        <option value="">All Roles</option>
                        <option value="Faculty">Faculty</option>
                        <option value="Student">Student</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="filter-department">Department</label>
                    <select id="filter-department">
                        <option value="">All Departments</option>
                        <option value="BCA">BCA</option>
                        <option value="Bio.Tech">Bio.Tech</option>
                        <option value="English">English</option>
                        <option value="B.com">B.com</option>
                        <option value="Maths">Maths</option>
                        <option value="Physics">Physics</option>
                    </select>
                </div>
                <div class="form-group" id="semester-filter" style="display: none;">
                    <label for="filter-semester">Semester</label>
                    <select id="filter-semester">
                        <option value="">All Semesters</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                        <option value="7">7</option>
                        <option value="8">8</option>
                    </select>
                </div>
                <button id="apply-filter-btn" class="filter-btn">Apply Filters</button>
            </div>
        </div>

        <div class="user-list">
            <h3 class="section-heading">User List</h3>
            <table id="user-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th>Semester</th>
                        <th>Email</th>
                        <th>Phone Number</th>
                    </tr>
                </thead>
                <tbody id="user-table-body"></tbody>
            </table>
            <p id="no-users-message" style="display: none;">No users found.</p>
        </div>

        <div class="message-section">
            <h3 class="section-heading">Send College Message</h3>
            <div class="message-form-container">
                <form id="college-message-form">
                    <div class="form-group">
                        <label for="college-message">Message</label>
                        <textarea id="college-message" name="message" rows="4" required placeholder="Enter your college-related message"></textarea>
                    </div>
                    <button type="submit" class="action-btn" id="submit-message-btn">Submit Message</button>
                </form>
                <p id="message-success" class="message-success" style="display: none;">Message submitted successfully!</p>
                <p id="message-error" class="message-error" style="display: none;">Error submitting message. Please try again.</p>
            </div>
        </div>

        <!-- User Details Modal -->
        <div class="modal" id="user-details-modal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="modal-title">User Details</h2>
                    <button class="close-btn" id="close-modal" aria-label="Close modal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body" id="user-details-content">
                    <!-- User details or edit form will be populated here -->
                </div>
                <div class="modal-footer">
                    <button class="action-btn" id="edit-user-btn" style="display: none;">Edit</button>
                    <button class="action-btn" id="save-user-btn" style="display: none;">Save</button>
                    <button class="action-btn" id="cancel-edit-btn" style="display: none;">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Session check
            let currentUsername = '';
            fetch('check_session.php')
                .then(response => response.json())
                .then(data => {
                    console.log('Session check:', data);
                    if (!data.logged_in || data.user.role !== 'Admin') {
                        window.location.href = 'index.html';
                    } else {
                        currentUsername = data.user.username;
                        document.querySelector('.user-name').textContent = data.user.username;
                        document.querySelector('.user-role').textContent = data.user.role;
                        document.querySelector('.user-avatar').textContent = data.user.username.charAt(0).toUpperCase();
                        loadDepartments();
                        loadUsers();
                    }
                })
                .catch(error => {
                    console.error('Session check error:', error);
                    window.location.href = 'index.html';
                });

            // Sidebar toggle logic
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const toggleBtn = document.getElementById('toggle-sidebar');
            const mobileToggle = document.getElementById('mobile-toggle');

            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
                toggleBtn.setAttribute('aria-expanded', !sidebar.classList.contains('collapsed'));
            });

            mobileToggle.addEventListener('click', () => {
                sidebar.classList.toggle('mobile-show');
                if (sidebar.classList.contains('mobile-show')) {
                    sidebar.querySelector('.nav-link').focus();
                } else {
                    mobileToggle.focus();
                }
            });

            document.addEventListener('click', (e) => {
                if (window.innerWidth <= 768 && !sidebar.contains(e.target) && !mobileToggle.contains(e.target)) {
                    sidebar.classList.remove('mobile-show');
                }
            });

            toggleBtn.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');
                    toggleBtn.setAttribute('aria-expanded', !sidebar.classList.contains('collapsed'));
                }
            });

            mobileToggle.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    sidebar.classList.toggle('mobile-show');
                }
            });

            // Modal handling
            const modal = document.getElementById('user-details-modal');
            const closeModalBtn = document.getElementById('close-modal');
            const userDetailsContent = document.getElementById('user-details-content');
            const editUserBtn = document.getElementById('edit-user-btn');
            const saveUserBtn = document.getElementById('save-user-btn');
            const cancelEditBtn = document.getElementById('cancel-edit-btn');
            let currentUserId = null;
            let currentRole = null;

            closeModalBtn.addEventListener('click', () => {
                modal.style.display = 'none';
                editUserBtn.style.display = 'none';
                saveUserBtn.style.display = 'none';
                cancelEditBtn.style.display = 'none';
            });

            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.style.display = 'none';
                    editUserBtn.style.display = 'none';
                    saveUserBtn.style.display = 'none';
                    cancelEditBtn.style.display = 'none';
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && modal.style.display === 'block') {
                    modal.style.display = 'none';
                    editUserBtn.style.display = 'none';
                    saveUserBtn.style.display = 'none';
                    cancelEditBtn.style.display = 'none';
                }
            });

            // Load departments for filter
            function loadDepartments() {
                fetch('fetch_departments.php')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(departments => {
                        const departmentSelect = document.getElementById('filter-department');
                        const existingOptions = Array.from(departmentSelect.options).map(opt => opt.value);
                        departments.forEach(dept => {
                            if (!existingOptions.includes(dept) && dept !== '') {
                                const option = document.createElement('option');
                                option.value = dept;
                                option.textContent = dept;
                                departmentSelect.appendChild(option);
                            }
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching departments:', error);
                    });
            }

            // Load users with filters
            function loadUsers() {
                const role = document.getElementById('filter-role').value;
                const department = document.getElementById('filter-department').value;
                const semester = document.getElementById('filter-semester').value;
                const query = new URLSearchParams({
                    role: role,
                    department: department,
                    semester: semester
                }).toString();

                fetch(`fetch_users.php?${query}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Fetched users:', data);
                        const userTableBody = document.getElementById('user-table-body');
                        const totalUsersCount = document.getElementById('total-users-count');
                        const noUsersMessage = document.getElementById('no-users-message');

                        totalUsersCount.textContent = data.total;
                        userTableBody.innerHTML = '';

                        if (data.users.length === 0) {
                            noUsersMessage.style.display = 'block';
                            return;
                        }

                        noUsersMessage.style.display = 'none';
                        data.users.forEach(user => {
                            const tr = document.createElement('tr');
                            tr.dataset.userId = user.id;
                            tr.innerHTML = `
                                <td>${user.username}</td>
                                <td>${user.name || '-'}</td>
                                <td>${user.role}</td>
                                <td>${user.department || '-'}</td>
                                <td>${user.semester || '-'}</td>
                                <td>${user.email || '-'}</td>
                                <td>${user.phone_no || '-'}</td>
                            `;
                            tr.style.cursor = 'pointer';
                            tr.addEventListener('click', () => showUserDetails(user.id, user.role));
                            userTableBody.appendChild(tr);
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching users:', error);
                        document.getElementById('no-users-message').style.display = 'block';
                    });
            }

            // Show user details or edit form in modal
            function showUserDetails(userId, role) {
                currentUserId = userId;
                currentRole = role;
                fetch(`fetch_user_details.php?id=${userId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('User details:', data);
                        let html = '';
                        if (data.error) {
                            html = `<p class="error">${data.error}</p>`;
                            editUserBtn.style.display = 'none';
                        } else if (role === 'Student') {
                            html = `
                                <p><strong>Roll Number:</strong> <span class="user-detail" data-field="roll_number">${data.roll_number || '-'}</span></p>
                                <p><strong>Name:</strong> <span class="user-detail" data-field="name">${data.name || '-'}</span></p>
                                <p><strong>Semester:</strong> <span class="user-detail" data-field="semester">${data.semester || '-'}</span></p>
                                <p><strong>Department:</strong> <span class="user-detail" data-field="department">${data.department || '-'}</span></p>
                                <p><strong>Email:</strong> <span class="user-detail" data-field="email">${data.email || '-'}</span></p>
                                <p><strong>Phone Number:</strong> <span class="user-detail" data-field="phone_no">${data.phone_no || '-'}</span></p>
                                <p><strong>Address:</strong> <span class="user-detail" data-field="address">${data.address || '-'}</span></p>
                            `;
                            editUserBtn.style.display = 'block';
                        } else if (role === 'Faculty') {
                            html = `
                                <p><strong>Faculty ID:</strong> <span class="user-detail" data-field="faculty_id">${data.faculty_id || '-'}</span></p>
                                <p><strong>Name:</strong> <span class="user-detail" data-field="name">${data.name || '-'}</span></p>
                                <p><strong>Department:</strong> <span class="user-detail" data-field="department">${data.department || '-'}</span></p>
                                <p><strong>Office Location:</strong> <span class="user-detail" data-field="office_location">${data.office_location || '-'}</span></p>
                                <p><strong>Email:</strong> <span class="user-detail" data-field="email">${data.email || '-'}</span></p>
                                <p><strong>Course:</strong> <span class="user-detail" data-field="course">${data.course || '-'}</span></p>
                                <p><strong>Phone Number:</strong> <span class="user-detail" data-field="phone_no">${data.phone_no || '-'}</span></p>
                                <p><strong>Available Days:</strong> <span class="user-detail" data-field="available_day">${data.available_day || '-'}</span></p>
                            `;
                            editUserBtn.style.display = 'block';
                        } else {
                            html = `
                                <p><strong>User ID:</strong> <span class="user-detail" data-field="id">${data.id || '-'}</span></p>
                                <p><strong>Username:</strong> <span class="user-detail" data-field="username">${data.username || '-'}</span></p>
                                <p><strong>Role:</strong> <span class="user-detail" data-field="role">${data.role || '-'}</span></p>
                                <p><strong>Email:</strong> <span class="user-detail" data-field="email">${data.email || '-'}</span></p>
                            `;
                            editUserBtn.style.display = 'none';
                        }
                        userDetailsContent.innerHTML = html;
                        modal.style.display = 'block';
                        editUserBtn.style.display = (role === 'Student' || role === 'Faculty') ? 'block' : 'none';
                        saveUserBtn.style.display = 'none';
                        cancelEditBtn.style.display = 'none';
                        document.getElementById('modal-title').textContent = 'User Details';
                        closeModalBtn.focus();
                    })
                    .catch(error => {
                        console.error('Error fetching user details:', error);
                        userDetailsContent.innerHTML = '<p class="error">Failed to load user details.</p>';
                        modal.style.display = 'block';
                        editUserBtn.style.display = 'none';
                        saveUserBtn.style.display = 'none';
                        cancelEditBtn.style.display = 'none';
                    });
            }

            // Toggle to edit mode
            editUserBtn.addEventListener('click', () => {
                fetch(`fetch_user_details.php?id=${currentUserId}`)
                    .then(response => response.json())
                    .then(data => {
                        let html = '';
                        if (currentRole === 'Student') {
                            html = `
                                <form id="edit-user-form">
                                    <input type="hidden" name="user_id" value="${currentUserId}">
                                    <input type="hidden" name="role" value="Student">
                                    <div class="form-group">
                                        <label for="edit-name">Name</label>
                                        <input type="text" id="edit-name" name="name" value="${data.name || ''}" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit-semester">Semester</label>
                                        <select id="edit-semester" name="semester">
                                            <option value="">Select Semester</option>
                                            <option value="1" ${data.semester === '1' ? 'selected' : ''}>1</option>
                                            <option value="2" ${data.semester === '2' ? 'selected' : ''}>2</option>
                                            <option value="3" ${data.semester === '3' ? 'selected' : ''}>3</option>
                                            <option value="4" ${data.semester === '4' ? 'selected' : ''}>4</option>
                                            <option value="5" ${data.semester === '5' ? 'selected' : ''}>5</option>
                                            <option value="6" ${data.semester === '6' ? 'selected' : ''}>6</option>
                                            <option value="7" ${data.semester === '7' ? 'selected' : ''}>7</option>
                                            <option value="8" ${data.semester === '8' ? 'selected' : ''}>8</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit-department">Department</label>
                                        <select id="edit-department" name="department">
                                            <option value="">Select Department</option>
                                            <option value="BCA" ${data.department === 'BCA' ? 'selected' : ''}>BCA</option>
                                            <option value="Bio.Tech" ${data.department === 'Bio.Tech' ? 'selected' : ''}>Bio.Tech</option>
                                            <option value="English" ${data.department === 'English' ? 'selected' : ''}>English</option>
                                            <option value="B.com" ${data.department === 'B.com' ? 'selected' : ''}>B.com</option>
                                            <option value="Maths" ${data.department === 'Maths' ? 'selected' : ''}>Maths</option>
                                            <option value="Physics" ${data.department === 'Physics' ? 'selected' : ''}>Physics</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit-email">Email</label>
                                        <input type="email" id="edit-email" name="email" value="${data.email || ''}" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit-phone_no">Phone Number</label>
                                        <input type="tel" id="edit-phone_no" name="phone_no" value="${data.phone_no || ''}" pattern="[0-9]{10}" title="Enter a 10-digit phone number">
                                    </div>
                                    <div class="form-group">
                                        <label for="edit-address">Address</label>
                                        <textarea id="edit-address" name="address">${data.address || ''}</textarea>
                                    </div>
                                </form>
                            `;
                        } else if (currentRole === 'Faculty') {
                            html = `
                                <form id="edit-user-form">
                                    <input type="hidden" name="user_id" value="${currentUserId}">
                                    <input type="hidden" name="role" value="Faculty">
                                    <div class="form-group">
                                        <label for="edit-name">Name</label>
                                        <input type="text" id="edit-name" name="name" value="${data.name || ''}" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit-department">Department</label>
                                        <select id="edit-department" name="department">
                                            <option value="">Select Department</option>
                                            <option value="BCA" ${data.department === 'BCA' ? 'selected' : ''}>BCA</option>
                                            <option value="Bio.Tech" ${data.department === 'Bio.Tech' ? 'selected' : ''}>Bio.Tech</option>
                                            <option value="English" ${data.department === 'English' ? 'selected' : ''}>English</option>
                                            <option value="B.com" ${data.department === 'B.com' ? 'selected' : ''}>B.com</option>
                                            <option value="Maths" ${data.department === 'Maths' ? 'selected' : ''}>Maths</option>
                                            <option value="Physics" ${data.department === 'Physics' ? 'selected' : ''}>Physics</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit-office_location">Office Location</label>
                                        <input type="text" id="edit-office_location" name="office_location" value="${data.office_location || ''}">
                                    </div>
                                    <div class="form-group">
                                        <label for="edit-email">Email</label>
                                        <input type="email" id="edit-email" name="email" value="${data.email || ''}" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit-course">Course</label>
                                        <input type="text" id="edit-course" name="course" value="${data.course || ''}">
                                    </div>
                                    <div class="form-group">
                                        <label for="edit-phone_no">Phone Number</label>
                                        <input type="tel" id="edit-phone_no" name="phone_no" value="${data.phone_no || ''}" pattern="[0-9]{10}" title="Enter a 10-digit phone number">
                                    </div>
                                    <div class="form-group">
                                        <label for="edit-available_day">Available Days</label>
                                        <input type="text" id="edit-available_day" name="available_day" value="${data.available_day || ''}" placeholder="e.g., Monday,Wednesday,Friday">
                                    </div>
                                </form>
                            `;
                        }
                        userDetailsContent.innerHTML = html;
                        document.getElementById('modal-title').textContent = 'Edit User';
                        editUserBtn.style.display = 'none';
                        saveUserBtn.style.display = 'block';
                        cancelEditBtn.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Error fetching user details for edit:', error);
                        userDetailsContent.innerHTML = '<p class="error">Failed to load user details.</p>';
                        editUserBtn.style.display = 'none';
                        saveUserBtn.style.display = 'none';
                        cancelEditBtn.style.display = 'none';
                    });
            });

            // Save edited user details
            saveUserBtn.addEventListener('click', () => {
                const form = document.getElementById('edit-user-form');
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                const formData = new FormData(form);
                const data = Object.fromEntries(formData);

                fetch('update_user_details.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            alert('User details updated successfully!');
                            modal.style.display = 'none';
                            editUserBtn.style.display = 'none';
                            saveUserBtn.style.display = 'none';
                            cancelEditBtn.style.display = 'none';
                            loadUsers();
                            showUserDetails(currentUserId, currentRole);
                        } else {
                            alert(result.error || 'Error updating user details.');
                        }
                    })
                    .catch(error => {
                        console.error('Error updating user details:', error);
                        alert('Error updating user details. Please try again.');
                    });
            });

            // Cancel edit
            cancelEditBtn.addEventListener('click', () => {
                showUserDetails(currentUserId, currentRole);
            });

            // Submit college message
            const messageForm = document.getElementById('college-message-form');
            const messageSuccess = document.getElementById('message-success');
            const messageError = document.getElementById('message-error');

            messageForm.addEventListener('submit', (e) => {
                e.preventDefault();
                if (!messageForm.checkValidity()) {
                    messageForm.reportValidity();
                    return;
                }

                const formData = new FormData(messageForm);
                const data = {
                    message: formData.get('message'),
                    created_by: currentUsername
                };

                fetch('submit_message.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            messageSuccess.style.display = 'block';
                            messageError.style.display = 'none';
                            messageForm.reset();
                            setTimeout(() => {
                                messageSuccess.style.display = 'none';
                            }, 3000);
                        } else {
                            messageError.textContent = result.error || 'Error submitting message.';
                            messageError.style.display = 'block';
                            messageSuccess.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Error submitting message:', error);
                        messageError.textContent = 'Error submitting message. Please try again.';
                        messageError.style.display = 'block';
                        messageSuccess.style.display = 'none';
                    });
            });

            // Show/hide semester filter based on role
            document.getElementById('filter-role').addEventListener('change', (e) => {
                const semesterFilter = document.getElementById('semester-filter');
                semesterFilter.style.display = e.target.value === 'Student' ? 'block' : 'none';
            });

            // Apply filters on button click
            document.getElementById('apply-filter-btn').addEventListener('click', loadUsers);
        });
    </script>
</body>
</html>