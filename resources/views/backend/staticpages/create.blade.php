@extends('backend.layouts.app')
@section('title', ' | Create Static Page')
@push('custom-styles')
    <link rel="stylesheet" href="https://unpkg.com/grapesjs/dist/css/grapes.min.css">
    <style>
        #gjs {
            border: 1px solid #dcdcdc;
            min-height: 600px;
        }

        .gjs-editor-wrapper {
            background: #ffffff;
        }
    </style>
@endpush
@section('content')
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Create Page</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Create Page
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Static Page Details</h4>
                        </div>

                        <form name="createStaticPage" id="createStaticPage" class="px-3"
                            action="{{ route('admin.static-pages.store') }}" method="POST">
                            @csrf
                            <div class="card-body">
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-2">
                                        <label class="form-label">Page Title</label>
                                    </div>
                                    <div class="col-md-10">
                                        <input type="text" class="form-control" name="title" required>
                                    </div>
                                </div>
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-2">
                                        <label class="form-label">Status</label>
                                    </div>
                                    <div class="col-md-10">
                                        <select class="form-select" name="status" required>
                                            <option value="active" selected>Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-2">
                                        <label class="form-label">Header Menu</label>
                                    </div>
                                    <div class="col-md-10">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="show_in_header"
                                                value="1">
                                            <label class="form-check-label">Show in header</label>
                                        </div>
                                        <select class="form-select mt-2" name="header_parent">
                                            <option value="">Select parent menu</option>
                                            @foreach ($menuParents as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <textarea class="form-control mt-2" name="header_menu_description" rows="2"
                                            placeholder="Header menu short description (optional)"></textarea>
                                        <input type="number" class="form-control mt-2" name="header_order"
                                            placeholder="Header menu order (optional)">
                                    </div>
                                </div>
                                <div class="form-group row mb-4 align-center">
                                    <div class="col-md-2">
                                        <label class="form-label">Footer Menu</label>
                                    </div>
                                    <div class="col-md-10">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="show_in_footer"
                                                value="1">
                                            <label class="form-check-label">Show in footer</label>
                                        </div>
                                        <input type="number" class="form-control mt-2" name="footer_order"
                                            placeholder="Footer order (optional)">
                                    </div>
                                </div>
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-2">
                                        <label class="form-label">Description</label>
                                    </div>
                                    <div class="col-md-12 gjs-editor-wrapper">
                                        <div id="gjs"></div>
                                    </div>
                                </div>
                                <input type="hidden" name="gjs-html" id="gjs-html">
                                <input type="hidden" name="gjs-css" id="gjs-css">
                                <input type="hidden" name="gjs-components" id="gjs-components">
                                <input type="hidden" name="gjs-styles" id="gjs-styles">
                            </div>
                            <div class="card-footer d-flex">
                                <button type="submit" class="btn btn-primary save-btn">Submit</button>
                                <button type="reset" class="btn btn-danger mx-2">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('custom-scripts')
    <script src="https://unpkg.com/grapesjs"></script>
    <script src="https://unpkg.com/grapesjs-blocks-basic"></script>
    <script src="https://unpkg.com/grapesjs-plugin-forms"></script>
    <script>
        window.addEventListener('load', function() {
            const editor = grapesjs.init({
                container: '#gjs',
                height: '600px',
                fromElement: false,
                storageManager: false,
                canvas: {
                    styles: [
                        'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap'
                    ]
                },
                styleManager: {
                    sectors: [{
                            name: 'General',
                            open: false,
                            buildProps: ['float', 'display', 'position', 'top', 'right', 'left',
                                'bottom', 'overflow', 'visibility'
                            ]
                        },
                        {
                            name: 'Dimension',
                            open: false,
                            buildProps: ['width', 'height', 'max-width', 'min-height', 'margin',
                                'padding'
                            ]
                        },
                        {
                            name: 'Typography',
                            open: false,
                            buildProps: ['font-family', 'font-size', 'font-weight', 'letter-spacing',
                                'color', 'line-height', 'text-align', 'text-shadow',
                                'text-decoration'
                            ],
                            properties: [{
                                name: 'Font family',
                                property: 'font-family',
                                type: 'select',
                                defaults: 'Poppins, sans-serif',
                                options: [{
                                        value: 'Arial, Helvetica, sans-serif',
                                        name: 'Arial'
                                    },
                                    {
                                        value: 'Arial Black, Gadget, sans-serif',
                                        name: 'Arial Black'
                                    },
                                    {
                                        value: 'Brush Script MT, sans-serif',
                                        name: 'Brush Script MT'
                                    },
                                    {
                                        value: 'Comic Sans MS, cursive, sans-serif',
                                        name: 'Comic Sans MS'
                                    },
                                    {
                                        value: 'Courier New, Courier, monospace',
                                        name: 'Courier New'
                                    },
                                    {
                                        value: 'Georgia, serif',
                                        name: 'Georgia'
                                    },
                                    {
                                        value: 'Helvetica, sans-serif',
                                        name: 'Helvetica'
                                    },
                                    {
                                        value: 'Impact, Charcoal, sans-serif',
                                        name: 'Impact'
                                    },
                                    {
                                        value: 'Lucida Sans Unicode, Lucida Grande, sans-serif',
                                        name: 'Lucida Sans Unicode'
                                    },
                                    {
                                        value: 'Tahoma, Geneva, sans-serif',
                                        name: 'Tahoma'
                                    },
                                    {
                                        value: 'Times New Roman, Times, serif',
                                        name: 'Times New Roman'
                                    },
                                    {
                                        value: 'Trebuchet MS, Helvetica, sans-serif',
                                        name: 'Trebuchet MS'
                                    },
                                    {
                                        value: 'Verdana, Geneva, sans-serif',
                                        name: 'Verdana'
                                    },
                                    {
                                        value: 'Poppins, sans-serif',
                                        name: 'Poppins'
                                    }
                                ]
                            }]
                        },
                        {
                            name: 'Decorations',
                            open: false,
                            buildProps: ['background-color', 'border-radius', 'border', 'box-shadow',
                                'background', 'background-image'
                            ]
                        },
                        {
                            name: 'Extra',
                            open: false,
                            buildProps: ['opacity', 'transition', 'transform']
                        },
                        {
                            name: 'Flex',
                            open: false,
                            buildProps: ['flex-direction', 'flex-wrap', 'justify-content',
                                'align-items', 'align-content', 'order', 'flex-basis', 'flex-grow',
                                'flex-shrink', 'align-self'
                            ]
                        }
                    ]
                },
                plugins: ['gjs-blocks-basic', 'gjs-plugin-forms'],
                pluginsOpts: {
                    'gjs-blocks-basic': {
                        blocks: ['column1', 'column2', 'column3', 'column3-7', 'text', 'link', 'image',
                            'map', 'button', 'divider'
                        ]
                    },
                    'gjs-plugin-forms': {}
                },
                assetManager: {
                    upload: "{{ route('admin.static-pages.assets') }}",
                    uploadName: 'file',
                    headers: {
                        'X-CSRF-TOKEN': window.csrf_token
                    }
                }
            });

            editor.addStyle(`
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
                `);

            const rightPanelWidth = 300;
            editor.on('load', () => {
                const container = editor.getContainer();
                const viewsPanel = container.querySelector('.gjs-pn-views-container');
                if (viewsPanel) {
                    viewsPanel.style.width = `${rightPanelWidth}px`;
                    viewsPanel.style.minWidth = `${rightPanelWidth}px`;
                    viewsPanel.style.maxWidth = `${rightPanelWidth}px`;
                    viewsPanel.style.flex = `0 0 ${rightPanelWidth}px`;
                    viewsPanel.style.top = '40px';
                    viewsPanel.style.height = 'calc(100% - 40px)';
                }
                const canvas = container.querySelector('.gjs-cv-canvas');
                if (canvas) {
                    canvas.style.width = `calc(100% - ${rightPanelWidth}px)`;
                }
            });

            const formEl = document.getElementById('createStaticPage');
            formEl.addEventListener('keydown', function(e) {
                if (e.key !== 'Enter') return;
                const target = e.target;
                if (!target) return;
                if (target.closest('#gjs') || target.closest('.gjs-pn-panels')) {
                    e.preventDefault();
                }
            });

            $('.save-btn').on('click', function(e) {
                e.preventDefault();
                $('#gjs-html').val(editor.getHtml());
                $('#gjs-css').val(editor.getCss());
                $('#gjs-components').val(JSON.stringify(editor.getComponents()));
                $('#gjs-styles').val(JSON.stringify(editor.getStyle()));

                const validator = validateForm($('form[name="createStaticPage"]'), {}, {});
                if (validator.form()) {
                    $.ajax({
                        url: $('#createStaticPage').attr('action'),
                        method: $('#createStaticPage').attr('method'),
                        data: $('#createStaticPage').serialize(),
                        success: function(response) {
                            swalAlert2(response.status, response.message, response.redirect);
                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 3000);
                        },
                        error: function(response) {
                            swalAlert(response.responseJSON.status, response.responseJSON
                                .errors, 4000);
                        }
                    });
                }
            });
        });
    </script>
@endpush
