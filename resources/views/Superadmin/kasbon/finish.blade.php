@extends('layouts.app')
@section('title', 'Kasbon Index')
@section('content')
    <style>
        table.projects tbody tr.bg-light>td {
            background-color: #f2f2f2  !important;
        }
#f2f2f2
        table.projects tbody tr.bg-white>td {
            background-color: #ffffff !important;
        }
    </style>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between w-100 align-items-center">
                    <h3 class="card-title mb-0">Kasbon Ongoing - {{ $employee->first_name }} {{ $employee->last_name }}</h3>
                    <a href="{{ route('kasbon.index') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped projects">
                        <thead>
                            <tr>
                                <th style="width: 10%" class="text-center">Month</th>
                                <th style="width: 10%" class="text-center">Per Installments</th>
                                <th style="width: 10%" class="text-center">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($kasbon as $c)
                                <tr>
                                    <td class="text-center">{{$c->start_month}}</td>
                                    <td class="text-center">Rp. {{ number_format($c->installment_amount, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end">
                <form action="{{ route('kasbon.finish', $employee->employee_id) }}" 
                      method="POST" 
                      style="display:inline-block;">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-sm">
                        Finish Kasbon
                    </button>
                </form>
            </div>            
        </div>
    </section>    
@endsection