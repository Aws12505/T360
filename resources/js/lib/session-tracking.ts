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

/**
 * Track user login event
 * @param userId User identifier (optional)
 */
export function trackLogin(userId?: string | number): void {
  // Initialize session if not already done
  if (!localStorage.getItem('sessionStart')) {
    initializeSession();
  }
  
  // Track login event
  trackEvent('userLogin', {
    timestamp: new Date().toISOString(),
    userId: userId || 'anonymous'
  });
}