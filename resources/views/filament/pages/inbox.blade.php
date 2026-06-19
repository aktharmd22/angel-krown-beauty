<x-filament-panels::page>
    <style>
        .ak-inbox{--wine:#8B1A4F;--wine2:#6E1340;--gold:#C9A24B;--gold2:#E7CE8E}
        .ak-wrap{display:grid;grid-template-columns:330px 1fr;height:76vh;border:1px solid #e8e1d8;border-radius:18px;overflow:hidden;background:#fff;box-shadow:0 24px 50px -28px rgba(94,15,54,.35)}

        /* ── list ── */
        .ak-list{display:flex;flex-direction:column;min-height:0;border-right:1px solid #eee5da;background:#fff}
        .ak-list-head{padding:14px 16px;background:linear-gradient(120deg,var(--wine),var(--wine2));color:#fff}
        .ak-list-title{display:flex;align-items:center;justify-content:space-between}
        .ak-list-title b{display:flex;align-items:center;gap:8px;font-weight:600;letter-spacing:-.01em}
        .ak-count{font-size:11px;font-weight:600;background:rgba(255,255,255,.18);padding:2px 9px;border-radius:999px}
        .ak-search{position:relative;margin-top:12px}
        .ak-search svg{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.65)}
        .ak-search input{width:100%;border:0;border-radius:999px;background:rgba(255,255,255,.16);color:#fff;font-size:13px;padding:9px 14px 9px 36px;outline:none}
        .ak-search input::placeholder{color:rgba(255,255,255,.65)}
        .ak-search input:focus{box-shadow:0 0 0 2px rgba(255,255,255,.4)}
        .ak-items{flex:1;overflow-y:auto}
        .ak-item{width:100%;text-align:left;display:flex;align-items:center;gap:12px;padding:12px;border:0;border-bottom:1px solid #f4efe9;background:transparent;cursor:pointer;position:relative;transition:background .15s}
        .ak-item:hover{background:#faf6f1}
        .ak-item.active{background:rgba(139,26,79,.06)}
        .ak-item.active::before{content:"";position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--wine);border-radius:0 3px 3px 0}
        .ak-av{flex:none;width:44px;height:44px;border-radius:50%;display:grid;place-items:center;color:#fff;font-weight:600;background:linear-gradient(145deg,var(--gold2),var(--gold));box-shadow:0 1px 3px rgba(0,0,0,.12)}
        .ak-av.sm{width:40px;height:40px}
        .ak-item-body{flex:1;min-width:0}
        .ak-row{display:flex;align-items:center;justify-content:space-between;gap:8px}
        .ak-name{font-weight:600;font-size:13px;color:#2b2b2b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .ak-time{font-size:10px;color:#9b9b9b;flex:none}
        .ak-preview{font-size:12px;color:#7b7b7b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:2px}
        .ak-unread{flex:none;min-width:20px;height:20px;padding:0 6px;border-radius:999px;background:var(--wine);color:#fff;font-size:11px;font-weight:600;display:grid;place-items:center}

        /* ── thread ── */
        .ak-thread{display:flex;flex-direction:column;min-height:0}
        .ak-thread-head{display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid #ece5dc;background:#fff}
        .ak-thread-head .nm{font-weight:600;color:#2b2b2b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .ak-thread-head .ph{font-size:12px;color:#9b9b9b;display:flex;align-items:center;gap:5px}
        .ak-back{display:none;background:0;border:0;color:#666;cursor:pointer;margin-left:-4px}
        .ak-msgs{flex:1;overflow-y:auto;padding:18px 26px;background-color:#F5EDE4;background-image:radial-gradient(rgba(139,26,79,.05) 1px,transparent 1px);background-size:22px 22px}
        .ak-date{display:flex;justify-content:center;padding:8px 0}
        .ak-date span{font-size:11px;font-weight:500;color:#6b7280;background:rgba(255,255,255,.85);padding:3px 12px;border-radius:999px;box-shadow:0 1px 2px rgba(0,0,0,.06)}
        .ak-mrow{display:flex;margin-bottom:3px}
        .ak-mrow.out{justify-content:flex-end}
        .ak-bubble{max-width:74%;padding:7px 11px 5px;font-size:13.5px;line-height:1.45;border-radius:14px;box-shadow:0 1px 1px rgba(0,0,0,.07);position:relative}
        .ak-bubble.in{background:#fff;color:#1f2937;border-bottom-left-radius:4px}
        .ak-bubble.out{background:var(--wine);color:#fff;border-bottom-right-radius:4px}
        .ak-text{white-space:pre-wrap;word-break:break-word}
        .ak-meta{display:flex;align-items:center;justify-content:flex-end;gap:4px;font-size:10px;margin-top:2px}
        .ak-bubble.in .ak-meta{color:#9ca3af}
        .ak-bubble.out .ak-meta{color:rgba(255,255,255,.72)}
        .ak-tick{width:15px;height:11px}

        /* ── composer ── */
        .ak-composer{padding:12px;border-top:1px solid #ece5dc;background:#fff}
        .ak-inputbar{display:flex;align-items:flex-end;gap:8px;border:1px solid #e7e0d7;background:#f8f5f1;border-radius:24px;padding:5px 5px 5px 16px;transition:border-color .15s,box-shadow .15s}
        .ak-inputbar:focus-within{border-color:var(--wine);box-shadow:0 0 0 3px rgba(139,26,79,.1)}
        .ak-input{flex:1;border:0;background:transparent;resize:none;font-size:13.5px;line-height:1.4;padding:7px 0;max-height:120px;outline:none;color:#222}
        .ak-send{flex:none;width:40px;height:40px;border-radius:50%;background:var(--wine);color:#fff;display:grid;place-items:center;border:0;cursor:pointer;box-shadow:0 4px 10px -3px rgba(139,26,79,.6);transition:background .15s}
        .ak-send:hover{background:#7a1746}
        .ak-hint{font-size:11px;color:#a3a3a3;text-align:center;margin-top:8px}
        .ak-hint kbd{background:#f1ece6;border-radius:4px;padding:0 5px;font-size:10px}
        .spin{animation:akspin 1s linear infinite}@keyframes akspin{to{transform:rotate(360deg)}}

        /* ── empty states ── */
        .ak-empty{height:100%;display:grid;place-items:center;text-align:center;padding:32px}
        .ak-empty-thread{flex:1;display:grid;place-items:center;text-align:center;padding:32px;background-color:#F5EDE4;background-image:radial-gradient(rgba(139,26,79,.05) 1px,transparent 1px);background-size:22px 22px}
        .ak-empty-ico{margin:0 auto 14px;width:78px;height:78px;border-radius:50%;display:grid;place-items:center;color:#fff;background:linear-gradient(145deg,var(--wine),var(--wine2));box-shadow:0 14px 30px -12px rgba(94,15,54,.6)}
        .ak-empty-ico.soft{width:56px;height:56px;margin-bottom:12px;background:rgba(139,26,79,.08);color:var(--wine);box-shadow:none}
        .ak-empty h3{font-size:17px;font-weight:600;color:#444}
        .ak-empty p{font-size:13px;color:#777;margin-top:6px;max-width:18rem}
        .ak-empty .sub{font-size:11px;color:#9b9b9b;margin-top:12px}

        /* ── dark mode ── */
        .dark .ak-wrap{background:#1f2027;border-color:rgba(255,255,255,.08)}
        .dark .ak-list,.dark .ak-thread-head,.dark .ak-composer{background:#1f2027;border-color:rgba(255,255,255,.08)}
        .dark .ak-item{border-color:rgba(255,255,255,.05)}
        .dark .ak-item:hover{background:rgba(255,255,255,.04)}
        .dark .ak-name,.dark .ak-thread-head .nm{color:#e8e8e8}
        .dark .ak-bubble.in{background:#2c2d36;color:#e5e7eb}
        .dark .ak-inputbar{background:rgba(255,255,255,.05);border-color:rgba(255,255,255,.1)}
        .dark .ak-input{color:#eee}

        /* ── responsive: one pane at a time ── */
        @media(max-width:768px){
            .ak-wrap{grid-template-columns:1fr;height:80vh}
            .ak-wrap[data-open="1"] .ak-list{display:none}
            .ak-wrap[data-open="0"] .ak-thread{display:none}
            .ak-back{display:block}
            .ak-msgs{padding:16px}
        }
    </style>

    <div class="ak-inbox" wire:poll.6s>
        <div class="ak-wrap" data-open="{{ $selectedId ? '1' : '0' }}">

            {{-- ───────── conversation list ───────── --}}
            <aside class="ak-list">
                <div class="ak-list-head">
                    <div class="ak-list-title">
                        <b>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                            Messages
                        </b>
                        <span class="ak-count">{{ $this->conversations->count() }}</span>
                    </div>
                    <div class="ak-search">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3" stroke-linecap="round"/></svg>
                        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search name or number…">
                    </div>
                </div>

                <div class="ak-items">
                    @forelse ($this->conversations as $c)
                        <button type="button" wire:click="selectConversation({{ $c->id }})" wire:key="conv-{{ $c->id }}"
                            class="ak-item {{ $selectedId === $c->id ? 'active' : '' }}">
                            <span class="ak-av">{{ strtoupper(\Illuminate\Support\Str::substr($c->display(), 0, 1)) }}</span>
                            <span class="ak-item-body">
                                <span class="ak-row">
                                    <span class="ak-name">{{ $c->display() }}</span>
                                    <span class="ak-time">{{ optional($c->last_message_at)->diffForHumans(short: true) }}</span>
                                </span>
                                <span class="ak-row">
                                    <span class="ak-preview">{{ $c->last_message ?: 'Tap to open chat' }}</span>
                                    @if ($c->unread_count)
                                        <span class="ak-unread">{{ $c->unread_count }}</span>
                                    @endif
                                </span>
                            </span>
                        </button>
                    @empty
                        <div class="ak-empty">
                            <div>
                                <div class="ak-empty-ico soft">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                                </div>
                                <h3 style="font-size:14px">No conversations yet</h3>
                                <p style="font-size:12px">Customer messages will appear here.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </aside>

            {{-- ───────── thread ───────── --}}
            <section class="ak-thread">
                @if ($this->conversation)
                    <div class="ak-thread-head">
                        <button type="button" class="ak-back" wire:click="$set('selectedId', null)">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <span class="ak-av sm">{{ strtoupper(\Illuminate\Support\Str::substr($this->conversation->display(), 0, 1)) }}</span>
                        <div style="min-width:0">
                            <div class="nm">{{ $this->conversation->display() }}</div>
                            <div class="ph">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M17.6 6.3a8 8 0 0 0-12.4 9.7L4 20l4.1-1.1A8 8 0 1 0 17.6 6.3zm-5.6 12a6.6 6.6 0 0 1-3.4-.9l-.2-.1-2.4.6.6-2.3-.2-.3a6.6 6.6 0 1 1 5.6 3z"/></svg>
                                {{ $this->conversation->wa_phone }}
                            </div>
                        </div>
                    </div>

                    <div x-data="{ toBottom(){ $nextTick(() => { if ($refs.msgs) $refs.msgs.scrollTop = $refs.msgs.scrollHeight }) } }"
                        x-init="toBottom()" @scroll-bottom.window="toBottom()" style="flex:1;min-height:0;display:flex">
                        <div x-ref="msgs" class="ak-msgs" style="width:100%">
                            @php $lastDate = null; @endphp
                            @foreach ($this->messages as $m)
                                @php $d = $m->created_at->format('Y-m-d'); @endphp
                                @if ($d !== $lastDate)
                                    @php $lastDate = $d; @endphp
                                    <div class="ak-date"><span>{{ $m->created_at->isToday() ? 'Today' : ($m->created_at->isYesterday() ? 'Yesterday' : $m->created_at->format('d M Y')) }}</span></div>
                                @endif
                                <div class="ak-mrow {{ $m->direction === 'outbound' ? 'out' : 'in' }}" wire:key="msg-{{ $m->id }}">
                                    <div class="ak-bubble {{ $m->direction === 'outbound' ? 'out' : 'in' }}">
                                        <div class="ak-text">{{ $m->body }}</div>
                                        <div class="ak-meta">
                                            <span>{{ $m->created_at->format('H:i') }}</span>
                                            @if ($m->direction === 'outbound')
                                                @if ($m->status === 'failed')
                                                    <svg class="ak-tick" viewBox="0 0 24 24" fill="currentColor" style="color:#fca5a5;width:14px;height:14px"><path d="M12 2 1 21h22L12 2zm1 14h-2v2h2v-2zm0-6h-2v4h2v-4z"/></svg>
                                                @elseif ($m->status === 'read')
                                                    <svg class="ak-tick" viewBox="0 0 20 11" fill="none" style="color:#F2D58A;width:17px"><path d="M1 5.8 4.3 9 9.6 2M7 8.2 8.6 9.8 14.8 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                @elseif ($m->status === 'delivered')
                                                    <svg class="ak-tick" viewBox="0 0 20 11" fill="none" style="width:17px"><path d="M1 5.8 4.3 9 9.6 2M7 8.2 8.6 9.8 14.8 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                @else
                                                    <svg class="ak-tick" viewBox="0 0 14 11" fill="none"><path d="M1 5.8 4.3 9 13 1.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <form wire:submit="sendReply" class="ak-composer">
                        <div class="ak-inputbar">
                            <textarea class="ak-input" wire:model="reply" rows="1" placeholder="Type a message…"
                                x-data x-on:keydown.enter="if (!$event.shiftKey){ $event.preventDefault(); $wire.sendReply() }"
                                x-on:input="$el.style.height='auto';$el.style.height=Math.min($el.scrollHeight,120)+'px'"></textarea>
                            <button type="submit" class="ak-send" wire:loading.attr="disabled" wire:target="sendReply">
                                <svg wire:loading.remove wire:target="sendReply" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="margin-left:-2px"><path d="M3.4 20.4 21.6 12 3.4 3.6 3.5 10l11 2-11 2z"/></svg>
                                <svg wire:loading wire:target="sendReply" class="spin" width="20" height="20" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2.5" stroke-opacity=".3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                            </button>
                        </div>
                        <p class="ak-hint"><kbd>Enter</kbd> to send · replies deliver within Meta’s 24-hour window</p>
                    </form>
                @else
                    <div class="ak-empty-thread">
                        <div>
                            <div class="ak-empty-ico">
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                            </div>
                            <h3>Your conversations</h3>
                            <p>Select a chat on the left to read and reply to customer messages.</p>
                            <p class="sub">Replies are delivered within Meta’s 24-hour customer-service window.</p>
                        </div>
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-filament-panels::page>
