<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ItineraryItem extends Model { protected $fillable=['trip_id','day_number','title','location','start_time','end_time','cost','notes','sort_order']; protected function casts(): array{return ['cost'=>'decimal:2'];} public function trip(){return $this->belongsTo(Trip::class);} }
