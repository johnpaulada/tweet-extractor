<?php

use Illuminate\Http\Request;
use Abraham\TwitterOAuth\TwitterOAuth;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return response('Bitch. Nope.', 404);
});

Route::get('/tweets', ['middleware' => 'cors', function (Request $request)
{
    $key               = Config::get('twitter.credentials.key');
    $secret            = Config::get('twitter.credentials.secret');
    $accessToken       = Config::get('twitter.credentials.access_token.token');
    $accessTokenSecret = Config::get('twitter.credentials.access_token.secret');

    $connection = new TwitterOAuth($key, $secret, $accessToken, $accessTokenSecret);

    $connection->host           = 'https://api.twitter.com/1.1/';
    $connection->ssl_verifypeer = TRUE;
    $connection->content_type   = 'application/x-www-form-urlencoded';
 
    $count         = intval($request->input('count'));
    $maxPerRequest = 200;

    $tweets = [];
    $options = [
        "screen_name"     => $request->input('handle'),
        "count"           => $maxPerRequest,
        "exclude_replies" => true,
        "include_rts"     => false,
        "trim_user"       => true
    ];

    while (count($tweets) <= $count) {
        $statuses = $connection->get("statuses/user_timeline", $options);
        $tweets = array_merge($tweets, $statuses);
        $options['max_id'] = $tweets[count($tweets)-1]->id_str;
    }

    return response()->json([
        'tweets' => array_slice($tweets, 0, $count)
    ]);
}]);