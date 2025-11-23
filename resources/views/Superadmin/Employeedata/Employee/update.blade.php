    @extends('layouts.app')
    @section('title', 'Employee/edit')
    @section('content')
    <div class="content">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-xl-6">
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Edit Employee</h3>
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

                    <form id="update-form" action="{{ route('employee.update', $employeeModel->employee_id) }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="first_name">First Name</label>
                                        <input type="text" name="first_name" id="first_name" class="form-control"
                                            value="{{ old('first_name', $employeeModel->first_name) }}" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="last_name">Last Name</label>
                                        <input type="text" name="last_name" id="last_name" class="form-control"
                                            value="{{ old('last_name', $employeeModel->last_name) }}" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" name="email" id="email" class="form-control"
                                            value="{{ old('email', $employeeModel->email) }}" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="check_in_time">Time Check-In</label>
                                        <input type="time" name="check_in_time" class="form-control"
                                            value="{{ old('check_in_time', '10:00', $employeeModel->check_in_time) }}"
                                            required>
                                    </div>

                                    <div class="form-group">
                                        <label for="check_out_time">Time Check-Out</label>
                                        <input type="time" name="check_out_time" class="form-control"
                                            value="{{ old('check_out_time', '17:00', $employeeModel->check_out_time) }}"
                                            required>
                                    </div>

                                    <div class="form-group">
                                        <label for="division_id">Division</label>
                                        <select name="division_id" id="division_id" class="form-control" required>
                                            @foreach ($divisions as $division)
                                            <option value="{{ $division->id }}"
                                                {{ old('division_id', $employeeModel->division_id) == $division->id ? 'selected' : '' }}>
                                                {{ $division->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="employee_type">Employee Type</label>
                                        <select name="employee_type" id="employee_type" class="form-control">
                                            <option value="Permanent"
                                                {{ old('employee_type', $employeeModel->employee_type) == 'Permanent' ? 'selected' : '' }}>
                                                Permanent</option>
                                            <option value="Freelance"
                                                {{ old('employee_type', $employeeModel->employee_type) == 'Freelance' ? 'selected' : '' }}>
                                                Freelance</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="place_birth">Place of Birth</label>
                                        <input type="text" name="place_birth" id="place_birth" class="form-control"
                                            value="{{ old('place_birth', $employeeModel->place_birth) }}" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="date_birth">Date of Birth</label>
                                        <input type="date" name="date_birth" id="date_birth" class="form-control"
                                            value="{{ old('date_birth', $employeeModel->date_birth) }}">
                                    </div>

                                    <div class="form-group">
                                        <label for="identity_number">Identity Number</label>
                                        <input type="number" name="identity_number" id="identity_number"
                                            class="form-control"
                                            value="{{ old('identity_number', $employeeModel->identity_number) }}">
                                    </div>

                                    <div class="form-group">
                                        <label for="address">Address</label>
                                        <input type="text" name="address" id="address" class="form-control"
                                            value="{{ old('address', $employeeModel->address) }}">
                                    </div>

                                    <div class="form-group">
                                        <label for="current_address">Current Address</label>
                                        <input type="text" name="current_address" id="current_address"
                                            class="form-control"
                                            value="{{ old('current_address', $employeeModel->current_address) }}">
                                    </div>

                                    <div class="form-group">
                                        <label for="blood_type">Blood Type</label>
                                        <select name="blood_type" id="blood_type" class="form-control">
                                            <option value="A"
                                                {{ old('blood_type', $employeeModel->blood_type) == 'A' ? 'selected' : '' }}>
                                                A</option>
                                            <option value="B"
                                                {{ old('blood_type', $employeeModel->blood_type) == 'B' ? 'selected' : '' }}>
                                                B</option>
                                            <option value="AB"
                                                {{ old('blood_type', $employeeModel->blood_type) == 'AB' ? 'selected' : '' }}>
                                                AB</option>
                                            <option value="O"
                                                {{ old('blood_type', $employeeModel->blood_type) == 'O' ? 'selected' : '' }}>
                                                O</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="blood_rhesus">Blood Rhesus</label>
                                        <input type="text" name="blood_rhesus" id="blood_rhesus" class="form-control"
                                            value="{{ old('blood_rhesus', $employeeModel->blood_rhesus) }}">
                                    </div>

                                    <div class="form-group">
                                        <label for="phone_number">Phone Number</label>

                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">+62</span>
                                            </div>
                                            <input type="text" name="phone_number" id="phone_number"
                                                class="form-control"
                                                value="{{ old('phone_number', $employeeModel->phone_number) }}" required
                                                oninput="phonenumber(this)" onkeypress="validatephonenumber(event)">
                                        </div>
                                        <small id="numberwarning" class="form-text text-danger" style="display: none;">
                                            Please enter only numbers.
                                        </small>
                                    </div>

                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="hp_number">Hp Number</label>

                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">+62</span>
                                            </div>
                                            <input type="text" name="hp_number" id="hp_number" class="form-control"
                                                value="{{ old('hp_number', $employeeModel->hp_number) }}" required
                                                oninput="phonenumber(this)" onkeypress="validatephonenumber(event)">
                                        </div>
                                        <small id="numberwarning" class="form-text text-danger" style="display: none;">
                                            Please enter only numbers.
                                        </small>
                                    </div>
                                    <div class="form-group">
                                        <label for="marital_status">Marital Status</label>
                                        <select name="marital_status" id="marital_status" class="form-control">
                                            <option value="Single"
                                                {{ old('marital_status', $employeeModel->marital_status) == 'Single' ? 'selected' : '' }}>
                                                Single</option>
                                            <option value="Married"
                                                {{ old('marital_status', $employeeModel->marital_status) == 'Married' ? 'selected' : '' }}>
                                                Married</option>
                                            <option value="Widow"
                                                {{ old('marital_status', $employeeModel->marital_status) == 'Widow' ? 'selected' : '' }}>
                                                Widow</option>
                                            <option value="Widower"
                                                {{ old('marital_status', $employeeModel->marital_status) == 'Widower' ? 'selected' : '' }}>
                                                Widower</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="cv_file">CV File</label>
                                        <input type="file" name="cv_file" id="cv_file" class="form-control">

                                    </div>
                                    <div class="form-group">
                                        <label>Last CV Update:</label>
                                        <span>{{ \Carbon\Carbon::parse($employeeModel->update_cv)->format('d F Y') }}</span>

                                        @if ($employeeModel->cv_file)
                                        <div class="mt-2" id="currentCvFile">
                                            <strong>Current File: </strong> {{ basename($employeeModel->cv_file) }}
                                        </div>
                                        @endif
                                    </div>



                                    <div class="form-group">
                                        <label for="last_education">Last Education</label>
                                        <select name="last_education" id="last_education" class="form-control">
                                            <option value="Elementary School"
                                                {{ old('last_education', $employeeModel->last_education) == 'Elementary School' ? 'selected' : '' }}>
                                                Elementary School</option>
                                            <option value="Junior High School"
                                                {{ old('last_education', $employeeModel->last_education) == 'Junior High School' ? 'selected' : '' }}>
                                                Junior High School</option>
                                            <option value="Senior High School"
                                                {{ old('last_education', $employeeModel->last_education) == 'Senior High School' ? 'selected' : '' }}>
                                                Senior High School</option>
                                            <option value="Vocational High School"
                                                {{ old('last_education', $employeeModel->last_education) == 'Vocational High School' ? 'selected' : '' }}>
                                                Vocational High School</option>
                                            <option value="Associate Degree 1"
                                                {{ old('last_education', $employeeModel->last_education) == 'Associate Degree 1' ? 'selected' : '' }}>
                                                Associate Degree 1</option>
                                            <option value="Associate Degree 2"
                                                {{ old('last_education', $employeeModel->last_education) == 'Associate Degree 2' ? 'selected' : '' }}>
                                                Associate Degree 2</option>
                                            <option value="Associate Degree 3"
                                                {{ old('last_education', $employeeModel->last_education) == 'Associate Degree 3' ? 'selected' : '' }}>
                                                Associate Degree 3</option>
                                            <option value="Bachelor’s Degree"
                                                {{ old('last_education', $employeeModel->last_education) == 'Bachelor’s Degree' ? 'selected' : '' }}>
                                                Bachelor’s Degree</option>
                                            <option value="Master’s Degree"
                                                {{ old('last_education', $employeeModel->last_education) == 'Master’s Degree' ? 'selected' : '' }}>
                                                Master’s Degree</option>
                                            <option value="Doctoral Degree"
                                                {{ old('last_education', $employeeModel->last_education) == 'Doctoral Degree' ? 'selected' : '' }}>
                                                Doctoral Degree</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="degree">Degree</label>
                                        <input type="text" name="degree" id="degree" class="form-control"
                                            value="{{ old('degree', $employeeModel->degree) }}">
                                    </div>

                                    <div class="form-group">
                                        <label for="starting_date">Starting Date</label>
                                        <input type="date" name="starting_date" id="starting_date"
                                            class="form-control"
                                            value="{{ old('starting_date', $employeeModel->starting_date) }}">
                                    </div>

                                    <div class="form-group">
                                        <label for="interview_by">Interviewed By</label>
                                        <input type="text" name="interview_by" id="interview_by" class="form-control"
                                            value="{{ old('interview_by', $employeeModel->interview_by) }}">
                                    </div>

                                    <div class="form-group">
                                        <label for="current_salary">Current Salary</label>
                                        <input type="text" name="current_salary" id="current_salary"
                                            class="form-control"
                                            value="{{ old('current_salary', number_format($employeeModel->current_salary, 0, ',', '.')) }}"
                                            oninput="formatCurrency(this)" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="current_salary">Attendance Allowance</label>
                                        <input type="text" name="attendance_allowance" id="attendance_allowance"
                                            class="form-control"
                                            value="{{ old('attendance_allowance', number_format($employeeModel->attendance_allowance, 0, ',', '.')) }}"
                                            oninput="formatCurrency(this)" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="positional_allowance">Positional Allowance</label>
                                        <input type="text" name="positional_allowance" id="positional_allowance"
                                            class="form-control" value="{{ old('positional_allowance', number_format($employeeModel->positional_allowance, 0, ',', '.')) }}"
                                            oninput="formatCurrency(this)">
                                    </div>

                                    <div class="form-group">
                                        <label for="transport_allowance">Transport Allowance</label>
                                        <input type="text" name="transport_allowance" id="transport_allowance"
                                            class="form-control" value="{{ old('transport_allowance', number_format($employeeModel->transport_allowance, 0, ',', '.')) }}"
                                            oninput="formatCurrency(this)">
                                        <small class="text-muted">Stock Opname = 0</small>

                                    </div>

                                    <div class="form-group">
                                        <label for="bonus_allowance">Bonus</label>
                                        <input type="text" name="bonus_allowance" id="bonus_allowance"
                                            class="form-control" value="{{ old('bonus_allowance', number_format($employeeModel->bonus_allowance, 0, ',', '.')) }}"
                                            oninput="formatCurrency(this)">
                                    </div>

                                    <div class="form-group">
                                        <label for="insurance">Insurance</label>
                                        <select name="insurance" id="insurance" class="form-control">
                                            <option value="1"
                                                {{ old('insurance', $employeeModel->insurance) == '1' ? 'selected' : '' }}>
                                                Yes</option>
                                            <option value="0"
                                                {{ old('insurance', $employeeModel->insurance) == '0' ? 'selected' : '' }}>
                                                No</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="serious_illness">Serious Illness</label>
                                        <input type="text" name="serious_illness" id="serious_illness"
                                            class="form-control"
                                            value="{{ old('serious_illness', $employeeModel->serious_illness) }}">
                                    </div>

                                    <div class="form-group">
                                        <label for="hereditary_disease">Hereditary Disease</label>
                                        <input type="text" name="hereditary_disease" id="hereditary_disease"
                                            class="form-control"
                                            value="{{ old('hereditary_disease', $employeeModel->hereditary_disease) }}">
                                    </div>

                                    <div class="form-group">
                                        <label for="emergency_contact">Emergency Contact</label>
                                        <input type="text" name="emergency_contact" id="emergency_contact"
                                            class="form-control"
                                            value="{{ old('emergency_contact', $employeeModel->emergency_contact) }}">
                                    </div>

                                    <div class="form-group">
                                        <label for="relations">Relations</label>
                                        <select name="relations" id="relations" class="form-control">
                                            <option value="Parent"
                                                {{ old('relations', $employeeModel->relations) == 'Parent' ? 'selected' : '' }}>
                                                Parent</option>
                                            <option value="Guardian"
                                                {{ old('relations', $employeeModel->relations) == 'Guardian' ? 'selected' : '' }}>
                                                Guardian</option>
                                            <option value="Husband"
                                                {{ old('relations', $employeeModel->relations) == 'Husband' ? 'selected' : '' }}>
                                                Husband</option>
                                            <option value="Wife"
                                                {{ old('relations', $employeeModel->relations) == 'Wife' ? 'selected' : '' }}>
                                                Wife</option>
                                            <option value="Sibling"
                                                {{ old('relations', $employeeModel->relations) == 'Sibling' ? 'selected' : '' }}>
                                                Sibling</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="emergency_number">Emergency Number</label>

                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">+62</span>
                                            </div>
                                            <input type="text" name="emergency_number" id="emergency_number"
                                                class="form-control"
                                                value="{{ old('emergency_number', $employeeModel->emergency_number) }}"
                                                required oninput="phonenumber(this)"
                                                onkeypress="validatephonenumber(event)">
                                        </div>
                                        <small id="numberwarning" class="form-text text-danger" style="display: none;">
                                            Please enter only numbers.
                                        </small>
                                    </div>


                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select name="status" id="status" class="form-control">
                                            <option value="Active"
                                                {{ old('status', $employeeModel->status) == 'Active' ? 'selected' : '' }}>
                                                Active</option>
                                            <option value="Inactive"
                                                {{ old('status', $employeeModel->status) == 'Inactive' ? 'selected' : '' }}>
                                                Inactive</option>
                                            <option value="Pending"
                                                {{ old('status', $employeeModel->status) == 'Pending' ? 'selected' : '' }}>
                                                Pending</option>
                                            <option value="Suspend"
                                                {{ old('status', $employeeModel->status) == 'Suspend' ? 'selected' : '' }}>
                                                Suspend</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="button" class="btn btn-primary" id="submit-btn">Update</button>
                            <a href="{{ route('employee.index') }}" class="btn btn-secondary">Back</a>
                        </div>
                    </form>
                    <script>
                        function formatCurrency(input) {
                            let value = input.value.replace(/[^\d]/g, '');
                            value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            input.value = value;
                        }

                        // Hapus titik sebelum kirim ke server
                        document.addEventListener('DOMContentLoaded', function() {
                            const form = document.querySelector('form');
                            if (form) {
                                form.addEventListener('submit', function() {
                                    let salaryInput = document.getElementById('current_salary');
                                    if (salaryInput) {
                                        salaryInput.value = salaryInput.value.replace(/\./g, '');
                                    }

                                    let allowanceInput = document.getElementById('attendance_allowance');
                                    if (allowanceInput) {
                                        allowanceInput.value = allowanceInput.value.replace(/\./g, '');
                                    }
                                });
                            }
                        });
                    </script>

                    <script>
                        document.getElementById('submit-btn').addEventListener('click', function() {
                            Swal.fire({
                                title: "Do you want to save the changes?",
                                showDenyButton: true,
                                showCancelButton: true,
                                confirmButtonText: "Save",
                                denyButtonText: `Don't save`
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    Swal.fire("Saved!", "", "success").then(() => {
                                        document.getElementById('update-form').submit();
                                    });
                                } else if (result.isDenied) {
                                    Swal.fire("Changes are not saved", "", "info");
                                }
                            });
                        });
                    </script>

                    <script>
                        // Function to automatically update phone number prefix
                        function updatephonenumber(input) {
                            let value = input.value;
                            if (value.startsWith('0')) {
                                input.value = '+62' + value.slice(1);
                            }
                        }

                        // Function to validate phone number input (only numbers allowed)
                        function validatephonenumber(event) {
                            const input = event.target;
                            const char = String.fromCharCode(event.which);
                            if (!/[0-9]/.test(char)) {
                                event.preventDefault();
                                document.getElementById('numberwarning').style.display = 'block';
                            } else {
                                document.getElementById('numberwarning').style.display = 'none';
                            }
                        }
                    </script>
                </div>
            </div>
        </section>
    </div>
    @endsection
    @section('scripts')
    <script>
        // Cek jika ada pesan error di session
        @if(session('error'))
        Swal.fire({
            icon: "error",
            title: "Oops...",
            text: "{{ session('error') }}",
        });
        @endif
    </script>
    @endsection