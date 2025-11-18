@extends('layouts.app')
@section('title', 'Kasbon/Edit')
@section('content')
    <div class="content">
        <section class="content">
            <div class="container-fluid">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Edit Cash Advance</h3>
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

                    <form action="{{ route('kasbon.update', $kasbon->cash_advance_id) }}" method="POST" id="kasbonForm">
                        @csrf
                        @method('PUT')
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
                                                    {{ (old('employee_id', $kasbon->employee_id) == $emp->employee_id) ? 'selected' : '' }}>
                                                    {{ $emp->first_name }} {{ $emp->last_name }} | {{ $emp->division->name ?? 'No Division' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Total Amount --}}
                                    <div class="mb-3">
                                        <label for="total_amount" class="form-label">Total Amount (Rp)</label>
                                        <input type="text" name="total_amount" id="total_amount" class="form-control"
                                            value="{{ old('total_amount', number_format($kasbon->total_amount, 0, ',', '.')) }}" oninput="formatCurrency(this)" required>
                                    </div>

                                    {{-- Installments --}}
                                    <div class="mb-3">
                                        <label for="installments" class="form-label">Installments (x)</label>
                                        <select name="installments" id="installments" class="form-control" required>
                                            <option value="">-- Select Installments --</option>
                                            @for($i = 1; $i <= 3; $i++)
                                            <option value="{{ $i }}" {{ old('installments', $kasbon->installments) == $i ? 'selected' : '' }}>
                                                {{ $i }}
                                            </option>
                                        @endfor
                                        </select>
                                    </div>
                                </div>

                                <!-- RIGHT COLUMN -->
                                <div class="col-md-6">
                                    {{-- Start Month --}}
                                    <div class="mb-3">
                                        <label for="start_month" class="form-label">Start Month</label>
                                        <input type="month" name="start_month" id="start_month" class="form-control"
                                            value="{{ old('start_month', \Carbon\Carbon::parse($kasbon->start_month)->format('Y-m')) }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="installment_amount" class="form-label">Installment Amount (Rp)</label>
                                        <input type="text" name="installment_amount"  id="installment_amount" class="form-control"
                                            value="{{ old('installment_amount', number_format ($kasbon->installment_amount, 0, ',', '.')) }}" oninput="formatCurrency(this)">
                                    </div>
                                </div>

                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary" id="saveBtn">Update</button>
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
            const form = document.getElementById('kasbonForm');
            const totalAmountInput = document.getElementById('total_amount');
            totalAmountInput.value = totalAmountInput.value.replace(/\./g, '');
            Swal.fire({
                position: 'top-center',
                icon: 'success',
                title: 'Cash Advance Succesfully Saved',
                showConfirmButton: false,   
                timer: 1200
            }).then(() => {
                form.submit();
            });
        });
    </script>
@endsection