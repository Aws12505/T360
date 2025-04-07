// If this file doesn't already exist in your project
import { ref, computed } from 'vue';

interface Toast {
  id: string;
  title?: string;
  description?: string;
  action?: {
    label: string;
    onClick: () => void;
  };
  duration?: number;
}

const toasts = ref<Toast[]>([]);

export function useToast() {
  const toast = (props: Omit<Toast, 'id'>) => {
    const id = Math.random().toString(36).substring(2, 9);
    const newToast = {
      id,
      ...props,
      duration: props.duration || 5000,
    };
    
    toasts.value = [...toasts.value, newToast];
    
    setTimeout(() => {
      dismissToast(id);
    }, newToast.duration);
    
    return id;
  };
  
  const dismissToast = (id: string) => {
    toasts.value = toasts.value.filter(t => t.id !== id);
  };
  
  return {
    toasts: computed(() => toasts.value),
    toast,
    dismissToast,
  };
}