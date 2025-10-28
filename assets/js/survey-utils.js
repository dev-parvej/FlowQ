/**
 * WP Dynamic Survey JavaScript Utilities
 * Supporting functions and classes for enhanced survey functionality
 *
 * @package FlowQ
 * @version 2.0.0
 */

(function(window) {
    'use strict';

    /**
     * Survey Utilities Namespace
     */
    window.WPDynamicSurveyUtils = {

        /**
         * DOM Utilities
         */
        dom: {
            /**
             * Create element with attributes and content
             */
            createElement(tag, attributes = {}, content = '') {
                const element = document.createElement(tag);

                Object.keys(attributes).forEach(key => {
                    if (key === 'data') {
                        Object.keys(attributes[key]).forEach(dataKey => {
                            element.dataset[dataKey] = attributes[key][dataKey];
                        });
                    } else if (key === 'style' && typeof attributes[key] === 'object') {
                        Object.assign(element.style, attributes[key]);
                    } else {
                        element.setAttribute(key, attributes[key]);
                    }
                });

                if (content) {
                    if (typeof content === 'string') {
                        element.innerHTML = content;
                    } else {
                        element.appendChild(content);
                    }
                }

                return element;
            },

            /**
             * Check if element is in viewport
             */
            isInViewport(element) {
                const rect = element.getBoundingClientRect();
                return (
                    rect.top >= 0 &&
                    rect.left >= 0 &&
                    rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                    rect.right <= (window.innerWidth || document.documentElement.clientWidth)
                );
            },

            /**
             * Smooth scroll to element
             */
            scrollToElement(element, offset = 0) {
                const elementPosition = element.getBoundingClientRect().top + window.pageYOffset;
                const offsetPosition = elementPosition - offset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            },

            /**
             * Get all focusable elements within container
             */
            getFocusableElements(container) {
                const focusableSelectors = [
                    'a[href]',
                    'button:not([disabled])',
                    'input:not([disabled]):not([type="hidden"])',
                    'select:not([disabled])',
                    'textarea:not([disabled])',
                    '[tabindex]:not([tabindex="-1"])',
                    '[contenteditable="true"]'
                ].join(', ');

                return Array.from(container.querySelectorAll(focusableSelectors))
                    .filter(element => {
                        return !element.hasAttribute('disabled') &&
                               element.offsetParent !== null &&
                               window.getComputedStyle(element).visibility !== 'hidden';
                    });
            }
        },

        /**
         * Form Utilities
         */
        form: {
            /**
             * Serialize form to object
             */
            serialize(form) {
                const formData = new FormData(form);
                const data = {};

                for (let [key, value] of formData.entries()) {
                    if (data[key]) {
                        if (Array.isArray(data[key])) {
                            data[key].push(value);
                        } else {
                            data[key] = [data[key], value];
                        }
                    } else {
                        data[key] = value;
                    }
                }

                return data;
            },

            /**
             * Validate form field
             */
            validateField(field) {
                const errors = [];
                const value = field.value.trim();
                const type = field.type;
                const required = field.hasAttribute('required');

                // Required validation
                if (required && !value) {
                    errors.push(`${this.getFieldLabel(field)} is required`);
                    return { isValid: false, errors };
                }

                // Skip other validations if field is empty and not required
                if (!value && !required) {
                    return { isValid: true, errors: [] };
                }

                // Type-specific validation
                switch (type) {
                    case 'email':
                        if (!this.isValidEmail(value)) {
                            errors.push('Please enter a valid email address');
                        }
                        break;

                    case 'tel':
                        if (!this.isValidPhone(value)) {
                            errors.push('Please enter a valid phone number');
                        }
                        break;

                    case 'url':
                        if (!this.isValidUrl(value)) {
                            errors.push('Please enter a valid URL');
                        }
                        break;

                    case 'number':
                        const min = field.getAttribute('min');
                        const max = field.getAttribute('max');
                        const num = parseFloat(value);

                        if (isNaN(num)) {
                            errors.push('Please enter a valid number');
                        } else {
                            if (min !== null && num < parseFloat(min)) {
                                errors.push(`Value must be at least ${min}`);
                            }
                            if (max !== null && num > parseFloat(max)) {
                                errors.push(`Value must be no more than ${max}`);
                            }
                        }
                        break;
                }

                // Length validation
                const minLength = field.getAttribute('minlength');
                const maxLength = field.getAttribute('maxlength');

                if (minLength && value.length < parseInt(minLength)) {
                    errors.push(`Must be at least ${minLength} characters long`);
                }

                if (maxLength && value.length > parseInt(maxLength)) {
                    errors.push(`Must be no more than ${maxLength} characters long`);
                }

                // Pattern validation
                const pattern = field.getAttribute('pattern');
                if (pattern && !new RegExp(pattern).test(value)) {
                    errors.push(field.getAttribute('title') || 'Invalid format');
                }

                return {
                    isValid: errors.length === 0,
                    errors
                };
            },

            /**
             * Get field label
             */
            getFieldLabel(field) {
                const label = document.querySelector(`label[for="${field.id}"]`);
                if (label) {
                    return label.textContent.trim().replace('*', '');
                }

                return field.getAttribute('placeholder') ||
                       field.getAttribute('aria-label') ||
                       field.name ||
                       'Field';
            },

            /**
             * Show field error
             */
            showFieldError(field, message) {
                this.clearFieldError(field);

                field.classList.add('error');
                field.setAttribute('aria-invalid', 'true');

                const errorElement = this.dom.createElement('div', {
                    class: 'field-error',
                    role: 'alert',
                    'aria-live': 'polite'
                }, message);

                field.parentNode.appendChild(errorElement);
            },

            /**
             * Clear field error
             */
            clearFieldError(field) {
                field.classList.remove('error');
                field.removeAttribute('aria-invalid');

                const existingError = field.parentNode.querySelector('.field-error');
                if (existingError) {
                    existingError.remove();
                }
            },

            /**
             * Validation helpers
             */
            isValidEmail(email) {
                const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return pattern.test(email);
            },

            isValidPhone(phone) {
                const pattern = /^[\+]?[1-9][\d]{0,15}$/;
                const cleaned = phone.replace(/[\s\-\(\)\.]/g, '');
                return pattern.test(cleaned) && cleaned.length >= 10;
            },

            isValidUrl(url) {
                try {
                    new URL(url);
                    return true;
                } catch {
                    return false;
                }
            }
        },

        /**
         * Animation Utilities
         */
        animation: {
           
        },

        /**
         * Data Utilities
         */
        data: {
            /**
             * Deep clone object
             */
            deepClone(obj) {
                if (obj === null || typeof obj !== 'object') {
                    return obj;
                }

                if (obj instanceof Date) {
                    return new Date(obj.getTime());
                }

                if (obj instanceof Array) {
                    return obj.map(item => this.deepClone(item));
                }

                if (typeof obj === 'object') {
                    const cloned = {};
                    Object.keys(obj).forEach(key => {
                        cloned[key] = this.deepClone(obj[key]);
                    });
                    return cloned;
                }

                return obj;
            },

            /**
             * Deep merge objects
             */
            deepMerge(target, source) {
                const result = { ...target };

                Object.keys(source).forEach(key => {
                    if (source[key] && typeof source[key] === 'object' && !Array.isArray(source[key])) {
                        result[key] = this.deepMerge(result[key] || {}, source[key]);
                    } else {
                        result[key] = source[key];
                    }
                });

                return result;
            },

            /**
             * Generate unique ID
             */
            generateId(prefix = 'id') {
                return `${prefix}_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
            },

            /**
             * Format time duration
             */
            formatDuration(ms) {
                const seconds = Math.floor(ms / 1000);
                const minutes = Math.floor(seconds / 60);
                const hours = Math.floor(minutes / 60);

                if (hours > 0) {
                    return `${hours}h ${minutes % 60}m ${seconds % 60}s`;
                } else if (minutes > 0) {
                    return `${minutes}m ${seconds % 60}s`;
                } else {
                    return `${seconds}s`;
                }
            },

            /**
             * Format file size
             */
            formatFileSize(bytes) {
                const units = ['B', 'KB', 'MB', 'GB'];
                let size = bytes;
                let unitIndex = 0;

                while (size >= 1024 && unitIndex < units.length - 1) {
                    size /= 1024;
                    unitIndex++;
                }

                return `${size.toFixed(1)} ${units[unitIndex]}`;
            },

            /**
             * Debounce function
             */
            debounce(func, wait, immediate = false) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        timeout = null;
                        if (!immediate) func(...args);
                    };
                    const callNow = immediate && !timeout;
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                    if (callNow) func(...args);
                };
            },

            /**
             * Throttle function
             */
            throttle(func, limit) {
                let inThrottle;
                return function(...args) {
                    if (!inThrottle) {
                        func.apply(this, args);
                        inThrottle = true;
                        setTimeout(() => inThrottle = false, limit);
                    }
                };
            }
        },

        /**
         * Accessibility Utilities
         */
        a11y: {
            /**
             * Set focus with announcement
             */
            setFocusWithAnnouncement(element, announcement) {
                element.focus();
                if (announcement) {
                    this.announce(announcement);
                }
            },

            /**
             * Announce to screen readers
             */
            announce(message, priority = 'polite') {
                const announcer = this.getAnnouncer(priority);
                announcer.textContent = message;

                // Clear after announcement
                setTimeout(() => {
                    announcer.textContent = '';
                }, 1000);
            },

            /**
             * Get or create screen reader announcer
             */
            getAnnouncer(priority = 'polite') {
                const id = `announcer-${priority}`;
                let announcer = document.getElementById(id);

                if (!announcer) {
                    announcer = document.createElement('div');
                    announcer.id = id;
                    announcer.className = 'sr-only';
                    announcer.setAttribute('aria-live', priority);
                    announcer.setAttribute('aria-atomic', 'true');
                    document.body.appendChild(announcer);
                }

                return announcer;
            },

            /**
             * Trap focus within container
             */
            trapFocus(container) {
                const focusableElements = WPDynamicSurveyUtils.dom.getFocusableElements(container);

                if (focusableElements.length === 0) return;

                const firstElement = focusableElements[0];
                const lastElement = focusableElements[focusableElements.length - 1];

                const handleTabKey = (e) => {
                    if (e.key !== 'Tab') return;

                    if (e.shiftKey) {
                        if (document.activeElement === firstElement) {
                            e.preventDefault();
                            lastElement.focus();
                        }
                    } else {
                        if (document.activeElement === lastElement) {
                            e.preventDefault();
                            firstElement.focus();
                        }
                    }
                };

                container.addEventListener('keydown', handleTabKey);

                // Return cleanup function
                return () => {
                    container.removeEventListener('keydown', handleTabKey);
                };
            },

            /**
             * Add ARIA attributes for better accessibility
             */
            enhanceAccessibility(element, options = {}) {
                const {
                    role,
                    label,
                    describedBy,
                    expanded,
                    pressed,
                    selected,
                    hidden
                } = options;

                if (role) element.setAttribute('role', role);
                if (label) element.setAttribute('aria-label', label);
                if (describedBy) element.setAttribute('aria-describedby', describedBy);
                if (expanded !== undefined) element.setAttribute('aria-expanded', expanded);
                if (pressed !== undefined) element.setAttribute('aria-pressed', pressed);
                if (selected !== undefined) element.setAttribute('aria-selected', selected);
                if (hidden !== undefined) element.setAttribute('aria-hidden', hidden);
            }
        },

        /**
         * Storage Utilities
         */
        storage: {
            /**
             * Check if storage is available
             */
            isAvailable(type = 'localStorage') {
                try {
                    const storage = window[type];
                    const testKey = '__storage_test__';
                    storage.setItem(testKey, 'test');
                    storage.removeItem(testKey);
                    return true;
                } catch (error) {
                    return false;
                }
            },

            /**
             * Set item with expiration
             */
            setWithExpiry(key, value, ttl) {
                const now = Date.now();
                const item = {
                    value: value,
                    expiry: now + ttl
                };
                localStorage.setItem(key, JSON.stringify(item));
            },

            /**
             * Get item with expiration check
             */
            getWithExpiry(key) {
                const itemStr = localStorage.getItem(key);
                if (!itemStr) return null;

                try {
                    const item = JSON.parse(itemStr);
                    const now = Date.now();

                    if (now > item.expiry) {
                        localStorage.removeItem(key);
                        return null;
                    }

                    return item.value;
                } catch (error) {
                    localStorage.removeItem(key);
                    return null;
                }
            },

            /**
             * Clear expired items
             */
            clearExpired() {
                const keys = Object.keys(localStorage);
                const now = Date.now();

                keys.forEach(key => {
                    try {
                        const itemStr = localStorage.getItem(key);
                        const item = JSON.parse(itemStr);

                        if (item.expiry && now > item.expiry) {
                            localStorage.removeItem(key);
                        }
                    } catch (error) {
                        // Not a valid expiring item, ignore
                    }
                });
            }
        },

        /**
         * Network Utilities
         */
        network: {
            /**
             * Check if online
             */
            isOnline() {
                return navigator.onLine;
            },
        },

        /**
         * Performance Utilities
         */
        performance: {
        }
    };

    // Add CSS for screen reader only text
    if (!document.querySelector('#survey-utils-styles')) {
        const style = document.createElement('style');
        style.id = 'survey-utils-styles';
        style.textContent = `
            .sr-only {
                position: absolute !important;
                width: 1px !important;
                height: 1px !important;
                padding: 0 !important;
                margin: -1px !important;
                overflow: hidden !important;
                clip: rect(0, 0, 0, 0) !important;
                white-space: nowrap !important;
                border: 0 !important;
            }

            .field-error {
                color: #d63638;
                font-size: 14px;
                margin-top: 4px;
                display: block;
            }

            .error {
                border-color: #d63638 !important;
                box-shadow: 0 0 0 1px #d63638 !important;
            }
        `;
        document.head.appendChild(style);
    }

})(window);