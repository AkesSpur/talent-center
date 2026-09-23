import './bootstrap';

import Alpine from 'alpinejs';
import { registerAppShell } from './app-shell';
import { registerSupportUpload } from './support-upload';

window.Alpine = Alpine;

registerAppShell(Alpine);
registerSupportUpload(Alpine);

Alpine.start();
