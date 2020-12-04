<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class URL extends Model
{
    use HasFactory;
    const URL_BASE = 'https://aqueous-chamber-43842.herokuapp.com/api/';
    const URL_AWS = 'https://initiative-solution.eu-west-3.amazonaws.com/';
}
