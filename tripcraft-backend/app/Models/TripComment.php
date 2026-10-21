<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TripComment extends Model { protected $fillable=['trip_id','user_id','item_id','comment']; public function trip(){return $this->belongsTo(Trip::class);} public function user(){return $this->belongsTo(User::class);} public function item(){return $this->belongsTo(ItineraryItem::class,'item_id');} }
