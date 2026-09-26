import './bootstrap';

import Alpine from 'alpinejs';
import { registerAppShell } from './app-shell';
import { registerSupportUpload } from './support-upload';
import { registerSupportAnalytics } from './support-analytics';

window.Alpine = Alpine;

registerAppShell(Alpine);
registerSupportUpload(Alpine);
registerSupportAnalytics(Alpine);

Alpine.start();
