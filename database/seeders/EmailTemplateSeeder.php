<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        EmailTemplate::firstOrCreate(
            ['name' => 'Weekly Security Briefing'],
            [
                'subject'      => '🔒 {{site_name}} Weekly: Zero-Day Threats & Fintech Defense Brief',
                'type'         => 'newsletter',
                'preview_text' => 'This week: Ransomware surge in cloud banking, kernel exploits, and hardening checklists.',
                'body'         => <<<'HTML'
<h2>Hello {{name}},</h2>

<p>Welcome to this week's cybersecurity & fintech intelligence briefing from <strong>{{site_name}}</strong>. Our security research team has aggregated the highest-priority vulnerabilities and threat vectors analyzed this week.</p>

<div style="background:#f8f9fa; border-left:4px solid #111111; padding:16px 20px; margin:24px 0;">
    <h3 style="margin-top:0; color:#111111; font-size:16px;">Top Intelligence Highlights</h3>
    <ul style="margin-bottom:0; padding-left:18px; color:#444;">
        <li><strong>CVE-2026-8812:</strong> Remote code execution in multi-tenant API gateways patched. Immediate deployment recommended.</li>
        <li><strong>Supply Chain Audit:</strong> NPM malicious packages impersonating popular Web3 cryptographic libraries.</li>
        <li><strong>Fintech Compliance:</strong> New digital asset custody guidelines published by international regulatory authorities.</li>
    </ul>
</div>

<p>For in-depth analysis and vulnerability mitigations, visit our live research dispatch platform:</p>

<p style="text-align:center; margin:30px 0;">
    <a href="{{site_url}}" style="background:#111111; color:#ffffff; padding:12px 28px; text-decoration:none; font-weight:bold; display:inline-block; font-size:14px;">
        Read Full Security Analysis →
    </a>
</p>

<p>Stay vigilant,<br><strong>The {{site_name}} Editorial & Research Team</strong></p>
HTML,
            ]
        );

        EmailTemplate::firstOrCreate(
            ['name' => 'Critical Security Advisory / Zero-Day Patch Alert'],
            [
                'subject'      => '🚨 URGENT ADVISORY: High Severity Patch Required — {{site_name}}',
                'type'         => 'transactional',
                'preview_text' => 'Immediate action required: High severity flaw disclosed affecting financial microservices.',
                'body'         => <<<'HTML'
<div style="background:#fff3cd; border:1px solid #ffeeba; border-left:4px solid #d9534f; padding:16px 20px; margin-bottom:24px;">
    <strong style="color:#d9534f; font-size:16px;">CRITICAL SECURITY ALERT</strong>
    <p style="margin:8px 0 0 0; color:#856404; font-size:14px;">A severe vulnerability requires immediate administrative review.</p>
</div>

<h2>Attention {{name}},</h2>

<p>An urgent zero-day vulnerability has been disclosed affecting enterprise gateway implementations. Threat actors are actively scanning for unpatched endpoints.</p>

<table style="width:100%; border-collapse:collapse; margin:20px 0;">
    <tr>
        <td style="padding:10px; border:1px solid #e5e5e5; background:#f8f8f6; font-weight:bold; width:30%;">Severity</td>
        <td style="padding:10px; border:1px solid #e5e5e5; color:#d9534f; font-weight:bold;">CRITICAL (CVSS 9.8)</td>
    </tr>
    <tr>
        <td style="padding:10px; border:1px solid #e5e5e5; background:#f8f8f6; font-weight:bold;">Impact Vector</td>
        <td style="padding:10px; border:1px solid #e5e5e5;">Unauthenticated Remote Code Execution</td>
    </tr>
    <tr>
        <td style="padding:10px; border:1px solid #e5e5e5; background:#f8f8f6; font-weight:bold;">Action Required</td>
        <td style="padding:10px; border:1px solid #e5e5e5;">Deploy vendor patch v2.4.1 immediately or disable legacy endpoints</td>
    </tr>
</table>

<p>Please review our official response playbook:</p>

<p style="margin:25px 0;">
    <a href="{{site_url}}" style="background:#d9534f; color:#ffffff; padding:12px 24px; text-decoration:none; font-weight:bold; display:inline-block; font-size:14px;">
        Access Mitigation Playbook →
    </a>
</p>

<p>Security Operations Center,<br>{{site_name}}</p>
HTML,
            ]
        );

        EmailTemplate::firstOrCreate(
            ['name' => 'Fintech Innovation & Industry Updates'],
            [
                'subject'      => '⚡ Next-Gen Fintech & Crypto Architecture Digest',
                'type'         => 'custom',
                'preview_text' => 'Exploring quantum-resistant cryptography, AI-powered fraud detection, and open banking protocols.',
                'body'         => <<<'HTML'
<h2>Hi {{name}},</h2>

<p>As the intersection of distributed finance and digital security evolves at breakneck speed, staying ahead of cryptographic developments is essential.</p>

<p>Here is what is driving discussions in the fintech engineering community this month:</p>

<ol style="line-height:1.8; color:#333;">
    <li><strong>Post-Quantum Encryption in Modern Payment Rails:</strong> How global financial networks are transitioning to lattice-based cryptographic algorithms.</li>
    <li><strong>Zero-Knowledge Fraud Proofs:</strong> Verifying transaction authenticity without revealing sensitive user credentials or balances.</li>
    <li><strong>AI Agent Security in Automated Trading:</strong> Mitigating adversarial attacks against algorithmic execution systems.</li>
</ol>

<p>Explore all technical teardowns and interactive benchmarks on {{site_name}}.</p>

<p style="text-align:center; margin:30px 0;">
    <a href="{{site_url}}" style="background:#111111; color:#ffffff; padding:12px 28px; text-decoration:none; font-weight:bold; display:inline-block; font-size:14px;">
        Explore Articles & Benchmarks →
    </a>
</p>

<p>Best regards,<br>{{site_name}} Insights</p>
HTML,
            ]
        );
    }
}
