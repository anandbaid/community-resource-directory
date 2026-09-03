<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

import FieldError from '@/Components/FieldError.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';

const props = defineProps({
    submitUrl: { type: String, required: true },
    // Null until an import has actually run.
    lastImport: { type: Object, default: null },
});

const form = useForm({ uploaded_file: null });
const showErrors = ref(false);

function onFile(event) {
    form.uploaded_file = event.target.files[0] ?? null;
}

function submit() {
    form.post(props.submitUrl, {
        forceFormData: true,
        onSuccess: () => form.reset('uploaded_file'),
    });
}
</script>

<template>
    <PageHeader
        title="Import Organizations"
        :breadcrumbs="[{ label: 'Home' }, { label: 'Import Organizations' }]"
    />

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card position-relative">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Import Organizations</h4>
                        </div>

                        <form class="px-3" @submit.prevent="submit">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="uploaded_file">Upload XLS File</label>
                                    <input
                                        id="uploaded_file"
                                        type="file"
                                        class="form-control"
                                        accept=".xls,.xlsx,.ods"
                                        required
                                        @change="onFile"
                                    >
                                    <FieldError :message="form.errors.uploaded_file" />
                                </div>
                            </div>

                            <div class="card-footer text-center">
                                <button type="submit" class="btn btn-sm btn-primary save-btn" :disabled="form.processing">
                                    {{ form.processing ? 'Uploading…' : 'Upload' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Last Imported Response</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th scope="col">Status</th>
                                            <th scope="col">Total Records</th>
                                            <th scope="col">Imported Records</th>
                                            <th scope="col">Error</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="lastImport">
                                            <td>{{ lastImport.status }}</td>
                                            <td>{{ lastImport.total }}</td>
                                            <td>{{ lastImport.imported }}</td>
                                            <td>
                                                <button
                                                    v-if="lastImport.errors.length"
                                                    type="button"
                                                    class="btn btn-primary"
                                                    @click="showErrors = !showErrors"
                                                >
                                                    <i class="fas fa-eye"></i>
                                                    {{ lastImport.errors.length }}
                                                </button>
                                                <span v-else class="text-muted">None</span>
                                            </td>
                                        </tr>
                                        <tr v-else>
                                            <td colspan="4" class="text-center">No records found</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!--
                                The Blade page put these in a Bootstrap modal.
                                Inline keeps them selectable and printable, which
                                is what an import log is for.
                            -->
                            <div v-if="showErrors && lastImport" class="mt-3">
                                <h6>Errors</h6>
                                <ul class="list-unstyled mb-0">
                                    <li v-for="(error, index) in lastImport.errors" :key="index">
                                        Row {{ error.row }}: {{ error.message }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
