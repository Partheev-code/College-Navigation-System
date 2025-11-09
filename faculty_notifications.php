<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - College Navigation System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="faculty.css">
    <style>
        .notifications-section, .college-messages-section {
            margin: 20px;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .section-heading {
            font-size: 1.5em;
            color: #333;
        }
        .action-btn {
            background-color: #007bff;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .action-btn:hover {
            background-color: #0056b3;
        }
        .notifications-list-container, .messages-list-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .notification-item, .message-item {
            display: flex;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .notification-date, .message-date {
            min-width: 60px;
            text-align: center;
            margin-right: 15px;
        }
        .notification-day, .message-day {
            font-size: 1.5em;
            font-weight: bold;
            color: #007bff;
        }
        .notification-month, .message-month {
            font-size: 0.9em;
            color: #666;
        }
        .notification-info, .message-info {
            flex: 1;
        }
        .notification-title, .message-title {
            font-weight: bold;
            color: #333;
        }
        .notification-time, .message-time {
            color: #666;
            font-size: 0.9em;
            margin: 5px 0;
        }
        .notification-message, .message-content {
            color: #444;
        }
        .notifications-loading, .messages-loading {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin: 20px 0;
        }
        .notifications-error, .messages-error {
            text-align: center;
            color: #d9534f;
            font-size: 14px;
            margin: 20px 0;
        }
    </style>
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
                <div class="user-role">Faculty</div>
            </div>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="faculty_dashboard.html" class="nav-link" id="nav-dashboard">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <div class="section-title">Teaching</div>
            <li class="nav-item">
                <a href="faculty_dashboard.php#manage-attendance" class="nav-link" id="nav-manage-attendance">
                    <i class="fas fa-user-check"></i>
                    <span>Manage Attendance</span>
                </a>
            </li>
            <div class="section-title">Services</div>
            <li class="nav-item">
                <a href="manage_appointments.php" class="nav-link">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Manage Appointments</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="faculty_notifications.php" class="nav-link active" id="nav-notifications">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
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
            <h1 class="page-title">Notifications</h1>
            <div class="header-actions">
                <a href="faculty_profile.php" class="profile-btn" aria-label="Edit faculty profile">
                    <i class="fas fa-user"></i>
                </a>
            </div>
        </div>

        <div class="notifications-section">
            <div class="section-header">
                <h3 class="section-heading">Admin Messages</h3>
                <a href="faculty_dashboard.html" class="action-btn">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
            <div class="notifications-list-container">
                <div class="notifications-list" id="notifications-list">
                    <div class="notifications-loading" id="notifications-loading">
                        <i class="fas fa-spinner fa-spin"></i> Loading...
                    </div>
                    <div class="notifications-error" id="notifications-error" style="display: none;"></div>
                </div>
            </div>
        </div>

        <div class="college-messages-section">
            <div class="section-header">
                <h3 class="section-heading">College Messages</h3>
            </div>
            <div class="messages-list-container">
                <div class="messages-list" id="college-messages-list">
                    <div class="messages-loading" id="college-messages-loading">
                        <i class="fas fa-spinner fa-spin"></i> Loading...
                    </div>
                    <div class="messages-error" id="college-messages-error" style="display: none;"></div>
                </div>
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
                    if (!data.logged_in || data.user.role !== 'Faculty') {
                        window.location.href = 'index.html';
                    } else {
                        document.querySelector('.user-name').textContent = data.user.username;
                        document.querySelector('.user-role').textContent = data.user.role;
                        document.querySelector('.user-avatar').textContent = data.user.username.charAt(0).toUpperCase();
                        loadNotifications();
                        loadCollegeMessages();
                    }
                })
                .catch(error => {
                    console.error('Session check error:', error);
                    window.location.href = 'index.html';
                });

            // Load notifications dynamically
            function loadNotifications() {
                const notificationsList = document.getElementById('notifications-list');
                const notificationsLoading = document.getElementById('notifications-loading');
                const notificationsError = document.getElementById('notifications-error');

                notificationsLoading.style.display = 'block';
                notificationsError.style.display = 'none';
                notificationsList.innerHTML = '';

                fetch('fetch_notifications.php')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(notifications => {
                        notificationsLoading.style.display = 'none';
                        if (notifications.length === 0) {
                            notificationsError.textContent = 'No notifications found.';
                            notificationsError.style.display = 'block';
                            return;
                        }

                        notifications.forEach(notification => {
                            const notificationItem = document.createElement('div');
                            notificationItem.className = 'notification-item';
                            notificationItem.innerHTML = `
                                <div class="notification-date">
                                    <div class="notification-day">${notification.day}</div>
                                    <div class="notification-month">${notification.month.slice(0, 3)}</div>
                                </div>
                                <div class="notification-info">
                                    <div class="notification-title">${notification.title}</div>
                                    <div class="notification-time">
                                        <i class="far fa-clock"></i> ${notification.time}
                                    </div>
                                    <div class="notification-message">${notification.message}</div>
                                </div>
                            `;
                            notificationsList.appendChild(notificationItem);
                        });
                    })
                    .catch(error => {
                        notificationsLoading.style.display = 'none';
                        console.error('Error fetching notifications:', error);
                        notificationsError.textContent = 'Error loading notifications. Please try again.';
                        notificationsError.style.display = 'block';
                    });
            }

            // Load college messages dynamically
            function loadCollegeMessages() {
                const messagesList = document.getElementById('college-messages-list');
                const messagesLoading = document.getElementById('college-messages-loading');
                const messagesError = document.getElementById('college-messages-error');

                messagesLoading.style.display = 'block';
                messagesError.style.display = 'none';
                messagesList.innerHTML = '';

                fetch('fetch_college_messages.php')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(messages => {
                        messagesLoading.style.display = 'none';
                        if (messages.length === 0) {
                            messagesError.textContent = 'No college messages found.';
                            messagesError.style.display = 'block';
                            return;
                        }

                        messages.forEach(message => {
                            const messageItem = document.createElement('div');
                            messageItem.className = 'message-item';
                            messageItem.innerHTML = `
                                <div class="message-date">
                                    <div class="message-day">${message.day}</div>
                                    <div class="message-month">${message.month.slice(0, 3)}</div>
                                </div>
                                <div class="message-info">
                                    <div class="message-title">Message from ${message.created_by}</div>
                                    <div class="message-time">
                                        <i class="far fa-clock"></i> ${message.time}
                                    </div>
                                    <div class="message-content">${message.message}</div>
                                </div>
                            `;
                            messagesList.appendChild(messageItem);
                        });
                    })
                    .catch(error => {
                        messagesLoading.style.display = 'none';
                        console.error('Error fetching college messages:', error);
                        messagesError.textContent = 'Error loading college messages. Please try again.';
                        messagesError.style.display = 'block';
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