/**
 * Participants Page JavaScript
 * @package FlowQ
 */

(function() {
    'use strict';

    window.toggleAccordion = 
function toggleAccordion(index) {
    var content = document.getElementById('participant-content-' + index);
    var arrow = content.parentElement.querySelector('.accordion-arrow');

    if (content.style.display === 'none' || content.style.display === '') {
        content.style.display = 'block';
        arrow.classList.add('expanded');

        // Add smooth animation
        content.style.maxHeight = content.scrollHeight + 'px';

        // Add entrance animation for cards
        setTimeout(function() {
            content.style.opacity = '1';
        }, 50);
    } else {
        content.style.display = 'none';
        arrow.classList.remove('expanded');
        content.style.maxHeight = '0';
        content.style.opacity = '0';
    }
}

// Enhanced page load animation
function initializeAnimations() {
    var cards = document.querySelectorAll('.participant-card');
    cards.forEach(function(card, index) {
        card.style.setProperty('--nth-child', index);
    });
}

// Initialize animations when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeAnimations();
    initializeResponseAnimations();
    convertToLocalTime();

    // Update relative timestamps every minute
    setInterval(convertToLocalTime, 0);
});

// Enhanced time conversion with relative timestamps
function convertToLocalTime() {
    var timeElements = document.querySelectorAll('.local-time[data-utc]');

    timeElements.forEach(function(element) {
        var utcTime = element.getAttribute('data-utc');
        if (utcTime) {
            try {
                // Parse the UTC time and convert to local
                var date = new Date(utcTime + ' UTC');
                var now = new Date();
                var timeDiff = now - date;

                // Check if this is in response timestamp (use relative time)
                if (element.closest('.response-timestamp')) {
                    var relativeTime = getRelativeTime(timeDiff);
                    element.textContent = relativeTime;

                    // Add full timestamp as title
                    var fullTime = date.toLocaleString(undefined, {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    });
                    element.title = fullTime + ' (UTC: ' + utcTime + ')';
                } else {
                    // Regular timestamp formatting for other elements
                    var options = {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    };

                    // Check if this is just a time (no date needed)
                    if (element.closest('.response-time')) {
                        options = {
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: true
                        };
                    }

                    var localTimeString = date.toLocaleString(undefined, options);
                    element.textContent = localTimeString;

                    // Add timezone info as title
                    element.title = 'Your local time (UTC time: ' + utcTime + ')';
                }
            } catch (e) {
                console.warn('Could not parse time:', utcTime);
            }
        }
    });
}

// Helper function to get relative time
function getRelativeTime(timeDiff) {
    var seconds = Math.floor(timeDiff / 1000);
    var minutes = Math.floor(seconds / 60);
    var hours = Math.floor(minutes / 60);
    var days = Math.floor(hours / 24);

    if (seconds < 60) {
        return 'Just now';
    } else if (minutes < 60) {
        return minutes + 'm ago';
    } else if (hours < 24) {
        return hours + 'h ago';
    } else if (days < 7) {
        return days + 'd ago';
    } else {
        return Math.floor(days / 7) + 'w ago';
    }
}

// Enhanced response card animations (disabled)
function initializeResponseAnimations() {
    // Animation disabled for Survey Responses
}

// Run conversion when page loads
document.addEventListener('DOMContentLoaded', convertToLocalTime);

// Handle per-page change
function changePerPage(value) {
    var currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('per_page', value);
    currentUrl.searchParams.delete('paged'); // Reset to first page when changing per-page
    window.location.href = currentUrl.toString();
}



})();
