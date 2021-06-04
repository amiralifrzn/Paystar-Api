<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostApiController extends Controller
{
    public function index()
    {
        return Post::all();
    }

    public function store() //$request->all()
    {
        request()->validate([
            'title' => 'required',
            'content' => 'required'
        ]);

        $status = Post::create([
            'title' => request('title'),
            'content' => request('content'),
        ]);

        return [
            'id' => $status->id,
            'status' => $status
        ];
    }

    public function show(Post $post)
    {
        return $post;
    }

    public function update(Post $post)
    {
        request()->validate([
            'title' => 'required',
            'content' => 'required'
        ]);

        $status = $post->update([
            'title' => request('title'),
            'content' => request('content'),
        ]);

        return [
            'status' => $status
        ];
    }

    public function destroy(Post $post)
    {
        $status = $post->delete();

        return [
            'status' => $status
        ];
    }
}
