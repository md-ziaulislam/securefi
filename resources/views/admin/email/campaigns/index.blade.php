@extends('layouts.admin')
@section('title', 'Email Campaigns')
@section('header_title', 'Marketing Campaigns & Broadcasts')

@section('content')
<div class="space-y-6">

    {{-- Top Action Bar --}}
    <div class="bg-white border border-[#111111]/15 p-4 flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-[#111111]">Broadcast Campaigns</h3>
            <p class="text-xs font-mono text-[#808080]">Send email newsletters and promotions to your subscriber base via SMTP.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.email.campaigns.create') }}" class="btn-primary text-xs px-4 py-2 font-mono flex items-center gap-1.5">
                <span>+ Create Campaign</span>
            </a>
        </div>
    </div>

    {{-- Campaigns Table --}}
    <div class="bg-white border border-[#111111]/15">
        @if($campaigns->isEmpty())
            <div class="p-12 text-center space-y-3">
                <div class="text-[#808080] font-mono text-sm">No campaigns launched yet.</div>
                <a href="{{ route('admin.email.campaigns.create') }}" class="btn-primary text-xs px-4 py-2 font-mono inline-block">
                    Create Your First Campaign
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs font-mono">
                    <thead class="border-b border-[#111111]/10 bg-[#F8F8F6]">
                        <tr class="text-[10px] uppercase tracking-wider text-[#808080]">
                            <th class="py-3 px-5 text-left">Campaign Name & Subject</th>
                            <th class="py-3 px-5 text-left">Template</th>
                            <th class="py-3 px-5 text-left">Audience</th>
                            <th class="py-3 px-5 text-left">Status</th>
                            <th class="py-3 px-5 text-left">Delivery Stats</th>
                            <th class="py-3 px-5 text-left">Date</th>
                            <th class="py-3 px-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#111111]/10">
                        @foreach($campaigns as $campaign)
                            <tr class="hover:bg-[#F8F8F6] transition-colors">
                                <td class="py-3.5 px-5">
                                    <div class="font-bold text-[#111111] text-xs font-sans">{{ $campaign->name }}</div>
                                    <div class="text-[11px] text-[#666666] font-sans truncate max-w-sm">{{ $campaign->subject }}</div>
                                </td>
                                <td class="py-3.5 px-5 text-[#808080]">
                                    {{ $campaign->template ? $campaign->template->name : 'Custom HTML' }}
                                </td>
                                <td class="py-3.5 px-5">
                                    <span class="inline-block px-2 py-0.5 text-[10px] uppercase font-mono bg-[#F5F1E8] border border-[#111111]/10">
                                        {{ $campaign->audience }} subscribers
                                    </span>
                                </td>
                                <td class="py-3.5 px-5">
                                    @if($campaign->status === 'sent')
                                        <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            ✓ Sent
                                        </span>
                                    @elseif($campaign->status === 'sending')
                                        <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 animate-pulse">
                                            Sending...
                                        </span>
                                    @elseif($campaign->status === 'failed')
                                        <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            Failed
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold bg-gray-100 text-gray-700 border border-gray-200">
                                            Draft
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-5">
                                    @if($campaign->status === 'sent' || $campaign->sent_count > 0)
                                        <div class="text-[11px] text-emerald-700 font-bold">{{ $campaign->sent_count }} delivered</div>
                                        @if($campaign->failed_count > 0)
                                            <div class="text-[10px] text-rose-600">{{ $campaign->failed_count }} failed</div>
                                        @endif
                                    @else
                                        <span class="text-[#808080]">—</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-5 text-[#808080]">
                                    @if($campaign->sent_at)
                                        Sent: {{ $campaign->sent_at->format('M d, Y H:i') }}
                                    @else
                                        Created: {{ $campaign->created_at->format('M d, Y') }}
                                    @endif
                                </td>
                                <td class="py-3.5 px-5 text-right space-x-1.5 whitespace-nowrap">
                                    <button type="button" onclick="promptCampaignTest('{{ route('admin.email.campaigns.test', $campaign) }}')"
                                        class="px-2 py-1 border border-[#111111]/20 hover:bg-[#F5F1E8] text-[11px] font-mono transition-colors" title="Send test copy to your inbox">
                                        Test Preview
                                    </button>

                                    @if($campaign->status !== 'sent')
                                        <form action="{{ route('admin.email.campaigns.send', $campaign) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Launch this campaign and send emails now via SMTP?')">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 bg-emerald-600 text-white hover:bg-emerald-700 transition-colors text-[11px] font-bold">
                                                Send Now →
                                            </button>
                                        </form>
                                    @endif

                                    <form action="{{ route('admin.email.campaigns.destroy', $campaign) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Are you sure you want to delete campaign &quot;{{ $campaign->name }}&quot;?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-1 text-rose-600 hover:text-rose-800 hover:bg-rose-50 transition-colors text-[11px]">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<form id="campaign-test-form" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="test_email" id="campaign-test-email-input">
</form>

<script>
function promptCampaignTest(url) {
    const defaultEmail = "{{ auth()->user()->email ?? '' }}";
    const email = prompt("Enter recipient email address for campaign preview test:", defaultEmail);
    if (!email || !email.includes('@')) return;

    const form = document.getElementById('campaign-test-form');
    form.action = url;
    document.getElementById('campaign-test-email-input').value = email.trim();
    form.submit();
}
</script>
@endsection
