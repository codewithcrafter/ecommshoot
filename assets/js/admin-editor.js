/**
 * ECOMSHOOT - Vanilla JS Rich Text WYSIWYG Editor
 * Studio Content Management System Architecture with zero frameworks.
 */

class EcomshootRichEditor {
  constructor(options = {}) {
    this.container = typeof options.container === 'string' ? document.querySelector(options.container) : options.container;
    this.textarea = typeof options.textarea === 'string' ? document.querySelector(options.textarea) : options.textarea;
    this.markdownTextarea = typeof options.markdownTextarea === 'string' ? document.querySelector(options.markdownTextarea) : options.markdownTextarea;
    this.uploadUrl = options.uploadUrl || 'api-media-upload.php';
    this.mediaListUrl = options.mediaListUrl || 'api-media-list.php';
    this.csrfToken = options.csrfToken || '';
    this.articleId = options.articleId || '0';
    this.articleStatus = options.articleStatus || 'published';
    this.articleTitle = options.articleTitle || '';
    this.liveUrl = options.liveUrl || '';
    this.storageKey = 'ecomshoot_draft_' + this.articleId;

    this.activeMode = 'wysiwyg'; // 'wysiwyg', 'source', 'markdown', 'preview'
    this.savedRange = null;
    this.autosaveTimer = null;

    if (this.container && this.textarea) {
      this.init();
    }
  }

  init() {
    this.buildUI();
    this.populateInitialContent();
    this.attachEvents();
    this.updateStats();
    this.initAutosave();
  }

  buildUI() {
    this.container.classList.add('rich-editor-wrapper');
    this.container.innerHTML = `
      <!-- A. Redesigned Editor Header -->
      <div class="editor-top-action-bar">
        <div class="editor-title-status-wrap">
          <h3 class="editor-header-title">Article Content Editor</h3>
          <span class="editor-status-badge ${this.articleStatus === 'published' ? 'published' : 'draft'}">
            ● ${this.articleStatus === 'published' ? 'Published' : 'Draft'}
          </span>
          ${this.liveUrl ? `<a href="${this.liveUrl}" target="_blank" class="btn-admin btn-admin-outline" style="font-size: 0.75rem; padding: 0.25rem 0.65rem;" title="View Live Public Article">View Live Article &nearr;</a>` : ''}
        </div>
        <div class="editor-header-actions">
          <button type="button" class="btn-admin btn-admin-outline" id="editorBtnSaveDraft" style="font-size: 0.8rem; padding: 0.35rem 0.85rem;">Save Draft</button>
          <button type="button" class="btn-admin btn-admin-outline" id="editorBtnQuickPreview" style="font-size: 0.8rem; padding: 0.35rem 0.85rem;">Preview</button>
          <button type="button" class="btn-admin btn-admin-primary" id="editorBtnSubmitForm" style="font-size: 0.8rem; padding: 0.35rem 1rem;">Publish / Update</button>
        </div>
      </div>

      <!-- B. Mode Switcher Tabs Strip -->
      <div class="editor-mode-tabs">
        <div class="editor-tabs-group">
          <button type="button" class="editor-tab-btn active" data-mode="wysiwyg">
            <span>✏️</span> Rich Text Editor
          </button>
          <button type="button" class="editor-tab-btn" data-mode="source">
            <span>💻</span> HTML Source
          </button>
          <button type="button" class="editor-tab-btn" data-mode="markdown">
            <span>📝</span> Markdown Code
          </button>
          <button type="button" class="editor-tab-btn" data-mode="preview">
            <span>👁️</span> Live Preview
          </button>
        </div>
        <div class="editor-stats-bar">
          <span id="editorWordCount">0 words</span>
          <span>•</span>
          <span id="editorCharCount">0 characters</span>
          <span>•</span>
          <span class="editor-autosave-indicator" id="editorAutosaveStatus">✓ Ready</span>
        </div>
      </div>

      <!-- C. Reorganized 7-Group Toolbar -->
      <div class="rich-editor-toolbar" id="editorToolbar">
        
        <!-- GROUP 1: HISTORY -->
        <div class="toolbar-group" title="History Controls">
          <button type="button" class="tb-btn-icon" data-cmd="undo" title="Undo (Ctrl+Z)" aria-label="Undo">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M12.5 8c-2.65 0-5.05.99-6.9 2.6L2 7v9h9l-3.62-3.62c1.39-1.16 3.16-1.88 5.12-1.88 3.54 0 6.55 2.31 7.6 5.5l2.37-.78C21.08 11.03 17.15 8 12.5 8z"/></svg>
          </button>
          <button type="button" class="tb-btn-icon" data-cmd="redo" title="Redo (Ctrl+Y)" aria-label="Redo">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M18.4 10.6C16.55 8.99 14.15 8 11.5 8c-4.65 0-8.58 3.03-9.96 7.22L3.9 16c1.05-3.19 4.05-5.5 7.6-5.5 1.95 0 3.73.72 5.12 1.88L13 16h9V7l-3.6 3.6z"/></svg>
          </button>
        </div>

        <!-- GROUP 2: TEXT & HEADINGS -->
        <div class="toolbar-group" title="Heading Level">
          <select class="tb-select-heading" id="tbFormatBlock" title="Heading Format" aria-label="Heading Format">
            <option value="p">Paragraph</option>
            <option value="h1">Heading 1 (H1)</option>
            <option value="h2">Heading 2 (H2)</option>
            <option value="h3">Heading 3 (H3)</option>
            <option value="h4">Heading 4 (H4)</option>
            <option value="h5">Heading 5 (H5)</option>
            <option value="h6">Heading 6 (H6)</option>
          </select>
        </div>

        <!-- GROUP 3: FORMATTING -->
        <div class="toolbar-group" title="Text Styles">
          <button type="button" class="tb-btn-icon" data-cmd="bold" title="Bold (Ctrl+B)" aria-label="Bold"><strong>B</strong></button>
          <button type="button" class="tb-btn-icon" data-cmd="italic" title="Italic (Ctrl+I)" aria-label="Italic"><em>I</em></button>
          <button type="button" class="tb-btn-icon" data-cmd="underline" title="Underline (Ctrl+U)" aria-label="Underline"><u>U</u></button>
          <button type="button" class="tb-btn-icon" data-cmd="strikeThrough" title="Strikethrough" aria-label="Strikethrough"><s>S</s></button>
          <div class="tb-color-wrap" title="Text Color" aria-label="Text Color">
            <input type="color" id="tbTextColor" value="#0F172A">
          </div>
        </div>

        <!-- GROUP 4: ALIGNMENT -->
        <div class="toolbar-group" title="Text Alignment">
          <button type="button" class="tb-btn-icon" data-cmd="justifyLeft" title="Align Left" aria-label="Align Left">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M15 15H3v2h12v-2zm0-8H3v2h12V7zM3 13h18v-2H3v2zm0 8h18v-2H3v2zM3 3v2h18V3H3z"/></svg>
          </button>
          <button type="button" class="tb-btn-icon" data-cmd="justifyCenter" title="Align Center" aria-label="Align Center">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M7 15v2h10v-2H7zm-4 6h18v-2H3v2zm0-8h18v-2H3v2zm4-6v2h10V7H7zM3 3v2h18V3H3z"/></svg>
          </button>
          <button type="button" class="tb-btn-icon" data-cmd="justifyRight" title="Align Right" aria-label="Align Right">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M3 21h18v-2H3v2zm6-4h12v-2H9v2zm-6-4h18v-2H3v2zm6-4h12V7H9v2zM3 3v2h18V3H3z"/></svg>
          </button>
          <button type="button" class="tb-btn-icon" data-cmd="justifyFull" title="Justify" aria-label="Justify">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M3 21h18v-2H3v2zm0-4h18v-2H3v2zm0-4h18v-2H3v2zm0-4h18V7H3v2zm0-6v2h18V3H3z"/></svg>
          </button>
        </div>

        <!-- GROUP 5: CONTENT STRUCTURE -->
        <div class="toolbar-group" title="Lists & Quotes">
          <button type="button" class="tb-btn-icon" data-cmd="insertUnorderedList" title="Bulleted List" aria-label="Bulleted List">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M4 10.5c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5-.67-1.5-1.5-1.5zm0-6c-.83 0-1.5.67-1.5 1.5S3.17 7.5 4 7.5 5.5 6.83 5.5 6 4.83 4.5 4 4.5zm0 12c-.83 0-1.5.68-1.5 1.5s.68 1.5 1.5 1.5 1.5-.68 1.5-1.5-.67-1.5-1.5-1.5zM7 19h14v-2H7v2zm0-6h14v-2H7v2zm0-8v2h14V5H7z"/></svg>
          </button>
          <button type="button" class="tb-btn-icon" data-cmd="insertOrderedList" title="Numbered List" aria-label="Numbered List">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M2 17h2v.5H3v1h1v.5H2v1h3v-4H2v1zm1-9h1V4H2v1h1v3zm-1 3h1.8L2 13.1v.9h3v-1H3.2L5 10.9V10H2v1zm5-6v2h14V5H7zm0 14h14v-2H7v2zm0-6h14v-2H7v2z"/></svg>
          </button>
          <button type="button" class="tb-btn-icon" id="tbBlockquoteBtn" title="Blockquote" aria-label="Blockquote">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M6 17h3l2-4V7H5v6h3zm8 0h3l2-4V7h-6v6h3z"/></svg>
          </button>
          <button type="button" class="tb-btn-icon" data-cmd="insertHorizontalRule" title="Horizontal Divider" aria-label="Horizontal Divider">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M4 11h16v2H4z"/></svg>
          </button>
          <button type="button" class="tb-btn-icon" id="tbCodeBlockBtn" title="Code Block" aria-label="Code Block">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M9.4 16.6L4.8 12l4.6-4.6L8 6l-6 6 6 6 1.4-1.4zm5.2 0l4.6-4.6-4.6-4.6L16 6l6 6-6 6-1.4-1.4z"/></svg>
          </button>
        </div>

        <!-- GROUP 6: INSERT MEDIA, LINKS & TABLES -->
        <div class="toolbar-group" title="Insert Elements">
          <button type="button" class="tb-btn-icon" id="tbLinkBtn" title="Insert Link" aria-label="Insert Link">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z"/></svg>
          </button>
          <button type="button" class="tb-btn-icon" data-cmd="unlink" title="Remove Link" aria-label="Remove Link">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M17 7h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1 0 1.43-.98 2.63-2.31 2.98l1.46 1.46C20.88 15.61 22 13.95 22 12c0-2.76-2.24-5-5-5zm-1 4h-2.19l2 2H16v-2zm-7.6 3.4l1.6 1.6H7c-2.76 0-5-2.24-5-5 0-1.95 1.12-3.61 2.75-4.46L2.1 4.3 3.5 2.9l17.6 17.6-1.4 1.4-4.8-4.8H7c-1.71 0-3.1-1.39-3.1-3.1 0-1.43.98-2.63 2.31-2.98l2.19 2.18z"/></svg>
          </button>
          <button type="button" class="tb-btn-action" id="tbUploadImgBtn" title="Upload Image Asset">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16h6v-6h4l-7-7-7 7h4zm-4 2h14v2H5z"/></svg>
            <span>Upload Image</span>
          </button>
          <button type="button" class="tb-btn-action" id="tbMediaLibBtn" title="Studio Media Vault">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
            <span>Media Vault</span>
          </button>
          <button type="button" class="tb-btn-action" id="tbTableBtn" title="Insert Responsive Table">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M20 2H4c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 8h-6V4h6v6zm-8-6v6H4V4h8zm-8 8h8v8H4v-8zm10 8v-8h6v8h-6z"/></svg>
            <span>Table</span>
          </button>
        </div>

        <!-- GROUP 7: VIEW CONTROLS -->
        <div class="toolbar-group" style="margin-left: auto;">
          <button type="button" class="tb-btn-action" id="tbFullscreenBtn" title="Toggle Fullscreen Canvas (Esc to exit)">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"/></svg>
            <span>Fullscreen</span>
          </button>
        </div>

      </div>

      <!-- D. Document Writing Canvas ("Paper" Workspace) -->
      <div class="editor-canvas-outer" id="editorCanvasOuter">
        <div class="rich-editor-canvas" id="editorCanvas" contenteditable="true" spellcheck="true" role="textbox" aria-multiline="true"></div>
      </div>

      <!-- E. Raw HTML Source Editor -->
      <div class="editor-code-pane" id="editorHtmlPane" style="display: none;">
        <div style="font-size: 0.8rem; color: #94A3B8; margin-bottom: 0.5rem; display: flex; justify-content: space-between;">
          <span>HTML Source Code (Sanitized server-side on submit)</span>
          <span style="color: #38BDF8;">Direct markup editing enabled</span>
        </div>
        <textarea id="editorRawHtml" class="editor-code-textarea" spellcheck="false"></textarea>
      </div>

      <!-- F. Markdown Source Editor -->
      <div class="editor-code-pane" id="editorMarkdownPane" style="display: none;">
        <div style="font-size: 0.8rem; color: #94A3B8; margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
          <span>Markdown Source (Synchronized with /content/blogs/${this.articleId}.md)</span>
          <button type="button" id="btnSyncMdToHtml" class="btn-admin btn-admin-outline" style="font-size: 0.72rem; padding: 0.2rem 0.5rem; color: #38BDF8; border-color: #38BDF8;">
            Sync Markdown to Rich Text Canvas &rarr;
          </button>
        </div>
        <textarea id="editorMdInput" class="editor-code-textarea" spellcheck="false"></textarea>
      </div>

      <!-- G. Live Formatted Preview Canvas -->
      <div id="editorPreviewBox" class="editor-preview-canvas" style="display: none;"></div>

      <!-- Hidden file input for inline image upload -->
      <input type="file" id="editorInlineFileInput" accept="image/jpeg,image/png,image/webp,image/gif" style="display: none;">
    `;

    this.canvas = this.container.querySelector('#editorCanvas');
    this.canvasOuter = this.container.querySelector('#editorCanvasOuter');
    this.htmlPane = this.container.querySelector('#editorHtmlPane');
    this.rawHtmlArea = this.container.querySelector('#editorRawHtml');
    this.markdownPane = this.container.querySelector('#editorMarkdownPane');
    this.mdInputArea = this.container.querySelector('#editorMdInput');
    this.previewBox = this.container.querySelector('#editorPreviewBox');
    this.toolbar = this.container.querySelector('#editorToolbar');
    this.fileInput = this.container.querySelector('#editorInlineFileInput');
  }

  populateInitialContent() {
    let rawContent = this.textarea.value.trim();
    let mdContent = this.markdownTextarea ? this.markdownTextarea.value.trim() : '';

    // If content contains markdown markers (e.g. "## ") and not HTML tags, convert to HTML for canvas
    if (rawContent) {
      if (this.isMarkdown(rawContent)) {
        this.canvas.innerHTML = this.convertMarkdownToHtml(rawContent);
        this.mdInputArea.value = rawContent;
      } else {
        this.canvas.innerHTML = rawContent;
        this.mdInputArea.value = mdContent || this.convertHtmlToMarkdown(rawContent);
      }
    } else if (mdContent) {
      this.canvas.innerHTML = this.convertMarkdownToHtml(mdContent);
      this.mdInputArea.value = mdContent;
    } else {
      // Empty template paragraph
      this.canvas.innerHTML = '<p><br></p>';
      this.mdInputArea.value = '';
    }

    this.rawHtmlArea.value = this.canvas.innerHTML;
    this.syncToTextarea();
  }

  isMarkdown(text) {
    if (!text) return false;
    // Check if it has HTML tags
    if (/<[a-z][\s\S]*>/i.test(text)) {
      return false;
    }
    // Check for markdown headings, lists, blockquotes, bold
    return /(^#{1,6}\s)|(^\s*[\-\*]\s)|(^\s*\d+\.\s)|(^\s*>\s)|(\*\*.*?\*\*)/m.test(text);
  }

  attachEvents() {
    // Top action buttons
    const form = this.container.closest('form');
    this.container.querySelector('#editorBtnSubmitForm').addEventListener('click', (e) => {
      e.preventDefault();
      if (form) form.submit();
    });

    this.container.querySelector('#editorBtnSaveDraft').addEventListener('click', (e) => {
      e.preventDefault();
      const statusSelect = document.getElementById('status');
      if (statusSelect) statusSelect.value = 'draft';
      if (form) form.submit();
    });

    this.container.querySelector('#editorBtnQuickPreview').addEventListener('click', (e) => {
      e.preventDefault();
      this.switchMode('preview');
    });

    // Mode tabs
    this.container.querySelectorAll('.editor-tab-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        this.switchMode(btn.dataset.mode);
      });
    });

    // Formatting commands
    this.toolbar.querySelectorAll('[data-cmd]').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const cmd = btn.dataset.cmd;
        this.exec(cmd);
      });
    });

    // Format Block (H1-H6, P)
    const formatSelect = this.container.querySelector('#tbFormatBlock');
    formatSelect.addEventListener('change', () => {
      const val = formatSelect.value;
      this.exec('formatBlock', `<${val}>`);
    });

    // Text Color
    const colorPicker = this.container.querySelector('#tbTextColor');
    colorPicker.addEventListener('change', () => {
      this.exec('foreColor', colorPicker.value);
    });

    // Blockquote
    this.container.querySelector('#tbBlockquoteBtn').addEventListener('click', () => {
      this.exec('formatBlock', '<blockquote>');
    });

    // Code block
    this.container.querySelector('#tbCodeBlockBtn').addEventListener('click', () => {
      this.insertCodeBlock();
    });

    // Table
    this.container.querySelector('#tbTableBtn').addEventListener('click', () => {
      this.promptTable();
    });

    // Link
    this.container.querySelector('#tbLinkBtn').addEventListener('click', () => {
      this.promptLink();
    });

    // Upload Image
    this.container.querySelector('#tbUploadImgBtn').addEventListener('click', () => {
      this.saveSelection();
      this.fileInput.click();
    });

    this.fileInput.addEventListener('change', () => {
      if (this.fileInput.files && this.fileInput.files[0]) {
        this.uploadInlineImage(this.fileInput.files[0]);
      }
    });

    // Media Library Vault
    this.container.querySelector('#tbMediaLibBtn').addEventListener('click', () => {
      this.saveSelection();
      this.openMediaModal();
    });

    // Fullscreen toggle
    this.container.querySelector('#tbFullscreenBtn').addEventListener('click', () => {
      this.toggleFullscreen();
    });

    // Escape exits fullscreen
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && this.container.classList.contains('is-fullscreen')) {
        this.toggleFullscreen();
      }
    });

    // Live typing listeners
    ['input', 'keyup', 'paste'].forEach(evt => {
      this.canvas.addEventListener(evt, () => {
        this.syncToTextarea();
        this.updateStats();
        this.markDirty();
      });
    });

    this.rawHtmlArea.addEventListener('input', () => {
      this.canvas.innerHTML = this.rawHtmlArea.value;
      this.syncToTextarea();
      this.updateStats();
      this.markDirty();
    });

    this.mdInputArea.addEventListener('input', () => {
      this.markDirty();
    });

    // Sync Markdown to HTML button
    this.container.querySelector('#btnSyncMdToHtml').addEventListener('click', () => {
      const md = this.mdInputArea.value;
      this.canvas.innerHTML = this.convertMarkdownToHtml(md);
      this.syncToTextarea();
      this.switchMode('wysiwyg');
      alert('Markdown successfully synchronized into Rich Text Canvas!');
    });

    // Form submit hook: ensure textarea has the latest canvas HTML
    if (form) {
      form.addEventListener('submit', () => {
        this.syncToTextarea();
      });
    }
  }

  exec(command, value = null) {
    this.canvas.focus();
    document.execCommand(command, false, value);
    this.syncToTextarea();
    this.updateStats();
  }

  saveSelection() {
    const sel = window.getSelection();
    if (sel.getRangeAt && sel.rangeCount) {
      this.savedRange = sel.getRangeAt(0).cloneRange();
    }
  }

  restoreSelection() {
    if (this.savedRange) {
      const sel = window.getSelection();
      sel.removeAllRanges();
      sel.addRange(this.savedRange);
    }
  }

  switchMode(mode) {
    // If leaving wysiwyg, sync out
    if (this.activeMode === 'wysiwyg') {
      this.rawHtmlArea.value = this.canvas.innerHTML;
      this.mdInputArea.value = this.convertHtmlToMarkdown(this.canvas.innerHTML);
    } else if (this.activeMode === 'source') {
      this.canvas.innerHTML = this.rawHtmlArea.value;
      this.mdInputArea.value = this.convertHtmlToMarkdown(this.rawHtmlArea.value);
    } else if (this.activeMode === 'markdown') {
      this.canvas.innerHTML = this.convertMarkdownToHtml(this.mdInputArea.value);
      this.rawHtmlArea.value = this.canvas.innerHTML;
    }

    this.activeMode = mode;
    this.container.querySelectorAll('.editor-tab-btn').forEach(b => {
      b.classList.toggle('active', b.dataset.mode === mode);
    });

    // Hide all view panels
    this.canvasOuter.style.display = 'none';
    this.htmlPane.style.display = 'none';
    this.markdownPane.style.display = 'none';
    this.previewBox.style.display = 'none';
    this.toolbar.style.display = 'none';

    if (mode === 'wysiwyg') {
      this.toolbar.style.display = 'flex';
      this.canvasOuter.style.display = 'flex';
      this.canvas.focus();
    } else if (mode === 'source') {
      this.htmlPane.style.display = 'block';
      this.rawHtmlArea.focus();
    } else if (mode === 'markdown') {
      this.markdownPane.style.display = 'block';
      this.mdInputArea.focus();
    } else if (mode === 'preview') {
      this.previewBox.innerHTML = `
        <div style="font-family: var(--font-heading); font-size: 2rem; font-weight: 700; margin-bottom: 1.5rem; color: #0F172A; border-bottom: 2px solid #E2E8F0; padding-bottom: 0.75rem;">
          ${this.escapeHtml(document.getElementById('title') ? document.getElementById('title').value : 'Article Preview')}
        </div>
        <div class="article-content">${this.canvas.innerHTML}</div>
      `;
      this.previewBox.style.display = 'block';
    }

    this.syncToTextarea();
    this.updateStats();
  }

  syncToTextarea() {
    const html = this.canvas.innerHTML;
    this.textarea.value = html;
    if (this.markdownTextarea) {
      this.markdownTextarea.value = this.convertHtmlToMarkdown(html);
    }
  }

  updateStats() {
    const text = this.canvas.innerText || '';
    const cleanWords = text.trim() ? text.trim().split(/\s+/).filter(Boolean) : [];
    const words = cleanWords.length;
    const chars = text.length;

    const wordEl = this.container.querySelector('#editorWordCount');
    const charEl = this.container.querySelector('#editorCharCount');
    if (wordEl) wordEl.textContent = `${words} words`;
    if (charEl) charEl.textContent = `${chars} characters`;
  }

  markDirty() {
    const statusEl = this.container.querySelector('#editorAutosaveStatus');
    if (statusEl) {
      statusEl.textContent = '● Saving...';
      statusEl.style.color = '#D97706';
    }
    clearTimeout(this.autosaveTimer);
    this.autosaveTimer = setTimeout(() => {
      this.saveDraft();
    }, 1500);
  }

  saveDraft() {
    try {
      const now = new Date();
      const timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
      localStorage.setItem(this.storageKey, JSON.stringify({
        html: this.canvas.innerHTML,
        timestamp: timeStr
      }));
      const statusEl = this.container.querySelector('#editorAutosaveStatus');
      if (statusEl) {
        statusEl.textContent = `✓ Autosaved at ${timeStr}`;
        statusEl.style.color = '#059669';
      }
    } catch (e) {}
  }

  initAutosave() {
    try {
      const saved = localStorage.getItem(this.storageKey);
      if (saved && (!this.canvas.innerText.trim() || this.canvas.innerText.trim() === '')) {
        const data = JSON.parse(saved);
        if (data.html && confirm(`A recent draft from ${data.timestamp} was found in browser storage. Restore it?`)) {
          this.canvas.innerHTML = data.html;
          this.syncToTextarea();
          this.updateStats();
        }
      }
    } catch (e) {}
  }

  toggleFullscreen() {
    this.container.classList.toggle('is-fullscreen');
    const isFull = this.container.classList.contains('is-fullscreen');
    document.body.style.overflow = isFull ? 'hidden' : '';
    const fsBtn = this.container.querySelector('#tbFullscreenBtn span');
    if (fsBtn) {
      fsBtn.textContent = isFull ? 'Exit' : 'Fullscreen';
    }
  }

  insertCodeBlock() {
    const sel = window.getSelection();
    const text = sel ? sel.toString() : '';
    const block = document.createElement('pre');
    const code = document.createElement('code');
    code.textContent = text || '// Enter code snippet here';
    block.appendChild(code);
    this.insertNode(block);
  }

  insertNode(node) {
    this.canvas.focus();
    const sel = window.getSelection();
    if (sel.getRangeAt && sel.rangeCount) {
      const range = sel.getRangeAt(0);
      range.deleteContents();
      range.insertNode(node);
      range.collapse(false);
    } else {
      this.canvas.appendChild(node);
    }
    this.syncToTextarea();
    this.updateStats();
  }

  promptLink() {
    this.saveSelection();
    const selectedText = this.savedRange ? this.savedRange.toString() : '';

    this.showModal({
      title: 'Insert / Edit Hyperlink',
      content: `
        <div class="admin-form-group">
          <label class="admin-form-label">Link Text</label>
          <input type="text" id="modalLinkText" class="admin-form-control" value="${this.escapeHtml(selectedText)}">
        </div>
        <div class="admin-form-group">
          <label class="admin-form-label">Destination URL</label>
          <input type="url" id="modalLinkUrl" class="admin-form-control" placeholder="https://example.com or /services.php" required>
        </div>
        <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; cursor: pointer;">
          <input type="checkbox" id="modalLinkTarget" checked> Open link in a new browser tab
        </label>
      `,
      onConfirm: () => {
        const text = document.getElementById('modalLinkText').value.trim();
        const url = document.getElementById('modalLinkUrl').value.trim();
        const newTab = document.getElementById('modalLinkTarget').checked;

        if (!url) return;

        this.restoreSelection();
        const a = document.createElement('a');
        a.href = url;
        a.textContent = text || url;
        if (newTab) {
          a.target = '_blank';
          a.rel = 'noopener noreferrer';
        }
        this.insertNode(a);
      }
    });
  }

  promptTable() {
    this.saveSelection();
    this.showModal({
      title: 'Insert Responsive Data Table',
      content: `
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="admin-form-group">
            <label class="admin-form-label">Columns (Headers)</label>
            <input type="number" id="modalTableCols" class="admin-form-control" min="1" max="8" value="3">
          </div>
          <div class="admin-form-group">
            <label class="admin-form-label">Rows</label>
            <input type="number" id="modalTableRows" class="admin-form-control" min="1" max="20" value="4">
          </div>
        </div>
      `,
      onConfirm: () => {
        const cols = parseInt(document.getElementById('modalTableCols').value) || 3;
        const rows = parseInt(document.getElementById('modalTableRows').value) || 4;

        this.restoreSelection();

        let tableHtml = '<table><thead><tr>';
        for (let c = 1; c <= cols; c++) {
          tableHtml += `<th>Column Header ${c}</th>`;
        }
        tableHtml += '</tr></thead><tbody>';
        for (let r = 1; r <= rows; r++) {
          tableHtml += '<tr>';
          for (let c = 1; c <= cols; c++) {
            tableHtml += `<td>Row ${r} Data ${c}</td>`;
          }
          tableHtml += '</tr>';
        }
        tableHtml += '</tbody></table><p><br></p>';

        this.exec('insertHTML', tableHtml);
      }
    });
  }

  uploadInlineImage(file) {
    const formData = new FormData();
    formData.append('image', file);
    formData.append('csrf_token', this.csrfToken);

    const statusEl = this.container.querySelector('#editorAutosaveStatus');
    if (statusEl) statusEl.textContent = '⏳ Uploading Image...';

    fetch(this.uploadUrl, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': this.csrfToken
      },
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        this.restoreSelection();
        const fig = document.createElement('figure');
        fig.className = 'article-image-figure';
        const img = document.createElement('img');
        img.src = data.url;
        img.alt = data.alt || file.name;
        img.loading = 'lazy';
        fig.appendChild(img);
        if (data.caption) {
          const cap = document.createElement('figcaption');
          cap.textContent = data.caption;
          fig.appendChild(cap);
        }
        this.insertNode(fig);
        if (statusEl) statusEl.textContent = '✓ Image Inserted';
      } else {
        alert('Image upload failed: ' + (data.error || 'Server error'));
      }
    })
    .catch(() => {
      alert('Network error while uploading image.');
    })
    .finally(() => {
      this.fileInput.value = '';
    });
  }

  openMediaModal() {
    this.showModal({
      title: 'Studio Media Vault & Image Assets',
      content: `
        <div style="font-size: 0.82rem; color: #64748B; margin-bottom: 0.75rem;">Click an image asset to select and insert:</div>
        <div class="editor-media-grid" id="editorMediaGrid">
          <div style="padding: 2rem; text-align: center; color: #64748B; grid-column: 1 / -1;">Loading media items...</div>
        </div>
      `,
      onConfirm: () => {
        const selected = document.querySelector('.editor-media-item.selected');
        if (selected) {
          this.restoreSelection();
          const img = document.createElement('img');
          img.src = selected.dataset.url;
          img.alt = selected.dataset.alt || 'Studio Asset';
          img.loading = 'lazy';
          this.insertNode(img);
        }
      }
    });

    fetch(this.mediaListUrl)
      .then(r => r.json())
      .then(data => {
        const grid = document.getElementById('editorMediaGrid');
        if (!grid) return;
        if (data.success && data.items && data.items.length) {
          grid.innerHTML = data.items.map(item => `
            <div class="editor-media-item" data-url="${this.escapeHtml(item.full_url)}" data-alt="${this.escapeHtml(item.alt_text || item.file_name)}">
              <img src="${this.escapeHtml(item.full_url)}" alt="${this.escapeHtml(item.alt_text || '')}">
            </div>
          `).join('');

          grid.querySelectorAll('.editor-media-item').forEach(el => {
            el.addEventListener('click', () => {
              grid.querySelectorAll('.editor-media-item').forEach(i => i.classList.remove('selected'));
              el.classList.add('selected');
            });
          });
        } else {
          grid.innerHTML = '<div style="padding: 2rem; text-align: center; color: #64748B; grid-column: 1 / -1;">No media assets found in vault. Upload one first.</div>';
        }
      });
  }

  showModal(opts) {
    const existing = document.getElementById('editorGlobalModal');
    if (existing) existing.remove();

    const backdrop = document.createElement('div');
    backdrop.className = 'editor-modal-backdrop';
    backdrop.id = 'editorGlobalModal';
    backdrop.innerHTML = `
      <div class="editor-modal">
        <div class="editor-modal-header">
          <div class="editor-modal-title">${opts.title}</div>
          <button type="button" class="editor-modal-close" id="modalCloseBtn">&times;</button>
        </div>
        <div class="editor-modal-body">${opts.content}</div>
        <div class="editor-modal-footer">
          <button type="button" class="btn-admin btn-admin-outline" id="modalCancelBtn">Cancel</button>
          <button type="button" class="btn-admin btn-admin-primary" id="modalConfirmBtn">Insert / Apply</button>
        </div>
      </div>
    `;
    document.body.appendChild(backdrop);

    const close = () => backdrop.remove();
    backdrop.querySelector('#modalCloseBtn').addEventListener('click', close);
    backdrop.querySelector('#modalCancelBtn').addEventListener('click', close);
    backdrop.querySelector('#modalConfirmBtn').addEventListener('click', () => {
      if (opts.onConfirm) opts.onConfirm();
      close();
    });
  }

  escapeHtml(str) {
    return (str || '').replace(/[&<>"']/g, m => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    })[m]);
  }

  convertMarkdownToHtml(md) {
    if (!md) return '<p><br></p>';
    let html = md
      .replace(/^### (.*$)/gim, '<h3>$1</h3>')
      .replace(/^## (.*$)/gim, '<h2>$1</h2>')
      .replace(/^# (.*$)/gim, '<h1>$1</h1>')
      .replace(/^\> (.*$)/gim, '<blockquote>$1</blockquote>')
      .replace(/\*\*(.*?)\*\*/gim, '<strong>$1</strong>')
      .replace(/\*(.*?)\*/gim, '<em>$1</em>')
      .replace(/!\[(.*?)\]\((.*?)\)/gim, '<img alt="$1" src="$2" loading="lazy">')
      .replace(/\[(.*?)\]\((.*?)\)/gim, '<a href="$2">$1</a>')
      .replace(/^\- (.*$)/gim, '<li>$1</li>')
      .replace(/\n\n/gim, '</p><p>')
      .replace(/\n/gim, '<br>');
    return '<p>' + html + '</p>';
  }

  convertHtmlToMarkdown(html) {
    if (!html) return '';
    let md = html
      .replace(/<h1[^>]*>(.*?)<\/h1>/gi, '\n\n# $1\n\n')
      .replace(/<h2[^>]*>(.*?)<\/h2>/gi, '\n\n## $1\n\n')
      .replace(/<h3[^>]*>(.*?)<\/h3>/gi, '\n\n### $1\n\n')
      .replace(/<h4[^>]*>(.*?)<\/h4>/gi, '\n\n#### $1\n\n')
      .replace(/<blockquote[^>]*>(.*?)<\/blockquote>/gi, '\n\n> $1\n\n')
      .replace(/<strong[^>]*>(.*?)<\/strong>/gi, '**$1**')
      .replace(/<b[^>]*>(.*?)<\/b>/gi, '**$1**')
      .replace(/<em[^>]*>(.*?)<\/em>/gi, '*$1*')
      .replace(/<i[^>]*>(.*?)<\/i>/gi, '*$1*')
      .replace(/<a[^>]*href="([^"]*)"[^>]*>(.*?)<\/a>/gi, '[$2]($1)')
      .replace(/<img[^>]*src="([^"]*)"[^>]*alt="([^"]*)"[^>]*>/gi, '![$2]($1)')
      .replace(/<li[^>]*>(.*?)<\/li>/gi, '- $1\n')
      .replace(/<p[^>]*>(.*?)<\/p>/gi, '\n\n$1\n\n')
      .replace(/<br\s*[\/]?>/gi, '\n')
      .replace(/<hr\s*[\/]?>/gi, '\n\n---\n\n')
      .replace(/<[^>]+>/gi, '');
    return md.replace(/\n{3,}/g, '\n\n').trim();
  }
}

// Global initialization helper
window.initRichEditor = function(selector, options = {}) {
  const el = document.querySelector(selector);
  if (el) {
    return new EcomshootRichEditor({ container: el, ...options });
  }
  return null;
};
