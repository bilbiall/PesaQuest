<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserQuest extends Model
{
    protected $fillable = [
        'user_id', 'quest_id', 'submitted_at', 'completed_at', 'approved_by',
        'step_progress',
    ];

    protected $casts = [
        'submitted_at'  => 'datetime',
        'completed_at'  => 'datetime',
        'step_progress' => 'array',
    ];

    public function user()     { return $this->belongsTo(User::class); }
    public function quest()    { return $this->belongsTo(Quest::class); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }

    public function isPending(): bool   { return $this->submitted_at && !$this->completed_at; }
    public function isApproved(): bool  { return $this->completed_at !== null; }
}
