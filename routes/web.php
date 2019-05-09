<?php
/**
 * @author: wanghui
 * @date: 2017/4/19 下午7:46
 * @email: wanghui@yonglibao.com
 */

//首页
Route::group(['namespace'=>'Web'], function() {

    Route::get('/',['as'=>'website.index','uses'=>'IndexController@index']);

    Route::get('/articleInfo/{id}',['as'=>'website.articleInfo','uses'=>'IndexController@articleInfo'])->where(['id'=>'[0-9]+']);

    Route::get('/weapp/getProductShareLongInfo/{id}',['uses'=>'WeappController@getProductShareLongInfo'])->where(['id'=>'[0-9]+']);
    Route::get('/weapp/getProductShareShortInfo/{id}',['uses'=>'WeappController@getProductShareShortInfo'])->where(['id'=>'[0-9]+']);
    Route::get('/weapp/getReviewShareLongInfo/{id}',['uses'=>'WeappController@getReviewShareLongInfo'])->where(['id'=>'[0-9]+']);
    Route::get('/weapp/getReviewShareShortInfo/{id}',['uses'=>'WeappController@getReviewShareShortInfo'])->where(['id'=>'[0-9]+']);
    Route::get('/weapp/getAlbumShareLongInfo/{id}',['uses'=>'WeappController@getAlbumShareLongInfo'])->where(['id'=>'[0-9]+']);

});
