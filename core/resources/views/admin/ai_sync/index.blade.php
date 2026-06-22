@extends('admin.layouts.app')
@section('panel')
    <!-- Statistics Cards -->
    <div class="row gy-4 mb-30">
        <div class="col-xxl-3 col-sm-6">
            <div class="card bg--primary has-link box--shadow2">
                <a href="{{ route('admin.item.index') }}" class="item-link"></a>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="widget-one__icon">
                            <i class="la la-video text-white"></i>
                        </div>
                        <div class="widget-one__content">
                            <h3 class="text-white">{{ $stats['total_items'] }}</h3>
                            <p class="text-white text--small">@lang('Total Library Items')</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-sm-6">
            <div class="card bg--success has-link box--shadow2">
                <a href="{{ route('admin.item.single') }}" class="item-link"></a>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="widget-one__icon">
                            <i class="la la-film text-white"></i>
                        </div>
                        <div class="widget-one__content">
                            <h3 class="text-white">{{ $stats['total_movies'] }}</h3>
                            <p class="text-white text--small">@lang('Total Movies')</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-sm-6">
            <div class="card bg--info has-link box--shadow2">
                <a href="{{ route('admin.item.episode') }}" class="item-link"></a>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="widget-one__icon">
                            <i class="la la-tv text-white"></i>
                        </div>
                        <div class="widget-one__content">
                            <h3 class="text-white">{{ $stats['total_tv'] }}</h3>
                            <p class="text-white text--small">@lang('TV Shows / Series')</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-sm-6">
            <div class="card bg--danger box--shadow2">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="widget-one__icon">
                            <i class="la la-image text-white"></i>
                        </div>
                        <div class="widget-one__content">
                            <h3 class="text-white">{{ $stats['missing_posters'] }}</h3>
                            <p class="text-white text--small">@lang('Missing Posters / Banners')</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row gy-4">
        <!-- Settings Column -->
        <div class="col-xl-6">
            <div class="card box--shadow2 mb-30">
                <div class="card-header bg--dark">
                    <h5 class="text-white mb-0"><i class="las la-robot"></i> @lang('AI OpenRouter Settings')</h5>
                </div>
                <form action="{{ route('admin.ai.sync.saveSettings') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label>@lang('OpenRouter API Key')</label>
                            <input class="form-control" type="password" name="openrouter_key" placeholder="Enter OpenRouter API Key" value="{{ gs('openrouter_key') }}">
                            <small class="text-muted">@lang('Used to connect to Gemini AI model via OpenRouter for metadata lookup & repair. Will fall back to .env if empty.')</small>
                        </div>

                        <div class="form-group">
                            <label>@lang('AI Model')</label>
                            <select class="form-control select2" name="ai_model" data-minimum-results-for-search="-1">
                                <option value="google/gemini-2.5-flash" @selected(gs('ai_model') == 'google/gemini-2.5-flash')>@lang('Google Gemini 2.5 Flash (Recommended)')</option>
                                <option value="google/gemini-2.5-pro" @selected(gs('ai_model') == 'google/gemini-2.5-pro')>@lang('Google Gemini 2.5 Pro (High Accuracy)')</option>
                                <option value="meta-llama/llama-3-8b-instruct" @selected(gs('ai_model') == 'meta-llama/llama-3-8b-instruct')>@lang('Meta Llama 3 8B Instruct')</option>
                            </select>
                        </div>

                        <ul class="list-group list-group-flush mt-3">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <p class="fw-bold mb-0">@lang('AI Metadata Enrichment')</p>
                                    <small class="text-muted">@lang('Auto-generate missing synopsis, taglines, casts and directors using AI during sync')</small>
                                </div>
                                <div class="form-group mb-0">
                                    <input type="checkbox" data-width="100px" data-size="small" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-height="30" data-on="@lang('Enable')" data-off="@lang('Disable')" name="ai_enrich" value="1" @if (gs('ai_enrich')) checked @endif>
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <p class="fw-bold mb-0">@lang('Auto-Repair Poster Images')</p>
                                    <small class="text-muted">@lang('Enables checking and recovering missing item posters using AI fallback lookup')</small>
                                </div>
                                <div class="form-group mb-0">
                                    <input type="checkbox" data-width="100px" data-size="small" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-height="30" data-on="@lang('Enable')" data-off="@lang('Disable')" name="auto_repair_posters" value="1" @if (gs('auto_repair_posters')) checked @endif>
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <p class="fw-bold mb-0">@lang('Continuous Sync Mode')</p>
                                    <small class="text-muted">@lang('Include IMDb syncing operations in the Laravel Scheduler tasks automatically')</small>
                                </div>
                                <div class="form-group mb-0">
                                    <input type="checkbox" data-width="100px" data-size="small" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-height="30" data-on="@lang('Enable')" data-off="@lang('Disable')" name="auto_sync" value="1" @if (gs('auto_sync')) checked @endif>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45">@lang('Save AI Configuration')</button>
                    </div>
                </form>
            </div>

            <!-- Manual Import Trigger Card -->
            <div class="card box--shadow2 mb-30">
                <div class="card-header bg--dark">
                    <h5 class="text-white mb-0"><i class="las la-sync"></i> @lang('Manual IMDb Bulk Import')</h5>
                </div>
                <form action="{{ route('admin.ai.sync.triggerSync') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <label>@lang('Import Limit')</label>
                                <input class="form-control" type="number" name="limit" min="1" max="100" value="10">
                            </div>
                            <div class="col-sm-6 form-group">
                                <label>@lang('Content Type')</label>
                                <select class="form-control select2" name="type" data-minimum-results-for-search="-1">
                                    <option value="all">@lang('All (Movies & Shows)')</option>
                                    <option value="movie">@lang('Movies Only')</option>
                                    <option value="tv">@lang('TV Shows Only')</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="library" id="libraryMode" value="1">
                                <label class="form-check-label fw-bold" for="libraryMode">@lang('Deep Scan Library (Import popular history list instead of only trending)')</label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn--success w-100 h-45"><i class="las la-cloud-download-alt"></i> @lang('Start IMDb Sync Now')</button>
                    </div>
                </form>
            </div>

            <!-- Repair Banners Card -->
            <div class="card box--shadow2">
                <div class="card-header bg--dark">
                    <h5 class="text-white mb-0"><i class="las la-hammer"></i> @lang('Poster Diagnostics & Repair')</h5>
                </div>
                <form action="{{ route('admin.ai.sync.triggerRepair') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <p class="mb-0 text-muted">@lang('Scans the database items for missing or default landscape and portrait files on the local filesystem. Resolves them using the IMDb search API and AI Fallback lookup.')</p>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn--warning w-100 h-45 text-white"><i class="las la-tools"></i> @lang('Scan & Repair Posters')</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sync Log / Live Tracker Column -->
        <div class="col-xl-6">
            <div class="card box--shadow2 h-100 d-flex flex-column">
                <div class="card-header bg--dark d-flex justify-content-between align-items-center">
                    <h5 class="text-white mb-0"><i class="las la-list-alt"></i> @lang('Sync Logs & Tracker')</h5>
                    <form action="{{ route('admin.ai.sync.clearLogs') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline--light btn--sm"><i class="las la-trash"></i> @lang('Clear Log')</button>
                    </form>
                </div>
                <div class="card-body flex-grow-1 p-0 d-flex flex-column">
                    <!-- Logs Console Area -->
                    <pre class="flex-grow-1 m-0 bg-dark text-success p-3 overflow-auto" style="height: 520px; font-family: monospace; font-size: 12.5px; border-radius: 0 0 4px 4px; line-height: 1.5;">{{ $logs }}</pre>

                    @if(session('sync_output') || session('repair_output'))
                        <div class="p-3 bg-secondary border-top">
                            <h6 class="text-white mb-2">@lang('Last Process CLI Output')</h6>
                            <pre class="bg-dark text-info p-2 m-0 overflow-auto" style="max-height: 200px; font-family: monospace; font-size: 11px;">{{ session('sync_output') ?? session('repair_output') }}</pre>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .widget-one__icon {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            font-size: 24px;
            margin-right: 15px;
        }
        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
@endpush
