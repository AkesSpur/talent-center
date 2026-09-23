/**
 * Helpdesk attachments: the upload picker on the ticket and reply forms, and
 * the image lightbox in the conversation.
 *
 * The forms still work as plain multipart posts without JS. With JS they are
 * sent over XHR, so the page can show real upload progress, offer a cancel,
 * and keep everything the user typed when the server refuses something.
 */

// Shown as thumbnails; mirrors SupportAttachmentService::PREVIEWABLE_MIMES.
const PREVIEWABLE = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

const ICONS = {
    pdf: 'fa-file-pdf',
    doc: 'fa-file-word',
    docx: 'fa-file-word',
    xls: 'fa-file-excel',
    xlsx: 'fa-file-excel',
    zip: 'fa-file-zipper',
    txt: 'fa-file-lines',
};

/** «49 Б», «93 КБ», «3,8 МБ» — the same rules as SupportAttachmentService::formatBytes(). */
export function formatBytes(bytes) {
    if (bytes < 1024) {
        return `${bytes} Б`;
    }
    const kb = bytes / 1024;
    if (kb < 1023.5) {
        return `${Math.max(1, Math.round(kb))} КБ`;
    }
    const mb = (Math.round((kb / 1024) * 10) / 10).toFixed(1).replace(/\.0$/, '').replace('.', ',');
    return `${mb} МБ`;
}

function formatDuration(seconds) {
    const total = Math.max(1, Math.round(seconds));
    if (total < 60) {
        return `${total} с`;
    }
    const minutes = Math.floor(total / 60);
    const rest = total % 60;
    return rest ? `${minutes} мин ${rest} с` : `${minutes} мин`;
}

function extensionOf(name) {
    const dot = name.lastIndexOf('.');
    return dot > 0 ? name.slice(dot + 1).toLowerCase() : '';
}

const carriesFiles = (event) => Array.from(event.dataTransfer?.types ?? []).includes('Files');

// Browsers call every pasted screenshot "image.png"; give it a readable name.
function namePasted(file, number) {
    const extension = extensionOf(file.name) || file.type.split('/')[1] || 'png';
    const stamp = new Date()
        .toLocaleString('ru-RU', { dateStyle: 'short', timeStyle: 'medium' })
        .replace(',', '')
        .replaceAll(':', '-');
    const suffix = number ? ` (${number})` : '';
    return new File([file], `Снимок экрана ${stamp}${suffix}.${extension}`, { type: file.type, lastModified: Date.now() });
}

// A reply lands on the same page with a new #fragment, and assign() alone would only scroll.
function goTo(url) {
    const target = new URL(url, window.location.href);
    const here = new URL(window.location.href);

    if (target.origin === here.origin && target.pathname === here.pathname && target.search === here.search) {
        history.scrollRestoration = 'manual';
        history.replaceState(history.state, '', target.href);
        window.location.reload();
        return;
    }

    window.location.assign(target.href);
}

// Russian text for every failure that isn't a field error. Only 409 (our own refusals) uses the server's words.
function failureText(xhr, body) {
    switch (xhr.status) {
        case 0:
            return 'Нет связи с сервером. Проверьте интернет и отправьте ещё раз — текст и файлы остались в форме.';
        case 401:
            return 'Вы вышли из аккаунта. Скопируйте текст, войдите снова и отправьте его ещё раз.';
        case 403:
            return 'Нет доступа к этой заявке.';
        case 404:
            return 'Заявка не найдена — возможно, её удалили.';
        case 409:
            return body?.message || 'Отправить не получилось — обновите страницу.';
        case 413:
            return 'Сервер не принял такой объём: уберите часть файлов или выберите файлы поменьше. Текст остался в форме.';
        case 419:
            return 'Страница была открыта слишком долго, и сессия истекла. Скопируйте текст, обновите страницу и отправьте ещё раз.';
        case 429: {
            const wait = Number(xhr.getResponseHeader('Retry-After'));
            return wait > 0
                ? `Слишком много отправок подряд. Попробуйте снова через ${formatDuration(wait)}.`
                : 'Слишком много отправок подряд. Попробуйте чуть позже.';
        }
        default:
            return 'На сервере произошла ошибка. Попробуйте отправить ещё раз через минуту — текст и файлы остались в форме.';
    }
}

export function registerSupportUpload(Alpine) {
    Alpine.data('supportUpload', (config) => {
        // Kept out of Alpine's reactive state on purpose.
        let request = null;
        let startedAt = 0;
        let dragDepth = 0;
        let nextId = 1;

        // A file dropped beside the form must not open in the tab and wipe what was typed.
        const guardDrop = (event) => {
            if (!carriesFiles(event)) {
                return;
            }
            event.preventDefault();
            if (!event.target.closest?.('[data-upload-drop]')) {
                event.dataTransfer.dropEffect = 'none';
            }
        };
        const guardLeave = (event) => {
            event.preventDefault();
            event.returnValue = '';
        };

        return {
            items: [],
            notices: [],
            errors: {},
            formError: '',
            formNotice: '',
            sending: false,
            phase: '',
            progress: 0,
            loaded: 0,
            total: 0,
            speed: 0,
            dragging: false,

            init() {
                window.addEventListener('dragover', guardDrop);
                window.addEventListener('drop', guardDrop);
            },

            destroy() {
                window.removeEventListener('dragover', guardDrop);
                window.removeEventListener('drop', guardDrop);
                window.removeEventListener('beforeunload', guardLeave);
                this.items.forEach((item) => item.previewUrl && URL.revokeObjectURL(item.previewUrl));
            },

            get totalBytes() {
                return this.items.reduce((sum, item) => sum + item.size, 0);
            },

            get summary() {
                return `${this.items.length} из ${config.maxFiles} файлов · ${formatBytes(this.totalBytes)} из ${formatBytes(config.maxTotalBytes)}`;
            },

            get progressLabel() {
                if (this.phase === 'processing') {
                    return 'Файлы загружены, сохраняем…';
                }
                if (this.phase === 'done') {
                    return 'Готово, открываем…';
                }
                return `Загрузка файлов: ${this.progress} %`;
            },

            get progressDetail() {
                if (this.phase !== 'uploading' || !this.total) {
                    return '';
                }
                const amount = `${formatBytes(this.loaded)} из ${formatBytes(this.total)}`;
                return this.speed ? `${amount} · осталось ≈ ${formatDuration((this.total - this.loaded) / this.speed)}` : amount;
            },

            // ── Picking ───────────────────────────────

            openPicker() {
                if (!this.sending) {
                    this.$refs.fileInput.click();
                }
            },

            picked(event) {
                this.addFiles(event.target.files);
            },

            pasted(event) {
                const files = Array.from(event.clipboardData?.files ?? []);
                // Text — including rich text from Word, which can carry an image copy — pastes as usual.
                if (!files.length || event.clipboardData.getData('text/plain')) {
                    return;
                }
                event.preventDefault();
                this.addFiles(files.map((file, i) => namePasted(file, files.length > 1 ? i + 1 : 0)));
            },

            dragEnter(event) {
                if (carriesFiles(event) && !this.sending) {
                    dragDepth += 1;
                    this.dragging = true;
                }
            },

            dragLeave(event) {
                if (carriesFiles(event)) {
                    dragDepth = Math.max(0, dragDepth - 1);
                    this.dragging = dragDepth > 0;
                }
            },

            dropped(event) {
                dragDepth = 0;
                this.dragging = false;
                if (carriesFiles(event) && !this.sending) {
                    this.addFiles(event.dataTransfer.files);
                }
            },

            /** Adds what fits the limits; everything else is refused on the spot, before any upload. */
            addFiles(fileList) {
                if (this.sending) {
                    return;
                }

                const notices = [];
                const overCount = [];
                let total = this.totalBytes;

                for (const file of Array.from(fileList)) {
                    const duplicate = this.items.some((item) =>
                        item.name === file.name && item.size === file.size && item.file.lastModified === file.lastModified);
                    if (duplicate) {
                        continue;
                    }

                    const extension = extensionOf(file.name);

                    if (!config.extensions.includes(extension)) {
                        notices.push(`«${file.name}»: такой формат прикрепить нельзя. Подходят ${config.extensionsText}.`);
                    } else if (file.size === 0) {
                        notices.push(`«${file.name}» — пустой файл, прикреплять нечего.`);
                    } else if (file.size > config.maxFileBytes) {
                        notices.push(`«${file.name}» весит ${formatBytes(file.size)}, а можно до ${formatBytes(config.maxFileBytes)}. Выберите файл поменьше.`);
                    } else if (this.items.length >= config.maxFiles) {
                        overCount.push(`«${file.name}»`);
                    } else if (total + file.size > config.maxTotalBytes) {
                        notices.push(`«${file.name}» не добавлен: вместе с ним вложения превысят ${formatBytes(config.maxTotalBytes)}.`);
                    } else {
                        total += file.size;
                        this.items.push({
                            id: nextId++,
                            file,
                            name: file.name,
                            size: file.size,
                            sizeLabel: formatBytes(file.size),
                            icon: ICONS[extension] ?? 'fa-file-image',
                            previewUrl: PREVIEWABLE.includes(file.type) ? URL.createObjectURL(file) : null,
                            error: '',
                        });
                    }
                }

                if (overCount.length) {
                    notices.push(`Можно прикрепить не более ${config.maxFiles} файлов. Не добавлены: ${overCount.join(', ')}.`);
                }

                this.notices = notices;
                this.clearFileErrors();
                this.syncInput();
            },

            // Named .png but not really an image (the server will refuse it): show the plain icon instead.
            previewFailed(item) {
                if (item.previewUrl) {
                    URL.revokeObjectURL(item.previewUrl);
                    item.previewUrl = null;
                }
            },

            remove(id) {
                const index = this.items.findIndex((item) => item.id === id);
                if (this.sending || index === -1) {
                    return;
                }
                const [item] = this.items.splice(index, 1);
                if (item.previewUrl) {
                    URL.revokeObjectURL(item.previewUrl);
                }
                this.notices = [];
                this.clearFileErrors();
                this.syncInput();
                this.$nextTick(() => this.$refs.pickButton?.focus());
            },

            // The real <input> mirrors the list, so a post without the XHR path still carries every file.
            syncInput() {
                try {
                    const transfer = new DataTransfer();
                    this.items.forEach((item) => transfer.items.add(item.file));
                    this.$refs.fileInput.files = transfer.files;
                } catch {
                    /* older browser: submit() appends the files itself */
                }
            },

            clearFileErrors() {
                this.errors = Object.fromEntries(Object.entries(this.errors).filter(([key]) => !key.startsWith('files')));
            },

            fieldErrors(field) {
                return Object.entries(this.errors)
                    .filter(([key]) => key === field || key.startsWith(`${field}.`))
                    .flatMap(([, messages]) => messages);
            },

            // ── Sending ───────────────────────────────

            submit(event) {
                event.preventDefault();
                if (this.sending) {
                    return;
                }

                const form = event.target;
                const data = new FormData(form);
                data.delete('files[]');
                this.items.forEach((item) => data.append('files[]', item.file, item.name));
                if (event.submitter?.name) {
                    data.append(event.submitter.name, event.submitter.value);
                }

                this.errors = {};
                this.formError = '';
                this.formNotice = '';
                this.items.forEach((item) => {
                    item.error = '';
                });
                this.sending = true;
                this.phase = 'uploading';
                this.progress = 0;
                this.loaded = 0;
                this.total = 0;
                this.speed = 0;
                startedAt = performance.now();
                window.addEventListener('beforeunload', guardLeave);

                const xhr = new XMLHttpRequest();
                request = xhr;
                xhr.open('POST', form.action);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.upload.addEventListener('progress', (e) => this.uploadProgress(e));
                xhr.upload.addEventListener('load', () => {
                    this.phase = 'processing';
                    this.progress = 100;
                });
                xhr.addEventListener('load', () => this.finished(xhr));
                xhr.addEventListener('error', () => this.stopped(failureText(xhr, null)));
                xhr.addEventListener('abort', () => this.stopped('', 'Отправка отменена. Текст и файлы остались в форме — можно отправить ещё раз.'));
                xhr.send(data);
            },

            cancel() {
                request?.abort();
            },

            uploadProgress(event) {
                if (!event.lengthComputable || this.phase !== 'uploading') {
                    return;
                }
                this.loaded = event.loaded;
                this.total = event.total;
                // 100 % only once the server has everything (upload "load" event).
                this.progress = Math.min(99, Math.floor((event.loaded / event.total) * 100));
                const seconds = (performance.now() - startedAt) / 1000;
                if (seconds > 0.5) {
                    this.speed = event.loaded / seconds;
                }
            },

            finished(xhr) {
                let body = null;
                try {
                    body = JSON.parse(xhr.responseText);
                } catch {
                    /* an HTML error page, or a PHP warning printed ahead of the JSON */
                }

                if (xhr.status >= 200 && xhr.status < 300) {
                    this.phase = 'done';
                    this.progress = 100;
                    window.removeEventListener('beforeunload', guardLeave);
                    goTo(body?.redirect || xhr.responseURL || window.location.href);
                    return; // stays "sending" until the next page replaces this one
                }

                if (xhr.status === 422 && body?.errors) {
                    this.stopped('');
                    this.showErrors(body.errors);
                    return;
                }

                this.stopped(failureText(xhr, body));
            },

            stopped(error, notice = '') {
                window.removeEventListener('beforeunload', guardLeave);
                request = null;
                this.sending = false;
                this.phase = '';
                this.progress = 0;
                this.formError = error;
                this.formNotice = notice;
            },

            showErrors(errors) {
                const rest = {};
                for (const [key, messages] of Object.entries(errors)) {
                    const match = /^files\.(\d+)$/.exec(key);
                    const item = match ? this.items[Number(match[1])] : null;
                    if (item) {
                        item.error = messages[0];
                    } else {
                        rest[key] = messages;
                    }
                }
                this.errors = rest;

                // Bring the first message into view — on a phone it can be far above the button.
                this.$nextTick(() => {
                    const first = Array.from(this.$root.querySelectorAll('[data-field-error], [data-file-error]'))
                        .find((el) => el.offsetParent !== null);
                    first?.scrollIntoView({ block: 'center', behavior: 'smooth' });
                });
            },
        };
    });

    // One per page; opened by any [data-lightbox] link via $dispatch('support-lightbox', $el).
    Alpine.data('supportLightbox', () => {
        let opener = null;

        return {
            open: false,
            items: [],
            index: 0,
            loading: false,
            broken: false,

            get current() {
                return this.items[this.index] ?? null;
            },

            show(trigger) {
                const links = Array.from(document.querySelectorAll('[data-lightbox]'));
                this.items = links.map((link) => ({
                    src: link.dataset.src,
                    name: link.dataset.name,
                    size: link.dataset.size,
                    download: link.dataset.download,
                }));
                this.index = Math.max(0, links.indexOf(trigger));
                opener = trigger;
                this.open = true;
                this.startLoading();
                document.body.classList.add('overflow-hidden');
                this.$nextTick(() => this.$refs.close?.focus());
            },

            close() {
                if (!this.open) {
                    return;
                }
                this.open = false;
                document.body.classList.remove('overflow-hidden');
                opener?.focus();
                opener = null;
            },

            step(delta) {
                if (!this.open || this.items.length < 2) {
                    return;
                }
                this.index = (this.index + delta + this.items.length) % this.items.length;
                this.startLoading();
            },

            startLoading() {
                this.loading = true;
                this.broken = false;
                // Reopening the same picture sets no new src, so no load event would come.
                this.$nextTick(() => {
                    const image = this.$refs.image;
                    if (image?.complete && image.naturalWidth > 0) {
                        this.loading = false;
                    }
                });
            },

            // Keep Tab inside the dialog while it is open.
            trapFocus(event) {
                const focusable = Array.from(this.$refs.dialog.querySelectorAll('a[href], button:not([disabled])'))
                    .filter((el) => el.offsetParent !== null);
                if (!focusable.length) {
                    return;
                }
                const first = focusable[0];
                const last = focusable[focusable.length - 1];
                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            },
        };
    });
}
