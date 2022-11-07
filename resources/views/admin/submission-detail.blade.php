@extends('partials.main')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Submission Detail</h1>
        </div>
        <div class="section-body">
            <h2 class="section-title">Submission Detail</h2>
            <p class="section-lead">Detail submission user yang masuk pada sistem Metembang Bali.</p>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ $submission->title }}</h4>
                        </div>
                        <div class="card-body">
                            @if (session('success') || session('error'))
                                <div class="row mb-3">
                                    <div class="col">
                                        @include('partials.alert')
                                    </div>
                                </div>
                            @endif
                            <div class="row">
                                <div class="col">
                                    <img src="{{ get_cover_url($submission) }}" class="rounded float-start" alt="cover"
                                        height="200">
                                </div>
                            </div>
                            <dl class="row mt-4">
                                <!-- Author -->
                                <dt class="col-sm-3">Penulis</dt>
                                <dd class="col-sm-9">{{ $submission->user->name }}</dd>
                                <!-- Title -->
                                <dt class="col-sm-3">Judul Tembang</dt>
                                <dd class="col-sm-9">{{ $submission->title }}</dd>
                                <!-- Category -->
                                <dt class="col-sm-3">Kategori Tembang</dt>
                                <dd class="col-sm-9">{{ format_category($submission->category) }}</dd>
                                <!-- Sub-Category -->
                                <dt class="col-sm-3">Sub Kategori Tembang</dt>
                                <dd class="col-sm-9">
                                    {{ $submission->sub_category ? format_category($submission->sub_category) : '-' }}</dd>
                                <!-- Meaning -->
                                <dt class="col-sm-3">Makna Tembang</dt>
                                <dd class="col-sm-9">{{ $submission->meaning ?? '-' }}</dd>
                                <!-- Mood -->
                                <dt class="col-sm-3">Suasana Tembang</dt>
                                <dd class="col-sm-9">{{ $submission->mood ? ucfirst($submission->mood) : '-' }}</dd>
                                <!-- Lyrics (Balinese) -->
                                <dt class="col-sm-3">Lirik Tembang</dt>
                                <dd class="col-sm-9">{!! format_lyrics_html($submission->lyrics) !!}</dd>
                                <!-- Lyrics (Indonesian) -->
                                <dt class="col-sm-3">Lirik Tembang (Indonesia)</dt>
                                <dd class="col-sm-9">{!! $submission->lyricsIDN ? format_lyrics_html($submission->lyricsIDN) : '-' !!}</dd>
                                <!-- Audio -->
                                <dt class="col-sm-3">Audio Tembang</dt>
                                <dd class="col-sm-9">
                                    @if ($submission->audio_path)
                                        <audio controls>
                                            <source src="{{ get_audio_url($submission) }}" type="audio/mpeg">
                                            Your browser does not support the audio element.
                                        </audio>
                                    @else
                                        -
                                    @endif
                                </dd>
                                <!-- Cover Source -->
                                <dt class="col-sm-3">Sumber Cover</dt>
                                <dd class="col-sm-9">{{ $submission->cover_source ?? '-' }}</dd>
                                <!-- Rule -->
                                <dt class="col-sm-3">Aturan/Uger-uger Tembang</dt>
                                <dd class="col-sm-9">
                                    @if ($submission->rule)
                                        <strong>{{ format_individual_name($submission->rule->name) }}</strong>
                                        <dl class="row mb-0">
                                            <!-- Guru Dingdong -->
                                            <dd class="col-sm-8 mb-0">
                                                <div class="row">
                                                    <div class="col col-sm-4 col-lg-3">Guru Dingdong</div>
                                                    <div class="col">{{ $submission->rule->guru_dingdong }}</div>
                                                </div>
                                            </dd>
                                            <!-- Guru Wilang -->
                                            <dd class="col-sm-8 mb-0">
                                                <div class="row">
                                                    <div class="col col-sm-4 col-lg-3">Guru Wilang</div>
                                                    <div class="col">{{ $submission->rule->guru_wilang }}</div>
                                                </div>
                                            </dd>
                                            <!-- Guru Gatra -->
                                            <dd class="col-sm-8 mb-0">
                                                <div class="row">
                                                    <div class="col col-sm-4 col-lg-3">Guru Gatra</div>
                                                    <div class="col">{{ $submission->rule->guru_gatra }}</div>
                                                </div>
                                            </dd>

                                        </dl>
                                    @else
                                        -
                                    @endif
                                    <!-- Usages -->
                                <dt class="col-sm-3">Kegunaan</dt>
                                <dd class="col-sm-9">{!! format_usages_html($submission->usages) !!}</dd>
                                <!-- Created At -->
                                <dt class="col-sm-3">Tanggal Dibuat</dt>
                                <dd class="col-sm-9">{{ $submission->created_at->format('d F Y') }}</dd>
                                <!-- Updated At -->
                                <dt class="col-sm-3">Tanggal Diubah</dt>
                                <dd class="col-sm-9">{{ $submission->updated_at->format('d F Y') }}</dd>
                                <!-- Status -->
                                <dt class="col-sm-3">Status</dt>
                                <dd class="col-sm-9">
                                    <div class="badge {{ submission_status_color($submission->status) }}">
                                        {{ ucfirst($submission->status) }}</div>
                                </dd>
                            </dl>
                        </div>
                        <div class="card-footer text-center">
                            <div class="d-flex justify-content-center">
                                @if ($submission->status == 'pending')
                                    <form action="{{ route('admin.submission.reject', $submission->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-lg btn-danger btn-icon icon-left mx-2">
                                            <i class="fas fa-trash mr-2"></i>Reject</button>
                                    </form>
                                    <form action="{{ route('admin.submission.accept', $submission->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-lg btn-success btn-icon icon-left mx-2">
                                            <i class="fa fa-check mr-2"></i>Accept</button>
                                    </form>
                                @endif
                                <form action="{{ route('admin.submission.destroy', $submission->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-lg btn-outline-danger btn-icon icon-left mx-2">
                                        <i class="fas fa-trash mr-2"></i>Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
