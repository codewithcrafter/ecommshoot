/**
 * ECOMSHOOT - Admin Panel Vanilla JavaScript Engine
 */

document.addEventListener('DOMContentLoaded', () => {

  // 1. Live Markdown Preview Renderer
  const markdownInput = document.getElementById('markdownContentInput');
  const markdownPreview = document.getElementById('markdownPreviewArea');

  if (markdownInput && markdownPreview) {
    const renderMarkdownToHtml = (md) => {
      let html = md;
      // Headings
      html = html.replace(/^### (.*$)/gim, '<h3>$1</h3>');
      html = html.replace(/^## (.*$)/gim, '<h2>$1</h2>');
      html = html.replace(/^# (.*$)/gim, '<h1>$1</h1>');
      // Bold & Italic
      html = html.replace(/\*\*(.*?)\*\*/gim, '<strong>$1</strong>');
      html = html.replace(/\*(.*?)\*/gim, '<em>$1</em>');
      // Blockquotes
      html = html.replace(/^\> (.*$)/gim, '<blockquote>$1</blockquote>');
      // Code blocks
      html = html.replace(/```([\s\S]*?)```/gim, '<pre><code>$1</code></pre>');
      // Inline code
      html = html.replace(/`([^`\n]+)`/gim, '<code>$1</code>');
      // Links
      html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/gim, '<a href="$2" target="_blank">$1</a>');
      // Images
      html = html.replace(/!\[([^\]]*)\]\(([^)]+)\)/gim, '<img src="$2" alt="$1" style="max-width:100%; border-radius:8px;">');
      // Paragraphs
      html = html.replace(/^\s*(\n)?(.+)/gim, function(m){
        return /\<(\/)?(h1|h2|h3|pre|code|blockquote|ul|ol|li)/.test(m) ? m : '<p>'+m+'</p>';
      });
      return html;
    };

    const updatePreview = () => {
      markdownPreview.innerHTML = renderMarkdownToHtml(markdownInput.value);
    };

    markdownInput.addEventListener('input', updatePreview);
    updatePreview();
  }

  // 2. Schema JSON Validator
  const schemaTextarea = document.getElementById('schemaJsonTextarea');
  const jsonStatusBadge = document.getElementById('jsonValidationStatus');

  if (schemaTextarea && jsonStatusBadge) {
    const validateSchemaJson = () => {
      const val = schemaTextarea.value.trim();
      if (!val) {
        jsonStatusBadge.innerHTML = '<span style="color:#64748B;">Enter JSON-LD Schema</span>';
        return;
      }
      try {
        const parsed = JSON.parse(val);
        if (parsed['@context'] && parsed['@type']) {
          jsonStatusBadge.innerHTML = '<span style="color:#10B981; font-weight:700;">✓ Valid Schema.org JSON-LD</span>';
        } else {
          jsonStatusBadge.innerHTML = '<span style="color:#F59E0B; font-weight:700;">⚠ Valid JSON, but missing "@context" or "@type"</span>';
        }
      } catch (err) {
        jsonStatusBadge.innerHTML = '<span style="color:#EF4444; font-weight:700;">✕ Invalid JSON syntax: ' + err.message + '</span>';
      }
    };

    schemaTextarea.addEventListener('input', validateSchemaJson);
    validateSchemaJson();
  }

  // 3. Media URL Copy Helper
  const copyButtons = document.querySelectorAll('.btn-copy-url');
  copyButtons.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const url = btn.getAttribute('data-url');
      if (url) {
        navigator.clipboard.writeText(url).then(() => {
          const originalText = btn.innerText;
          btn.innerText = 'Copied!';
          setTimeout(() => { btn.innerText = originalText; }, 2000);
        });
      }
    });
  });

});

