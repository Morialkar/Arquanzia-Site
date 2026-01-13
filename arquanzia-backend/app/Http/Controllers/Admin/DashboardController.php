<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\Comment;
use App\Models\EncyclopediaNode;
use App\Models\Post;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'postsCount' => Post::count(),
            'usersCount' => User::count(),
            'commentsCount' => Comment::count(),
            'booksCount' => Book::count(),
            'chaptersCount' => Chapter::count(),
            'encyclopediaCount' => EncyclopediaNode::articles()->count(),
            'categoriesCount' => EncyclopediaNode::categories()->count(),
            'recentPosts' => Post::orderBy('created_at', 'desc')->limit(5)->get(),
        ]);
    }
}
