@extends('layouts.app')
@section('title', 'Offrequest/create')
@section('content')
    <section class="content">
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title">Add Off Requests</h3>
            </div>
            @if ($errors->any())
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            let errorMessages = '';
                            @foreach ($errors->all() as $error)
                                errorMessages += '{{ $error }}\n';
                            @endforeach

                            Swal.fire({
                                icon: "error",
                                title: "Oops...",
                                text: errorMessages,
                            });
                        });
                    </script>
                @endif
            <div class="card-body">
                <form id="quickForm" action="{{ route('offrequest.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" value="{{ Auth::user()->name }}" readonly>
                    </div>

                    <div class="form-group">

                        <label for="title">Off Type</label>
                        <select class="form-control" id="title" name="title" required>
                            <option value="Sick">Sick</option>
                            <option value="Holiday">Holiday</option>
                            <option value="Family Matters">Family Matters</option>
                            <option value="Absence">Absence</option>
                            <option value="Personal Time">Personal Time</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" class="form-control" id="description" rows="4"
                            placeholder="Enter a description of the leave request" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="start_event">Start Event</label>
                        <input type="datetime-local" name="start_event" class="form-control" id="start_event" required
                            value="{{ old('start_event', now()->format('Y-m-d') . 'T09:00') }}">
                    </div>

                    <div class="form-group">
                        <label for="end_event">End Event</label>
                        <input type="datetime-local" name="end_event" class="form-control" id="end_event" required
                            value="{{ old('end_event', now()->format('Y-m-d') . 'T17:00') }}">
                    </div>


                    <div class="form-group">
                        <label for="manager_id">Select Manager</label>
                        <select name="manager_id" id="manager_id" class="form-control" required>
                            <option value="">Select Manager</option>
                            @foreach ($approvers as $approver)
                                <option value="{{ $approver->user_id }}">{{ $approver->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="image">Upload Proof of Leave</label>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" name="image"
                                    class="custom-file-input @error('image') is-invalid @enderror" id="image">
                                <label class="custom-file-label" for="image">Choose file</label>
                            </div>
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fas fa-upload"></i></span>
                            </div>
                        </div>
                        @error('image')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <button type="submit" id="savecuti" class="btn btn-primary">Save</button>
                    <a href="{{ route('offrequest.index') }}" class="btn btn-secondary">Back</a>
                </form>
            </div>
        </div>
    </section>

    <script>
        function getCurrentDate() {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        const startEventInput = document.getElementById('start_event');
        const endEventInput = document.getElementById('end_event');

        const minDate = getCurrentDate();
        startEventInput.setAttribute('min', minDate);
        endEventInput.setAttribute('min', minDate);

        startEventInput.addEventListener('click', function() {
            if (!startEventInput.value) {
                startEventInput.value = minDate;
            }
        });

        startEventInput.addEventListener('change', function() {
            const startDate = startEventInput.value;
            endEventInput.setAttribute('min', startDate);
            if (!endEventInput.value || endEventInput.value < startDate) {
                endEventInput.value = startDate;
            }
        });

        document.querySelector('.custom-file-input').addEventListener('change', function(e) {
            var fileName = document.getElementById("image").files[0].name;
            var nextSibling = e.target.nextElementSibling;
            nextSibling.innerText = fileName;
        });
    </script>

    <script>
        // Handle the button click for Save
        document.getElementById('savecuti').addEventListener('click', function(event) {
            event.preventDefault(); // Prevent default form submission

            // Menampilkan SweetAlert setelah tombol Save diklik
            Swal.fire({
                position: 'top-center',
                icon: 'success',
                title: 'Your work has been saved',
                showConfirmButton: false,
                timer: 1500
            }).then(function() {
                // Submit form setelah alert selesai
                document.getElementById('quickForm').submit(); // Ensure form ID is correct
            });
        });
    </script>
@endsection
