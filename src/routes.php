<?php
/**
 * Created by PhpStorm.
 * User: Husnain
 * Date: 6/2/2016
 * Time: 12:09 PM
 */

Route::group(['middleware' => ['web']], function () {
    Route::get('socialpacks', 'jedelhu\socialpack\SocialpackController@index');
    Route::any('socialpacks/twitter', 'jedelhu\socialpack\SocialpackController@loginTwitter');
    Route::get('socialpacks/logout-twitter', 'jedelhu\socialpack\SocialpackController@logoutTwitter');
    Route::get('callbackTwitter', 'jedelhu\socialpack\SocialpackController@callbackTwitter');
    Route::any('loginFacebook', 'jedelhu\socialpack\SocialpackController@loginFacebook');
    Route::get('setCredential', 'jedelhu\socialpack\SocialpackController@setCredential');
    Route::get('socialpacks/profile-facebook', 'jedelhu\socialpack\SocialpackController@profileFacebook');
//    Route::get('callbackFacebook', 'jedelhu\socialpack \SocialpackController@callbackFacebook');
});