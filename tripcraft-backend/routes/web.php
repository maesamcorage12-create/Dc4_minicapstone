<?php
use Illuminate\Support\Facades\Route;
Route::get('/', fn()=>response()->json(['app'=>'TripCraft Bohol API','status'=>'running']));
