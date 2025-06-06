/**
 * Real-Time Notifications System
 * Phase 3 Implementation for SoNaMA IT Task Management
 */

class RealTimeNotifications {
    constructor() {
        this.pusher = null;
        this.channel = null;
        this.userId = null;
        this.isConnected = false;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        
        this.init();
    }

    /**
     * Initialize the real-time notification system
     */
    init() {
        // Get user ID from meta tag
        const userIdMeta = document.querySelector('meta[name="user-id"]');
        if (!userIdMeta) {
            console.warn('User ID not found in meta tags');
            return;
        }
        
        this.userId = userIdMeta.getAttribute('content');
        
        // Initialize Pusher
        this.initPusher();
        
        // Setup event listeners
        this.setupEventListeners();
        
        console.log('Real-time notifications initialized for user:', this.userId);
    }

    /**
     * Initialize Pusher connection
     */
    initPusher() {
        try {
            this.pusher = new Pusher(window.pusherConfig.key, {
                cluster: window.pusherConfig.cluster,
                encrypted: true,
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                }
            });

            // Subscribe to user's private channel
            this.channel = this.pusher.subscribe(`private-user.${this.userId}`);
            
            // Connection event handlers
            this.pusher.connection.bind('connected', () => {
                this.isConnected = true;
                this.reconnectAttempts = 0;
                console.log('Connected to real-time notifications');
                this.showConnectionStatus('connected');
            });

            this.pusher.connection.bind('disconnected', () => {
                this.isConnected = false;
                console.log('Disconnected from real-time notifications');
                this.showConnectionStatus('disconnected');
                this.attemptReconnect();
            });

            this.pusher.connection.bind('error', (error) => {
                console.error('Pusher connection error:', error);
                this.showConnectionStatus('error');
            });

        } catch (error) {
            console.error('Failed to initialize Pusher:', error);
        }
    }

    /**
     * Setup event listeners for different notification types
     */
    setupEventListeners() {
        if (!this.channel) return;

        // Task status changed event
        this.channel.bind('task.status.changed', (data) => {
            this.handleTaskStatusChanged(data);
        });

        // Task overdue event
        this.channel.bind('task.overdue', (data) => {
            this.handleTaskOverdue(data);
        });

        // Dashboard updated event
        this.channel.bind('dashboard.updated', (data) => {
            this.handleDashboardUpdated(data);
        });
    }

    /**
     * Handle task status change notifications
     */
    handleTaskStatusChanged(data) {
        console.log('Task status changed:', data);
        
        // Update badges
        this.updateNotificationBadges();
        
        // Show toast notification
        this.showToast(
            'Tâche mise à jour',
            `La tâche "${data.task.title}" est maintenant ${this.getStatusText(data.new_status)}`,
            this.getStatusColor(data.new_status)
        );

        // Update task in Kanban view if present
        this.updateKanbanTask(data.task);
        
        // Trigger custom event for other components
        this.dispatchCustomEvent('taskStatusChanged', data);
    }

    /**
     * Handle task overdue notifications
     */
    handleTaskOverdue(data) {
        console.log('Task overdue:', data);
        
        // Update badges with overdue count
        this.updateNotificationBadges();
        
        // Show critical alert for overdue task
        this.showCriticalAlert(
            'Tâche en retard !',
            data.message,
            data.task
        );

        // Show browser notification if permitted
        this.showBrowserNotification(
            'Tâche en retard',
            data.message,
            'warning'
        );
        
        // Trigger custom event
        this.dispatchCustomEvent('taskOverdue', data);
    }

    /**
     * Handle dashboard update notifications
     */
    handleDashboardUpdated(data) {
        console.log('Dashboard updated:', data);
        
        // Update dashboard statistics
        this.updateDashboardStats(data.stats);
        
        // Update notification badges
        this.updateNotificationBadges();
        
        // Trigger custom event
        this.dispatchCustomEvent('dashboardUpdated', data);
    }

    /**
     * Update notification badges in real-time
     */
    updateNotificationBadges() {
        // Fetch fresh badge data
        fetch('/api/notification-badges', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            this.renderBadges(data);
        })
        .catch(error => {
            console.error('Failed to update badges:', error);
        });
    }

    /**
     * Render updated badges
     */
    renderBadges(badgeData) {
        // Update main notification badge
        const mainBadge = document.querySelector('.notification-badge');
        if (mainBadge) {
            mainBadge.textContent = badgeData.total_count;
            
            // Add pulse animation if there are overdue tasks
            if (badgeData.overdue_count > 0) {
                mainBadge.classList.add('pulse-animation');
            } else {
                mainBadge.classList.remove('pulse-animation');
            }
        }

        // Update pending tasks badge
        const pendingBadge = document.querySelector('.pending-badge');
        if (pendingBadge) {
            pendingBadge.textContent = badgeData.pending_count;
        }

        // Update overdue tasks badge
        const overdueBadge = document.querySelector('.overdue-badge');
        if (overdueBadge) {
            overdueBadge.textContent = badgeData.overdue_count;
            overdueBadge.style.display = badgeData.overdue_count > 0 ? 'inline' : 'none';
        }

        // Update dropdown content
        this.updateDropdownContent(badgeData);
    }

    /**
     * Update notification dropdown content
     */
    updateDropdownContent(badgeData) {
        const dropdown = document.querySelector('.notification-dropdown-content');
        if (!dropdown) return;

        let html = '<li class="dropdown-header">Tâches non terminées</li>';
        
        // Add overdue tasks section if any
        if (badgeData.overdue_count > 0) {
            html += '<li class="dropdown-header text-danger">🚨 Tâches en retard</li>';
            badgeData.overdue_tasks.forEach(task => {
                html += `
                    <li>
                        <a class="dropdown-item text-danger" href="/tasks/${task.id}">
                            <strong>${task.title}</strong>
                            <br><small>En retard de ${task.overdue_minutes} minutes</small>
                        </a>
                    </li>
                `;
            });
            html += '<li><hr class="dropdown-divider"></li>';
        }

        // Add pending tasks
        if (badgeData.pending_count > 0) {
            html += '<li class="dropdown-header">📋 Tâches en attente</li>';
            badgeData.pending_tasks.forEach(task => {
                html += `
                    <li>
                        <a class="dropdown-item" href="/tasks/${task.id}">
                            ${task.title}
                            ${task.due_date ? `<br><small>Échéance: ${task.due_date}</small>` : ''}
                        </a>
                    </li>
                `;
            });
        }

        if (badgeData.total_count === 0) {
            html += '<li class="dropdown-item text-muted">Aucune tâche en attente</li>';
        }

        dropdown.innerHTML = html;
    }

    /**
     * Show toast notification
     */
    showToast(title, message, type = 'info') {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <strong>${title}</strong><br>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        // Add to toast container
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            document.body.appendChild(container);
        }

        container.appendChild(toast);

        // Show toast
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        // Remove after hiding
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }

    /**
     * Show critical alert for overdue tasks
     */
    showCriticalAlert(title, message, task) {
        // Create modal for critical alerts
        const alertModal = document.createElement('div');
        alertModal.className = 'modal fade';
        alertModal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-danger">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">🚨 ${title}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>${message}</p>
                        <p><strong>Tâche:</strong> ${task.title}</p>
                        <p><strong>Priorité:</strong> ${this.getPriorityText(task.priority)}</p>
                    </div>
                    <div class="modal-footer">
                        <a href="/tasks/${task.id}" class="btn btn-primary">Voir la tâche</a>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(alertModal);
        const modal = new bootstrap.Modal(alertModal);
        modal.show();

        // Remove modal after hiding
        alertModal.addEventListener('hidden.bs.modal', () => {
            alertModal.remove();
        });
    }

    /**
     * Show browser notification
     */
    showBrowserNotification(title, message, type = 'info') {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(title, {
                body: message,
                icon: '/favicon.ico',
                tag: 'task-notification'
            });
        }
    }

    /**
     * Request browser notification permission
     */
    requestNotificationPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }

    /**
     * Utility methods
     */
    getStatusText(status) {
        const statusMap = {
            'to_do': 'à faire',
            'in_progress': 'en cours',
            'completed': 'terminée'
        };
        return statusMap[status] || status;
    }

    getStatusColor(status) {
        const colorMap = {
            'to_do': 'primary',
            'in_progress': 'warning',
            'completed': 'success'
        };
        return colorMap[status] || 'secondary';
    }

    getPriorityText(priority) {
        const priorityMap = {
            'low': 'Faible',
            'medium': 'Moyenne',
            'high': 'Haute'
        };
        return priorityMap[priority] || priority;
    }

    /**
     * Show connection status
     */
    showConnectionStatus(status) {
        const indicator = document.querySelector('.connection-indicator');
        if (indicator) {
            indicator.className = `connection-indicator ${status}`;
            indicator.title = status === 'connected' ? 'Connecté' : 
                            status === 'disconnected' ? 'Déconnecté' : 'Erreur de connexion';
        }
    }

    /**
     * Attempt to reconnect
     */
    attemptReconnect() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            setTimeout(() => {
                console.log(`Attempting to reconnect... (${this.reconnectAttempts}/${this.maxReconnectAttempts})`);
                this.pusher.connect();
            }, 2000 * this.reconnectAttempts);
        }
    }

    /**
     * Dispatch custom events for other components
     */
    dispatchCustomEvent(eventName, data) {
        const event = new CustomEvent(eventName, { detail: data });
        document.dispatchEvent(event);
    }

    /**
     * Update Kanban task if present
     */
    updateKanbanTask(task) {
        const taskElement = document.querySelector(`[data-id="${task.id}"]`);
        if (taskElement) {
            // Move task to appropriate column
            const targetColumn = document.querySelector(`#${task.status}`);
            if (targetColumn) {
                targetColumn.appendChild(taskElement);
            }
        }
    }

    /**
     * Update dashboard statistics
     */
    updateDashboardStats(stats) {
        // Update various dashboard elements
        Object.keys(stats).forEach(key => {
            const element = document.querySelector(`[data-stat="${key}"]`);
            if (element) {
                element.textContent = stats[key];
            }
        });
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if Pusher config is available
    if (window.pusherConfig) {
        window.realTimeNotifications = new RealTimeNotifications();
        
        // Request notification permission
        window.realTimeNotifications.requestNotificationPermission();
    }
});
