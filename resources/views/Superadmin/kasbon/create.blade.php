@extends('layouts.app')
@section('title', 'Kasbon/Create')
@section('content')
    <div class="content">
        <section class="content">
            <div class="container-fluid">
                <div class="card card-primary">

                    @if (session('error'))
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: '{{ session('error') }}',
                                });
                            });
                        </script>
                    @endif

                    <div class="card-header">
                        <h3 class="card-title">Add Cash Advance</h3>
                    </div>

                    @if ($errors->any())
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                let errorMessages = '';
                                @foreach($errors->all() as $error)
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

                    <form action="{{ route('kasbon.store') }}" method="POST" id="kasbonForm">
                        @csrf
                        <div class="card-body">
                            <div class="row">

                                <!-- LEFT COLUMN -->
                                <div class="col-md-6">
                                    {{-- Employee --}}
                                    <div class="mb-3">
                                        <label for="employee_id" class="form-label">Employee</label>
                                        <select name="employee_id" id="employee_id" class="form-select" required>
                                            <option value="">-- Select Employee --</option>
                                            @foreach ($employees as $emp)
                                                <option value="{{ $emp->employee_id }}" {{ old('employee_id') == $emp->employee_id ? 'selected' : '' }}
                                                    {{ (old('employee_id', $employee_id ?? '') == $emp->employee_id) ? 'selected' : '' }}>
                                                    {{ $emp->first_name }} {{ $emp->last_name }} | {{ $emp->division->name ?? 'No Division' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Total Amount --}}
                                    <div class="mb-3">
                                        <label for="total_amount" class="form-label">Total Amount (Rp)</label>
                                        <input type="text" name="total_amount" id="total_amount" class="form-control"
                                            value="{{ old('total_amount') }}" oninput="formatCurrency(this)" required>
                                    </div>

                                    {{-- Installments --}}
                                    <div class="mb-3">
                                        <label for="installments" class="form-label">Installments (x)</label>
                                        <select name="installments" id="installments" class="form-control" required>
                                            <option value="">-- Select Installments --</option>
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- RIGHT COLUMN -->
                                <div class="col-md-6">
                                    {{-- Start Month --}}
                                    <div class="mb-3">
                                        <label for="start_month" class="form-label">Start Month</label>
                                        <input type="month" name="start_month" id="start_month" class="form-control"
                                            value="{{ old('start_month', now()->format('Y-m')) }}" required>
                                    </div>
                                </div>

                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                                <a href="{{ route('kasbon.index') }}" class="btn btn-secondary">Back</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>

    <script>
        // Format angka (contoh: 1000000 -> 1.000.000)
        function formatCurrency(input) {
            let value = input.value.replace(/\D/g, '');
            input.value = value ? new Intl.NumberFormat('id-ID').format(value) : '';
        }

        // Saat klik Save, hapus titik format uang sebelum submit
        document.getElementById('saveBtn').addEventListener('click', function (event) {
            event.preventDefault();
            const totalAmountInput = document.getElementById('total_amount');
            totalAmountInput.value = totalAmountInput.value.replace(/\./g, '');
            Swal.fire({
                position: 'top-center',
                icon: 'success',
                title: 'Cash Advance Succesfully Saved',
                showConfirmButton: false,   
                timer: 1200
            }).then(() => {
                document.getElementById('kasbonForm').submit();
            });
        });
    </script>
@endsection