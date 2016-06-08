# Socialpack
Facebook and Twitter Libraries

## Installation

Begin by installing this package through Composer.

Add this line in composer.json file under "require":

```
"jedelhu/Socialpack": "dev-master",
```

Add this line in app.php under 'providers':

```
Jedelhu\Socialpack\SocialpackServiceProvider::class
```

Run this command in  command line:

```
php artisan vendor:publish --provider="Jedelhu\Socialpack\SocialpackServiceProvider"
```
## Configuration


Give valid app_id and app_secret keys for both api in config\socialpack.php


## Change following code in facebook sdk v4 package(to convert in laravel):

Go to vendor\facebook\php-sdk-v4\src\Facebook\Helpers\FacebookRedirectLoginHelper.php on line 123,

I change this script:
```
private function makeUrl($redirectUrl, array $scope, array $params = [], $separator = '&')
{
    $state = $this->pseudoRandomStringGenerator->getPseudoRandomString(static::CSRF_LENGTH);
    $this->persistentDataHandler->set('state', $state);

    return $this->oAuth2Client->getAuthorizationUrl($redirectUrl, $state, $scope, $params, $separator);
}
```

into (I add Session::put('state', $state);)
```
private function makeUrl($redirectUrl, array $scope, array $params = [], $separator = '&')
{
    $state = $this->pseudoRandomStringGenerator->getPseudoRandomString(static::CSRF_LENGTH);
    $this->persistentDataHandler->set('state', $state);
    Session::put('state', $state);
    return $this->oAuth2Client->getAuthorizationUrl($redirectUrl, $state, $scope, $params, $separator);
}
```

and on line 234, I change this script:
```
protected function validateCsrf()
{
    $state = $this->getState();
    $savedState = $this->persistentDataHandler->get('state');

    if (!$state || !$savedState) {
        throw new FacebookSDKException('Cross-site request forgery validation failed. Required param "state" missing.');
    }

    $savedLen = strlen($savedState);
    $givenLen = strlen($state);

    if ($savedLen !== $givenLen) {
        throw new FacebookSDKException('Cross-site request forgery validation failed. The "state" param from the URL and session do not match.');
    }

    $result = 0;
    for ($i = 0; $i < $savedLen; $i++) {
        $result |= ord($state[$i]) ^ ord($savedState[$i]);
    }

    if ($result !== 0) {
        throw new FacebookSDKException('Cross-site request forgery validation failed. The "state" param from the URL and session do not match.');
    }
}
```
into (I added $this->persistentDataHandler->set('state', Session::get('state'));)
```
protected function validateCsrf()
{
    $state = $this->getState();
    $this->persistentDataHandler->set('state', Session::get('state'));
    $savedState = $this->persistentDataHandler->get('state');

    if (!$state || !$savedState) {
        throw new FacebookSDKException('Cross-site request forgery validation failed. Required param "state" missing.');
    }

    $savedLen = strlen($savedState);
    $givenLen = strlen($state);

    if ($savedLen !== $givenLen) {
        throw new FacebookSDKException('Cross-site request forgery validation failed. The "state" param from the URL and session do not match.');
    }

    $result = 0;
    for ($i = 0; $i < $savedLen; $i++) {
        $result |= ord($state[$i]) ^ ord($savedState[$i]);
    }

    if ($result !== 0) {
        throw new FacebookSDKException('Cross-site request forgery validation failed. The "state" param from the URL and session do not match.');
    }
}
```

## How to use for Twitter Api

Add this call at the top of the controller:

```
use Jedelhu\Socialpack\SocialpackController;
```

Call function:
```
$result=$social->loginTwitter($data);
```

To get profile info(Includes profile info and images):

```
        $data=array(
            'profile' => "yes",
            );
```

To get tweet:

```
        $data=array(
            'recent_tweets' => array(
                "show" => "yes"
            )
            );
```
To post Tweet:

```
              $data=array(
                   'post_tweet' => array(
                       'show' => "no",
                       'message' => "Test Tweet"
                   )
                   );
```

## How to use for Facebook Api

Add this at the top of the controller:
```
use Jedelhu\Socialpack\SocialpackController;
```

Call function( get session in loginFacebook() function):
```
 Session::put('data', $data);

$result=$social->loginFacebook();

```

To get profile info:

```
        $data=array(
            'profile' => "yes",
            );
```

To get Friends:

```
        $data=array(
            'friends' => "yes",
                  );
```
To get Profile Image:

```
             $data=array(
                'profile_image' => "yes",
            );
```
To get published post:

```
             $data=array(
                'published_post' => "yes",
            );
```
To get like pages:

```
             $data=array(
                'like_pages' => "yes",
            );
```
To get all photos:

```
             $data=array(
                'all_photos' => "yes",
            );
```

Post on timeline:

```
             $data=array(
                'post_timeline' => "yes",
                array(
                "show" => "yes",
                "message" => "My post "
                ),
            );
```
Post Image on timeline:

```
             $data=array(
                'post_timeline' => "yes",
                array(
                "show" => "yes",
                "message" => "My post ",
                "url" => ""
                ),
            );
```