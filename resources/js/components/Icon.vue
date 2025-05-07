<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';
import type { Component } from 'vue';

import {
  Truck,
  User,
  Users,
  UserCog,
  Settings,
  Home,
  Edit,
  Sun,
  Moon,
  Monitor,
  BookOpen,
  Folder,
  LayoutGrid,
  Menu,
  Search,
  BarChart,
  ShieldCheck,
  CheckCircle,
  Clock,
  ClipboardList,
  LineChart,
  Gauge,
  Trash,
  RefreshCw,
  Plus,
  Pencil,
  RotateCcw,
  Download,
  Upload,
  Calendar,
  X,
  Undo,
  Lock,
  Unlock,
  Eye,
  Send,
  TicketIcon,
  MessageCircle,
  HelpCircle,
  TriangleAlert,
  ShieldAlert,
  // Add these new icons
} from 'lucide-vue-next';

// Map of icon names to components
export type IconName =
  | 'truck'
  | 'user'
  | 'users'
  | 'userCog'
  | 'settings'
  | 'home'
  | 'edit'
  | 'sun'
  | 'moon'
  | 'monitor'
  | 'bookOpen'
  | 'folder'
  | 'layoutGrid'
  | 'menu'
  | 'search'
  | 'barChart'
  | 'shieldCheck'
  | 'checkCircle'
  | 'clock'
  | 'clipboardList'
  | 'lineChart'
  | 'gauge'
  | 'trash'
  |'refresh'
  |'plus'
  | 'pencil'
  | 'rotate_ccw'
  | 'download'
  | 'upload'
  | 'calendar'
  | 'x'
  | 'undo'
  | 'lock'
  | 'unlock'
  | 'eye'
  | 'send'
  | 'ticket'
  | 'messageCircle'
  | 'helpCircle'
  | 'triangleAlert'
  | 'shieldAlert';

const ICONS: Record<IconName, Component> = {
  truck: Truck,
  user: User,
  users: Users,
  userCog: UserCog,
  settings: Settings,
  home: Home,
  edit: Edit,
  sun: Sun,
  moon: Moon,
  monitor: Monitor,
  bookOpen: BookOpen,
  folder: Folder,
  layoutGrid: LayoutGrid,
  menu: Menu,
  search: Search,
  barChart: BarChart,
  shieldCheck: ShieldCheck,
  checkCircle: CheckCircle,
  clock: Clock,
  clipboardList: ClipboardList,
  lineChart: LineChart,
  gauge: Gauge,
  trash: Trash,
  refresh: RefreshCw,
  plus: Plus,
  pencil: Pencil,
  rotate_ccw: RotateCcw,
  download: Download,
  upload: Upload,
  calendar: Calendar,
  x: X,
  undo: Undo,
  lock: Lock,
  unlock: Unlock,
  eye: Eye,
  send: Send,
  ticket: TicketIcon,
  messageCircle: MessageCircle,
  helpCircle: HelpCircle,
  triangleAlert: TriangleAlert,
  shieldAlert: ShieldAlert,
};

// 3. Define props, with defaults for optional values:
interface Props {
  name: IconName;
  class?: string;
  size?: number | string;
  color?: string;
  strokeWidth?: number | string;
}
const props = withDefaults(defineProps<Props>(), {
  class: '',
  size: 16,
  strokeWidth: 2,
});

// 4. Compute combined class names:
const className = computed(() => cn('h-4 w-4', props.class));

// 5. Lookup the actual icon component based on the name prop:
const IconComponent = computed(() => ICONS[props.name]);
</script>

<template>
  <component
    :is="IconComponent"
    :class="className"
    :size="props.size"
    :stroke-width="props.strokeWidth"
    :color="props.color"
  />
</template>
