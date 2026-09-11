# Product Requirements Document (PRD)

## প্রজেক্টের নাম: SecuroFi.Tech

**Developer:** Ziaul Islam
**ডকুমেন্ট ভার্সন:** 1.2 (Updated — Real-Time Visitor Tracking, GeoIP/Device Analytics, Advanced Features যুক্ত)
**তারিখ:** September 2026

---

## ১. প্রজেক্ট ওভারভিউ

SecuroFi.Tech একটি **ডাইনামিক ব্লগ/আর্টিকেল ওয়েবসাইট**, যা তৈরি হবে **Laravel + MySQL** স্ট্যাকে। ওয়েবসাইটটি গ্লোবাল (সকল দেশের) অডিয়েন্সের জন্য তৈরি হবে এবং নিচের বিষয়গুলোর উপর কন্টেন্ট পাবলিশ করবে:

- AI Tools & SaaS
- Web Hosting / Domains / Website Tools
- Cybersecurity & Privacy
- Personal Finance

ওয়েবসাইটের মূল লক্ষ্য হলো — মানসম্মত আর্টিকেল/ব্লগ পাবলিশ করে অর্গানিক ট্রাফিক আনা এবং সেই ট্রাফিক থেকে **এডস (Ads)** ও **এফিলিয়েট মার্কেটিং** এর মাধ্যমে ইনকাম জেনারেট করা — যেখানে সম্পূর্ণ কন্ট্রোল থাকবে একটি **সিকিউর, ফুল-ফাংশনাল অ্যাডমিন প্যানেল** থেকে, কোনো কোড টাচ করা ছাড়াই।

---

## ২. প্রজেক্ট গোল / অবজেক্টিভ

1. একটি ফাস্ট, SEO-friendly, মোবাইল-রেসপনসিভ ব্লগ ওয়েবসাইট তৈরি করা।
2. অ্যাডমিন প্যানেল থেকে কোনো কোড ছাড়াই সম্পূর্ণ কন্টেন্ট ম্যানেজমেন্ট সম্ভব করা।
3. অ্যাডমিন প্যানেল থেকেই যেকোনো ওয়েবসাইটের (নিজের বা কাস্টম থার্ড-পার্টি) Ads / Affiliate লিংক ম্যানেজ করা।
4. On-page এবং Full-website SEO সম্পূর্ণভাবে অ্যাডমিন প্যানেল থেকে নিয়ন্ত্রণযোগ্য করা — কোনো কোড এডিট ছাড়াই।
5. একটি dedicated **dev-info** পেজ রাখা, যা ডেভেলপার তথ্য দেখাবে এবং অ্যাডমিন প্যানেলের সব সেকশনে দ্রুত অ্যাক্সেসের জন্য লিংক দেবে (শুধুমাত্র অথোরাইজড ইউজারদের জন্য)।
6. স্কেলেবল আর্কিটেকচার — ভবিষ্যতে নতুন ক্যাটাগরি, নতুন ফিচার, মাল্টি-অ্যাডমিন যোগ করা সহজ হবে এমনভাবে বানানো।

---

## ৩. টার্গেট অডিয়েন্স

- গ্লোবাল রিডার/ভিজিটর যারা AI টুলস, SaaS প্রোডাক্ট, ওয়েব হোস্টিং/ডোমেইন, সাইবার সিকিউরিটি এবং পার্সোনাল ফাইন্যান্স নিয়ে তথ্য খুঁজছেন।
- সার্চ ইঞ্জিন (Google) ও AI/LLM (ChatGPT, Gemini, Claude ইত্যাদি) — যারা কন্টেন্ট ক্রল/রেফারেন্স করবে, তাই কন্টেন্ট SEO ও AEO (Answer Engine Optimization) ফ্রেন্ডলি হতে হবে।
- Admin/Owner — যিনি কন্টেন্ট, Ads, Affiliate লিংক এবং SEO ম্যানেজ করবেন।

---

## ৪. টেকনোলজি স্ট্যাক

| Layer | Technology |
|---|---|
| Backend Framework | Laravel (Latest LTS) |
| Database | MySQL |
| Frontend (Public Site) | Blade + Tailwind CSS (Alpine.js হালকা ইন্টারঅ্যাকশনের জন্য) — ডিজাইন সিস্টেম: "Minimalism & Swiss Style" (design.md রেফারেন্স, দেখুন সেকশন ৪.১) |
| Admin Panel | Laravel Blade Custom Panel অথবা Filament PHP (দ্রুত, সিকিউর, প্রোডাকশন-রেডি অ্যাডমিন প্যানেলের জন্য সুপারিশকৃত) |
| Editor | TinyMCE / CKEditor 5 (রিচ টেক্সট আর্টিকেল এডিটর, ইমেজ আপলোডসহ) |
| Authentication | Laravel Breeze/Fortify + 2FA (Google Authenticator) |
| Roles & Permissions | spatie/laravel-permission |
| Media Storage | Local storage (public disk) — future-ready for S3/Cloud |
| Caching | Laravel Cache (file/redis) + route/config caching |
| Queue | Laravel Queue (database driver, পরে redis) |
| Image Optimization | intervention/image |
| SEO Package | spatie/laravel-sitemap + কাস্টম SEO মডিউল |
| Real-Time Layer | Laravel Reverb / Soketi (Self-hosted WebSocket) অথবা Pusher — Live Visitor Tracking-এর জন্য |
| GeoIP / Device Detection | stevebauman/location (Country/City লুকআপ) + jenssegers/agent (Device/OS/Browser ডিটেকশন) |
| Server | Nginx/Apache + PHP-FPM (Hostinger/VPS compatible) |

---

## ৪.১ ডিজাইন সিস্টেম (UI/UX Design Reference)

ওয়েবসাইটের ভিজ্যুয়াল ডিজাইন **"Minimalism & Swiss Style"** কনসেপ্টের উপর ভিত্তি করে তৈরি হবে (b2b SaaS / Enterprise / Professional Tools-এর জন্য উপযুক্ত একটি ক্লিন, গ্রিড-বেসড, হাই-কন্ট্রাস্ট ডিজাইন সিস্টেম)। এই ডিজাইন সিস্টেমটি Tailwind Config + CSS Variables আকারে ইমপ্লিমেন্ট হবে, যাতে অ্যাডমিন প্যানেল থেকে থিম কালার/টাইপোগ্রাফি টোকেন পরিবর্তন করলে পুরো সাইটে সেটা রিফ্লেক্ট হয়।

**কালার প্যালেট (Design Tokens):**

| Token | Color | ব্যবহার |
|---|---|---|
| Primary | #000000 (Off-black/Charcoal ব্যবহার হবে, pure black না) | Text, Primary Button, Accent |
| Secondary | #FFFFFF | Background, Card Surface |
| Tertiary | #F5F1E8 (Beige) | Decorative/Section BG |
| Neutral | #808080 (Grey) | Secondary Text, Border |
| Surface | #B38B6D (Taupe) | Decorative Accent |

**টাইপোগ্রাফি:**
- Sans-serif ফন্ট ফ্যামিলি (Display/Hero: Bold 700, Body: Regular 400, Label/Caption: Medium 500)।
- Hero: `clamp(2.5rem, 5vw, 4rem)`, H1: 2.25rem, H2: 1.5rem, Body: 1rem/1.6 line-height।
- Monospace (JetBrains Mono) — কোড/মেটাডেটার জন্য।

**লেআউট নীতিমালা:**
- CSS Grid ভিত্তিক, ম্যাক্স-উইথ 1280px, সেন্টারড, 1.5rem সাইড প্যাডিং।
- Hero: Split-screen (Text বামে, Visual ডানে)।
- Feature সেকশন: Zig-zag alternating layout — সমান ৩-কলাম লেআউট এড়িয়ে চলা হবে।
- 768px-এর নিচে সব মাল্টি-কলাম লেআউট সিঙ্গেল কলামে কোলাপস করবে (No horizontal overflow)।
- Base Corner Radius: 0px (Sharp/Geometric shapes), Rounded Scale: sm 2px / md 4px / lg 8px (টোকেন হিসেবে সংরক্ষিত, ভবিষ্যতে থিম পরিবর্তনে ব্যবহারযোগ্য)।

**মোশন/অ্যানিমেশন:**
- Ease-out কার্ভ, 200-300ms ডিউরেশন।
- Entry: Fade + translate-Y (16px → 0), 420ms, লিস্ট আইটেমের জন্য 80ms স্ট্যাগার।
- শুধুমাত্র `transform` ও `opacity` অ্যানিমেট হবে (পারফরম্যান্সের জন্য, layout-triggering প্রপার্টি এড়িয়ে চলা হবে)।

**কম্পোনেন্ট গাইডলাইন:**
- Primary Button: Sharp edges, Accent fill, Hover-এ 8% darken + shadow lift।
- Card: Sharp corners, হালকা shadow (`0 2px 12px rgba(0,0,0,0.06)`), 1px border।
- Input: Label উপরে, Focus ring 2px accent (floating label ব্যবহার হবে না)।
- Skeleton Loader ব্যবহার হবে (circular spinner না)।
- Empty State: Icon + টেক্সট + Action বাটন।

**Do's:** Grid-based 12–16 কলাম লেআউট, স্পষ্ট টাইপোগ্রাফি হায়ারার্কি, WCAG AAA কন্ট্রাস্ট, মোবাইল-রেসপনসিভ গ্রিড।

**Don'ts:** UI-তে ইমোজি ব্যবহার না করা (Lucide/Heroicons আইকন সিস্টেম ব্যবহার হবে), Pure black না ব্যবহার করা, ওভার-স্যাচুরেটেড কালার এড়ানো, সমান ৩-কলাম ফিচার লেআউট না করা, `h-screen`-এর পরিবর্তে `min-h-[100dvh]` ব্যবহার, "Elevate/Seamless/Unleash/Next-Gen" জাতীয় AI-ক্লিশে কপিরাইটিং এড়ানো, ব্রোকেন ইমেজ লিংক না রাখা।

> **নোট:** এই ডিজাইন টোকেনগুলো (কালার, টাইপোগ্রাফি, স্পেসিং, রাউন্ডিং) অ্যাডমিন প্যানেলের **Branding & Design Settings** মডিউল থেকে পরিবর্তনযোগ্য হবে (নিচে ৫.২.৯-এ বিস্তারিত), যাতে ভবিষ্যতে ডিজাইন থিম আপডেট করতে কোড টাচ করতে না হয়।

---

## ৫. মূল ফিচার লিস্ট (Feature Breakdown)

### ৫.১ পাবলিক ওয়েবসাইট (Frontend)

- **হোমপেজ:** ফিচারড/লেটেস্ট আর্টিকেল, ক্যাটাগরি-ওয়াইজ সেকশন, ট্রেন্ডিং পোস্ট।
- **ক্যাটাগরি পেজ:** AI Tools & SaaS / Web Hosting-Domains-Website Tools / Cybersecurity & Privacy / Personal Finance — প্রতিটির আলাদা লিস্টিং পেজ, প্যাজিনেশনসহ।
- **সিঙ্গেল আর্টিকেল পেজ:**
  - টাইটেল, ফিচারড ইমেজ, অথর, পাবলিশ ডেট, রিডিং টাইম।
  - রিচ কন্টেন্ট (ইমেজ, টেবিল, কোড ব্লক, এমবেড সাপোর্ট)।
  - ইনলাইন Ads স্লট (আর্টিকেলের মধ্যে/উপরে/নিচে বসানোর অপশন)।
  - ইনলাইন Affiliate প্রোডাক্ট বক্স/কম্পারিজন টেবিল (কোনো কোড ছাড়া অ্যাডমিন থেকে ইনসার্ট করা যাবে)।
  - রিলেটেড আর্টিকেল সেকশন।
  - সোশ্যাল শেয়ার বাটন।
  - Table of Contents (অটো-জেনারেটেড, দীর্ঘ আর্টিকেলের জন্য)।
  - Schema Markup (Article/FAQ/Product — SEO মডিউল থেকে নিয়ন্ত্রিত)।
- **সার্চ ফাংশনালিটি:** সাইট-ওয়াইড আর্টিকেল সার্চ (কীওয়ার্ড/ট্যাগ ভিত্তিক)।
- **ট্যাগ পেজ:** ট্যাগ অনুযায়ী আর্টিকেল ফিল্টার।
- **ডাইনামিক পেজসমূহ (অ্যাডমিন থেকে ম্যানেজড):** About, Contact, Privacy Policy, Terms & Conditions, Affiliate Disclosure, Advertise With Us — এই পেজগুলো হার্ডকোডেড নয়, বরং অ্যাডমিন প্যানেলের **Pages মডিউল** থেকে সম্পূর্ণ কন্টেন্ট, SEO মেটা এবং URL/Slug এডিট করা যাবে (বিস্তারিত ৫.২.১০-এ)। Contact পেজে ফর্ম সাবমিশন হ্যান্ডলিং থাকবে, বাকিগুলো রিচ-টেক্সট কন্টেন্ট পেজ হিসেবে কাজ করবে।
- **dev-info পেজ:** ডেভেলপার (Ziaul Islam) সম্পর্কে বিস্তারিত তথ্য + অ্যাডমিন প্যানেলের বিভিন্ন সেকশনের কুইক-লিংক (এই পেজের কন্টেন্ট অ্যাডমিন থেকে এডিটেবল, এবং লিংকগুলোও অ্যাডমিন থেকে অ্যাড/রিমুভ করা যাবে)।
- **নিউজলেটার সাবস্ক্রিপশন (Optional):** ইমেইল ক্যাপচার ফর্ম।
- **কমেন্ট সিস্টেম (Optional):** বিল্ট-ইন অথবা থার্ড-পার্টি (Disqus) — অ্যাডমিন থেকে টগল করা যাবে।
- **404 / Custom Error Pages।**
- **Dark/Light Mode (Optional)।**

### ৫.২ অ্যাডমিন প্যানেল

#### ৫.২.১ Authentication & Access Control
- সিকিউর লগইন (Rate-limited, Brute-force protection)।
- 2FA (Google Authenticator) — সাপোর্টেড।
- Role-based access (Super Admin, Admin, Editor, Author) — spatie/laravel-permission দিয়ে।
- Activity Log (কে কখন কী পরিবর্তন করেছে তার লগ)।

#### ৫.২.২ Dashboard
- মোট আর্টিকেল, ক্যাটাগরি, ভিউ কাউন্ট, Ads পারফরম্যান্স ওভারভিউ।
- সাম্প্রতিক অ্যাক্টিভিটি ফিড।
- Google Analytics/Search Console ইন্টিগ্রেশন সামারি (Optional widget)।
- **Live Visitors Widget:** এই মুহূর্তে কতজন সাইটে আছেন তার কুইক কাউন্ট (বিস্তারিত ৫.২.১১-এ)।

#### ৫.২.৩ Content Management (Articles/Blog)
- আর্টিকেল Create/Edit/Delete/Draft/Schedule/Publish।
- রিচ টেক্সট এডিটর (ইমেজ, টেবিল, ভিডিও এমবেড)।
- ক্যাটাগরি ও ট্যাগ ম্যানেজমেন্ট (Add/Edit/Delete)।
- ফিচারড ইমেজ আপলোড + অটো-কম্প্রেশন/রিসাইজ।
- SEO ফিল্ডস প্রতিটি আর্টিকেলের জন্য (নিচে SEO সেকশনে বিস্তারিত)।
- Media Library (সব আপলোড করা ইমেজ এক জায়গায়, রি-ইউজ করার জন্য)।
- Bulk Actions (Delete/Publish/Unpublish একাধিক আর্টিকেল একসাথে)।

#### ৫.২.৪ Ads Management Module
- Ad Slot তৈরি করা যাবে (যেমন: Header, Sidebar, In-Article-Top, In-Article-Middle, In-Article-Bottom, Footer)।
- প্রতিটি স্লটে HTML/Script কোড (Google AdSense, অন্য কোনো Ad Network) বসানো যাবে অ্যাডমিন থেকে — ফ্রন্টএন্ড কোড টাচ করা ছাড়াই।
- Ad Slot অন/অফ টগল।
- নির্দিষ্ট ক্যাটাগরি বা আর্টিকেলে নির্দিষ্ট Ad দেখানো/লুকানো (targeting)।
- Custom Banner Ads (ইমেজ + লিংক আপলোড করে নিজস্ব ব্যানার অ্যাড ম্যানেজ করা)।

#### ৫.২.৫ Affiliate Marketing Module
- যেকোনো ওয়েবসাইটের (Amazon, অথবা যেকোনো কাস্টম শপ/সার্ভিস) Affiliate প্রোডাক্ট/লিংক অ্যাড করা যাবে।
- প্রোডাক্ট ফিল্ডস: নাম, ইমেজ, প্রাইস (Optional), শর্ট ডেসক্রিপশন, Affiliate লিংক, Rel attribute (sponsored/nofollow অটো-অ্যাড)।
- তিন ধরনের ডিসপ্লে ফরম্যাট: Single Product Box, Product List, Comparison Table।
- Shortcode/Block সিস্টেম — অ্যাডমিন প্যানেল থেকে জেনারেট করা শর্টকোড আর্টিকেল এডিটরে বসালেই প্রোডাক্ট রেন্ডার হবে।
- Affiliate Disclosure অটো-ইনজেক্ট অপশন (আর্টিকেলের উপরে/নিচে)।
- Click Tracking (কোন লিংকে কতবার ক্লিক হয়েছে তার বেসিক অ্যানালিটিক্স)।

#### ৫.২.৬ SEO Management Module (কোড ছাড়া, ফুল কন্ট্রোল অ্যাডমিন থেকে)

**On-Page SEO (প্রতিটি আর্টিকেল/পেজের জন্য):**
- Meta Title, Meta Description।
- Focus Keyword + কীওয়ার্ড ডেনসিটি/SEO স্কোর ইন্ডিকেটর (Optional, যেমন Yoast-এর মতো)।
- Canonical URL।
- Open Graph (OG) ট্যাগস (Title, Description, Image) — Facebook/LinkedIn শেয়ারের জন্য।
- Twitter Card ট্যাগস।
- Schema/Structured Data (Article, FAQ, Product, Breadcrumb) — অ্যাডমিন থেকে টগল/এডিট করা যাবে।
- Image Alt Text ম্যানেজমেন্ট (Media Library থেকেই)।
- Slug/URL কাস্টমাইজেশন + রিডাইরেক্ট চেইন থেকে বাঁচার জন্য ওয়ার্নিং।

**Full-Website SEO:**
- Global Site Title, Meta Description, Default OG Image।
- অটো-জেনারেটেড XML Sitemap (sitemap.xml) — অ্যাডমিন থেকে অন/অফ ও রিজেনারেট করা যাবে।
- robots.txt এডিটর (অ্যাডমিন প্যানেল থেকেই কন্টেন্ট এডিট করা যাবে, ফাইল সিস্টেম টাচ করা লাগবে না)।
- 301/302 Redirect Manager (পুরনো URL → নতুন URL ম্যাপিং, কোড ছাড়া)।
- 404 Log Monitor (কোন URL-এ ভিজিটর 404 পাচ্ছে তার লিস্ট, যাতে রিডাইরেক্ট সেট করা যায়)।
- Broken Link Checker (Optional, শিডিউলড স্ক্যান)।
- Google Analytics / Google Search Console / Bing Webmaster ভেরিফিকেশন কোড ইনপুট ফিল্ড (অ্যাডমিন থেকে বসানো যাবে, হেডে অটো-ইনজেক্ট হবে)।
- Custom Header/Footer Script Injection Field (Ads/Analytics/Chat widget স্ক্রিপ্টের জন্য, কোনো Blade ফাইল এডিট ছাড়া)।
- Sitewide Schema (Organization/Website Schema) সেটিংস।

#### ৫.২.৭ dev-info পেজ ম্যানেজমেন্ট
- dev-info পেজের কন্টেন্ট (ডেভেলপার বায়ো, কন্ট্যাক্ট, স্কিলস ইত্যাদি) অ্যাডমিন থেকে এডিট করা যাবে।
- এই পেজ থেকে অ্যাডমিন প্যানেলের বিভিন্ন সেকশনের (Articles, Ads, Affiliate, SEO, Users, Settings) কুইক-লিংক ম্যানেজ করা যাবে — লিংকের লেবেল/URL/অর্ডার অ্যাডমিন থেকে কনফিগারেবল।

#### ৫.২.৮ User & Role Management
- Admin/Editor/Author ইউজার Add/Edit/Delete।
- প্রতিটি রোলের জন্য আলাদা পারমিশন (কে কী করতে পারবে)।

#### ৫.২.৯ General Settings

**Branding & Identity:**
- সাইটের নাম, ট্যাগলাইন পরিবর্তন।
- Logo আপলোড (Light mode + Dark mode ভ্যারিয়েন্ট আলাদা আপলোড করা যাবে)।
- Favicon আপলোড (মাল্টি-সাইজ, অটো-জেনারেটেড)।
- Social Share ডিফল্ট ইমেজ (OG Image) আপলোড।

**Design/Theme Customizer (design.md টোকেন-ভিত্তিক):**
- Primary/Secondary/Tertiary/Neutral/Surface — ৫টি কালার টোকেন কালার-পিকার দিয়ে পরিবর্তন করা যাবে (লাইভ প্রিভিউসহ)।
- Typography সেটিংস: ফন্ট ফ্যামিলি সিলেক্ট (প্রি-সেট Google Fonts লিস্ট থেকে), হেডিং/বডি ফন্ট সাইজ স্কেল।
- Corner Radius স্কেল (sm/md/lg) — Sharp (0px) থেকে Rounded পর্যন্ত অ্যাডজাস্ট করা যাবে।
- Spacing/Density প্রিসেট (Compact / Balanced / Airy)।
- Dark Mode / Light Mode টগল (সাইট-ওয়াইড ডিফল্ট মোড সেট করা)।
- Button স্টাইল প্রিসেট (Sharp/Rounded, Solid/Outline)।
- Reset to Default অপশন (মূল design.md টেমপ্লেটে ফিরে যাওয়ার জন্য)।

**অন্যান্য:**
- Social Media লিংক ম্যানেজমেন্ট (Footer/Header এ দেখানোর জন্য)।
- Contact Form সাবমিশন লিস্ট (Contact পেজ থেকে আসা মেসেজ দেখা/রিপ্লাই/ডিলিট)।
- Backup ম্যানেজমেন্ট (ডেটাবেজ ব্যাকআপ ডাউনলোড/শিডিউল)।
- Email/SMTP সেটিংস (নোটিফিকেশন ও নিউজলেটারের জন্য)।

#### ৫.২.১০ Dynamic Pages Management Module

- **Page Builder (CMS-Style):** Privacy Policy, Terms & Conditions, Contact, About, Affiliate Disclosure, Advertise With Us — সহ যেকোনো কাস্টম পেজ অ্যাডমিন প্যানেল থেকে Create/Edit/Delete করা যাবে। কোনো পেজ Blade ফাইলে হার্ডকোড থাকবে না।
- প্রতিটি পেজের জন্য: Title, Slug/URL (কাস্টমাইজযোগ্য), Rich Text Content (Editor দিয়ে), Status (Draft/Published), Show in Footer/Header Menu (টগল)।
- প্রতিটি পেজের নিজস্ব SEO ফিল্ডস (Meta Title, Description, OG Image, Canonical, Schema) — SEO মডিউলের সাথে ইন্টিগ্রেটেড।
- **Contact পেজ বিশেষভাবে:** ফর্ম বিল্ডার (নাম, ইমেইল, মেসেজ ফিল্ড ডিফল্ট, নতুন ফিল্ড অ্যাড করার অপশন), সাবমিশন অ্যাডমিন প্যানেলে সংরক্ষিত হবে এবং ইমেইল নোটিফিকেশন যাবে।
- Page Reordering (মেনুতে কোন পেজ কোন অর্ডারে দেখাবে তা Drag & Drop দিয়ে সাজানো)।
- Legal পেজগুলোর (Privacy Policy/Terms) জন্য "Last Updated" ডেট অটো-ট্র্যাক হবে।
#### ৫.২.১১ Real-Time Visitor Tracking / Live Analytics Dashboard

অ্যাডমিন প্যানেলে একটি **Live Traffic Monitor** থাকবে, যেখানে রিয়েল-টাইমে দেখা যাবে সাইটে এই মুহূর্তে কারা আছেন এবং তারা কোথা থেকে/কীভাবে এসেছেন।

- **Live Visitor Counter:** এই মুহূর্তে সাইটে সক্রিয় (online) ভিজিটরের সংখ্যা রিয়েল-টাইম আপডেট হবে (WebSocket/Pusher/Soketi অথবা Laravel Echo + polling দিয়ে ইমপ্লিমেন্ট)।
- **Visitor Detail Table (Live Feed):** প্রতিটি সক্রিয় ভিজিটরের জন্য —
  - **Platform/Device:** Desktop / Mobile / Tablet।
  - **Operating System:** Windows, macOS, Android, iOS, Linux ইত্যাদি।
  - **Browser:** Chrome, Firefox, Safari, Edge ইত্যাদি (Version সহ)।
  - **Country/City:** IP-ভিত্তিক জিও-লোকেশন (GeoIP লুকআপ)।
  - **Current Page:** এই মুহূর্তে কোন পেজ/আর্টিকেল দেখছেন।
  - **Referrer Source:** Direct / Google / Facebook / ChatGPT-Perplexity(AI Referral) / অন্য কোনো সাইট।
  - **Session Duration:** কতক্ষণ ধরে সাইটে আছেন।
  - **New vs Returning Visitor** ব্যাজ।
- **Live World Map:** ভিজিটরদের লোকেশন একটি ইন্টারঅ্যাকটিভ ওয়ার্ল্ড ম্যাপে ডট/হিটম্যাপ আকারে দেখানো হবে।
- **Historical Analytics Dashboard:**
  - Daily/Weekly/Monthly ট্রাফিক গ্রাফ (Line/Bar chart)।
  - টপ কান্ট্রি, টপ ব্রাউজার, টপ ডিভাইস, টপ রেফারার — পাই চার্ট/লিস্ট আকারে।
  - টপ পারফর্মিং আর্টিকেল/পেজ (Most Viewed) — টাইম-রেঞ্জ ফিল্টার সহ।
  - Bounce Rate ও Average Session Duration এস্টিমেট।
- **Filter & Export:** ডেট রেঞ্জ, কান্ট্রি, ডিভাইস অনুযায়ী ফিল্টার করা যাবে এবং CSV/Excel এক্সপোর্ট করা যাবে।
- **Bot/Crawler Detection:** সার্চ ইঞ্জিন বট (Googlebot, Bingbot ইত্যাদি) এবং AI ক্রলার (GPTBot, ClaudeBot, PerplexityBot) আলাদাভাবে ট্যাগ করে দেখানো হবে, যাতে রিয়েল হিউম্যান ট্রাফিক ও বট ট্রাফিক আলাদা বোঝা যায়।
- **Alert/Notification (Optional):** হঠাৎ ট্রাফিক স্পাইক বা সন্দেহজনক অ্যাক্টিভিটি (একই IP থেকে অতিরিক্ত রিকোয়েস্ট) হলে অ্যাডমিনকে নোটিফিকেশন।
- **Privacy Compliance:** IP অ্যানোনিমাইজেশন অপশন এবং GDPR/Cookie Consent ব্যানার (নিচে দেখুন) — যাতে ট্র্যাকিং প্রাইভেসি রেগুলেশন মেনে চলে।

#### ৫.২.১২ আরও কিছু অ্যাডভান্সড ফিচার (Advanced/Additional Features)

- **Cookie Consent (GDPR/CCPA Compliant):** অ্যাডমিন থেকে কাস্টমাইজযোগ্য কুকি কনসেন্ট ব্যানার, টেক্সট/বাটন কালার/পজিশন কনফিগারযোগ্য।
- **Multi-Currency/Region Aware Affiliate Links (Optional):** ভিজিটরের দেশ অনুযায়ী Amazon-এর মতো রিজিওন-স্পেসিফিক Affiliate লিংক (যেমন amazon.com vs amazon.co.uk) অটো-সুইচ করার লজিক।
- **Content Performance Score:** প্রতিটি আর্টিকেলের জন্য একটি ওভারঅল স্কোর (Views + Engagement + Affiliate Click + SEO health একত্রে) — অ্যাডমিন ড্যাশবোর্ডে র‍্যাংকড লিস্ট আকারে।
- **API Access Layer (Optional):** সাইটের আর্টিকেল/ক্যাটাগরি ডেটা এক্সপোজ করার জন্য একটি সিকিউর REST API (Sanctum টোকেন-বেসড), ভবিষ্যতে মোবাইল অ্যাপ/হেডলেস ফ্রন্টএন্ডের জন্য।
- **Webhook Integration:** নতুন আর্টিকেল পাবলিশ হলে Slack/Discord/Zapier-এ অটো-নোটিফিকেশন পাঠানোর অপশন।
- **Scheduled/Auto-Publish Content Calendar:** অ্যাডমিন প্যানেলে একটি ক্যালেন্ডার ভিউ যেখানে ভবিষ্যতের শিডিউলড আর্টিকেল দেখা যাবে।
- **Two-Factor + Login History:** প্রতিটি অ্যাডমিন লগইনের IP/Device/Location হিস্ট্রি সংরক্ষণ (Suspicious login flag সহ)।
- **Malware/Security Scanner (Optional):** পিরিয়ডিক ফাইল ইন্টিগ্রিটি চেক, যাতে হ্যাকড/ইনজেক্টেড কোড ডিটেক্ট করা যায়।
- **Progressive Web App (PWA) Support (Optional):** সাইট মোবাইলে অ্যাপের মতো ইনস্টল করা যাবে, অফলাইন ক্যাশিং সহ।
- **A/B Testing for Ads/CTA (Optional):** একই স্লটে দুটি ভ্যারিয়েন্ট চালিয়ে কোনটার পারফরম্যান্স ভালো তা তুলনা।
- **Export/Import Content:** JSON/CSV আকারে আর্টিকেল/পেজ এক্সপোর্ট-ইমপোর্ট (মাইগ্রেশন/ব্যাকআপ সহজ করার জন্য)।
- **Multi-Site Ready Architecture (Future Scope):** ভবিষ্যতে একই কোডবেজে একাধিক ব্র্যান্ড/ডোমেইন চালানোর সম্ভাবনা মাথায় রেখে ডেটাবেজ ডিজাইন করা।

---

## ৬. নিরাপত্তা (Security) রিকোয়ারমেন্টস

- CSRF Protection (Laravel বিল্ট-ইন)।
- XSS Protection (ইনপুট স্যানিটাইজেশন, বিশেষ করে রিচ টেক্সট কন্টেন্টে)।
- SQL Injection Protection (Eloquent ORM ব্যবহার, raw query এড়িয়ে চলা)।
- Login Rate Limiting / Brute-force Protection।
- 2FA (Admin লগইনের জন্য বাধ্যতামূলক করার অপশন)।
- Role-based Access Control (RBAC)।
- Activity Logging (Audit Trail)।
- HTTPS Enforced (SSL)।
- Secure File Upload Validation (শুধু নির্দিষ্ট ফাইল টাইপ/সাইজ এলাউ করা)।
- Regular Automated Backup।
- Directory Traversal / Brute-force IP Blocking (Optional Middleware)।

---

## ৭. নন-ফাংশনাল রিকোয়ারমেন্টস

- **পারফরম্যান্স:** পেজ লোড টাইম < 2 সেকেন্ড, ইমেজ লেজি-লোডিং, ক্যাশিং।
- **SEO Friendly:** ক্লিন URL স্ট্রাকচার, ফাস্ট লোডিং, মোবাইল-ফার্স্ট।
- **স্কেলেবিলিটি:** নতুন ক্যাটাগরি/মডিউল সহজে যোগ করার মতো আর্কিটেকচার।
- **রেসপনসিভনেস:** মোবাইল, ট্যাবলেট, ডেস্কটপ সব ডিভাইসে সঠিকভাবে কাজ করবে।
- **অ্যাক্সেসিবিলিটি:** বেসিক Accessibility স্ট্যান্ডার্ড মেনে চলা।
- **মাল্টি-ল্যাঙ্গুয়েজ রেডি (Optional Future Scope):** ভবিষ্যতে একাধিক ভাষা সাপোর্টের জন্য স্ট্রাকচার তৈরি রাখা।

---

## ৮. ডেটাবেজ স্ট্রাকচার (হাই-লেভেল ওভারভিউ)

| Table | মূল কলাম |
|---|---|
| users | id, name, email, password, role_id, 2fa_secret |
| roles / permissions | spatie/laravel-permission ডিফল্ট টেবিল |
| categories | id, name, slug, description, meta_title, meta_description |
| tags | id, name, slug |
| articles | id, title, slug, content, featured_image, category_id, author_id, status, published_at |
| article_tag (pivot) | article_id, tag_id |
| seo_meta | id, seoable_id, seoable_type (polymorphic), meta_title, meta_description, og_image, canonical_url, schema_json |
| ad_slots | id, name, position, code, status |
| affiliate_products | id, name, image, price, description, affiliate_url, source_site |
| affiliate_shortcodes | id, type (single/list/comparison), product_ids_json, shortcode |
| redirects | id, from_url, to_url, type (301/302) |
| pages | id, title, slug, content, template, status, show_in_menu, menu_order, meta_title, meta_description, og_image, updated_at (Last Updated) |
| settings | id, key, value (Global site settings key-value store — site name, logo, favicon, SMTP ইত্যাদি) |
| theme_settings | id, key, value (Design token key-value store — colors, fonts, radius, spacing, dark/light default) |
| visitor_logs | id, ip_address (anonymizable), session_id, country, city, device_type, os, browser, referrer_source, current_page, is_bot, is_returning, created_at, last_seen_at |
| login_history | id, user_id, ip_address, device, location, status (success/failed), created_at |
| activity_logs | id, user_id, action, description, created_at |
| media | id, file_path, alt_text, uploaded_by |
| newsletter_subscribers | id, email, subscribed_at |
| contact_submissions | id, name, email, message, submitted_at |

---

## ৯. রোলস অ্যান্ড পারমিশনস (উদাহরণ)

| Role | Permission |
|---|---|
| Super Admin | সব কিছু (Users, Settings, SEO, Ads, Affiliate, Articles) |
| Admin | Articles, Ads, Affiliate, SEO — ম্যানেজ করতে পারবে, User ম্যানেজমেন্ট পারবে না |
| Editor | Article Create/Edit/Publish, কিন্তু Ads/Affiliate/SEO Global সেটিংসে অ্যাক্সেস নাই |
| Author | নিজের আর্টিকেল Create/Edit (Draft পর্যন্ত), Publish পারমিশন Admin অ্যাপ্রুভাল সাপেক্ষে |

---

## ১০. ডেভেলপমেন্ট ফেজ (Milestones)

| Phase | কাজ |
|---|---|
| Phase 1 | Laravel প্রজেক্ট সেটআপ, ডেটাবেজ ডিজাইন, Authentication + 2FA |
| Phase 2 | Article/Category/Tag CRUD (Admin) + পাবলিক ব্লগ ফ্রন্টএন্ড (design.md ডিজাইন সিস্টেম অনুযায়ী UI ইমপ্লিমেন্টেশন) |
| Phase 3 | SEO Module (On-page + Full-site) |
| Phase 4 | Ads Management Module |
| Phase 5 | Affiliate Marketing Module (Shortcode সিস্টেমসহ) |
| Phase 6 | Dynamic Pages Module (Privacy Policy, Terms, Contact, About ইত্যাদি) + dev-info পেজ + Roles/Permissions |
| Phase 7 | Branding & Design Theme Customizer (Logo, Favicon, কালার/টাইপোগ্রাফি টোকেন সেটিংস) |
| Phase 8 | Security Hardening (Rate limiting, Activity Log, Backup) |
| Phase 9 | Real-Time Visitor Tracking Module (Live Traffic Monitor, GeoIP, Device Detection) + Advanced Features (Cookie Consent, Content Performance Score, Webhooks, API Layer) |
| Phase 10 | Testing, Performance Optimization, Deployment |

---

## ১১. ভবিষ্যৎ স্কোপ (Out of Scope for v1, কিন্তু বিবেচনাযোগ্য)

- মাল্টি-ল্যাঙ্গুয়েজ কন্টেন্ট।
- AI-powered কন্টেন্ট সাজেশন/অটো মেটা-জেনারেশন (Admin panel থেকে টগল করা যাবে এমন অপশনাল ফিচার)।
- Push Notification।
- Native Mobile App / PWA।
- Advanced A/B Testing for Ads।

---

*এই PRD ডকুমেন্টটি ডেভেলপমেন্ট শুরু করার আগে একটি রেফারেন্স গাইডলাইন হিসেবে কাজ করবে। প্রয়োজন অনুযায়ী এটি আপডেট করা যেতে পারে।*
