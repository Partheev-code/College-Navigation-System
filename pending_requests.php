<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Requests - College Navigation System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="pending_requests.css">
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
                <a href="admin_dashboard.html" class="nav-link" id="nav-dashboard">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <div class="section-title">Administration</div>
            <li class="nav-item">
                <a href="user_management.php" class="nav-link">
                    <i class="fas fa-users-cog"></i>
                    <span>User Management</span>
                </a>
            </li>
            <div class="section-title">Services</div>
            <li class="nav-item">
                <a href="pending_requests.php" class="nav-link active" id="nav-pending-requests">
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
            <h1 class="page-title">Pending Requests</h1>
        </div>

        <div class="requests-section">
            <div class="section-header">
                <h3 class="section-heading">Manage Requests</h3>
                <a href="admin_dashboard.html" class="action-btn">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
            <div class="requests-list-container">
                <div class="requests-loading" id="requests-loading">
                    <i class="fas fa-spinner fa-spin"></i> Loading...
                </div>
                <div class="requests-error" id="requests-error" style="display: none;"></div>
                <div class="table-wrapper">
                    <table id="requests-table">
                        <thead>
                            <tr>
                                <th>Request ID</th>
                                <th>User</th>
                                <th>Message</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <p id="no-requests-message" class="message" style="display: none;">
                    No requests found.
                </p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Session check
            fetch('check_session.php')
                .then(response => response.json())
                .then(data => {
                    console.log('Session check:', data);
                    if (!data.logged_in || data.user.role !== 'Admin') {
                        window.location.href = 'index.html';
                    } else {
                        document.querySelector('.user-name').textContent = data.user.username;
                        document.querySelector('.user-role').textContent = data.user.role;
                        document.querySelector('.user-avatar').textContent = data.user.username.charAt(0).toUpperCase();
                        loadRequests();
                    }
                })
                .catch(error => {
                    console.error('Session check error:', error);
                    window.location.href = 'index.html';
                });

            // Load requests dynamically
            function loadRequests() {
                const requestsTableBody = document.querySelector('#requests-table tbody');
                const requestsLoading = document.getElementById('requests-loading');
                const requestsError = document.getElementById('requests-error');
                const noRequestsMessage = document.getElementById('no-requests-message');

                requestsLoading.style.display = 'block';
                requestsError.style.display = 'none';
                noRequestsMessage.style.display = 'none';
                requestsTableBody.innerHTML = '';

                fetch('fetch_requests.php')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(requests => {
                        requestsLoading.style.display = 'none';
                        if (requests.length === 0) {
                            noRequestsMessage.style.display = 'block';
                            return;
                        }

                        requests.forEach(request => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td>${request.id}</td>
                                <td>${request.username}</td>
                                <td>${request.message || 'N/A'}</td>
                                <td>${request.created_at}</td>
                            `;
                            requestsTableBody.appendChild(tr);
                        });
                    })
                    .catch(error => {
                        requestsLoading.style.display = 'none';
                        console.error('Error fetching requests:', error);
                        requestsError.textContent = 'Error loading requests. Please try again.';
                        requestsError.style.display = 'block';
                    });
            }

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
        });
    </script>
</body>
</html>