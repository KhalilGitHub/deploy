<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Initiative extends Model
{
    protected $table = 'initiatives';
    protected $fillable = [
        'initname' ,
        'field' ,
        'typeorg' ,
        'member' ,
        'level' ,
        'location' ,
        'tel' ,
        'link' ,
        'needs' ,
        'description' ,
        'imgurl'
    ];
}
