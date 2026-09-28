/**
 * The knowledge-base article editor (ТЗ 8.5).
 *
 * Quill itself comes from the CDN, the same way the contest forms load it.
 * This module wires it to the form: it mirrors the body into a hidden field,
 * uploads pictures instead of embedding them as base64, and keeps the
 * subcategory select in step with the category.
 */

const MAX_IMAGE_BYTES = 10 * 1024 * 1024;

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

/** Quill's own toolbar, limited to what the «kb» purifier profile keeps. */
const TOOLBAR = [
    [{ header: [2, 3, 4, false] }],
    ['bold', 'italic', 'underline', 'strike'],
    [{ list: 'ordered' }, { list: 'bullet' }],
    [{ indent: '-1' }, { indent: '+1' }],
    [{ align: [] }],
    ['blockquote', 'code-block'],
    ['link', 'image'],
    ['clean'],
];

export function registerKbEditor(Alpine) {
    Alpine.data('kbEditor', (config) => ({
        quill: null,
        uploading: 0,
        imageError: '',
        caret: null,
        category: String(config.category ?? ''),
        subcategory: '',

        init() {
            this.mountEditor();

            // Applied once the x-for has rendered the options: assigning it
            // now would set a value the select does not yet offer, and the
            // saved subcategory would silently come back empty.
            this.$nextTick(() => {
                this.subcategory = String(config.subcategory ?? '');
            });

            // Changing the category invalidates whatever subcategory was set:
            // the pair is validated server-side and a stale one is refused.
            this.$watch('category', () => {
                this.subcategory = '';
            });
        },

        // ── Dependent selects ─────────────────────

        get subcategories() {
            return config.tree[this.category] ?? [];
        },

        // ── Editor ────────────────────────────────

        mountEditor() {
            if (typeof window.Quill !== 'function') {
                // Without the CDN, reveal the textarea the editor normally
                // writes into: the form degrades to raw HTML rather than
                // leaving an author with nowhere to type.
                this.$refs.input.classList.remove('hidden');
                this.$refs.fallback?.classList.remove('hidden');
                this.$refs.editor?.classList.add('hidden');

                return;
            }

            this.quill = new window.Quill(this.$refs.editor, {
                theme: 'snow',
                placeholder: 'Текст статьи…',
                modules: {
                    toolbar: {
                        container: TOOLBAR,
                        handlers: { image: () => this.pickImage() },
                    },
                },
            });

            if (config.content) {
                this.quill.clipboard.dangerouslyPasteHTML(config.content, 'silent');
            }

            // Mirrored continuously rather than on submit: the form is posted
            // by support-upload.js, and relying on listener order there would
            // be a silent data loss the first time it changed.
            this.sync();
            this.quill.on('text-change', () => this.sync());

            this.guardPastedImages();
            this.$refs.editor.addEventListener('paste', (event) => this.filesPasted(event));
            this.$refs.editor.addEventListener('drop', (event) => this.filesDropped(event));
        },

        sync() {
            const html = this.quill.root.innerHTML;

            this.$refs.input.value = html === '<p><br></p>' ? '' : html;
        },

        // ── Pictures ──────────────────────────────

        /**
         * Where the next picture goes. Taken before the file dialog opens,
         * because that takes focus away and Quill's own idea of the caret is
         * then whatever it last managed to save — which is not where the
         * author was typing.
         */
        captureCaret() {
            this.caret = this.quill.getSelection()?.index ?? this.quill.getLength();
        },

        pickImage() {
            this.captureCaret();
            this.$refs.imageInput.click();
        },

        imagePicked(event) {
            const files = Array.from(event.target.files ?? []);

            event.target.value = '';
            this.uploadAll(files);
        },

        filesPasted(event) {
            const images = Array.from(event.clipboardData?.files ?? []).filter(isImage);

            // Text — including rich text from Word — pastes as usual.
            if (!images.length || event.clipboardData.getData('text/plain')) {
                return;
            }

            event.preventDefault();
            this.captureCaret();
            this.uploadAll(images);
        },

        filesDropped(event) {
            const images = Array.from(event.dataTransfer?.files ?? []).filter(isImage);

            if (!images.length) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            this.captureCaret();
            this.uploadAll(images);
        },

        /**
         * One at a time. Run in parallel, each upload would insert at the
         * caret as it was before any of them finished, so several pictures
         * would land on top of each other mid-word.
         */
        async uploadAll(files) {
            for (const file of files) {
                await this.uploadImage(file);
            }
        },

        /**
         * A picture pasted inside copied HTML arrives as a data: URI, which the
         * sanitiser drops on save. Refusing it here means the author finds out
         * immediately instead of after publishing.
         */
        guardPastedImages() {
            const Delta = window.Quill.import('delta');

            this.quill.clipboard.addMatcher('IMG', (node, delta) => {
                if (!String(node.getAttribute('src') ?? '').startsWith('data:')) {
                    return delta;
                }

                this.imageError = 'Изображение из буфера обмена не вставлено. '
                    + 'Добавьте его кнопкой с картинкой на панели — так оно сохранится.';

                return new Delta();
            });
        },

        async uploadImage(file) {
            this.imageError = '';

            if (file.size > MAX_IMAGE_BYTES) {
                this.imageError = `«${file.name}» больше 10 МБ.`;

                return;
            }

            this.uploading++;

            try {
                const body = new FormData();

                body.append('image', file);

                const response = await fetch(config.imageUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken(), Accept: 'application/json' },
                    credentials: 'same-origin',
                    body,
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    this.imageError = payload.errors?.image?.[0]
                        ?? payload.message
                        ?? 'Не удалось загрузить изображение.';

                    return;
                }

                this.insert(payload.url);
            } catch {
                this.imageError = 'Не удалось загрузить изображение — проверьте соединение.';
            } finally {
                this.uploading--;
            }
        },

        insert(url) {
            const at = Math.min(this.caret ?? this.quill.getLength(), this.quill.getLength());

            this.quill.insertEmbed(at, 'image', url, 'user');
            // Advanced by hand: several pictures in one go then stay in order
            // instead of stacking on the same offset.
            this.caret = at + 1;
            this.quill.setSelection(this.caret, 0, 'silent');
            this.sync();
        },
    }));
}

function isImage(file) {
    return file instanceof File && file.type.startsWith('image/');
}

/**
 * «Вставить статью» in the operator's reply box (ТЗ 8.7): search published
 * articles and drop a title plus link into the reply at the cursor.
 */
export function registerArticlePicker(Alpine) {
    Alpine.data('articlePicker', (config) => ({
        open: false,
        term: '',
        results: [],
        searching: false,
        searched: false,
        error: '',
        timer: null,

        show() {
            this.open = true;
            this.$nextTick(() => this.$refs.search?.focus());
        },

        close() {
            this.open = false;
        },

        // One request per pause in typing rather than one per keystroke.
        queue() {
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.run(), 250);
        },

        async run() {
            const term = this.term.trim();

            this.error = '';

            if (term.length < 2) {
                this.results = [];
                this.searched = false;

                return;
            }

            this.searching = true;

            try {
                const response = await fetch(`${config.searchUrl}?q=${encodeURIComponent(term)}`, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    throw new Error(String(response.status));
                }

                this.results = (await response.json()).results ?? [];
            } catch {
                this.results = [];
                this.error = 'Не удалось найти статьи — попробуйте ещё раз.';
            } finally {
                this.searching = false;
                this.searched = true;
            }
        },

        insert(article) {
            const field = document.querySelector(config.target);

            if (!field) {
                return;
            }

            const snippet = `${article.title}: ${article.url}`;
            const start = field.selectionStart ?? field.value.length;
            const end = field.selectionEnd ?? start;
            const before = field.value.slice(0, start);
            const after = field.value.slice(end);
            // Never glue the link onto the end of a sentence already typed.
            const gap = before && !before.endsWith('\n') ? '\n' : '';

            field.value = before + gap + snippet + after;
            // Anything watching the textarea (counters, drafts) sees the change.
            field.dispatchEvent(new Event('input', { bubbles: true }));

            const caret = (before + gap + snippet).length;

            field.focus();
            field.setSelectionRange(caret, caret);

            this.close();
        },
    }));
}
