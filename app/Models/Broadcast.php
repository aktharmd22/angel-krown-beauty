<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Broadcast extends Model
{
    protected $fillable = [
        'name', 'whatsapp_template_id', 'audience_type', 'contact_group_id',
        'status', 'scheduled_at', 'total', 'sent_count', 'failed_count', 'finished_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function template()
    {
        return $this->belongsTo(WhatsappTemplate::class, 'whatsapp_template_id');
    }

    public function group()
    {
        return $this->belongsTo(ContactGroup::class, 'contact_group_id');
    }

    public function recipients()
    {
        return $this->hasMany(BroadcastRecipient::class);
    }

    /** Resolve the audience to a collection of ['name','phone']. */
    public function audience(): Collection
    {
        if ($this->audience_type === 'all_customers') {
            return Customer::query()->whereNotNull('phone')->get(['name', 'phone'])
                ->map(fn ($c) => ['name' => $c->name, 'phone' => $c->phone]);
        }

        if ($this->audience_type === 'group') {
            if (! $this->group) {
                return collect();
            }

            return $this->group->contacts()->where('subscribed', true)->get(['contacts.name', 'contacts.phone'])
                ->map(fn ($c) => ['name' => $c->name, 'phone' => $c->phone]);
        }

        return Contact::where('subscribed', true)->get(['name', 'phone'])
            ->map(fn ($c) => ['name' => $c->name, 'phone' => $c->phone]);
    }

    /** Build recipient rows from the audience and mark the broadcast queued. */
    public function queueForSending(): int
    {
        $list = $this->audience()
            ->filter(fn ($r) => filled($r['phone']))
            ->unique('phone')
            ->values();

        $this->recipients()->delete();

        $now = now();
        foreach ($list->chunk(500) as $chunk) {
            BroadcastRecipient::insert($chunk->map(fn ($r) => [
                'broadcast_id' => $this->id,
                'name' => $r['name'],
                'phone' => $r['phone'],
                'status' => 'pending',
                'created_at' => $now,
                'updated_at' => $now,
            ])->all());
        }

        $this->update([
            'status' => 'queued',
            'total' => $list->count(),
            'sent_count' => 0,
            'failed_count' => 0,
            'finished_at' => null,
        ]);

        return $list->count();
    }
}
