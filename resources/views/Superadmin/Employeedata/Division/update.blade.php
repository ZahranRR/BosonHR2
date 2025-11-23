@extends('layouts.app')
@section('title', 'Division/Update')
@section('content')
<div class="content">
    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Edit Division</h3>
                </div>

                @if ($errors->any())
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        let errorMessages = '';
                        @foreach($errors -> all() as $error)
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

                <form action="{{ route('divisions.update', $division) }}" method="POST" id="divisionForm">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <!-- Name -->
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" name="name" class="form-control"
                                        value="{{ old('name', $division->name) }}" required>
                                </div>

                                <!-- Description -->
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="3">{{ old('description', $division->description) }}</textarea>
                                </div>

                                <!-- Hourly Rate -->
                                <div class="mb-3">
                                    <label for="hourly_rate" class="form-label">Hourly Rate</label>
                                    <input type="text" name="hourly_rate" id="hourly_rate" class="form-control"
                                        value="{{ old('hourly_rate', $division->hourly_rate) }}" oninput="formatCurrency(this)">
                                </div>

                                <div class="form-group">
                                    <label>Time Check-In</label>
                                    <input type="time" name="check_in_time" class="form-control" required
                                        value="{{ old('check_in_time', \Carbon\Carbon::parse($division->check_in_time)->format('H:i')) }}">

                                </div>

                                <div class="form-group">
                                    <label>Time Check-Out</label>
                                    <input type="time" name="check_out_time" class="form-control" required
                                        value="{{ old('check_out_time', \Carbon\Carbon::parse($division->check_out_time)->format('H:i')) }}">
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <!-- Overtime -->
                                <div class="mb-3">
                                    <label for="has_overtime" class="form-label">Overtime</label>
                                    <select name="has_overtime" class="form-select" required>
                                        <option value="1" {{ old('has_overtime', $division->has_overtime) == '1' ? 'selected' : '' }}>Yes</option>
                                        <option value="0" {{ old('has_overtime', $division->has_overtime) == '0' ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>

                                <!-- Work Days -->
                                <div class="mb-3">
                                    <label class="form-label">Work Day</label>
                                    <div class="row">
                                        @php
                                        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                        @endphp

                                        @foreach ($days as $day)
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="work_days[]" value="{{ $day }}"
                                                    {{ is_array(old('work_days', $division->work_days)) && in_array($day, old('work_days', $division->work_days)) ? 'checked' : '' }}>
                                                <label class="form-check-label">{{ $day }}</label>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div> <!-- end row -->

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary" id="saveBtn">Update</button>
                            <a href="{{ route('divisions.index') }}" class="btn btn-secondary">Back</a>
                        </div>
                    </div> <!-- end card-body -->
                </form>
            </div>
        </div>
    </section>
</div>

<script>
    // Format angka saat diketik (ex: 50000 => 50.000)
    function formatCurrency(input) {
        let value = input.value.replace(/\D/g, '');
        if (value) {
            input.value = new Intl.NumberFormat('id-ID').format(value);
        } else {
            input.value = '';
        }
    }

    // Saat klik Save, tampilkan notifikasi lalu submit
    document.getElementById('saveBtn').addEventListener('click', function(event) {
        event.preventDefault();

        // Unformat hourly_rate agar yang dikirim ke server adalah angka mentah
        const hourlyInput = document.getElementById('hourly_rate');
        if (hourlyInput) {
            hourlyInput.value = hourlyInput.value.replace(/\./g, '');
        }

        Swal.fire({
            position: 'top-center',
            icon: 'success',
            title: 'Changes saved!',
            showConfirmButton: false,
            timer: 1500
        }).then(function() {
            document.getElementById('divisionForm').submit();
        });
    });
</script>
@endsection