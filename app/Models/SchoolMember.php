<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolMember extends Model
{
    protected $fillable = ['school_subscription_id', 'school_class_id', 'user_id', 'status', 'added_by_name'];

    public function schoolSubscription()
    {
        return $this->belongsTo(SchoolSubscription::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
