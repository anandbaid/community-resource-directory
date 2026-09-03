import { loadScript } from '@/lib/loadScript';

/**
 * GrapesJS, configured once.
 *
 * The create and edit Blade views each carried their own 135-line copy of this
 * object. They were identical apart from indentation, which is exactly the sort
 * of pair that drifts silently.
 *
 * GrapesJS stays on its CDN rather than going through Vite: it is a large
 * editor used on two admin screens, and bundling it would put it in the shared
 * chunk graph for no benefit.
 */
const CDN = {
    css: 'https://unpkg.com/grapesjs/dist/css/grapes.min.css',
    scripts: [
        'https://unpkg.com/grapesjs',
        'https://unpkg.com/grapesjs-blocks-basic',
        'https://unpkg.com/grapesjs-plugin-forms',
    ],
};

const RIGHT_PANEL_WIDTH = 300;

const FONT_OPTIONS = [
    { value: 'Arial, Helvetica, sans-serif', name: 'Arial' },
    { value: 'Arial Black, Gadget, sans-serif', name: 'Arial Black' },
    { value: 'Brush Script MT, sans-serif', name: 'Brush Script MT' },
    { value: 'Comic Sans MS, cursive, sans-serif', name: 'Comic Sans MS' },
    { value: 'Courier New, Courier, monospace', name: 'Courier New' },
    { value: 'Georgia, serif', name: 'Georgia' },
    { value: 'Helvetica, sans-serif', name: 'Helvetica' },
    { value: 'Impact, Charcoal, sans-serif', name: 'Impact' },
    { value: 'Lucida Sans Unicode, Lucida Grande, sans-serif', name: 'Lucida Sans Unicode' },
    { value: 'Poppins, sans-serif', name: 'Poppins' },
    { value: 'Tahoma, Geneva, sans-serif', name: 'Tahoma' },
    { value: 'Times New Roman, Times, serif', name: 'Times New Roman' },
    { value: 'Trebuchet MS, Helvetica, sans-serif', name: 'Trebuchet MS' },
    { value: 'Verdana, Geneva, sans-serif', name: 'Verdana' },
];

const STYLE_SECTORS = [
    {
        name: 'General',
        open: false,
        buildProps: ['float', 'display', 'position', 'top', 'right', 'left', 'bottom', 'overflow', 'visibility'],
    },
    {
        name: 'Dimension',
        open: false,
        buildProps: ['width', 'height', 'max-width', 'min-height', 'margin', 'padding'],
    },
    {
        name: 'Typography',
        open: false,
        buildProps: [
            'font-family', 'font-size', 'font-weight', 'letter-spacing',
            'color', 'line-height', 'text-align', 'text-shadow', 'text-decoration',
        ],
        properties: [
            {
                name: 'Font family',
                property: 'font-family',
                type: 'select',
                defaults: 'Poppins, sans-serif',
                options: FONT_OPTIONS,
            },
        ],
    },
    {
        name: 'Decorations',
        open: false,
        buildProps: [
            'background-color', 'border-radius', 'border', 'box-shadow', 'background', 'background-image',
        ],
    },
    {
        name: 'Extra',
        open: false,
        buildProps: ['opacity', 'transition', 'transform'],
    },
    {
        name: 'Flex',
        open: false,
        buildProps: [
            'flex-direction', 'flex-wrap', 'justify-content', 'align-items', 'align-content',
            'order', 'flex-basis', 'flex-grow', 'flex-shrink', 'align-self',
        ],
    },
];

// GrapesJS columns collapse to zero height without these.
const CANVAS_STYLE = `
    .gjs-column {
        min-height: 75px !important;
        height: auto !important;
        width: auto !important;
        min-width: 8% !important;
    }
    .gjs-cell {
        min-height: 75px !important;
        height: auto !important;
        width: auto !important;
        min-width: 8% !important;
    }
`;

let stylesheetAdded = false;

function addStylesheet() {
    if (stylesheetAdded) {
        return;
    }

    const link = document.createElement('link');

    link.rel = 'stylesheet';
    link.href = CDN.css;
    document.head.appendChild(link);

    stylesheetAdded = true;
}

/**
 * Widen the right-hand panel once GrapesJS has drawn its own chrome.
 */
function sizePanels(editor) {
    const container = editor.getContainer();
    const viewsPanel = container?.querySelector('.gjs-pn-views-container');

    if (viewsPanel) {
        Object.assign(viewsPanel.style, {
            width: `${RIGHT_PANEL_WIDTH}px`,
            minWidth: `${RIGHT_PANEL_WIDTH}px`,
            maxWidth: `${RIGHT_PANEL_WIDTH}px`,
            flex: `0 0 ${RIGHT_PANEL_WIDTH}px`,
            top: '40px',
            height: 'calc(100% - 40px)',
        });
    }

    const canvas = container?.querySelector('.gjs-cv-canvas');

    if (canvas) {
        canvas.style.width = `calc(100% - ${RIGHT_PANEL_WIDTH}px)`;
    }
}

/**
 * @param {object} options
 * @param {HTMLElement} options.container
 * @param {string} options.uploadUrl  Asset manager upload endpoint
 * @param {string} [options.html]     Existing page markup
 * @param {string} [options.css]      Existing page styles
 * @returns {Promise<object>} the GrapesJS editor
 */
export async function createPageBuilder({ container, uploadUrl, html = '', css = '' }) {
    addStylesheet();

    // Sequential: the plugins register themselves against the core global.
    for (const src of CDN.scripts) {
        await loadScript(src);
    }

    if (!window.grapesjs) {
        throw new Error('The page builder could not be loaded.');
    }

    const editor = window.grapesjs.init({
        container,
        height: '600px',
        fromElement: false,
        storageManager: false,
        canvas: {
            styles: ['https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap'],
        },
        styleManager: { sectors: STYLE_SECTORS },
        plugins: ['gjs-blocks-basic', 'gjs-plugin-forms'],
        pluginsOpts: {
            'gjs-blocks-basic': {
                blocks: ['column1', 'column2', 'column3', 'column3-7', 'text', 'link', 'image', 'map', 'button', 'divider'],
            },
            'gjs-plugin-forms': {},
        },
        assetManager: {
            upload: uploadUrl,
            uploadName: 'file',
            headers: { 'X-CSRF-TOKEN': window.csrf_token },
        },
    });

    editor.addStyle(CANVAS_STYLE);
    editor.on('load', () => sizePanels(editor));

    if (html) {
        editor.setComponents(html);
    }
    if (css) {
        editor.setStyle(css);
    }

    return editor;
}

/**
 * The four fields the controller reads off the request.
 */
export function pageBuilderData(editor) {
    if (!editor) {
        return { 'gjs-html': '', 'gjs-css': '', 'gjs-components': '', 'gjs-styles': '' };
    }

    return {
        'gjs-html': editor.getHtml(),
        'gjs-css': editor.getCss(),
        'gjs-components': JSON.stringify(editor.getComponents()),
        'gjs-styles': JSON.stringify(editor.getStyle()),
    };
}
