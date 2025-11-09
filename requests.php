<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Request - College Navigation System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="student.css">
    <style>
        .requests-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin: 20px 0;
        }
        .requests-container h2 {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
        }
        .request-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .request-form textarea {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            resize: vertical;
            min-height: 100px;
        }
        .request-form button {
            background: #007bff;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            align-self: flex-start;
        }
        .request-form button:hover {
            background: #0056b3;
        }
        .message {
            font-size: 14px;
            margin-top: 10px;
            display: none;
        }
        .message.success {
            color: #28a745;
        }
        .message.error {
            color: #d9534f;
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
                <div class="user-role"></div>
            </div>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="student_dashboard.html" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li><br>
            <div class="section-title">Academic</div><br>
            <li class="nav-item">
                <a href="attendance.html" class="nav-link">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Attendance</span>
                </a>
            </li><br>
            <div class="section-title">Services</div><br>
            <li class="nav-item">
                <a href="notifications.php" class="nav-link">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                </a>
            </li><br>
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
            <h1 class="page-title">Submit Request</h1>
            <div class="header-actions">
                <a href="student_profile.php" class="profile-btn" aria-label="Edit user profile">
                    <i class="fas fa-user"></i>
                </a>
            </div>
        </div>

        <div class="requests-container">
            <h2>Submit a Request</h2>
            <div class="request-form">
                <textarea id="request-message" placeholder="Enter your request here..." required></textarea>
                <button id="submit-request">Submit Request</button>
                <div id="request-message-display" class="message"></div>
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
                    if (!data.logged_in || data.user.role !== 'Student') {
                        window.location.href = 'index.html';
                    } else {
                        document.querySelector('.user-name').textContent = data.user.username;
                        document.querySelector('.user-role').textContent = data.user.role;
                        document.querySelector('.user-avatar').textContent = data.user.username.charAt(0).toUpperCase();
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

            // Request submission logic
            const submitButton = document.getElementById('submit-request');
            const requestMessage = document.getElementById('request-message');
            const messageDisplay = document.getElementById('request-message-display');

            submitButton.addEventListener('click', () => {
                const message = requestMessage.value.trim();
                if (!message) {
                    messageDisplay.textContent = 'Please enter a request message.';
                    messageDisplay.className = 'message error';
                    messageDisplay.style.display = 'block';
                    return;
                }

                fetch('submit_request.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ message })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        messageDisplay.textContent = 'Request submitted successfully!';
                        messageDisplay.className = 'message success';
                        messageDisplay.style.display = 'block';
                        requestMessage.value = '';
                    } else {
                        messageDisplay.textContent = data.message || 'Failed to submit request. Please try again.';
                        messageDisplay.className = 'message error';
                        messageDisplay.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error submitting request:', error);
                    messageDisplay.textContent = 'Error submitting request. Please try again.';
                    messageDisplay.className = 'message error';
                    messageDisplay.style.display = 'block';
                });
            });
        });
    </script>
</body>
</html>