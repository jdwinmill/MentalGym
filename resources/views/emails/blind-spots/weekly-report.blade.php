<x-mail::message>
# SHARPSTACK WEEKLY

You completed **{{ $sessionsThisWeek }} {{ Str::plural('session', $sessionsThisWeek) }}** this week.

@if(count($improving) > 0)
## WHAT'S IMPROVING

@foreach($improving as $item)
→ {{ $item }}
@endforeach

@endif
## WHAT NEEDS WORK

@foreach($needsWork as $item)
→ {{ $item }}
@endforeach

---

## PATTERN TO WATCH

{{ $patternToWatch }}

---

## THIS WEEK'S FOCUS

{{ $weeklyFocus }}

<x-mail::button :url="$startSessionUrl">
Start a Session
</x-mail::button>

@if($article)
---

## RECOMMENDED READ

**"{{ $article['title'] }}"**

{{ $article['description'] }}

<x-mail::button :url="$article['url']" color="secondary">
Read Article
</x-mail::button>
@endif

---

<small>
You're receiving this because you have a Pro subscription with weekly reports enabled.
[Unsubscribe from weekly reports]({{ $unsubscribeUrl }})
</small>

</x-mail::message>
