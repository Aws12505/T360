/**
 * Tracking module for Matomo Tag Manager integration
 * Provides helper functions to push tracking events to the dataLayer
 */

export interface TrackingEvent {
  event: string;
  [key: string]: any;
}

/**
 * Helper to push events to the dataLayer.
 * @param eventName The name of the event to track
 * @param data Additional data to include with the event
 */
export function trackEvent(eventName: string, data: Record<string, any> = {}): void {
  try {
    // Ensure the dataLayer is available.
    (window as any).dataLayer = (window as any).dataLayer || [];
    
    // Add common tracking properties
    const eventData = {
      event: eventName,
      page: window.location.pathname,
      timestamp: new Date().toISOString(),
      ...data
    };
    
    // Push to dataLayer
    (window as any).dataLayer.push(eventData);
    
    // Debug logging in development
    if (process.env.NODE_ENV === 'development') {
      console.debug(`[Tracking] ${eventName}`, eventData);
    }
  } catch (error) {
    // Silent fail in production, log in development
    if (process.env.NODE_ENV === 'development') {
      console.error(`[Tracking Error] Failed to track ${eventName}:`, error);
    }
  }
}

/**
 * Track a page view event
 * @param pageName Optional page name (defaults to current URL path)
 */
export function trackPageView(pageName?: string): void {
  try {
    const pageTitle = pageName || document.title;
    const pageUrl = window.location.pathname + window.location.search;
    
    trackEvent('pageView', {
      page: pageUrl,
      title: pageTitle,
      referrer: document.referrer,
      screenWidth: window.innerWidth,
      screenHeight: window.innerHeight,
      timestamp: new Date().toISOString()
    });
  } catch (error) {
    // Silent fail in production, log in development
    if (process.env.NODE_ENV === 'development') {
      console.error('[Tracking Error] Failed to track page view:', error);
    }
  }
}

/**
 * Track a user interaction event
 * @param action The action performed (e.g., 'click', 'submit')
 * @param category The category of the interaction (e.g., 'button', 'form')
 * @param label Optional label for the interaction
 * @param value Optional numeric value associated with the interaction
 */
export function trackInteraction(
  action: string,
  category: string,
  label?: string,
  value?: number
): void {
  trackEvent('interaction', {
    action,
    category,
    label,
    value
  });
}