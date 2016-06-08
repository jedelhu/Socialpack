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

## How to use after Adding package




