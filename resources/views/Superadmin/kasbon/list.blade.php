@extends('layouts.app')
@section('title', 'Kasbon List')
@section('content')
    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Kasbon List</h3>
                <div class="card-tools">
                    </a>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>

            <!-- Tabel untuk daftar permohonan overtime -->
            <div class="table-responsive">
                <table class="table table-striped projects">
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
                                    <td>{{ $k->employee->first_name }} {{ $k->employee->last_name }}</td>
                                    <td class="text-center">
                                        @if($k->employee && $k->employee->current_salary)
                                            {{ number_format($k->employee->current_salary, 0, ',', '.') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $k->total_amount }}</td>
                                    <td>{{ $k->installments }}</td>
                                    <td>{{ $k->installment_amount }}</td>
                                    <td>{{ $k->start_month }}</td>
                                    <td class="text-center">
                                        <span class="badge
                                                                    @if($k->status == 'ongoing') bg-warning
                                                                    @elseif ($k->status == 'completed') bg-success
                                                                    @else bg-danger @endif">
                                            {{ ucfirst($k->status) }}
                                        </span>
                                    </td>
                                    {{-- <td>{{ \Carbon\Carbon::parse($overtime->overtime_date)->format('d-m-Y') }}</td>
                                    <td>{{ $overtime->duration }} hours</td>
                                    <td>{{ $overtime->notes }}</td> --}}
                                    
                                    {{-- <td>
                                        <span class="badge {{ $overtime->status == 'approved' ? 'bg-success' : ($overtime->status == 'rejected' ? 'bg-danger' : 'bg-secondary') }}">
                                            {{ ucfirst($overtime->status) }}
                                        </span>
                                    </td> --}}
                               
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
