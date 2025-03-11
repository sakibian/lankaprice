@extends('front.layouts.main')

@section('content')
<div class="container my-5">
    <h1 class="mb-4 text-center">Latest Blog Posts</h1>

    <div class="row">
        <!-- Blog Posts Section -->
        <div class="col-md-9">
            <div class="row">
                @foreach($blogs as $blog)
                <div class="col-md-6 col-lg-6 mb-4">
                    <div class="card shadow-sm border-0">
                        <a href="{{ route('blog.show', $blog->slug) }}">
                            <img src="{{ $blog->image ? asset('storage/' . $blog->image) : asset('images/default-blog.jpg') }}" 
                                 alt="{{ $blog->title }}" 
                                 class="card-img-top rounded-top" 
                                 style="height: 200px; object-fit: cover;">
                        </a>
                        <div class="card-body">
                            <h3 class="card-title">
                                <a href="{{ route('blog.show', $blog->slug) }}" class="text-dark text-decoration-none my-1">
                                    {{ Str::limit($blog->title, 50) }}
                                </a>
                            </h3>
                            <p class="text-muted small fs-6">By Admin • {{ $blog->created_at->format('M d, Y') }}</p>
                            <p class="card-text fs-6 my-1">{!! Str::limit(strip_tags($blog->content), 100) !!}</p>

                            <!-- Show Tags -->
                            <p class="mt-2">
                                @foreach($blog->tags as $tag)
                                    <span class="badge bg-secondary fs-7 my-1">{{ $tag->name }}</span>
                                @endforeach
                            </p>

                            <a href="{{ route('blog.show', $blog->slug) }}" class="btn btn-primary btn-md">Read More</a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $blogs->links() }}
            </div>
        </div>

          <!-- Sidebar (Recent Blogs & Tags) -->
          <div class="col-md-3">
            <!-- Recent Blogs -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">Recent Blogs</div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        @foreach($recentBlogs as $recent)
                            <li class="mb-2">
                                <a href="{{ route('blog.show', $recent->slug) }}" class="text-decoration-none text-capitalize fs-7 hover:text-primary">
                                    {{ Str::limit($recent->title, 40) }}
                                </a>                                
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Tags -->
            <div class="card">
                <div class="card-header bg-primary text-white">Categories</div>
                <div class="card-body">
                    @if(isset($tags) && count($tags) > 0)
                        @foreach($tags as $tag)
                            @if(!empty($tag->slug))
                                <a href="{{ route('blog.tag', ['slug' => $tag->slug]) }}" class="badge bg-secondary text-white me-1 my-1 fs-7">
                                    {{ $tag->name }}
                                </a>
                            @endif
                        @endforeach
                    @else
                        <p>No tags available.</p>
                    @endif

                </div>
            </div>
            
        </div>

    </div>
</div>
@endsection
