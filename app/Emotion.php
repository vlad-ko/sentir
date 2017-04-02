<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Emotion extends Model
{
	public function phrase()
    {
        return $this->belongsTo('App\Phrase');
    }

    protected  $fillable = [
		'anger',
		'disgust',
		'fear',
		'joy',
		'sadness',
		'analytical',
		'confident',
		'tentative',
		'openness',
		'conscientiousness',
		'extraversion',
		'agreeableness',
    ];
}
