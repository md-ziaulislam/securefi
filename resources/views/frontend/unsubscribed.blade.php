@extends('layouts.app')

@section('title', 'Unsubscribed Successfully')

@section('content')
<div class="max-w-[1280px] mx-auto px-6 py-20">
    <div class="max-w-lg mx-auto bg-white border border-[#111111]/15 p-8 text-center space-y-5">
        <div class="w-12 h-12 bg-emerald-50 text-emerald-600 border border-emerald-200 rounded-full flex items-center justify-center mx-auto text-xl">
            ✓
        </div>

        <h1 class="text-2xl font-bold text-primary tracking-tight">
            You Have Been Unsubscribed
        </h1>

        <p class="text-sm text-primary/70 leading-relaxed font-sans">
            Your email address has been successfully removed from our newsletter distribution list. You will no longer receive marketing and dispatch emails from us.
        </p>

        <div class="pt-4 border-t border-[#111111]/10">
            <a href="{{ route('home') }}" class="btn-primary text-xs px-6 py-2.5 font-mono inline-block">
                Return to Homepage →
            </a>
        </div>
    </div>
</div>
@endsection
