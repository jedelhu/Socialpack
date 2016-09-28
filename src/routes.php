<?php
/**
 * Created by PhpStorm.
 * User: Husnain
 * Date: 6/2/2016
 * Time: 12:09 PM
 */

Route::group(['middleware' => ['web']], function () {
    Route::get('socialpacks', 'laraveldaily\socialpacks\SocialpackController@index');
    Route::any('socialpacks/twitter', 'laraveldaily\socialpacks\SocialpackController@loginTwitter');
    Route::get('socialpacks/logout-twitter', 'laraveldaily\socialpacks\SocialpackController@logoutTwitter');
    Route::get('callbackTwitter', 'laraveldaily\socialpacks\SocialpackController@callbackTwitter');
    Route::any('loginFacebook', 'laraveldaily\socialpacks\SocialpackController@loginFacebook');
    Route::get('setCredential', 'laraveldaily\socialpacks\SocialpackController@setCredential');
    Route::get('socialpacks/profile-facebook', 'laraveldaily\socialpacks\SocialpackController@profileFacebook');
//    Route::get('callbackFacebook', 'laraveldaily\socialpacks \SocialpackController@callbackFacebook');
});