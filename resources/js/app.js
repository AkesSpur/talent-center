import './bootstrap';

import Alpine from 'alpinejs';
import { registerAppShell } from './app-shell';
import { registerSupportUpload } from './support-upload';
import { registerSupportAnalytics } from './support-analytics';
import { registerKbEditor, registerArticlePicker } from './kb-editor';

window.Alpine = Alpine;

registerAppShell(Alpine);
registerSupportUpload(Alpine);
registerSupportAnalytics(Alpine);
registerKbEditor(Alpine);
registerArticlePicker(Alpine);

Alpine.start();
