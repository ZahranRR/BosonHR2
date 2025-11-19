@extends('layouts.app')
@section('title', 'Kasbon Index')
@section('content')
    <style>
        table.projects tbody tr.bg-light>td {
            background-color: #f2f2f2  !important;
        }

        table.projects tbody tr.bg-white>td {
            background-color: #ffffff !important;
        }

        .action-btn {
            width: 115px;       
            text-align: center;
        }
</style>

    </style>

    <section class="content">
        {{-- Card Kasbon --}}
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between w-100 align-items-center">
                    <h3 class="card-title mb-0">Data Kasbon</h3>

                    <div class="d-flex align-items-center">
                        <a href="{{ route('kasbon.create') }}" class="btn btn-primary" title="Create Employee">
                            <i class="fas fa-plus"></i> Add
                        </a>

                        <form action="{{ route('kasbon.index') }}" method="GET" class="form-inline ml-3">
                            <input type="text" name="search" class="form-control" placeholder="Search by name..."
                                value="{{ request()->query('search') }}">
                            <button type="submit" class="btn btn-secondary ml-2">Search</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table projects">
                        <thead>
                            <tr>
                                <th style="width: 10%">Employee Name</th>
                                <th style="width: 10%" class="text-center">Current Salary</th>
                                <th style="width: 10%" class="text-center">Total Amount</th>
                                <th style="width: 10%" class="text-center">Installments</th>
                                <th style="width: 10%" class="text-center">Per Installments</th>
                                <th style="width: 10%" class="text-center">Start Month</th>
                                <th style="width: 10%" class="text-center">Action</th>
                                <th style="width: 5%" class="text-center">Status</th>
                            </tr>
                        </thead>

                        @php
                            $lastEmployee = null;
                            $useGray = false;
                        @endphp

                        <tbody>
                            @forelse ($kasbon as $c)

                                @php
                                    if ($lastEmployee !== $c->employee->employee_id) {
                                        $useGray = !$useGray;
                                        $lastEmployee = $c->employee->employee_id;
                                    }
                                @endphp

                                <tr class="{{ $useGray ? 'bg-light' : 'bg-white' }}">
                                    <td>{{ $c->employee->first_name }} {{ $c->employee->last_name }}</td>
                                    <td class="text-center">Rp. {{ number_format($c->employee->current_salary, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">Rp. {{ number_format($c->total_amount, 0, ',', '.') }}</td>
                                    <td class="text-center">{{$c->installments}}</td>
                                    <td class="text-center">Rp. {{ number_format($c->installment_amount, 0, ',', '.') }}</td>
                                    <td class="text-center">{{$c->start_month}}</td>
                                    <td class="project-actions text-center">

                                        <form action="{{ route('kasbon.edit', $c->cash_advance_id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-info btn-sm mb-1 action-btn"
                                            @if($c->status === 'cancelled' || $c->status === 'completed') disabled @endif>
                                            Edit Kasbon
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('kasbon.approve' , $c->cash_advance_id) }}">
                                            @csrf
                                            <button type="button" 
                                                class="btn btn-success btn-sm mb-1 action-btn approve-kasbon"
                                                data-id="{{ $c->cash_advance_id }}"
                                                @if($c->status === 'cancelled' || $c->status === 'completed') disabled @endif>
                                                Approve Kasbon
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('kasbon.cancel', $c->cash_advance_id) }}" class="cancel-form">
                                            @csrf
                                            <button type="button"
                                                class="btn btn-danger btn-sm mb-1 action-btn cancel-kasbon"
                                                data-id="{{ $c->cash_advance_id }}"
                                                @if($c->status === 'cancelled' || $c->status === 'completed') disabled @endif>
                                                Cancel Kasbon
                                            </button>
                                        </form>                                        
                                    
                                    </td>
                                    <td class="text-center">
                                        <span class="badge
                                                                    @if($c->status == 'ongoing') bg-warning
                                                                    @elseif ($c->status == 'completed') bg-success
                                                                    @else bg-danger @endif">
                                            {{ ucfirst($c->status) }}
                                        </span>
                                    </td>
                                    </class=>
                            @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Tidak ada data kasbon.</td>
                                    </tr>
                                @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
        
            document.querySelectorAll('.cancel-kasbon').forEach(button => {
                button.addEventListener('click', function () {
        
                    let form = this.closest('form');
        
                    Swal.fire({
                        title: "Are you sure?",
                        text: "Kasbon akan dibatalkan dan tidak dapat diubah lagi.",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Yes, cancel it!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
        
                });
            });

                // Approve Kasbon
            document.querySelectorAll('.approve-kasbon').forEach(button => {
                button.addEventListener('click', function () {

                    let form = this.closest('form');

                    Swal.fire({
                        title: "Approve Kasbon?",
                        text: "Kasbon akan disetujui dan tidak dapat diubah lagi.",
                        icon: "question",
                        showCancelButton: true,
                        confirmButtonColor: "#28a745",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Yes, approve it!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });

                });
            });
        
        });
    </script>
        
    
@endsection