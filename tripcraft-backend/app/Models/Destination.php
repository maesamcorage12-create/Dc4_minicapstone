<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Destination extends Model { protected $fillable=['name','location','category','description','avg_daily_cost','tags','image_url','is_active']; protected function casts(): array{return ['tags'=>'array','is_active'=>'boolean','avg_daily_cost'=>'decimal:2'];} public function trips(){return $this->hasMany(Trip::class);} }
