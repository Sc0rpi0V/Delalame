import 'quill/dist/quill.snow.css';
import Quill from 'quill';

const toolbarOptions = [
    [{ header: [1, 2, 3, false] }],
    ['bold', 'italic', 'underline', 'strike'],
    [{ list: 'ordered' }, { list: 'bullet' }],
    [{ indent: '-1' }, { indent: '+1' }],
    ['link', 'blockquote'],
    ['clean'],
];

function initQuillOn(textarea) {
    if (textarea.dataset.quillReady) return;
    textarea.dataset.quillReady = '1';

    const editorDiv = document.createElement('div');
    editorDiv.id = 'quill-' + textarea.id;
    textarea.parentNode.insertBefore(editorDiv, textarea);
    textarea.style.display = 'none';

    const quill = new Quill(editorDiv, {
        theme: 'snow',
        modules: { toolbar: toolbarOptions },
        placeholder: 'Rédigez le contenu de la page ici…',
    });

    if (textarea.value.trim()) {
        quill.clipboard.dangerouslyPasteHTML(textarea.value);
    }

    textarea.form.addEventListener('submit', () => {
        textarea.value = quill.root.innerHTML;
    });
}

document.addEventListener('DOMContentLoaded', () => {
    // Un textarea cache dans un onglet Bootstrap inactif a une largeur de 0 :
    // on n'initialise Quill que sur l'onglet actif, puis à la demande à l'ouverture des autres.
    const activePane = document.querySelector('.tab-pane.active');
    if (activePane) {
        const textarea = activePane.querySelector('.legal-editor');
        if (textarea) initQuillOn(textarea);
    } else {
        document.querySelectorAll('.legal-editor').forEach(initQuillOn);
    }

    document.querySelectorAll('[data-bs-toggle="tab"]').forEach((btn) => {
        btn.addEventListener('shown.bs.tab', (e) => {
            const targetSelector = e.target.getAttribute('data-bs-target');
            const panel = targetSelector ? document.querySelector(targetSelector) : null;
            const textarea = panel ? panel.querySelector('.legal-editor') : null;
            if (textarea) initQuillOn(textarea);
        });
    });
});
