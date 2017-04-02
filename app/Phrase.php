<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Phrase extends Model
{
	public function emotion()
    {
        return $this->hasMany('App\Emotion');
    }

    protected $fillable = ['text'];
}
