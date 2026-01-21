<x-mail::message>
# SHARPSTACK

Hey {{ $userName }},

You've completed 5 sessions. That's enough data to see patterns.

We analyzed **{{ $totalResponses }} responses** and found:

<x-mail::panel>
<div style="text-align: center; padding: 20px 0;">
<div style="font-size: 24px; font-weight: bold; color: #e94560; margin-bottom: 16px;">
{{ $blindSpotCount }} BLIND {{ $blindSpotCount === 1 ? 'SPOT' : 'SPOTS' }} IDENTIFIED
</div>

<div style="font-family: monospace; color: #666; line-height: 1.8;">
@for ($i = 0; $i < min($blindSpotCount, 3); $i++)
@if ($i === 2)
• ██████████████████<br>
@else
• ██████████████████████████<br>
@endif
@endfor
</div>
</div>
</x-mail::panel>

These patterns are showing up consistently in your responses. They're costing you clarity, authority, or impact — and you can't see them.

**Pro members see exactly what these patterns are, where they show up, and how to fix them.**

<x-mail::button :url="$upgradeUrl">
Unlock Your Blind Spots
</x-mail::button>

---

Keep training. Your patterns become clearer with more data.

<small>
[Unsubscribe from these emails]({{ $unsubscribeUrl }})
</small>

</x-mail::message>
