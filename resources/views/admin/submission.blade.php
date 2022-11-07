@extends('partials.main')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Submission</h1>
        </div>
        <div class="section-body">
            <h2 class="section-title">Submission</h2>
            <p class="section-lead">Daftar submission user yang masuk pada sistem Metembang Bali.</p>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Tembang Submission Table</h4>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                @if (session('success') || session('error'))
                                <div class="p-4">
                                    @include('partials.alert')
                                </div>
                                @endif
                                <table class="table table-striped table-md">
                                    <tr>
                                        <th style="padding-left: 28px">#</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Sub-Category</th>
                                        <th>Created at</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                    @forelse ($submissions as $submission)
                                        <tr>
                                            <td style="padding-left: 28px">
                                                {{ ($submissions->currentPage() - 1) * $submissions->perPage() + $loop->iteration }}
                                            </td>
                                            <td>{{ $submission->title }}</td>
                                            <td>{{ $submission->category }}</td>
                                            <td>{{ $submission->sub_category }}</td>
                                            <td>{{ $submission->created_at }}</td>
                                            <td>
                                                <div class="badge {{ submission_status_color($submission->status) }}">
                                                    {{ ucfirst($submission->status) }}</div>
                                            </td>
                                            <td>
                                                <div class="d-flex">
                                                    <a href="{{ route('admin.submission.detail', $submission->id) }}"
                                                        class="btn btn-primary btn-action mr-1" data-toggle="tooltip"
                                                        title="Detail"><i class="fas fa-eye"></i></a>
                                                    <form action="{{ route('admin.submission.destroy', $submission->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        <button class="btn btn-danger btn-action" data-toggle="tooltip"
                                                            title="Delete"
                                                            data-confirm="Are You Sure?|This action can not be undone. Do you want to continue?"
                                                            data-confirm-yes="alert('Deleted')"><i
                                                                class="fas fa-trash"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7">No matching records found</td>
                                        </tr>
                                    @endforelse
                                </table>
                            </div>
                        </div>
                        <div class="card-footer text-center">
                            <div class="d-inline-block">
                                {{ $submissions->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
