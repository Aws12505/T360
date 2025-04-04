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



