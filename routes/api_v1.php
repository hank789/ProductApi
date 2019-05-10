<?php
/**
 * @author: wanghui
 * @date: 2017/4/6 下午3:12
 * @email: wanghui@yonglibao.com
 */

//登陆注册认证类
Route::group(['prefix' => 'auth','namespace'=>'Account'], function() {
    Route::post('register', 'AuthController@register');
    Route::post('login', 'AuthController@login');

    Route::post('forgot', 'AuthController@forgetPassword');
    Route::post('sendPhoneCode', 'AuthController@sendPhoneCode');

    Route::post('logout', 'AuthController@logout')->middleware('jwt.auth');

    //更换手机号
    Route::post('changePhone', 'AuthController@changePhone')->middleware('jwt.auth');

    //微信小程序注册
    Route::post('weapp/register', 'AuthController@registerWeapp')->middleware(['jwt.weappConfig','jwt.weappAuth']);
});

//用户信息
Route::group(['middleware' => ['jwt.auth','ban.user'],'namespace'=>'Account'], function() {
    //用户信息
    Route::get('profile/info','ProfileController@info');
    Route::post('profile/info','ProfileController@info');

    //用户修改密码
    Route::post('profile/password','ProfileController@updatePassword');
    //用户修改基本信息
    Route::post('profile/update','ProfileController@update');
});

//上传图片
Route::post('upload/img','ImageController@upload')->middleware('jwt.auth');

//意见反馈
Route::post('system/feedback','SystemController@feedback')->middleware('jwt.auth');

//接收文章推送
Route::post('system/pushArticle/{product_id}','SystemController@pushArticle');


//微信小程序
Route::group(['namespace'=>'Weapp','prefix' => 'weapp','middleware' => ['jwt.weappConfig']], function() {
    //获取用户微信信息
    Route::post('user/wxinfo','UserController@getWxUserInfo');
    //获取用户信息
    Route::post('user/info','UserController@getUserInfo')->middleware(['jwt.weappAuth']);
    //更新用户信息
    Route::post('user/updateUserInfo','UserController@updateUserInfo')->middleware(['jwt.weappAuth']);
    Route::post('user/updatePhone','UserController@updatePhone')->middleware(['jwt.weappAuth']);
    //存储表单提交的formId
    Route::post('user/saveFormId','UserController@saveFormId')->middleware(['jwt.weappAuth']);
    //获取二维码
    Route::post('user/getQrCode','UserController@getQrCode');

    //企业点评
    Route::post('search/tagProduct','SearchController@tagProduct')->middleware(['jwt.weappAuth']);
    Route::get('search/getCommonTagProduct','SearchController@getCommonTagProduct')->middleware(['jwt.weappAuth']);
    Route::get('product/info','ProductController@info');
    Route::post('product/reviewList','ProductController@reviewList')->middleware(['jwt.weappAuth']);
    Route::get('product/reviewInfo','ProductController@reviewInfo');
    Route::get('product/reviewCommentList','ProductController@reviewCommentList');
    Route::post('product/storeReview','ProductController@storeReview')->middleware(['jwt.weappAuth']);
    Route::post('product/addReviewImage','ProductController@addReviewImage')->middleware(['jwt.weappAuth']);
    Route::get('product/myReview','ProductController@myReviewList')->middleware(['jwt.weappAuth']);
    Route::get('product/getProductShareImage','ProductController@getProductShareImage');
    Route::get('product/getReviewShareImage','ProductController@getReviewShareImage');
    Route::get('product/getAlbumShareImage','ProductController@getAlbumShareImage');
    Route::get('product/albumList','ProductController@getAlbumList');
    Route::get('product/moreAlbum','ProductController@moreAlbum');
    Route::get('product/albumInfo','ProductController@albumInfo');
    Route::get('product/albumProductList','ProductController@albumProductList')->middleware(['jwt.weappAuth']);
    Route::post('product/supportAlbumProduct','ProductController@supportAlbumProduct')->middleware(['jwt.weappAuth']);
    Route::get('product/getAlbumSupports','ProductController@getAlbumSupports');
    Route::post('product/newsList','ProductController@newsList');
    Route::post('product/commentAlbum','ProductController@commentAlbum');
    Route::post('product/albumNewsList','ProductController@albumNewsList');
    Route::post('product/albumComments','ProductController@albumCommentList');
    Route::post('product/reportActivity','ProductController@reportActivity');
    Route::get('product/getHot','ProductController@getHotProducts');
    Route::get('product/hotAlbum','ProductController@getHotAlbum');

});

//微信小程序
Route::group(['prefix' => 'weapp','middleware' => ['jwt.weappConfig']], function() {
    //企业点评
    Route::post('product/feedback','SystemController@feedback')->middleware(['jwt.weappAuth']);
    Route::post('product/upvoteReview','Article\SubmissionVotesController@upVote')->middleware(['jwt.weappAuth']);
    Route::post('product/downvoteReview','Article\SubmissionVotesController@downVote')->middleware(['jwt.weappAuth']);
    Route::post('product/upvoteComment','Article\SubmissionVotesController@downVote')->middleware(['jwt.weappAuth']);
    Route::post('product/support/{source_type}',['uses'=>'SupportController@store'])->where(['source_type'=>'(answer|article|comment)'])->middleware(['jwt.weappAuth']);
});

//客户管理后台
Route::group(['namespace'=>'Manage','prefix' => 'manage','middleware' => ['jwt.auth','ban.user']], function() {
    Route::post('product/ideaList','ProductController@ideaList');
    Route::post('product/sourceList','ProductController@sourceList');
    Route::post('product/caseList','ProductController@caseList');
    Route::post('product/newsList','ProductController@newsList');
    Route::post('product/deleteIntroducePic','ProductController@deleteIntroducePic');
    Route::post('product/delSource','ProductController@delSource');
    Route::post('product/sortIdea','ProductController@sortIdea');
    Route::post('product/sortIntroducePic','ProductController@sortIntroducePic');
    Route::post('product/sortCase','ProductController@sortCase');
    Route::post('product/updateCaseStatus','ProductController@updateCaseStatus');
    Route::post('product/updateIdeaStatus','ProductController@updateIdeaStatus');
    Route::post('product/updateNewsStatus','ProductController@updateNewsStatus');
    Route::post('product/updateIdea','ProductController@updateIdea');
    Route::post('product/updateIntroducePic','ProductController@updateIntroducePic');
    Route::post('product/updateInfo','ProductController@updateInfo');
    Route::post('product/updateCase','ProductController@updateCase');
    Route::post('product/storeIdea','ProductController@storeIdea');
    Route::post('product/storeSource','ProductController@storeSource');
    Route::post('product/storeCase','ProductController@storeCase');
    Route::post('product/storeNews','ProductController@storeNews');
    Route::post('product/fetchUrlInfo','ProductController@fetchUrlInfo');
    Route::get('product/getIntroducePic','ProductController@getIntroducePic');
    Route::get('product/getViewData','ProductController@getViewData');
    Route::post('product/fetchSourceInfo','ProductController@fetchSourceInfo');
    Route::get('product/getInfo','ProductController@getInfo');

    Route::post('product/delOfficialReplyDianping','ProductController@delOfficialReplyDianping');
    Route::post('product/delDianping','ProductController@delDianping');
    Route::post('product/recommendDianping','ProductController@recommendDianping');
    Route::post('product/officialReplyDianping','ProductController@officialReplyDianping');
    Route::post('product/dianpingList','ProductController@dianpingList');
    Route::post('product/addCustomUserTag','ProductController@addCustomUserTag');
    Route::post('product/delCustomUserTag','ProductController@delCustomUserTag');

    Route::post('product/visitedUserList','ProductController@visitedUserList');
    Route::post('product/userVisitList','ProductController@userVisitList');
});
