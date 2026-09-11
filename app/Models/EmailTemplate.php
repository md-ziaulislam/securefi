<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailTemplate extends Model
{
    protected $fillable = ['name', 'subject', 'body', 'type', 'preview_text'];

    public function campaigns(): HasMany
    {
        return $this->hasMany(EmailCampaign::class, 'template_id');
    }

    /**
     * Replace template variables with actual values.
     */
    public function render(array $vars = []): string
    {
        $defaults = [
            'site_name'    => Setting::get('site_name', 'SecuroFi.Tech'),
            'site_url'     => config('app.url'),
            'current_year' => date('Y'),
        ];

        $all  = array_merge($defaults, $vars);
        $body = $this->body;

        foreach ($all as $key => $value) {
            $body = str_replace(['{{' . $key . '}}', '{{ ' . $key . ' }}'], $value, $body);
        }

        return $body;
    }
}
