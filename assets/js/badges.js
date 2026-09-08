// Real-time badge polling for notifications and messages
(function() {
    const POLL_INTERVAL = 5000; // 5 seconds
    
    // Simple base URL detection - go up to Clan_Management_System root
    let baseUrl = window.location.origin;
    const pathParts = window.location.pathname.split('/');
    const clanIndex = pathParts.indexOf('Clan_Management_System');
    
    if (clanIndex !== -1) {
        baseUrl += '/' + pathParts.slice(1, clanIndex + 1).join('/');
    } else {
        // If not found, assume we're in a subdirectory and go up 2 levels
        baseUrl += window.location.pathname.replace(/\/[^/]*\/[^/]*$/, '');
    }
    
    if (!baseUrl.endsWith('/')) {
        baseUrl += '/';
    }
    
    console.log('Badge polling started. Base URL:', baseUrl);
    console.log('Current pathname:', window.location.pathname);
    
    // Update notification badge
    function updateNotificationBadge() {
        const url = baseUrl + 'api/notification_count.php';
        console.log('Fetching notification count from:', url);
        fetch(url, {
            credentials: 'same-origin',
            cache: 'no-cache'
        })
            .then(response => {
                console.log('Notification response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Notification count data:', data);
                const badge = document.getElementById('notificationBadge');
                if (badge) {
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = 'inline-block';
                        console.log('✓ Notification badge updated:', data.count);
                    } else {
                        badge.style.display = 'none';
                        console.log('○ Notification badge hidden (count is 0)');
                    }
                } else {
                    console.log('✗ Notification badge element not found');
                }
            })
            .catch(error => console.error('✗ Error fetching notification count:', error));
    }
    
    // Update message badge
    function updateMessageBadge() {
        const url = baseUrl + 'api/message_count.php';
        console.log('Fetching message count from:', url);
        fetch(url, {
            credentials: 'same-origin',
            cache: 'no-cache'
        })
            .then(response => {
                console.log('Message response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Message count data:', data);
                // Update sidebar badge only
                const sidebarBadge = document.getElementById('messageBadge');
                
                if (sidebarBadge) {
                    if (data.count > 0) {
                        sidebarBadge.textContent = data.count;
                        sidebarBadge.style.display = 'inline-block';
                        console.log('✓ Message badge updated:', data.count);
                    } else {
                        sidebarBadge.style.display = 'none';
                        console.log('○ Message badge hidden (count is 0)');
                    }
                } else {
                    console.log('✗ Message badge element not found');
                }
            })
            .catch(error => console.error('✗ Error fetching message count:', error));
    }
    
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            console.log('DOM loaded, starting badge updates');
            updateNotificationBadge();
            updateMessageBadge();
        });
    } else {
        console.log('DOM already ready, starting badge updates');
        updateNotificationBadge();
        updateMessageBadge();
    }
    
    // Poll for updates
    setInterval(() => {
        updateNotificationBadge();
        updateMessageBadge();
    }, POLL_INTERVAL);
    
    // Also update when page becomes visible (for mobile)
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            console.log('Page became visible, updating badges');
            updateNotificationBadge();
            updateMessageBadge();
        }
    });
})();
