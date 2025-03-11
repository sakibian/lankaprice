<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Blog;
use App\Models\Page;
use Illuminate\Support\Str;
use Mews\Purifier\Purifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class BlogController extends Controller
{

    protected $purifier;

    public function __construct(Purifier $purifier)
    {
        $this->purifier = $purifier;
    }

    public function adminIndex()
    {
        $blogs = Blog::latest()->paginate(10);
        return view('admin.blogs.index', compact('blogs'));
    }

    public function adminCreate()
    {
        $tags = Tag::all(); // Fetch all available tags
        return view('admin.blogs.create', compact('tags'));
    }

    public function adminStore(Request $request)
    {
        // **Validate Request Data**
        $validatedData = $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
            'image'   => 'nullable|image|max:2048',
            'slug'    => 'nullable|string|unique:blogs,slug',
            'tags'    => 'array',
        ]);

        // **Sanitize Content to Prevent XSS**
        $validatedData['content'] = $this->purifier->clean($request->input('content'));

        // **Generate Unique Slug if not provided**
        if (!$request->filled('slug')) {
            $baseSlug = Str::slug($validatedData['title']);
            $slug = $baseSlug;
            $count = 1;

            while (Blog::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $count;
                $count++;
            }

            $validatedData['slug'] = $slug;
        }

        // **Assign the authenticated user ID**
        $validatedData['author_id'] = auth()->id();

        // **Store Image if Uploaded**
        if ($request->hasFile('image')) {
            $validatedData['image'] = $request->file('image')->store('blogs', 'public');
        }

        // **Create Blog Post**
        $blog = Blog::create($validatedData);

        // Attach Tags
        $tagIds = [];
        foreach ($request->tags as $tagName) {
            $tag = Tag::firstOrCreate(['name' => trim($tagName)]);
            $tagIds[] = $tag->id;
        }
        $blog->tags()->sync($tagIds);

        return redirect()->route('admin.blogs')->with('success', 'Blog post created successfully.');
    }


    public function adminEdit($id)
    {
        $blog = Blog::findOrFail($id);
        $tags = Tag::all();
        return view('admin.blogs.edit', compact('blog', 'tags'));
    }

    public function adminUpdate(Request $request, $id)
    {
        $blog = Blog::findOrFail($id);

        // **Validate Request Data**
        $validatedData = $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
            'image'   => 'nullable|image|max:2048',
            'slug'    => 'nullable|string|unique:blogs,slug,' . $id,
            'tags'    => 'array',
        ]);

        // **Sanitize Content to Prevent XSS**
        $validatedData['content'] = $this->purifier->clean($request->input('content'));
        

        // **Generate Unique Slug if not provided**
        if (!$request->filled('slug')) {
            $baseSlug = Str::slug($validatedData['title']);
            $slug = $baseSlug;
            $count = 1;

            while (Blog::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $count;
                $count++;
            }

            $validatedData['slug'] = $slug;
        }

        // **Assign the authenticated user ID if not already set**
        $validatedData['author_id'] = $blog->author_id ?? auth()->id();

        // **Store Image if Uploaded**
        if ($request->hasFile('image')) {
            // Delete the old image if a new one is uploaded
            if ($blog->image) {
                Storage::delete('public/' . $blog->image);
            }
            $validatedData['image'] = $request->file('image')->store('blogs', 'public');
        }

        // **Update the Blog Post**
        $blog->update($validatedData);

        // **Handle Tags**
        if ($request->has('tags')) {
            $tagIds = [];
            foreach ($request->tags as $tagName) {
                // Create new tags or get existing ones
                $tag = Tag::firstOrCreate(['name' => trim($tagName)]);
                $tagIds[] = $tag->id;
            }
            // Sync the tags to ensure the relationship is updated
            $blog->tags()->sync($tagIds);
        }

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

        $blogs = Blog::latest()->paginate(6);
        $recentBlogs = Blog::latest()->take(5)->get();
        $tags = Tag::all();

        $pages = Page::all();
    
        $og = [
            'title'       => 'Our Blogs',
            'description' => Blog::latest()->value('content') 
                ? Str::limit(strip_tags(Blog::latest()->value('content')), 150)
                : 'Stay updated with our latest blog posts',
        ];
    
        return view('front.blogs.index', compact('blogs', 'pages', 'og', 'recentBlogs', 'tags'));
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
        // Get the blog post by its slug
        $blog = Blog::where('slug', $slug)->firstOrFail();

        // Get all tags (if you're using the tags in the sidebar)
        $tags = Tag::all();

        // Get the tag related to the blog post if needed
        $recentBlogs = Blog::latest()->take(5)->get();

        // Open Graph metadata (optional)
        $og = [
            'title'       => $blog->title,
            'description' => Str::limit(strip_tags($blog->content ?? ''), 150), // Ensure content is not null
        ];

        // Pass variables to the view
        return view('front.blogs.show', compact('blog', 'recentBlogs', 'tags', 'og'));
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

    public function filterByTag($slug)
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();
        $blogs = $tag->blogs()->paginate(6);
        $recentBlogs = Blog::latest()->take(5)->get();
        $tags = Tag::all();

        $og = [
            'title'       => $tag->name,
            'description' => Str::limit(strip_tags($blog->content ?? ''), 150), // Ensure content is not null
        ];

        return view('front.blogs.index', compact('blogs', 'recentBlogs', 'tags', 'og'));
    }


   

}
