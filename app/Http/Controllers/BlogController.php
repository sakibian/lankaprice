<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{

    public function adminIndex()
    {
        $blogs = Blog::latest()->paginate(10);
        return view('admin.blogs.index', compact('blogs'));
    }

    public function adminCreate()
    {
        return view('admin.blogs.create');
    }

    public function adminStore(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:blogs,slug',
            'content' => 'required',
            'image' => 'nullable|image|max:2048',
        ]);
        
        // Assign the authenticated user ID
        $data['author_id'] = auth()->id(); 
        
        // Store image if uploaded
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('blogs', 'public');
        }
        
        // Create the blog post
        Blog::create($data);
        
        return redirect()->route('admin.blogs')->with('success', 'Blog post created successfully.');
        
    }

    public function adminEdit($id)
    {
        $blog = Blog::findOrFail($id);
        return view('admin.blogs.edit', compact('blog'));
    }

    public function adminUpdate(Request $request, $id)
    {
        $blog = Blog::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:blogs,slug,' . $id,
            'content' => 'required',
            'image' => 'nullable|image|max:2048',
        ]);

        // Keep the original author_id or update if necessary
        $data['author_id'] = $blog->author_id ?? auth()->id(); 

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('blogs', 'public');
        }

        $blog->update($data);
        
        return redirect()->route('admin.blogs')->with('success', 'Blog post updated successfully.');
    }


    public function adminDestroy($id)
    {
        $blog = Blog::findOrFail($id);
        $blog->delete();
        return redirect()->route('admin.blogs')->with('success', 'Blog post deleted successfully.');
    }
   
    public function index()
    {
        $blogs = Blog::latest()->paginate(10);
        return view('front.blogs.index', compact('blogs'));
    }

   
    public function create()
    {
        //
    }

    
    public function store(Request $request)
    {
        //
    }

    
    public function show($slug)
    {
        $blog = Blog::where('slug', $slug)->firstOrFail();
        return view('front.blogs.show', compact('blog'));
    }

   
    public function edit(Blog $blog)
    {
        //
    }

   
    public function update(Request $request, Blog $blog)
    {
        //
    }

   
    public function destroy(Blog $blog)
    {
        //
    }

   

}
