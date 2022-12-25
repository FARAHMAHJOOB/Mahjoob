<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelSpot extends Model
{
    use HasFactory;


    protected $table = "travels_spots";

    protected $fillable = ['travel_id',	'position','latitude','longitude','arrived_at'];

    
    public function travel() {
        return $this->belongsTo(Travel::class);
    }
}
