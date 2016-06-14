<?php
namespace Jedelhu\Socialpack;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Http\Controllers\Controller;
use Facebook;
use Session;


class SocialpackController extends Controller
{
    private $fb;

    public function index()
    {
        return view('socialpacks::index');
    }

    public function loginTwitter($data)
    {
        if (!Session::has('access_token')) {

            $connection = new TwitterOAuth(config('socialpack.composer_key'), config('socialpack.composer_secret'));
            $callback_url = url('/') . '/callbackTwitter';
            $request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => (string)$callback_url));

            Session::put('oauth_token', $request_token['oauth_token']);
            Session::put('oauth_token_secret', $request_token['oauth_token_secret']);
            $url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));

            return redirect()->away($url);
        } else {
            $access_token = Session::get('access_token');

            $connection = new TwitterOAuth(config('socialpack.composer_key'), config('socialpack.composer_secret'), $access_token['oauth_token'], $access_token['oauth_token_secret']);


            if (isset($data) && !empty($data)) {
                if (isset($data['profile']) && !empty($data['profile'])) {
                    if ($data['profile'] == "yes") {
                        // getting basic user info
                        $user = $connection->get("account/verify_credentials");
                        return $user;

                    }
                }
                if (isset($data['post_tweet']) && !empty($data['post_tweet'])) {
                    if ($data['post_tweet']["show"] == "yes") {
                        // posting tweet on user profile
                        $post = $connection->post('statuses/update', array('status' => $data['post_tweet']["message"]));
                        return $post;

                    }
                }

                if (isset($data['recent_tweets']) && !empty($data['recent_tweets'])) {
                    if ($data['recent_tweets']["show"] == "yes") {
                        // getting recent tweeets by user 'snowden' on twitter
                        $tweets = $connection->get('statuses/user_timeline', ['count' => 200, 'exclude_replies' => true, 'screen_name' => 'snowden', 'include_rts' => false]);
                        $totalTweets[] = $tweets;
                        $page = 0;

                        for ($count = 200; $count < 500; $count += 200) {
                            $max = count($totalTweets[$page]) - 1;
                            $tweets = $connection->get('statuses/user_timeline', ['count' => 200, 'exclude_replies' => true, 'max_id' => $totalTweets[$page][$max]->id_str, 'screen_name' => 'snowden', 'include_rts' => false]);
                            $totalTweets[] = $tweets;
                            $page += 1;
                        }
                        return $totalTweets;
                    }
                }

            }
        }
    }

    public function callbackTwitter()
    {

        if (isset($_REQUEST['oauth_verifier'], $_REQUEST['oauth_token']) && $_REQUEST['oauth_token'] == Session::get('oauth_token')) {
            $request_token = [];
            $request_token['oauth_token'] = Session::get('oauth_token');
            $request_token['oauth_token_secret'] = Session::get('oauth_token_secret');
            $connection = new TwitterOAuth(config('socialpack.composer_key'), config('socialpack.composer_secret'), $request_token['oauth_token'], $request_token['oauth_token_secret']);
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


    public function loginFacebook()
    {
        $data = Session::get('data');

        $fb = new Facebook\Facebook([
            'app_id' => config('socialpack.app_id'),
            'app_secret' => config('socialpack.app_secret'),
            'default_graph_version' => config('socialpack.default_graph_version'),
        ]);


        $helper = $fb->getRedirectLoginHelper();
        $callback_url = url('/') . '/loginFacebook';
        $permissions = ['user_birthday', 'user_location', 'user_website', 'email', 'user_friends', 'user_posts', 'user_photos', 'publish_pages', 'user_education_history', 'user_about_me', 'publish_actions']; // optional

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


            // validating the access token
            try {
                $request = $fb->get('/me');
            } catch (Facebook\Exceptions\FacebookResponseException $e) {
                // When Graph returns an error
                if ($e->getCode() == 190) {
                    unset($_SESSION['facebook_access_token']);
                    $helper = $fb->getRedirectLoginHelper();
                    $loginUrl = $helper->getLoginUrl((string)$callback_url, $permissions);
                    echo "<script>window.top.location.href='" . $loginUrl . "'</script>";
                }
                exit;
            } catch (Facebook\Exceptions\FacebookSDKException $e) {
                // When validation fails or other local issues
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }


            if (isset($data) && !empty($data)) {
                if (isset($data['profile']) && !empty($data['profile'])) {
                    if ($data['profile'] == "yes") {
                        $profile = $this->getProfileInfoFacebook($fb);
                        return $profile;
                    }
                }
                if (isset($data['friends']) && !empty($data['friends'])) {
                    if ($data['friends'] == "yes") {
                        $friends = $this->getFiendsFacebook($fb);
                        return $friends;
                    }
                }
                if (isset($data['profile_image']) && !empty($data['profile_image'])) {
                    if ($data['profile_image'] == "yes") {
                        $profile_image = $this->getProfileImageFacebook($fb);
                        return $profile_image;
                    }
                }
                if (isset($data['published_post']) && !empty($data['published_post'])) {
                    if ($data['published_post'] == "yes") {
                        $published_post = $this->getPublishPostFacebook($fb);
                        return $published_post;
                    }
                }
                if (isset($data['like_pages']) && !empty($data['like_pages'])) {
                    if ($data['like_pages'] == "yes") {
                        $like_pages = $this->getLikePagesFacebook($fb);
                        return $like_pages;
                    }
                }
                if (isset($data['all_photos']) && !empty($data['all_photos'])) {
                    if ($data['all_photos'] == "yes") {
                        $all_photos = $this->getAllPhotosFacebook($fb);
                        return $all_photos;
                    }
                }
                if (isset($data['published_post']) && !empty($data['published_post'])) {
                    if ($data['published_post'] == "yes") {
                        $published_post = $this->getPublishPostFacebook($fb);
                        return $published_post;
                    }
                }
                if (isset($data['post_timeline']) && !empty($data['post_timeline'])) {
                    if ($data['post_timeline']["show"] == "yes") {
                        $post_timeline = $this->postOnTimelineFacebook($fb, $data['post_timeline']["message"]);
                        return $post_timeline;
                    }
                }
                if (isset($data['post_link_timeline']) && !empty($data['post_link_timeline'])) {
                    if ($data['post_link_timeline']["show"] == "yes") {
                        $post_timeline = $this->postLinkOnTimelineFacebook($fb, $data['post_link_timeline']["link"]);
                        return $post_timeline;
                    }
                }

                if (isset($data['post_image_timeline']) && !empty($data['post_image_timeline'])) {
                    if ($data['post_image_timeline']["show"] == "yes") {
                        $post_image_timeline = $this->postImageOnTimelineFacebook($fb, $data['post_timeline']["message"], $data['post_timeline']["url"]);
                        return $post_image_timeline;
                    }
                }


            }

        } else {
            // replace your website URL same as added in the developers.facebook.com/apps e.g. if you used http instead of https and you used non-www version or www version of your website then you must add the same here
            $loginUrl = $helper->getLoginUrl((string)$callback_url, $permissions);
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

        return $profile;

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
        return $friendsfb;
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
        return $picture['url'];
        // showing picture on the screen
//        echo "<img src='" . $picture['url'] . "'/>";


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
        return $posts_response;
    }

    public function getLikePagesFacebook($fb)
    {
        // get list of pages liked by user
        try {
            $requestLikes = $fb->get('/me/likes?limit=100');
            $likes = $requestLikes->getGraphEdge();
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
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
        return $totalLikes;
    }


    public function postImageOnTimelineFacebook($fb, $msg, $url)
    {
        //upload image in timeline
        try {
            // message must come from the user-end
            $data = ['source' => $fb->fileToUpload($url), 'message' => $msg];
            $request = $fb->post('/me/photos', $data);
            $response = $request->getGraphNode()->asArray();
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
        return $response['id'];
    }

    public function postOnTimelineFacebook($fb, $msg)
    {
        // posting on user timeline using publish_actins permission
        try {
            // message must come from the user-end
            $data = ['message' => $msg];
            $request = $fb->post('/me/feed', $data);
            $response = $request->getGraphEdge()->asArray;
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
        return $response['id'];
    }

    public function postLinkOnTimelineFacebook($fb, $link)
    {
        // posting on user timeline using publish_actins permission
        try {
            // message must come from the user-end
            $data = ['link' => $link];
            $request = $fb->post('/me/feed', $data);
            $response = $request->getGraphEdge()->asArray;
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
        return $response['id'];
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
        $all_photo = array();
        foreach ($all_photos as $key) {
            $photo_request = $fb->get('/' . $key['id'] . '?fields=images');
            $photo = $photo_request->getGraphNode()->asArray();
            $all_photo[] = $photo;
            echo '<img src="' . $photo['images'][2]['source'] . '"><br>';
        }

        return $all_photo;

    }

}
