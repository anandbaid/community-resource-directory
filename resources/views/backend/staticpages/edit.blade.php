@extends('backend.layouts.app')
@section('title', ' | Edit Static Page')
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
                    <h3 class="mb-0">Edit Page</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Edit Page
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

                        <form name="editStaticPage" id="editStaticPage" class="px-3"
                            action="{{ route('admin.static-pages.update', $staticPage->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="card-body">
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-2">
                                        <label class="form-label">Page Title</label>
                                    </div>
                                    <div class="col-md-10">
                                        <input type="text" class="form-control" name="title"
                                            value="{{ $staticPage->title }}" required>
                                    </div>
                                </div>
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-2">
                                        <label class="form-label">Slug</label>
                                    </div>
                                    <div class="col-md-10">
                                        <input type="text" class="form-control" value="{{ $staticPage->slug }}" disabled>
                                    </div>
                                </div>
                                @if (empty($isLegacy))
                                    <div class="form-group row mb-3 align-center">
                                        <div class="col-md-2">
                                            <label class="form-label">Status</label>
                                        </div>
                                        <div class="col-md-10">
                                            <select class="form-select" name="status" required>
                                                <option value="active"
                                                    {{ $staticPage->status === 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="inactive"
                                                    {{ $staticPage->status === 'inactive' ? 'selected' : '' }}>Inactive
                                                </option>
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
                                                    value="1" {{ $staticPage->show_in_header ? 'checked' : '' }}>
                                                <label class="form-check-label">Show in header</label>
                                            </div>
                                            <select class="form-select mt-2" name="header_parent">
                                                <option value="">Select parent menu</option>
                                                @foreach ($menuParents as $key => $label)
                                                    <option value="{{ $key }}"
                                                        {{ $staticPage->header_parent === $key ? 'selected' : '' }}>
                                                        {{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <textarea class="form-control mt-2" name="header_menu_description" rows="2"
                                                placeholder="Header menu short description (optional)">{{ $staticPage->header_menu_description }}</textarea>
                                            <input type="number" class="form-control mt-2" name="header_order"
                                                value="{{ $staticPage->header_order }}"
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
                                                    value="1" {{ $staticPage->show_in_footer ? 'checked' : '' }}>
                                                <label class="form-check-label">Show in footer</label>
                                            </div>
                                            <input type="number" class="form-control mt-2" name="footer_order"
                                                value="{{ $staticPage->footer_order }}"
                                                placeholder="Footer order (optional)">
                                        </div>
                                    </div>
                                @endif
                                @if (!empty($isLegacy))
                                    <div class="form-group row mb-3 align-center">
                                        <div class="col-md-2">
                                            <label class="form-label">Description</label>
                                        </div>
                                        <div class="col-md-10">
                                            <textarea class="form-control h-300 ck-editor-content" name="description">{!! $staticPage->description ?? '' !!}</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3 align-center">
                                        <div class="col-md-2">
                                            <label class="form-label">Content 1</label>
                                        </div>
                                        <div class="col-md-10">
                                            <textarea class="form-control h-300 ck-editor-content" name="content_1">{!! $staticPage->content_1 ?? '' !!}</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3 align-center">
                                        <div class="col-md-2">
                                            <label class="form-label">Content 2</label>
                                        </div>
                                        <div class="col-md-10">
                                            <textarea class="form-control h-300 ck-editor-content" name="content_2">{!! $staticPage->content_2 ?? '' !!}</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3 align-center">
                                        <div class="col-md-2">
                                            <label class="form-label">Content 3</label>
                                        </div>
                                        <div class="col-md-10">
                                            <textarea class="form-control h-300 ck-editor-content" name="content_3">{!! $staticPage->content_3 ?? '' !!}</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3 align-center">
                                        <div class="col-md-2">
                                            <label class="form-label">Content 4</label>
                                        </div>
                                        <div class="col-md-10">
                                            <textarea class="form-control h-300 ck-editor-content" name="content_4">{!! $staticPage->content_4 ?? '' !!}</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3 align-center">
                                        <div class="col-md-2">
                                            <label class="form-label">Static Items</label>
                                        </div>
                                        <div class="col-md-10">
                                            <div id="static-items-wrapper">
                                                @foreach ($staticPageItems as $itemIndex => $item)
                                                    <div class="card mb-3 static-item"
                                                        data-item-index="{{ $itemIndex }}">
                                                        <div class="card-body">
                                                            <input type="hidden" name="items[{{ $itemIndex }}][id]"
                                                                value="{{ $item->id }}">
                                                            <input type="hidden"
                                                                name="items[{{ $itemIndex }}][delete]" value="0"
                                                                class="static-item-delete">
                                                            <div class="row mb-3">
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Title</label>
                                                                    <input type="text" class="form-control"
                                                                        name="items[{{ $itemIndex }}][title]"
                                                                        value="{{ $item->title }}">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Sub Title</label>
                                                                    <input type="text" class="form-control"
                                                                        name="items[{{ $itemIndex }}][sub_title]"
                                                                        value="{{ $item->sub_title }}">
                                                                </div>
                                                            </div>
                                                            <div class="row mb-3">
                                                                <div class="col-md-8">
                                                                    <label class="form-label">Description</label>
                                                                    <textarea class="form-control ck-editor-content" name="items[{{ $itemIndex }}][description]" rows="3">{{ $item->description }}</textarea>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Order</label>
                                                                    <input type="number" class="form-control"
                                                                        name="items[{{ $itemIndex }}][order]"
                                                                        value="{{ $item->order ?? 0 }}">
                                                                </div>
                                                            </div>
                                                            <div class="row mb-3">
                                                                <div class="col-md-8">
                                                                    <label class="form-label">Link (optional)</label>
                                                                    <input type="text" class="form-control"
                                                                        name="items[{{ $itemIndex }}][link]"
                                                                        value="{{ $item->link }}">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Image</label>
                                                                    <input type="file" class="form-control"
                                                                        name="items[{{ $itemIndex }}][image]">
                                                                    <input type="hidden"
                                                                        name="items[{{ $itemIndex }}][image_existing]"
                                                                        value="{{ $item->image }}">
                                                                    @if (!empty($item->image))
                                                                        <div class="mt-2">
                                                                            <img src="{{ asset($item->image) }}"
                                                                                alt="Item Image"
                                                                                style="max-width: 120px;">
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <button type="button"
                                                                class="btn btn-danger btn-sm remove-static-item">Remove</button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <button type="button" class="btn btn-primary btn-sm"
                                                id="add-static-item">Add Item</button>
                                        </div>
                                    </div>
                                @else
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
                                @endif
                            </div>
                            <div class="card-footer d-flex">
                                <button type="submit" class="btn btn-primary save-btn">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('custom-scripts')
    @if (empty($isLegacy))
        <script src="https://unpkg.com/grapesjs"></script>
        <script src="https://unpkg.com/grapesjs-blocks-basic"></script>
        <script src="https://unpkg.com/grapesjs-plugin-forms"></script>
    @else
        <script src="{{ asset('plugins/ckeditor/ckeditor.js') }}"></script>
    @endif
    <script>
        window.addEventListener('load', function() {
            const isLegacy = @json(!empty($isLegacy));
            let editor = null;
            if (!isLegacy) {
                editor = grapesjs.init({
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
                                buildProps: ['font-family', 'font-size', 'font-weight',
                                    'letter-spacing', 'color', 'line-height', 'text-align',
                                    'text-shadow', 'text-decoration'
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
                                buildProps: ['background-color', 'border-radius', 'border',
                                    'box-shadow', 'background', 'background-image'
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
                                    'align-items', 'align-content', 'order', 'flex-basis',
                                    'flex-grow', 'flex-shrink', 'align-self'
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

                const initialHtml = @json($pageContent['html'] ?? '');
                const initialCss = @json($pageContent['css'] ?? '');
                if (initialHtml) {
                    editor.setComponents(initialHtml);
                }
                if (initialCss) {
                    editor.setStyle(initialCss);
                }
            }

            const formEl = document.getElementById('editStaticPage');
            formEl.addEventListener('keydown', function(e) {
                if (e.key !== 'Enter') return;
                const target = e.target;
                if (!target) return;
                if (target.closest('#gjs') || target.closest('.gjs-pn-panels')) {
                    e.preventDefault();
                }
            });

            let staticItemIndex = {{ $staticPageItems->count() }};
            $('#add-static-item').on('click', function() {
                const index = staticItemIndex++;
                const itemHtml = `
                    <div class="card mb-3 static-item" data-item-index="${index}">
                        <div class="card-body">
                            <input type="hidden" name="items[${index}][id]" value="">
                            <input type="hidden" name="items[${index}][delete]" value="0" class="static-item-delete">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Title</label>
                                    <input type="text" class="form-control" name="items[${index}][title]" value="">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Sub Title</label>
                                    <input type="text" class="form-control" name="items[${index}][sub_title]" value="">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="items[${index}][description]" rows="3"></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Order</label>
                                    <input type="number" class="form-control" name="items[${index}][order]" value="0">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label class="form-label">Link (optional)</label>
                                    <input type="text" class="form-control" name="items[${index}][link]" value="">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Image</label>
                                    <input type="file" class="form-control" name="items[${index}][image]">
                                </div>
                            </div>
                            <button type="button" class="btn btn-danger btn-sm remove-static-item">Remove</button>
                        </div>
                    </div>
                `;
                $('#static-items-wrapper').append(itemHtml);
            });

            $(document).on('click', '.remove-static-item', function() {
                const itemCard = $(this).closest('.static-item');
                const deleteInput = itemCard.find('.static-item-delete');
                if (deleteInput.length) {
                    deleteInput.val('1');
                     itemCard.hide()
                } else {
                    itemCard.remove();
                }
            });

            $('.save-btn').on('click', function(e) {
                e.preventDefault();
                if (editor) {
                    $('#gjs-html').val(editor.getHtml());
                    $('#gjs-css').val(editor.getCss());
                    $('#gjs-components').val(JSON.stringify(editor.getComponents()));
                    $('#gjs-styles').val(JSON.stringify(editor.getStyle()));
                }
                if (isLegacy && window.editors && window.editors.length) {
                    window.editors.forEach(function(entry) {
                        if (entry && entry.element && entry.editor) {
                            entry.element.value = entry.editor.getData();
                        }
                    });
                }

                const validator = validateForm($('form[name="editStaticPage"]'), {}, {});
                if (validator.form()) {
                    const formElement = $('form[name="editStaticPage"]')[0];
                    const formData = new FormData(formElement);
                    $.ajax({
                        url: $('#editStaticPage').attr('action'),
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            swalAlert(response.status, response.message);
                            setTimeout(function() {
                               location.reload();
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
