<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Twitter;
use App\Phrase;
use App\Emotion;

/**
 * Class BaseController
 * @package App\Http\Controllers
 */
class BaseController extends Controller {

	protected  $analyzedEmotions = [
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
		'agreeableness'
	];


	public function index()
	{
		$text = 'This is example is full of confidence! You are awesome!';
		return view('index')->with(compact('text'));
	}

	public  function  analyze(Request $request)
	{
		$text = $request->input('text');
		$results = $this->analyseTweetWatson($text);
		$id = $this->store($text, $results);

		return json_encode(array_merge($results, ['text'=> $text, 'id' => $id]));
	}

	public function loadPhrases(Request $request)
	{
		$lastId = 1;
		if (isset($request->lastId)) {
			$lastId = $request->lastId;
		}
		$phrases = Phrase::where('id', '>', $lastId)->get()->sortByDesc('id');
		return json_encode($phrases);
	}

	public function compare(Request $request)
	{
		$results = Emotion::where('phrase_id', $request->id)->firstOrFail()->toArray();
		return json_encode($results);
	}

	public function store($text, $results)
    {

        $phrase = new Phrase;
        $phrase->text = $text;

        $emotionData = new Emotion;
        foreach ($this->analyzedEmotions as $emotion) {
        	$emotionData->$emotion = $results[$emotion];
        }

		$phrase->save();
		$phrase->emotion()->save($emotionData);
		return $phrase->id;
    }

	public function getTweets()
	{
		$tweets = Twitter::getSearch([
		    'q' => 'trump',
		    'until' => date('Y-m-d'),
		    'count' => 3,
		    'result_type' => 'recent'
		]);
		$totalTweetCount = count($tweets->statuses);
		$totalScore = 0;
		foreach ($tweets as $key => $value) {
			foreach ($value as $tweet) {
				if (isset($tweet->text)) {}
			}
		}
	}

	public function prepareResults()
	{
		if (isset($result->compound)) {
						$score = $result->compound;
						$totalScore += $score;
					}
					echo '<p>' . $tweet->text .
					'<br />Sentiment score:' . $score .
					'</p>';
	}

	public function analyseTweetWatson($tweet)
	{
		$data = array('text' => $tweet);
		$data_json = json_encode($data);
		// prepare cURL to algorithm endpoint
		$ch = curl_init();
		$headers = array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($data_json)
		);

		curl_setopt_array($ch, array(
			CURLOPT_USERPWD => "0cf63f61-5aa6-476f-ac48-b01f605b46dc:twMJ30T5koBK",
			CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
			CURLOPT_URL => 'https://gateway.watsonplatform.net/tone-analyzer/api/v3/tone?version=2016-05-19',
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_POSTFIELDS => $data_json,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true
		));

		// run the algorithm and get the results (usually a JSON-encoded string)
		$response_json = curl_exec($ch);
		curl_close($ch);
		$response = json_decode($response_json);
		// $response->result contains algorithm results (if any)
		// $response->error contains errors (if any)
		// $response->metadata has meta-information
		if(isset($response->error)) {
			print('ERROR: ');
			print_r($response->error);
		} else {
			$categories = $response->document_tone->tone_categories;
			foreach ($categories as $value) {
				foreach ($value as $tone) {
					if (is_array($tone)) {
						foreach ($tone as $result) {
							$watsonResult[strtolower($result->tone_name)] = $result->score;
						}
					}
				}
			}

			return $watsonResult;
		}
	}


	function analyseTweet($tweet)
	{
		// get your API Key at http://algorithmia.com/user#credentials
		$api_key = 'simrR28tPrZFZ343NqZjdskjv891';

		// pick an algorithm at http://algorithmia.com/algorithms/ -- and append a version number
		$algorithm = 'nlp/SocialSentimentAnalysis/0.1.3';

		// most algorithms accept JSON Objects
		$data = array('sentence' => $tweet);
		$data_json = json_encode($data);

		// prepare cURL to algorithm endpoint
		$ch = curl_init();
		$headers = array(
			'Content-Type: application/json',
			'Authorization: Simple ' . $api_key,
			'Content-Length: ' . strlen($data_json)
		);
		curl_setopt_array($ch, array(
		CURLOPT_URL => 'https://api.algorithmia.com/v1/algo/' . $algorithm,
		CURLOPT_HTTPHEADER => $headers,
		CURLOPT_POSTFIELDS => $data_json,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => true
		));

		// run the algorithm and get the results (usually a JSON-encoded string)
		$response_json = curl_exec($ch);
		curl_close($ch);
		$response = json_decode($response_json);
		// $response->result contains algorithm results (if any)
		// $response->error contains errors (if any)
		// $response->metadata has meta-information
		if(isset($response->error)) {
			print('ERROR: ');
			print_r($response->error);
		} else {
			return $response->result[0];
		}
	}
}