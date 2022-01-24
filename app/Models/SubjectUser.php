<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectUser extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'subject_user';

    public function subjects()
    {
        return $this->belongsTo(Subject::class);
    }
}
