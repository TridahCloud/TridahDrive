// Notification Manager
class NotificationManager {
    constructor() {
        this.unreadCount = 0;
        this.notifications = [];
        this.init();
    }

    init() {
        this.loadNotifications();
        this.setupEventListeners();
        
        // Refresh notifications every 30 seconds
        setInterval(() => this.loadNotifications(), 30000);
    }

    setupEventListeners() {
        const markAllReadBtn = document.getElementById('markAllReadBtn');
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', () => this.markAllAsRead());
        }

        // Load notifications when dropdown opens
        const dropdown = document.getElementById('notificationDropdown');
        if (dropdown) {
            dropdown.addEventListener('shown.bs.dropdown', () => {
                this.loadNotifications();
            });
        }
    }

    async loadNotifications() {
        try {
            const response = await fetch('/notifications?per_page=10');
            const data = await response.json();
            
            this.notifications = data.notifications.data || [];
            this.unreadCount = data.unread_count || 0;
            
            this.updateBadge();
            this.renderNotifications();
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    }

    updateBadge() {
        const badge = document.getElementById('notificationBadge');
        const markAllBtn = document.getElementById('markAllReadBtn');
        
        if (badge) {
            if (this.unreadCount > 0) {
                badge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
                badge.style.display = 'block';
                if (markAllBtn) markAllBtn.style.display = 'block';
            } else {
                badge.style.display = 'none';
                if (markAllBtn) markAllBtn.style.display = 'none';
            }
        }
    }

    renderNotifications() {
        const container = document.getElementById('notificationsList');
        if (!container) return;

        if (this.notifications.length === 0) {
            container.innerHTML = `
                <div class="text-center p-4" style="color: rgba(255, 255, 255, 0.6);">
                    <i class="fas fa-bell-slash fa-2x mb-2"></i>
                    <p class="mb-0">No notifications</p>
                </div>
            `;
            return;
        }

        const html = this.notifications.map(notification => {
            const icon = this.getNotificationIcon(notification.type);
            const timeAgo = this.timeAgo(notification.created_at);
            const isUnread = !notification.read ? 'notification-unread' : '';
            const driveId = notification.data?.drive_id;
            
            return `
                <div class="notification-item ${isUnread} p-3 border-bottom" data-id="${notification.id}">
                    <div class="d-flex gap-2">
                        <div class="notification-icon ${icon.color}">
                            <i class="fas ${icon.name}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1 small">${this.escapeHtml(notification.title)}</h6>
                                    <p class="mb-1 small text-muted">${this.escapeHtml(notification.message)}</p>
                                    <small class="text-muted">${timeAgo}</small>
                                </div>
                                ${!notification.read ? '<span class="badge bg-primary rounded-pill ms-2">New</span>' : ''}
                            </div>
                            ${driveId ? `
                                <div class="mt-2">
                                    <a href="/drives/${driveId}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-folder me-1"></i>View Drive
                                    </a>
                                </div>
                            ` : ''}
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-link btn-sm text-muted p-0" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                ${!notification.read ? `
                                    <li>
                                        <a class="dropdown-item mark-as-read" href="#" data-id="${notification.id}">
                                            <i class="fas fa-check me-2"></i>Mark as read
                                        </a>
                                    </li>
                                ` : `
                                    <li>
                                        <a class="dropdown-item mark-as-unread" href="#" data-id="${notification.id}">
                                            <i class="fas fa-envelope me-2"></i>Mark as unread
                                        </a>
                                    </li>
                                `}
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger delete-notification" href="#" data-id="${notification.id}">
                                        <i class="fas fa-trash me-2"></i>Delete
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        container.innerHTML = html;

        // Add event listeners
        container.querySelectorAll('.mark-as-read').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const id = btn.getAttribute('data-id');
                this.markAsRead(id);
            });
        });

        container.querySelectorAll('.mark-as-unread').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const id = btn.getAttribute('data-id');
                this.markAsUnread(id);
            });
        });

        container.querySelectorAll('.delete-notification').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const id = btn.getAttribute('data-id');
                this.deleteNotification(id);
            });
        });

        // Stop propagation on dropdown toggle button
        container.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        });

        // Stop propagation on the entire notification item dropdown area
        container.querySelectorAll('.dropdown').forEach(dropdown => {
            dropdown.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        });
    }

    async markAsRead(id) {
        try {
            const response = await fetch(`/notifications/${id}/read`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
            });

            const data = await response.json();
            this.unreadCount = data.unread_count || 0;
            this.updateBadge();
            this.loadNotifications();
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    async markAsUnread(id) {
        try {
            const response = await fetch(`/notifications/${id}/unread`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
            });

            const data = await response.json();
            this.unreadCount = data.unread_count || 0;
            this.updateBadge();
            this.loadNotifications();
        } catch (error) {
            console.error('Error marking notification as unread:', error);
        }
    }

    async markAllAsRead() {
        try {
            const response = await fetch('/notifications/read-all', {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
            });

            const data = await response.json();
            this.unreadCount = 0;
            this.updateBadge();
            this.loadNotifications();
            
            if (window.toastManager) {
                window.toastManager.success('All notifications marked as read');
            }
        } catch (error) {
            console.error('Error marking all as read:', error);
        }
    }

    async deleteNotification(id) {
        if (!confirm('Are you sure you want to delete this notification?')) {
            return;
        }

        try {
            const response = await fetch(`/notifications/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });

            const data = await response.json();
            this.unreadCount = data.unread_count || 0;
            this.updateBadge();
            this.loadNotifications();
        } catch (error) {
            console.error('Error deleting notification:', error);
        }
    }

    getNotificationIcon(type) {
        const icons = {
            'drive_invite': { name: 'fa-folder-plus', color: 'text-info' },
            'drive_role_changed': { name: 'fa-user-cog', color: 'text-warning' },
            'drive_removed': { name: 'fa-folder-minus', color: 'text-danger' },
        };
        
        return icons[type] || { name: 'fa-bell', color: 'text-secondary' };
    }

    timeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);
        
        if (diffInSeconds < 60) return 'Just now';
        if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
        if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
        if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d ago`;
        
        return date.toLocaleDateString();
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.notificationManager = new NotificationManager();
});

