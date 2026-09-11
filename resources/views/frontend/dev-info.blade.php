@extends('layouts.app')

@section('content')
<div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">

    <div class="max-w-4xl mx-auto space-y-8 sm:space-y-10">
        {{-- Header --}}
        <header class="border-b border-neutral/20 pb-6 sm:pb-8">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-3 h-3 bg-surface"></span>
                <span class="text-xs font-mono uppercase tracking-widest text-neutral">System & Lead Architect</span>
            </div>
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold tracking-tight text-primary">
                Developer Information
            </h1>
            <p class="text-xs sm:text-sm text-neutral mt-2 font-mono">
                Technical profile, engineering background, and developer contact endpoints.
            </p>
        </header>

        {{-- Architect Profile Card --}}
        <div class="p-5 sm:p-8 border border-neutral/20 bg-secondary grid grid-cols-1 md:grid-cols-12 gap-6 sm:gap-8 items-start">
            {{-- Portrait & Primary Identifiers (4 cols) --}}
            <div class="md:col-span-4 flex flex-col items-center text-center border-b md:border-b-0 md:border-r border-neutral/15 pb-8 md:pb-0 md:pr-8">
                <div class="w-32 h-32 bg-primary text-secondary flex items-center justify-center font-bold font-mono text-3xl mb-4 border border-neutral/30 overflow-hidden shadow-sm">
                    @php
                        $devPhoto = \App\Models\Setting::get('dev_photo');
                        $devName = \App\Models\Setting::get('dev_name', 'Ziaul Islam');
                    @endphp
                    @if ($devPhoto)
                        <img src="{{ $devPhoto }}" alt="{{ $devName }}" class="w-full h-full object-cover">
                    @else
                        <span>{{ strtoupper(substr($devName, 0, 2)) }}</span>
                    @endif
                </div>
                <h2 class="text-xl font-bold text-primary tracking-tight">{{ $devName }}</h2>
                <span class="text-xs font-mono text-neutral mt-1">
                    {{ \App\Models\Setting::get('dev_title', 'Lead Full-Stack Architect') }}
                </span>

                {{-- Direct Contact Endpoints --}}
                <div class="mt-6 flex flex-wrap justify-center gap-2 text-xs font-mono w-full">
                    @if ($email = \App\Models\Setting::get('dev_email'))
                        <a href="mailto:{{ $email }}" class="px-3 py-1.5 bg-tertiary border border-neutral/20 hover:border-primary text-primary transition-colors flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>Email</span>
                        </a>
                    @endif

                    @if ($github = \App\Models\Setting::get('dev_github'))
                        <a href="{{ $github }}" target="_blank" class="px-3 py-1.5 bg-tertiary border border-neutral/20 hover:border-primary text-primary transition-colors flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
                            <span>GitHub</span>
                        </a>
                    @endif

                    @if ($linkedin = \App\Models\Setting::get('dev_linkedin'))
                        <a href="{{ $linkedin }}" target="_blank" class="px-3 py-1.5 bg-tertiary border border-neutral/20 hover:border-primary text-primary transition-colors flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.46 8.76a1.64 1.64 0 1 0 0-3.28 1.64 1.64 0 0 0 0 3.28m1.39 9.74v-8.37H5.07v8.37z"/></svg>
                            <span>LinkedIn</span>
                        </a>
                    @endif

                    @if ($twitter = \App\Models\Setting::get('dev_twitter'))
                        <a href="{{ $twitter }}" target="_blank" class="px-3 py-1.5 bg-tertiary border border-neutral/20 hover:border-primary text-primary transition-colors flex items-center gap-1.5">
                            <span class="font-bold">X</span>
                            <span>Twitter</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Biography & Specializations (8 cols) --}}
            <div class="md:col-span-8 space-y-6">
                <div>
                    <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-neutral mb-2">
                        Professional Statement & Architecture Philosophy
                    </h3>
                    <p class="text-sm font-mono text-primary leading-relaxed">
                        {{ \App\Models\Setting::get('dev_bio', 'Full-stack software architect specializing in high-performance Laravel architectures, robust APIs, scalable databases, and security-first web applications.') }}
                    </p>
                </div>

                {{-- Core Competencies & Skills --}}
                <div class="border-t border-neutral/15 pt-6 space-y-3">
                    <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-neutral">
                        Core Competencies & Technology Stack
                    </h3>
                    @php
                        $skillsStr = \App\Models\Setting::get('dev_skills', 'Laravel, PHP, MySQL, Tailwind CSS, Web Security, RESTful APIs, System Architecture, Performance Tuning');
                        $skills = array_filter(array_map('trim', explode(',', $skillsStr)));
                    @endphp
                    <div class="flex flex-wrap gap-2">
                        @foreach($skills as $skill)
                            <span class="px-2.5 py-1 text-xs font-mono bg-tertiary border border-neutral/25 text-primary">
                                {{ $skill }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <div class="border-t border-neutral/15 pt-4 text-xs font-mono text-neutral">
                    <span>Engineering Focus: High Concurrency • Strict Typing • Zero-Bloat Performance</span>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
