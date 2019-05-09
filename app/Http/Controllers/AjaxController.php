<?php

namespace App\Http\Controllers;

use App\Logic\TagsLogic;
use App\Models\User;
use Illuminate\Http\Request;

class AjaxController extends Controller
{

    public function loadTags(Request $request)
    {
        $word = $request->input('word');
        $tags = [];
        if( strlen($word) > 10 ){
            return response()->json($tags);
        }
        $tag_type = $request->input('type','all');


        $data = TagsLogic::loadTags($tag_type,$word,'id');
        $tags = $data['tags'];

        return response()->json($tags);
    }



    public function loadUsers(Request $request)
    {
        $word = $request->input('word');

        $users = User::where('name','like',"%$word%")->take(20)->get();
        return response()->json($users->toArray());
    }





}
