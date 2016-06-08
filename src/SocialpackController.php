<?php
namespace Jedelhu\Socialpack ;

use App\Http\Controllers\Controller;
use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Http\Request;
use Session;
use Facebook;


class SocialpackController extends Controller
{
    private $fb;

    public function index()
    {
        return view('socialpacks::index');
    }

    public function loginTwitter(Request $request)
    {
        if (!Session::has('access_token')) {

            $connection = new TwitterOAuth(config('socialpacks.composer_key'), config('socialpacks.composer_secret'));

            $request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => url('/') . '/callbackTwitter'));
            Session::put('oauth_token', $request_token['oauth_token']);
            Session::put('oauth_token_secret', $request_token['oauth_token_secret']);
            $url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));

            return redirect()->away($url);
        } else {
            $access_token = Session::get('access_token');

            $connection = new TwitterOAuth(config('socialpacks.composer_key'), config('socialpacks.composer_secret'), $access_token['oauth_token'], $access_token['oauth_token_secret']);

            // getting basic user info
            $user = $connection->get("account/verify_credentials");


            // printing username on screen
            return view('socialpacks::logintwitter', compact('user'));
        }
    }

    public function callbackTwitter()
    {

        if (isset($_REQUEST['oauth_verifier'], $_REQUEST['oauth_token']) && $_REQUEST['oauth_token'] == Session::get('oauth_token')) {
            $request_token = [];
            $request_token['oauth_token'] = Session::get('oauth_token');
            $request_token['oauth_token_secret'] = Session::get('oauth_token_secret');
            $connection = new TwitterOAuth(config('socialpacks.composer_key'), config('socialpacks.composer_secret'), $request_token['oauth_token'], $request_token['oauth_token_secret']);
            $access_token = $connection->oauth("oauth/access_token", array("oauth_verifier" => $_REQUEST['oauth_verifier']));
            Session::put('access_token', $access_token);
            return redirect('/socialpacks/twitter');
        }
    }


    public function logoutTwitter()
    {

        Session::forget('oauth_token');
        Session::forget('oauth_token_secret');
        Session::forget('twkey');
        Session::forget('twsecret');
        Session::forget('access_token');

        return redirect('/socialpacks');
    }


    public function loginFacebook(Request $request)
    {

        session_start();

        $fb = new Facebook\Facebook([
            'app_id' => config('socialpacks.app_id'),
            'app_secret' => config('socialpacks.app_secret'),
            'default_graph_version' => config('socialpacks.default_graph_version'),
        ]);


        $helper = $fb->getRedirectLoginHelper();

        $permissions = ['user_birthday', 'user_location', 'user_website', 'email', 'user_friends', 'user_posts', 'user_photos','publish_pages','user_education_history','user_about_me','publish_actions']; // optional

        try {
            if (Session::has('facebook_access_token')) {
                $accessToken = $_SESSION['facebook_access_token'];
            } else {
                $accessToken = $helper->getAccessToken();
            }
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();

            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        if (isset($accessToken)) {
            if (isset($_SESSION['facebook_access_token'])) {
                $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
            } else {
                // getting short-lived access token
                $_SESSION['facebook_access_token'] = (string)$accessToken;

                // OAuth 2.0 client handler
                $oAuth2Client = $fb->getOAuth2Client();

                // Exchanges a short-lived access token for a long-lived one
                $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);

                $_SESSION['facebook_access_token'] = (string)$longLivedAccessToken;

                // setting default access token to be used in script
                $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
            }

            // redirect the user back to the same page if it has "code" GET variable
//            if (isset($_GET['code'])) {
//                header('Location: ./');
//            }

            // validating the access token
            try {
                $request = $fb->get('/me');
            } catch (Facebook\Exceptions\FacebookResponseException $e) {
                // When Graph returns an error
                if ($e->getCode() == 190) {
                    unset($_SESSION['facebook_access_token']);
                    $helper = $fb->getRedirectLoginHelper();
                    $loginUrl = $helper->getLoginUrl(url('/') . '/loginFacebook', $permissions);
                    echo "<script>window.top.location.href='" . $loginUrl . "'</script>";
                }
                exit;
            } catch (Facebook\Exceptions\FacebookSDKException $e) {
                // When validation fails or other local issues
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }

//            $this->getProfileInfoFacebook($fb);
//            $this->getFiendsFacebook($fb);
//            $this->getProfileImageFacebook($fb);
//            $this->getPublishPostFacebook($fb);

//            $this->getLikePagesFacebook($fb);
//            $this->getAllPhotosFacebook($fb);
//            $this->postOnTimelineFacebook($fb);
//            $this->postImageOnTimelineFacebook($fb);

            // Now you can redirect to another page and use the access token from $_SESSION['facebook_access_token']

//            return view('socialpacks::loginfacebook', compact('profile','friendsfb','picture'));
        } else {
            $helper = $fb->getRedirectLoginHelper();
            // replace your website URL same as added in the developers.facebook.com/apps e.g. if you used http instead of https and you used non-www version or www version of your website then you must add the same here
            $loginUrl = $helper->getLoginUrl(url('/') . '/loginFacebook', $permissions);
            echo '<a href="' . $loginUrl . '">Log in with Facebook!</a>';
        }

    }

    public function getProfileInfoFacebook($fb)
    {
        // getting basic info about user
        try {
            $profile_request = $fb->get('/me?fields=name,first_name,last_name,email,birthday,website,location,religion,quotes,political,languages,hometown,gender,education');
            $profile = $profile_request->getGraphNode()->asArray();
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            session_destroy();
            // redirecting user back to app login page
            header("Location: ./");
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        // printing $profile array on the screen which holds the basic info about user
//        dd($profile);

    }

    public function getFiendsFacebook($fb)
    {
        // get list of friends' names
        try {
            $requestFriends = $fb->get('/me/taggable_friends?fields=name&limit=100');
            $friends = $requestFriends->getGraphEdge();
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        // if have more friends than 100 as we defined the limit above on line no. 68
        $friendsfb = array();
        if ($fb->next($friends)) {
            $allFriends = array();
            $friendsArray = $friends->asArray();
            $allFriends = array_merge($friendsArray, $allFriends);
            while ($friends = $fb->next($friends)) {
                $friendsArray = $friends->asArray();
                $allFriends = array_merge($friendsArray, $allFriends);
            }
            foreach ($allFriends as $key) {
                $friendsfb[] = $key['name'];
                echo $key['name'] . "<br>";
            }
            echo count($allFriends);
        } else {
            $allFriends = $friends->asArray();
            $totalFriends = count($allFriends);
            foreach ($allFriends as $key) {
                $friendsfb[] = $key['name'];
                echo $key['name'] . "<br>";
            }
        }
    }

    public function getProfileImageFacebook($fb)
    {
        // getting profile picture of the user
        try {
            $requestPicture = $fb->get('/me/picture?redirect=false&height=300'); //getting user picture
            $requestProfile = $fb->get('/me'); // getting basic info
            $picture = $requestPicture->getGraphUser();
            $profile = $requestProfile->getGraphUser();
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        // showing picture on the screen
        echo "<img src='" . $picture['url'] . "'/>";

        // saving picture
        $img = __DIR__ . '/' . $profile['id'] . '.jpg';
//            file_put_contents($img, file_get_contents($picture['url']));

    }

    public function getPublishPostFacebook($fb)
    {

        // getting all posts published by user
        try {
            $posts_request = $fb->get('/me/posts?limit=500');
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        $total_posts = array();
        $posts_response = $posts_request->getGraphEdge();
        if ($fb->next($posts_response)) {
            $response_array = $posts_response->asArray();
            $total_posts = array_merge($total_posts, $response_array);
            while ($posts_response = $fb->next($posts_response)) {
                $response_array = $posts_response->asArray();
                $total_posts = array_merge($total_posts, $response_array);
            }
            print_r($total_posts);
        } else {
            $posts_response = $posts_request->getGraphEdge()->asArray();
            print_r($posts_response);
        }
    }

    public function getLikePagesFacebook($fb)
    {
        // get list of pages liked by user
        try {
            $requestLikes = $fb->get('/me/likes?limit=100');
            $likes = $requestLikes->getGraphEdge();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        $totalLikes = array();
        if ($fb->next($likes)) {
            $likesArray = $likes->asArray();
            $totalLikes = array_merge($totalLikes, $likesArray);
            while ($likes = $fb->next($likes)) {
                $likesArray = $likes->asArray();
                $totalLikes = array_merge($totalLikes, $likesArray);
            }
        } else {
            $likesArray = $likes->asArray();
            $totalLikes = array_merge($totalLikes, $likesArray);
        }

        // printing data on screen
        foreach ($totalLikes as $key) {
            echo $key['name'] . '<br>';
        }
    }


    public function postImageOnTimelineFacebook($fb){
        //upload image in timeline
            try {
                // message must come from the user-end
                $data = ['source' => $fb->fileToUpload(url('images/Lighthouse.jpg')), 'message' => 'my photo'];
                $request = $fb->post('/me/photos', $data);
                $response = $request->getGraphNode()->asArray();
            } catch(Facebook\Exceptions\FacebookResponseException $e) {
                // When Graph returns an error
                echo 'Graph returned an error: ' . $e->getMessage();
                exit;
            } catch(Facebook\Exceptions\FacebookSDKException $e) {
                // When validation fails or other local issues
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }
            echo $response['id'];
    }
    public function postOnTimelineFacebook($fb){
        // posting on user timeline using publish_actins permission
        try {
            // message must come from the user-end
            $data = ['message' => 'There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don\'t look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn\'t anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.'];
            $request = $fb->post('/me/feed', $data);
            $response = $request->getGraphEdge()->asArray;
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        echo $response['id'];
    }
    public function getAllPhotosFacebook($fb)
    {
        // getting all photos of user
            try {
                $photos_request = $fb->get('/me/photos?limit=100&type=uploaded');
                $photos = $photos_request->getGraphEdge();
            } catch (Facebook\Exceptions\FacebookResponseException $e) {
                // When Graph returns an error
                echo 'Graph returned an error: ' . $e->getMessage();
                exit;
            } catch (Facebook\Exceptions\FacebookSDKException $e) {
                // When validation fails or other local issues
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }

            $all_photos = array();
            if ($fb->next($photos)) {
                $photos_array = $photos->asArray();
                $all_photos = array_merge($photos_array, $all_photos);
                while ($photos = $fb->next($photos)) {
                    $photos_array = $photos->asArray();
                    $all_photos = array_merge($photos_array, $all_photos);
                }
            } else {
                $photos_array = $photos->asArray();
                $all_photos = array_merge($photos_array, $all_photos);
            }

            foreach ($all_photos as $key) {
                $photo_request = $fb->get('/' . $key['id'] . '?fields=images');
                $photo = $photo_request->getGraphNode()->asArray();
                echo '<img src="' . $photo['images'][2]['source'] . '"><br>';
            }

    }

}
