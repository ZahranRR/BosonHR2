@extends('layouts.app')
@section('title', 'Payroll/index')
@section('content')
<section class="content">


    {{-- Modal Kasbon --}}
    <div class="modal fade" id="kasbonModal" tabindex="-1" aria-labelledby="kasbonModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Cash Advance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="kasbonForm">
                        @csrf
                        <input type="hidden" id="kasbonPayrollId" name="payroll_id">

                        <div class="mb-3">
                            <label for="kasbonNominal" class="form-label">Nominal:</label>
                            <input type="number" class="form-control" id="kasbonNominal" name="cash_advance" placeholder="Masukkan nominal" min="0">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveCashAdvance">Save Changes</button>
                </div>
            </div>
        </div>
    </div>


    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between w-100 align-items-center">
                <h3 class="card-title mb-0">Payroll</h3>
                <div class="d-flex align-items-center">
                    {{-- Filter Form --}}
                    <form method="GET" action="{{ route('payroll.index') }}" class="form-inline d-flex mb-0 align-items-center">
                        <input type="text" name="search" class="form-control"
                            placeholder="Search by employee name..." value="{{ request()->query('search') }}">

                        <input type="month" name="month" class="form-control ml-2"
                            value="{{ request()->query('month', now()->format('Y-m')) }}">

                        <select name="division" class="form-control ml-2">
                            <option value="">All Divisions</option>
                            @foreach ($divisions as $division)
                            <option value="{{ $division->id }}" {{ in_array($division->id, (array)request()->query('division')) ? 'selected' : '' }}>
                                {{ $division->name }}
                            </option>
                            @endforeach
                        </select>

                        <button type="submit" class="btn btn-secondary ml-2">Search</button>
                    </form>

                    {{-- Export Button --}}
                    <form method="GET" action="{{ route('payroll.exports') }}" class="ml-3">
                        <button type="submit" class="btn btn-primary">Export to CSV</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped projects">
                    <thead>
                        <tr>
                            <th style="width: 10%">Employee Name</th>
                            <th style="width: 15%" class="text-center">Current Salary</th>
                            <th style="width: 5%" class="text-center">Total Days Worked</th>
                            <th style="width: 5%" class="text-center">Total Days Off</th>
                            <th style="width: 5%" class="text-center">Total Absent</th>
                            <th style="width: 5%" class="text-center">Total Late Check In</th>
                            <th style="width: 5%" class="text-center">Total Early Check Out</th>
                            <th style="width: 5%" class="text-center">Effective Work Days</th>
                            <th style="width: 5%" class="text-center">Overtime Hour</th>
                            <th style="width: 10%" class="text-center">Overtime Pay</th>
                            <th style="width: 15%"class="text-center text-danger">Cash Advance</th>
                            <th style="width: 15%" class="text-center">Total Salary</th>
                            <th style="width: 10%" class="text-center">Validation Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payrolls as $data)
                        <tr>
                            <td>{{ $data['employee_name'] }}</td>
                            <td class="text-left">
                                Rp. {{ number_format($data['current_salary'], 0, ',', '.') }}
                            </td>
                            <td class="text-center">{{ $data['total_days_worked'] }}</td>
                            <td class="text-center">{{ $data['total_days_off'] }}</td>
                            <td class="text-center">{{ $data['total_absent'] }}</td>
                            <td class="text-center">{{ $data['total_late_check_in'] }}</td>
                            <td class="text-center">{{ $data['total_early_check_out'] }}</td>
                            <td class="text-center">{{ $data['effective_work_days'] }}</td>
                            <td class="text-center">{{ $data['total_overtime'] }}</td>
                            <td class="text-left">Rp. {{ number_format($data['overtime_pay'], 0, ',', '.') }}</td>

                            <td class="text-left text-danger" id="cash-advance-{{ $data['payroll_id'] }}">
                                Rp. {{ number_format($data['cash_advance'], 0, ',', '.') }}
                            </td>

                            <td class="text-left">Rp. {{ number_format($data['total_salary'], 0, ',', '.') }}</td>

                            <td class="text-center">
                                @if (strtolower($data['status']) === 'pending')
                                <form method="POST" action="{{ route('payroll.approve', $data['payroll_id']) }}"
                                    style="display: inline;" id="approve-form-{{ $data['payroll_id'] }}">
                                    @csrf
                                    @method('PUT')
                                    <button type="button" class="btn btn-success btn-sm"
                                        onclick="confirmApprove({{ $data['payroll_id'] }})">Approve</button>
                                </form>
                                @else
                                <span class="badge badge-success">Approved</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center">No payroll data found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>


{{-- <script>
    function confirmApprove(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'You want to approve this payroll?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, approve it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/Superadmin/payroll/approve/${id}`,
                    method: 'PUT',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        Swal.fire('Approved!', 'The payroll has been approved.', 'success');
                        setTimeout(() => location.reload(), 1200);
                    },
                    error: function(err) {
                        Swal.fire('Error', 'Failed to approve payroll.', 'error');
                        console.error(err.responseText);
                    }
                });
            } else {
                Swal.fire('Cancelled', 'The payroll was not approved.', 'error');
            }
        });
    }
    </script> --}}
<script>
        function confirmApprove(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You want to approve this payroll?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, approve it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('approve-form-' + id).submit();
                    Swal.fire('Approved!', 'The payroll has been approved.', 'success');
                } else {
                    Swal.fire('Cancelled', 'The payroll was not approved.', 'error');
                }
            });
        }
    </script>

{{-- ... --}}
<script>
    $(document).ready(function() {
        // Saat tombol "Add Kasbon" diklik, isi hidden input di modal
        $('#kasbonModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget);
            const payrollId = button.data('id');
            const cashAdvance = button.data('cash-advance');

            console.log("Payroll ID from button:", payrollId);
            $('#kasbonPayrollId').val(payrollId);
            console.log("Set hidden input to:", $('#kasbonPayrollId').val());

            // Isi nilai input di dalam modal
            // Gunakan selector jQuery ($('#...')) untuk mengakses elemen
            $('#kasbonPayrollId').val(payrollId);
            $('#kasbonNominal').val(cashAdvance);

            // Opsional: Cek di konsol untuk memastikan nilai terisi dengan benar
            console.log("Payroll ID:", payrollId);
            console.log("Cash Advance:", cashAdvance);
        });

        // Saat tombol "Save Changes" di modal diklik
        $('#saveCashAdvance').on('click', function() {
            const payrollId = $('#kasbonPayrollId').val();
            const cashAdvance = $('#kasbonNominal').val();

            console.log("DEBUG >> Payroll ID:", payrollId);
            console.log("DEBUG >> Nominal:", cashAdvance);

            if (!payrollId || !cashAdvance) {
                Swal.fire('Error', 'Payroll ID dan nominal harus diisi.', 'error');
                return;
            }

            // Kirim AJAX ke route yang menggunakan parameter ID di URL
            $.ajax({
                url: `/Superadmin/payroll/cash-advance/${payrollId}`, // sesuaikan dgn route
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    payroll_id: payrollId,
                    cash_advance: cashAdvance
                },
                success: function(response) {
                    $('#kasbonModal').modal('hide');
                    $('#cash-advance-' + payrollId).html('Rp. ' + response.cash_advance);
                    Swal.fire('Berhasil', response.message, 'success');
                    $('#kasbonForm')[0].reset();
                    location.reload();
                },
                error: function(xhr) {
                    Swal.fire('Gagal', 'Gagal menyimpan data kasbon.', 'error');
                    console.error(xhr.responseJSON);
                }
            });
        });
    });
</script>





@endsection