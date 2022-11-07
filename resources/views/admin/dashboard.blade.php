@extends('partials.main')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Dashboard</h1>
        </div>
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary">
                        <i class="far fa-user"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Total Admin</h4>
                        </div>
                        <div class="card-body">
                            {{ $admin_count }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-danger">
                        <i class="fas fa-music"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Tembang Bali</h4>
                        </div>
                        <div class="card-body">
                            {{ $tembang_count }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-warning">
                        <i class="far fa-file"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Tembang Submission</h4>
                        </div>
                        <div class="card-body">
                            {{ $submission_count }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-success">
                        <i class="fas fa-circle"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Online Users</h4>
                        </div>
                        <div class="card-body">
                            {{ $user_count }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 col-md-12 col-12 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Latest Submissions</h4>
                        <div class="card-header-action">
                            <a href="{{ route('admin.submission') }}" class="btn btn-primary">View All</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($latest_submissions as $submission)
                                        <tr>
                                            <td>
                                                {{ $submission->title }}
                                                <div class="table-links">
                                                    <a class="disabled">{{ format_tembang_category($submission) }}</a>
                                                    <div class="bullet"></div>
                                                    <a>{{ $submission->created_at->diffForHumans() }}</a>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="font-weight-600">
                                                    <img src="{{ get_avatar_url($submission->user) }}" alt="avatar"
                                                        width="28" class="rounded-circle mr-1">
                                                    {{ $submission->user->name }}
                                                </div>
                                            </td>
                                            <td><a href="{{ route('admin.submission.detail', $submission->id) }}"
                                                    class="btn btn-primary btn-icon icon-left">
                                                    <i class="far fa-eye mr-2"></i>Detail</a></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3">No matching records found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-12 col-12 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Newcomer Users</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled list-unstyled-border">
                            @forelse ($latest_users as $user)
                                <li class="media">
                                    <img class="mr-3 rounded-circle" width="50" src="{{ get_avatar_url($user) }}"
                                        alt="avatar">
                                    <div class="media-body">
                                        <div class="float-right text-primary">{{ $user->created_at->diffForHumans() }}
                                        </div>
                                        <div class="media-title">{{ $user->name }}</div>
                                        <span class="text-small text-muted">{{ $user->name }} has registered to Metembang
                                            Bali in {{ $user->created_at->format('d F Y') }}</span>
                                    </div>
                                </li>
                            @empty
                                <li class="media">
                                    <div class="media-body">
                                        <div class="text-center">
                                            No matching records found
                                        </div>
                                    </div>
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('plugin_js')
@endsection
