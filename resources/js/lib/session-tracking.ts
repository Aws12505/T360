import { trackEvent } from './tracking';

/**
 * Initialize session tracking
 * Should be called when the application starts or user logs in
 */
export function initializeSession(): void {
  // Initialize dataLayer
  (window as any).dataLayer = (window as any).dataLayer || [];
  
  // Store session start time
  localStorage.setItem('sessionStart', Date.now().toString());
  
  // Track session start event
  trackEvent('sessionStart', {
    timestamp: new Date().toISOString(),
    userAgent: navigator.userAgent,
    screenWidth: window.innerWidth,
    screenHeight: window.innerHeight
  });
}

