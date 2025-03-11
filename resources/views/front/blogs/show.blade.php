@extends('front.layouts.main')

@section('content')
<div class="container my-5">
    <div class="row">
        <!-- Main Content Section (Blog Post with Image) -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                @if($blog->image)
                    <img src="{{ asset('storage/' . $blog->image) }}" alt="{{ $blog->title }}" class="card-img-top w-100" style="max-height: 400px; object-fit: cover;">
                @else
                    <img src="{{ asset('images/default-blog.jpg') }}" alt="No Image" class="card-img-top w-100" style="max-height: 400px; object-fit: cover;">
                @endif
                <div class="card-body text-center">
                    <h1 class="card-title">{{ $blog->title }}</h1>
                    <p class="text-muted fs-6">By Admin • {{ $blog->created_at->format('M d, Y') }}</p>
                </div>
            </div>

            <!-- Blog Content -->
            <div class="card shadow-sm border-0 p-4 mb-4">
                <div class="card-body">
                    <p class="card-text" style="line-height: 1.6; font-size: 18px;">
                        {!! $blog->content !!}
                    </p>
                </div>
            </div>

            <!-- Tags related to the blog -->
            <div class="card mb-4">
                <div class="card-body">
                    <strong class="fs-5 m-2">Tags:</strong>
                    @foreach($blog->tags as $tag)
                        <span class="badge bg-primary fs-6 my-1">{{ $tag->name }}</span>
                    @endforeach
                </div>
            </div>

            <!-- Back to Blog List -->
            <div class="text-center mt-4">
                <a href="{{ route('blog.index') }}" class="btn btn-outline-primary">← Back to Blogs</a>
            </div>
        </div>

        <!-- Sidebar Section (Recent Blogs & Tags) -->
        <div class="col-md-4">
            <!-- Recent Blogs -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">Recent Blogs</div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        @foreach($recentBlogs as $recent)
                            <li class="mb-2">
                                <a href="{{ route('blog.show', $recent->slug) }}" class="text-decoration-none text-capitalize fs-6 hover:text-primary">
                                    {{ Str::limit($recent->title, 40) }}
                                </a>                                
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Tags -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">Categories</div>
                <div class="card-body">
                    @foreach($tags as $tag)
                        @if(!empty($tag->slug))
                            <a href="{{ route('blog.tag', ['slug' => $tag->slug]) }}" class="badge bg-secondary text-white me-1 my-1 fs-6">
                                {{ $tag->name }}
                            </a>
                        @endif
                    @endforeach

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
