<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyName extends Model
{
    use HasFactory;
    protected $table = 'company_names';

    protected $fillable = ['name_company', 'company_address', 'image'];
}
