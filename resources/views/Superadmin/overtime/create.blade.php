@extends('layouts.app')
@section('title', 'Overtime/create')
@section('content')

    <div class="content">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-xl-6">
                        <h1>Overtime</h1>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Create Overtime</h3>
                    </div>

                    <form action="{{ route('overtime.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">

                            <!-- Employee Information (Readonly) -->
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" class="form-control" id="name" value="{{ Auth::user()->name }}" readonly>
                            </div>

                            <!-- Overtime Date -->
                            <div class="form-group">
                                <label for="overtime_date">Overtime Date</label>
                                <input type="date" name="overtime_date" id="overtime_date" class="form-control" required>
                            </div>

                            <!-- Overtime Duration -->
                            <div class="form-group">
                                <label for="duration" class="form-label">Duration (in hours)</label>
                                <select name="duration" id="duration" class="form-control" required>
                                    <option value="">-- Select Duration --</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                </select>
                            </div>


                            <!-- Overtime Notes -->
                            <div class="form-group">
                                <label for="notes">Notes</label>
                                <textarea name="notes" id="notes" class="form-control" required></textarea>
                            </div>

                            <!-- Attachment File (Image) -->
                            {{-- <div class="form-group">
                                <label for="attachment">Attachment (Image)</label>
                                <input type="file" name="attachment" id="attachment" class="custom-file-input @error('image') is-invalid @enderror">
                                <small class="text-muted">Upload Attachment (format: jpg, png, jpeg)</small>
                            </div> --}}

                            <div class="form-group">
                                <label for="attachment">Attachment (Image)</label>
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" name="attachment"
                                            class="custom-file-input @error('attachment') is-invalid @enderror" id="attachment">
                                        <label class="custom-file-label" for="attachment">Choose file</label>
                                    </div>
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fas fa-upload"></i></span>
                                    </div>
                                </div>
                                @error('attachment')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <!-- Select Manager -->
                            <div class="form-group">
                                <label for="manager_id">Select Manager</label>
                                <select name="manager_id" id="manager_id" class="form-control" required>
                                    <option value="">Select Manager</option>
                                    @foreach ($managers as $manager)
                                        <option value="{{ $manager->user_id }}">{{ $manager->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        <!-- Save Button -->
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Overtime Error',
                text: '{{ session('error') }}',
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: `{!! implode('<br>', $errors->all()) !!}`
            });
        </script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form');
            const attachmentInput = document.querySelector('#attachment');

            // Validasi error lainnya sebelum submit
            form.addEventListener('submit', function (e) {
                const date = document.querySelector('#overtime_date').value;
                const duration = document.querySelector('#duration').value;
                const notes = document.querySelector('#notes').value;
                const manager = document.querySelector('#manager_id').value;

                if (!date || !duration || !notes || !manager) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Missing Information',
                        text: 'Pastikan semua field sudah diisi dengan benar.',
                    });
                    return;
                }

                // Pastikan durasi valid (1 atau 2 jam)
                if (duration !== '1' && duration !== '2') {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Duration',
                        text: 'Durasi lembur hanya boleh 1 atau 2 jam.',
                    });
                }
            });
        });
    </script>

@endsection