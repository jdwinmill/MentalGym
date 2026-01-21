# Feedback Feature Specification

## Overview

A lightweight, non-intrusive feedback system for logged-in users to report issues or request features. The goal is to capture user input at the moment of friction without disrupting their training flow.

---

## 1. Feedback Bubble Button

### Placement
- Fixed position: bottom-right corner
- Offset: `24px` from right edge, `24px` from bottom edge
- Z-index: high enough to float above content, below modals

### Appearance
- **Shape:** Rounded pill or circle (40-48px diameter)
- **Color:** Neutral/muted (gray or soft version of brand color)—NOT the orange CTA color, to avoid competing with action buttons
- **Icon:** Simple chat bubble or lightbulb icon
- **Shadow:** Subtle, matching card shadows in existing design
- **Opacity:** Consider slight transparency (90-95%) when idle

### States
| State | Behavior |
|-------|----------|
| Idle | Static, subtle presence |
| Hover | Slight scale up (1.05), full opacity, optional tooltip "Share Feedback" |
| Active/Open | Highlighted or inverted colors |

### Mobile Considerations
- Same bottom-right position
- Ensure it doesn't overlap scrollable content at page bottom
- Touch target minimum 44x44px
- Consider hiding while user is actively scrolling (reappears on scroll stop)

---

## 2. Card Button Width Changes

### Current Problem
Full-width "Begin Training" buttons feel heavy and dominate the card visually.

### Solution
- **Width:** `fit-content` with horizontal padding (`px-8` / `32px` or similar)
- **Alignment:** Right-aligned within the card
- **Max-width:** Optional cap at `200px` to prevent overly long text from stretching

### Example CSS
```css
.card-cta {
  width: fit-content;
  padding: 12px 32px;
  margin-left: auto; /* right-align */
}
```

### Result
Cards feel lighter, more balanced. The CTA is still prominent but doesn't overwhelm the metadata above it.

---

## 3. Feedback Modal/Panel

### Trigger
Click/tap on feedback bubble button.

### Appearance Options
1. **Bottom sheet** (slides up from bottom) — good for mobile-first
2. **Popover** (appears above/beside the button) — lighter feel
3. **Small modal** (centered or anchored to bottom-right) — more traditional

**Recommendation:** Bottom-right anchored popover or slide-up panel. Keeps it contextual and doesn't feel like a big interruption.

### Form Fields

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| Title | Text input | No | Placeholder: "Brief summary (optional)" |
| Feedback | Textarea | Yes | Placeholder: "What's on your mind?" |
| Type | Segmented control or pills | No | Options: "Bug" / "Idea" / "Other" — defaults to "Idea" |

### Character Limits
- Title: 100 characters
- Feedback: 1000 characters (show remaining count near limit)

### Actions
- **Submit** button (primary style, but smaller than card CTAs)
- **Close/Cancel** — X button in corner or click outside to dismiss

### Submission Flow
1. User clicks Submit
2. Button shows loading state (spinner or "Sending...")
3. On success: 
   - Brief confirmation ("Thanks! We got it.")
   - Auto-close after 1.5s or on click
4. On error:
   - Inline error message
   - Keep form open so user can retry

---

## 4. Data Model

### Feedback Record
```typescript
interface Feedback {
  id: string;
  userId: string;
  type: 'bug' | 'idea' | 'other';
  title?: string;
  body: string;
  createdAt: timestamp;
  
  // Auto-captured context
  url: string;           // Current page/route
  userAgent: string;     // Browser info
  appVersion?: string;   // If versioned
}
```

### Storage
- Start simple: store in your existing database
- Or use a lightweight service (Notion API, Airtable, simple webhook to email)
- Can migrate to dedicated tool (Canny, etc.) if volume grows

---

## 5. UX Guidelines

### Do
- Keep the button small and unobtrusive
- Use friendly, casual copy ("What's on your mind?" not "Submit a ticket")
- Auto-capture context (page URL, timestamp) so users don't have to explain where they were
- Show appreciation on submit—they're helping you build something better

### Don't
- Animate the button (no bouncing, pulsing, or attention-seeking)
- Require fields beyond the feedback text itself
- Show the button on public/marketing pages (logged-in only)
- Make the modal large or intimidating

### Nice-to-Have (Future)
- Screenshot attachment option
- Feedback history ("Your past submissions")
- Status updates if you act on their feedback
- Quick reactions (👍👎) on specific features instead of freeform

---

## 6. Implementation Notes

### Visibility Logic
```typescript
// Only show feedback button when authenticated
{isLoggedIn && <FeedbackButton />}
```

### Keyboard Accessibility
- Button is focusable and activates on Enter/Space
- Modal traps focus while open
- Escape key closes modal
- Form fields are properly labeled

### Animation Suggestions
- Modal: fade in + slight slide up (150-200ms ease-out)
- Close: fade out (100ms)
- Keep it quick and subtle

---

## 7. Visual Reference

```
┌────────────────────────────────────────────┐
│  Practice                              ☀️  │
├────────────────────────────────────────────┤
│                                            │
│  Practice Modes                            │
│  Choose a mode to begin training           │
│                                            │
│  ┌──────────────────────────────────────┐  │
│  │ MBA+ Decision Lab                    │  │
│  │ strategic thinking                   │  │
│  │                                      │  │
│  │ Your Level              Novice       │  │
│  │ Total Exchanges            21        │  │
│  │                                      │  │
│  │              [▶ Begin Training]      │  │ ← narrower, right-aligned
│  └──────────────────────────────────────┘  │
│                                            │
│  ┌──────────────────────────────────────┐  │
│  │ Interview Prep                       │  │
│  │ ...                                  │  │
│  └──────────────────────────────────────┘  │
│                                            │
│                                     [💬]   │ ← feedback bubble
└────────────────────────────────────────────┘
```

---

## Summary

| Element | Key Decision |
|---------|--------------|
| Feedback button | Fixed bottom-right, 40-48px, muted color, no animation |
| Card CTAs | `fit-content` width, right-aligned |
| Form | Title (optional) + Body (required) + Type selector |
| Scope | Logged-in users only |
| Tone | Friendly, low-friction, appreciative |
