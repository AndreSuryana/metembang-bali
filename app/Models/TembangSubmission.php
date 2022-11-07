<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TembangSubmission extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id'
    ];

    /**
     * Get the user for the tembang submission.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the usages for the tembang submission.
     */
    public function usages()
    {
        return $this->hasMany(Usage::class);
    }

    /**
     * Get the rule for the tembang submission.
     */
    public function rule()
    {
        return $this->hasOne(Rule::class);
    }
}
