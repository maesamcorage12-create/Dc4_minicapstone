<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Trip extends Model {
    protected $fillable = ['owner_id','destination_id','title','start_date','end_date','budget','preferences','status','approved_by','approved_at','approval_note'];
    protected function casts(): array {
        return [
            'start_date' => 'date:Y-m-d',
            'end_date' => 'date:Y-m-d',
            'budget' => 'decimal:2',
            'preferences' => 'array',
            'approved_at' => 'datetime',
        ];
    }
    public function owner() {
        return $this->belongsTo(User::class, 'owner_id');
    }
    public function approver() {
        return $this->belongsTo(User::class, 'approved_by');
    }
    public function destination() {
        return $this->belongsTo(Destination::class);
    }
    public function members() {
        return $this->hasMany(TripMember::class);
    }
    public function items() {
        return $this->hasMany(ItineraryItem::class)->orderBy('day_number')->orderBy('sort_order');
    }
    public function comments() {
        return $this->hasMany(TripComment::class)->latest();
    }
}
