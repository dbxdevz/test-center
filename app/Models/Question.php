<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $dates = ['deleted_at'];

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function correctAnswers()
    {
        return $this->hasMany(Answer::class)->where('correct', true);
    }
}
