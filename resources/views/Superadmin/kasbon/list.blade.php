@extends('layouts.app')
@section('title', 'Kasbon List')
@section('content')
    <style>
        .table th, .table td {
            vertical-align: middle !important;
            text-align: center !important;
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }
        
        .table td:first-child, .table th:first-child {
            text-align: left !important;
        }
    </style>
    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Kasbon List</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>

            <!-- Tabel untuk daftar permohonan overtime -->
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Employee Name</th>
                            <th>Current Salary</th>
                            <th>Total Amount</th>
                            <th>Installments</th>
                            <th>Per Installments</th>
                            <th>Start Month</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                        <tbody>
                            @forelse ($kasbonlists as $k)
                        
                                <tr>
                                    <td class="text-left">{{ $k->employee->first_name }} {{ $k->employee->last_name }}</td>
                                    <td class="text-left"> Rp. 
                                        @if($k->employee && $k->employee->current_salary)
                                             {{ number_format($k->employee->current_salary, 0, ',', '.') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="text-center">Rp. {{ number_format($k->total_amount, 0, ',', '.') }}</td>
                                    <td class="text-center">{{ $k->installments }}</td>
                                    <td class="text-center">Rp. {{ number_format($k->installment_amount, 0, ',', '.') }}</td>
                                    <td class="text-center">{{ $k->start_month }}</td>
                                    <td class="text-center">
                                        <span class="badge
                                                                    @if($k->status == 'ongoing') bg-warning
                                                                    @elseif ($k->status == 'completed') bg-success
                                                                    @else bg-danger @endif">
                                            {{ ucfirst($k->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">There are no cash advance available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                </table>
            </div>
        </div>
    </section>

    <script>
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
            });
        @elseif (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '{{ session('error') }}',
            });
        @endif
    </script>

@endsection
