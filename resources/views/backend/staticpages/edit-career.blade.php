@extends('backend.layouts.app')
@section('title', ' | Edit Career Page')

@section('content')
    @php
        $segmentDefaults = [
            1 => [
                'text' => 'General Job Search Information',
                'image' => 'assets/img/segment-3.png',
            ],
            2 => [
                'text' => 'Where Can I Locate Job Openings',
                'image' => 'assets/img/segment-4.jpg',
            ],
            3 => [
                'text' => 'Interview Process & Success On The Job',
                'image' => 'assets/img/segment-2.png',
            ],
            4 => [
                'text' => 'How To Market Yourself',
                'image' => 'assets/img/segment-1.jpg',
            ],
        ];
        $segmentValues = [];
        for ($i = 1; $i <= 4; $i++) {
            $raw = $staticPage->{"content_$i"} ?? '';
            $decoded = json_decode((string) $raw, true);
            $segmentValues[$i] = [
                'text' => is_array($decoded) ? $decoded['text'] ?? '' : '',
                'description' => is_array($decoded) ? $decoded['description'] ?? '' : '',
                'image' => is_array($decoded) ? $decoded['image'] ?? '' : '',
            ];
        }
    @endphp

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Edit Career Page</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Edit Career Page
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
                            <h4 class="card-title mb-0">Career Page Details</h4>
                        </div>

                        <form name="editCareerPage" id="editCareerPage" class="px-3"
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
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-2">
                                        <label class="form-label">Description</label>
                                    </div>
                                    <div class="col-md-10">
                                        <textarea class="form-control h-300 ck-editor-content" name="description">{!! $staticPage->description ?? '' !!}</textarea>
                                    </div>
                                </div>

                                @for ($i = 1; $i <= 4; $i++)
                                    @php
                                        $segmentText = !empty($segmentValues[$i]['text'])
                                            ? $segmentValues[$i]['text']
                                            : $segmentDefaults[$i]['text'];
                                        $segmentDescription = $segmentValues[$i]['description'] ?? '';
                                        $segmentImage = !empty($segmentValues[$i]['image'])
                                            ? $segmentValues[$i]['image']
                                            : $segmentDefaults[$i]['image'];
                                        $segmentTopics = $careerTopicsBySegment[$i] ?? [];
                                    @endphp
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h5 class="mb-0">Segment {{ $i }}</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Image</label>
                                                    <input type="file" class="form-control"
                                                        name="segment_items[{{ $i }}][image]">
                                                </div>
                                                <div class="col-md-8">
                                                    <label class="form-label">Image Text</label>
                                                    <input type="text" class="form-control"
                                                        name="segment_items[{{ $i }}][text]"
                                                        value="{{ $segmentText }}" required>
                                                </div>
                                                @if (!empty($segmentImage))
                                                    <div class="mt-1">
                                                        <img src="{{ asset($segmentImage) }}"
                                                            alt="Segment {{ $i }} Image"
                                                            style="max-width: 160px;">
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md-12">
                                                    <label class="form-label">Description</label>
                                                    <textarea class="form-control ck-editor-content" rows="3" name="segment_items[{{ $i }}][description]"
                                                        >{{ $segmentDescription }}</textarea>
                                                </div>
                                            </div>


                                            <hr>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="mb-0">Topics</h6>
                                                <button type="button" class="btn btn-sm btn-primary add-topic-btn"
                                                    data-segment="{{ $i }}">Add Topic</button>
                                            </div>
                                            <div class="segment-topics-wrapper" id="segment-topics-{{ $i }}">
                                                @foreach ($segmentTopics as $topicIndex => $topic)
                                                    <div class="card mb-2 topic-item">
                                                        <div class="card-body">
                                                            <input type="hidden"
                                                                name="topic_items[{{ $i }}][{{ $topicIndex }}][id]"
                                                                value="{{ $topic->id }}">
                                                            <input type="hidden"
                                                                name="topic_items[{{ $i }}][{{ $topicIndex }}][delete]"
                                                                value="0" class="topic-item-delete">
                                                            <input type="hidden"
                                                                name="topic_items[{{ $i }}][{{ $topicIndex }}][image_existing]"
                                                                value="{{ $topic->image ?? '' }}">
                                                            <div class="row mb-2">
                                                                <div class="col-md-8">
                                                                    <label class="form-label">Title</label>
                                                                    <input type="text" class="form-control"
                                                                        name="topic_items[{{ $i }}][{{ $topicIndex }}][title]"
                                                                        value="{{ $topic->title }}" required>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Order</label>
                                                                    <input type="number" class="form-control"
                                                                        name="topic_items[{{ $i }}][{{ $topicIndex }}][order]"
                                                                        value="{{ $topic->order ?? 0 }}">
                                                                </div>
                                                            </div>
                                                            <div class="row mb-2">
                                                                <div class="col-md-8">
                                                                    <label class="form-label">Description</label>
                                                                    <textarea class="form-control ck-editor-content" rows="3"
                                                                        name="topic_items[{{ $i }}][{{ $topicIndex }}][description]">{{ $topic->description }}</textarea>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Image</label>
                                                                    <input type="file" class="form-control"
                                                                        name="topic_items[{{ $i }}][{{ $topicIndex }}][image]"
                                                                        {{ empty($topic->image) ? 'required' : '' }}>
                                                                    @if (!empty($topic->image))
                                                                        <div class="mt-2">
                                                                            <img src="{{ asset($topic->image) }}"
                                                                                alt="Topic image"
                                                                                style="max-width: 120px;">
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <button type="button"
                                                                class="btn btn-sm btn-danger remove-topic-btn">Remove</button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                            <div class="card-footer d-flex">
                                <button type="submit" class="btn btn-primary save-btn">Update Career Page</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-scripts')
    <script src="{{ asset('plugins/ckeditor/ckeditor.js') }}"></script>
    <script>
        window.addEventListener('load', function() {
            const initTopicEditor = function(element) {
                if (!element || typeof ClassicEditor === 'undefined') return;
                if (element.dataset.editorInitialized === '1') return;
                ClassicEditor.create(element)
                    .then((editor) => {
                        window.editors = window.editors || [];
                        window.editors.push({
                            element: element,
                            editor: editor
                        });
                        element.dataset.editorInitialized = '1';
                    })
                    .catch((error) => {
                        console.error('CKEditor initialization error:', error);
                    });
            };

            const topicIndexes = {
                1: {{ count($careerTopicsBySegment[1] ?? []) }},
                2: {{ count($careerTopicsBySegment[2] ?? []) }},
                3: {{ count($careerTopicsBySegment[3] ?? []) }},
                4: {{ count($careerTopicsBySegment[4] ?? []) }}
            };

            $(document).on('click', '.add-topic-btn', function() {
                const segment = Number($(this).data('segment'));
                const index = topicIndexes[segment]++;
                const topicHtml = `
                    <div class="card mb-2 topic-item">
                        <div class="card-body">
                            <input type="hidden" name="topic_items[${segment}][${index}][id]" value="">
                            <input type="hidden" name="topic_items[${segment}][${index}][delete]" value="0" class="topic-item-delete">
                            <div class="row mb-2">
                                <div class="col-md-8">
                                    <label class="form-label">Title</label>
                                    <input type="text" class="form-control" name="topic_items[${segment}][${index}][title]" value="" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Order</label>
                                    <input type="number" class="form-control" name="topic_items[${segment}][${index}][order]" value="0">
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-8">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control ck-editor-content" rows="3" name="topic_items[${segment}][${index}][description]"></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Image</label>
                                    <input type="file" class="form-control" name="topic_items[${segment}][${index}][image]" required>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-danger remove-topic-btn">Remove</button>
                        </div>
                    </div>
                `;
                $(`#segment-topics-${segment}`).append(topicHtml);
                const newTopic = $(`#segment-topics-${segment} .topic-item`).last();
                const topicDescription = newTopic.find('textarea.ck-editor-content').get(0);
                initTopicEditor(topicDescription);
            });

            $(document).on('click', '.remove-topic-btn', function() {
                const card = $(this).closest('.topic-item');
                const deleteInput = card.find('.topic-item-delete');
                if (deleteInput.length) {
                    deleteInput.val('1');
                    card.hide();
                } else {
                    card.remove();
                }
            });

            let isSubmitting = false;
            $('#editCareerPage').on('submit', function(e) {
                e.preventDefault();
                if (isSubmitting) {
                    return;
                }

                if (window.editors && window.editors.length) {
                    window.editors.forEach(function(entry) {
                        if (entry && entry.element && entry.editor) {
                            entry.element.value = entry.editor.getData();
                        }
                    });
                }

                const validator = validateForm($('form[name="editCareerPage"]'), {}, {});
                if (!validator.form()) {
                    return;
                }

                isSubmitting = true;
                const $submitBtn = $('.save-btn');
                const originalBtnText = $submitBtn.text();
                $submitBtn.prop('disabled', true).text('Updating...');

                const formElement = $('#editCareerPage')[0];
                const formData = new FormData(formElement);
                $.ajax({
                    url: $('#editCareerPage').attr('action'),
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
                        isSubmitting = false;
                        $submitBtn.prop('disabled', false).text(originalBtnText);
                        swalAlert(response.responseJSON.status, response.responseJSON.errors,
                            4000);
                    }
                });
            });
        });
    </script>
@endpush
