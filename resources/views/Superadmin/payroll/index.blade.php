@extends('layouts.app')
@section('title', 'Payroll/index')
@section('content')
<section class="content">
    <style>
        .table-container {
            overflow-x: auto;
            overflow-y: hidden;
            white-space: nowrap;
            width: 100%;
            position: relative;
        }

        .table-container table {
            width: max-content;
            min-width: 1100px;
        }

        #floating-scrollbar {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            z-index: 9999;
            background: #f1f1f1;
            height: 16px; /* tingginya seperti scrollbar */
        }

        #floating-scrollbar-content {
            height: 1px;
        }
    </style>

    <div class="modal fade" id="modalTransport">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formTransport">
                    @csrf
    
                    <div class="modal-header">
                        <h5 class="modal-title">Add Transport Allowance</h5>
                    </div>
    
                    <div class="modal-body">
                        <input type="hidden" id="payroll_id">
    
                        <label>Transport Allowance</label>
                        <input type="number" class="form-control" id="transport_value" oninput="formatNumber(this)">
                    </div>
    
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">
                            Save
                        </button>
                    </div>
    
                </form>
            </div>
        </div>
    </div>    

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

                        <select name="division" class="custom-select ml-2">
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
                        <input type="hidden" name="search" value="{{ request()->query('search') }}">
                        <input type="hidden" name="month" value="{{ request()->query('month') }}">
                        <input type="hidden" name="division" value="{{ request()->query('division') }}">

                        <button type="submit" class="btn btn-primary">Export to CSV</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-container">
                <table class="table table-striped projects">
                    <thead>
                        <tr>
                            <th style="width: 10%">Employee<br>Name</th>
                            <th style="width: 15%" class="text-center">Current<br>salary</th>
                            <th style="width: 5%" class="text-center">Total Days<br>Worked</th>
                            {{-- <th style="width: 5%" class="text-center">Total Days Off</th> --}}
                            <th style="width: 5%" class="text-center">Total<br>Absent</th>
                            <th style="width: 5%" class="text-center">Total Late<br>Check In</th>
                            <th style="width: 5%" class="text-center">Total Early<br>Check Out</th>
                            <th style="width: 5%" class="text-center">Effective<br>Work Days</th>
                            <th style="width: 5%" class="text-center">Attendance<br>Allowance</th>
                            <th style="width: 5%" class="text-center">Transport<br>Allowance</th>
                            <th style="width: 5%" class="text-center">Overtime<br>Hour</th>
                            <th style="width: 10%" class="text-center">Overtime<br> Pay</th>
                            <th style="width: 15%"class="text-center text-danger">Cash<br>Advance</th>
                            <th style="width: 15%" class="text-center">Total<br>Salary</th>
                            <th style="width: 10%" class="text-center">Action</th>
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
                            <td class="text-center">{{ $data['total_absent'] }}</td>
                            <td class="text-center">{{ $data['total_late_check_in'] }}</td>
                            <td class="text-center">{{ $data['total_early_check_out'] }}</td>
                            <td class="text-center">{{ $data['effective_work_days'] }}</td>
                            <td class="text-center">Rp. {{ number_format($data['attendance_allowance'], 0, ',', '.') }}</td>
                            <td class="text-center">Rp. {{ number_format($data['transport_allowance'], 0, ',', '.') }}</td>
                            <td class="text-center">{{ $data['total_overtime'] }}</td>
                            <td class="text-left">Rp. {{ number_format($data['overtime_pay'], 0, ',', '.') }}</td>

                            <td class="text-left text-danger" id="cash-advance-{{ $data['payroll_id'] }}">
                                Rp. {{ number_format($data['cash_advance'], 0, ',', '.') }}
                            </td>

                            <td class="text-left">Rp. {{ number_format($data['total_salary'], 0, ',', '.') }}</td>

                            <td>
                                <button 
                                    class="btn btn-info btn-sm add-transport-btn"
                                    data-id="{{ $data['payroll_id'] }}">
                                    Add Transport<br>Allowance
                                </button>
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

        function formatNumber(input) {
            // Ambil angka mentah
            let value = input.value.replace(/[^\d]/g, '');

            // Format ke ribuan
            input.value = new Intl.NumberFormat('id-ID').format(value);
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

        $('.add-transport-btn').on('click', function () {
            $('#payroll_id').val($(this).data('id'));
            $('#modalTransport').modal('show');
        });

        $('#formTransport').on('submit', function (e) {
            e.preventDefault();

            const payrollId = $('#payroll_id').val();

            let rawValue = $('#transport_value').val().replace(/\./g, '');

            $.ajax({
                url: `/Superadmin/payroll/update-transport/${payrollId}`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    transport_allowance: rawValue
                },
                success: function (res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    });

                    setTimeout(() => {
                        location.reload();
                    }, 1600);
                }
            });
        });
    });
</script>
@endsection