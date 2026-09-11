@extends('layouts.app')

@section('content')
<div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">

    <div class="max-w-2xl mx-auto">
        <header class="border-b border-neutral/20 pb-6 sm:pb-8 mb-8 sm:mb-10">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-3 h-3 bg-primary"></span>
                <span class="text-xs font-mono uppercase tracking-widest text-neutral">Direct Communication</span>
            </div>
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold tracking-tight text-primary">
                {{ $page->title }}
            </h1>
            <div class="mt-3 text-sm text-neutral leading-relaxed">
                {!! $page->content !!}
            </div>
        </header>

        @if (session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-300 text-emerald-900 text-xs font-mono mb-8">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-300 text-rose-900 text-xs font-mono mb-8">
                <ul class="list-disc pl-4 space-y-1">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('contact.submit') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Honeypot field --}}
            <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off">

            <div>
                <label for="name" class="block text-xs font-mono uppercase tracking-wider text-primary mb-2 font-medium">Your Full Name *</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                    class="w-full px-4 py-2.5 text-sm font-mono bg-white border border-neutral/30 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                    placeholder="e.g. Alex Mercer">
            </div>

            <div>
                <label for="email" class="block text-xs font-mono uppercase tracking-wider text-primary mb-2 font-medium">Work / Contact Email *</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                    class="w-full px-4 py-2.5 text-sm font-mono bg-white border border-neutral/30 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                    placeholder="alex@company.com">
            </div>

            <div>
                <label for="subject" class="block text-xs font-mono uppercase tracking-wider text-primary mb-2 font-medium">Subject / Inquiry Type</label>
                <input type="text" id="subject" name="subject" value="{{ old('subject') }}"
                    class="w-full px-4 py-2.5 text-sm font-mono bg-white border border-neutral/30 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                    placeholder="Security audit inquiry / Partnership">
            </div>

            <div>
                <label for="message" class="block text-xs font-mono uppercase tracking-wider text-primary mb-2 font-medium">Message Body *</label>
                <textarea id="message" name="message" rows="6" required
                    class="w-full px-4 py-2.5 text-sm font-mono bg-white border border-neutral/30 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                    placeholder="Provide details about your query...">{{ old('message') }}</textarea>
            </div>

            <div class="pt-2">
                @php
                    $captchaService = app(\App\Services\CaptchaService::class);
                @endphp
                {{-- CAPTCHA Widget (shown only when enabled for contact form) --}}
                @if ($captchaService->isEnabledForForm('contact'))
                    <div class="mb-4">
                        {!! $captchaService->widgetHtml('contact', 'contact') !!}
                        @error('captcha')
                            <p class="text-xs font-mono text-rose-700 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
                <button type="submit" class="btn-primary w-full text-xs font-mono uppercase tracking-wider py-3.5">
                    Submit Message Directly →
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
