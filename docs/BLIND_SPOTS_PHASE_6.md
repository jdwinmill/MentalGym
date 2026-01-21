# Phase 6: Blind Spots Dashboard

## Overview

Build an in-app dashboard where Pro users can view their training patterns, blind spots, improvements, and trajectory over time. This is the visual home for all Blind Spots data, complementing the weekly email with an always-available reference.

## Context

- Phases 1-5: Scoring, analysis, gating, and email delivery complete
- Phase 6: Visual dashboard for on-demand pattern viewing
- This is the "later" phase — email proves value first, dashboard adds depth

## Dependencies

- All previous phases complete
- BlindSpotService returns gated analysis
- User authentication and Pro subscription check

## Goals

1. Create dashboard page showing training patterns
2. Display blind spots, improvements, trends
3. Show trajectory over time (charts)
4. Link to targeted training sessions
5. Gate appropriately for free vs Pro users

---

## Page Structure

```
┌─────────────────────────────────────────────────────────────────────────┐
│  Blind Spots                                           [ Pro Badge ]    │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │  YOUR TRAINING SUMMARY                                          │   │
│  │                                                                  │   │
│  │  Sessions: 24        Responses: 67        Since: Jan 3, 2026   │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                                                                         │
│  ┌──────────────────────────┐  ┌──────────────────────────┐           │
│  │  BIGGEST GAP             │  │  BIGGEST WIN             │           │
│  │                          │  │                          │           │
│  │  Authority               │  │  Structure               │           │
│  │  Hedging in 67% of       │  │  Improved 40% in         │           │
│  │  responses               │  │  past 2 weeks            │           │
│  │                          │  │                          │           │
│  │  [ Train This ]          │  │                          │           │
│  └──────────────────────────┘  └──────────────────────────┘           │
│                                                                         │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │  SKILL TRAJECTORY                                               │   │
│  │                                                                  │   │
│  │  Authority    ████░░░░░░  Stuck         67% failure rate       │   │
│  │  Brevity      ██████░░░░  Stable        42% failure rate       │   │
│  │  Structure    ████████░░  Improving ↑   25% (was 55%)          │   │
│  │  Composure    ███████░░░  Stable        35% failure rate       │   │
│  │  Directness   █████░░░░░  Slipping ↓    52% (was 40%)          │   │
│  │                                                                  │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                                                                         │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │  PATTERN DETAILS                                                │   │
│  │                                                                  │   │
│  │  ▼ Authority (Blind Spot)                                       │   │
│  │    • Hedging detected in 8 of 12 responses                      │   │
│  │    • Most common in: Executive Communication drills             │   │
│  │    • You improve on iteration, but slip on first attempts       │   │
│  │    [ Train Authority ]                                          │   │
│  │                                                                  │   │
│  │  ▼ Structure (Improving)                                        │   │
│  │    • PREP/STAR used in 75% of responses (was 45%)               │   │
│  │    • Strongest in: Interview Prep drills                        │   │
│  │                                                                  │   │
│  │  ▶ Brevity (Stable)                                             │   │
│  │  ▶ Composure (Stable)                                           │   │
│  │  ▶ Directness (Slipping)                                        │   │
│  │                                                                  │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                                                                         │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │  UNIVERSAL PATTERNS                                             │   │
│  │                                                                  │   │
│  │  Hedging           ████████░░  65% of responses                 │   │
│  │  Filler phrases    ████░░░░░░  avg 2.3 per response             │   │
│  │  Apologies         ██░░░░░░░░  18% of responses                 │   │
│  │                                                                  │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                                                                         │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │  TREND OVER TIME                                    [4w ▼]      │   │
│  │                                                                  │   │
│  │  100% ┤                                                         │   │
│  │       │   ╭──╮                                                  │   │
│  │   75% ┤  ╱    ╲        Authority (hedging)                      │   │
│  │       │ ╱      ╲──────────────────────                          │   │
│  │   50% ┤╱                                                        │   │
│  │       │         ╭──────╮                                        │   │
│  │   25% ┤────────╱        ╲____  Structure                        │   │
│  │       │                                                         │   │
│  │    0% ┼────┬────┬────┬────┬────                                │   │
│  │       W1   W2   W3   W4   W5                                    │   │
│  │                                                                  │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## Free User View (Gated)

```
┌─────────────────────────────────────────────────────────────────────────┐
│  Blind Spots                                                            │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │                                                                  │   │
│  │                    🔒 3 BLIND SPOTS IDENTIFIED                   │   │
│  │                                                                  │   │
│  │     You've completed 8 sessions. We've found patterns in        │   │
│  │     your responses that could be holding you back.              │   │
│  │                                                                  │   │
│  │     ┌───────────────────────────────────────────────┐           │   │
│  │     │  • ██████████████████████████████████         │           │   │
│  │     │  • ██████████████████████████████████         │           │   │
│  │     │  • ██████████████████████████                 │           │   │
│  │     └───────────────────────────────────────────────┘           │   │
│  │                                                                  │   │
│  │     Pro members see exactly what these patterns are,            │   │
│  │     where they show up, and how to fix them.                    │   │
│  │                                                                  │   │
│  │                [ Unlock Blind Spots — Upgrade to Pro ]          │   │
│  │                                                                  │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## Routes

```php
// routes/web.php

Route::middleware(['auth'])->group(function () {
    Route::get('/blind-spots', [BlindSpotDashboardController::class, 'index'])
        ->name('blind-spots.index');
});
```

---

## Controller

```php
<?php

namespace App\Http\Controllers;

use App\Services\BlindSpotService;
use Inertia\Inertia;
use Inertia\Response;

class BlindSpotDashboardController extends Controller
{
    public function __construct(
        private BlindSpotService $service
    ) {}

    public function index(): Response
    {
        $user = auth()->user();
        $analysis = $this->service->getAnalysis($user);

        // Get historical data for charts (Pro only)
        $history = null;
        if ($analysis->isUnlocked) {
            $history = $this->service->getHistoricalTrends($user, weeks: 8);
        }

        return Inertia::render('BlindSpots/Index', [
            'analysis' => $analysis->toArray(),
            'history' => $history,
            'isPro' => $user->plan === 'pro' || $user->plan === 'unlimited',
        ]);
    }
}
```

---

## Historical Trends Service Method

Add to BlindSpotService:

```php
public function getHistoricalTrends(User $user, int $weeks = 8): array
{
    $trends = [];
    $now = now();

    for ($i = $weeks - 1; $i >= 0; $i--) {
        $weekStart = $now->copy()->subWeeks($i)->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();

        $scores = DrillScore::where('user_id', $user->id)
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->get();

        if ($scores->isEmpty()) {
            $trends[] = [
                'week' => $weekStart->format('M j'),
                'data' => null,
            ];
            continue;
        }

        $trends[] = [
            'week' => $weekStart->format('M j'),
            'data' => [
                'authority' => $this->calculateFailureRate($scores, 'hedging'),
                'brevity' => $this->calculateFailureRate($scores, 'ran_long'),
                'structure' => $this->calculateFailureRate($scores, 'structure_followed', invert: true),
                'composure' => $this->calculateFailureRate($scores, 'calm_tone', invert: true),
                'directness' => $this->calculateFailureRate($scores, 'direct_opening', invert: true),
            ],
            'sessions' => $scores->pluck('practice_session_id')->unique()->count(),
            'responses' => $scores->count(),
        ];
    }

    return $trends;
}

private function calculateFailureRate(Collection $scores, string $criteria, bool $invert = false): ?float
{
    $relevant = $scores->filter(fn($s) => isset($s->scores[$criteria]));
    
    if ($relevant->isEmpty()) {
        return null;
    }

    $failures = $relevant->filter(fn($s) => $invert 
        ? $s->scores[$criteria] === false 
        : $s->scores[$criteria] === true
    )->count();

    return round($failures / $relevant->count(), 2);
}
```

---

## Vue Component: BlindSpotsDashboard

```vue
<template>
  <div class="blind-spots-dashboard">
    <!-- Header -->
    <header class="dashboard-header">
      <h1>Blind Spots</h1>
      <span v-if="isPro" class="pro-badge">Pro</span>
    </header>

    <!-- Gated View for Free Users -->
    <GatedView 
      v-if="!analysis.is_unlocked" 
      :blind-spot-count="analysis.blind_spot_count"
      :total-sessions="analysis.total_sessions"
    />

    <!-- Full Dashboard for Pro Users -->
    <template v-else>
      <!-- Summary Stats -->
      <SummaryStats 
        :total-sessions="analysis.total_sessions"
        :total-responses="analysis.total_responses"
      />

      <!-- Biggest Gap / Win -->
      <div class="highlight-cards">
        <BiggestGapCard 
          :skill="analysis.biggest_gap"
          :details="getSkillDetails(analysis.biggest_gap)"
        />
        <BiggestWinCard 
          :skill="analysis.biggest_win"
          :details="getSkillDetails(analysis.biggest_win)"
        />
      </div>

      <!-- Skill Trajectory -->
      <SkillTrajectory :skills="allSkills" />

      <!-- Pattern Details (Expandable) -->
      <PatternDetails 
        :blind-spots="analysis.blind_spots"
        :improving="analysis.improving"
        :stable="analysis.stable"
        :slipping="analysis.slipping"
      />

      <!-- Universal Patterns -->
      <UniversalPatterns :patterns="analysis.universal_patterns" />

      <!-- Trend Chart -->
      <TrendChart 
        v-if="history"
        :history="history"
        :skills="['authority', 'structure']"
      />
    </template>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import GatedView from './GatedView.vue';
import SummaryStats from './SummaryStats.vue';
import BiggestGapCard from './BiggestGapCard.vue';
import BiggestWinCard from './BiggestWinCard.vue';
import SkillTrajectory from './SkillTrajectory.vue';
import PatternDetails from './PatternDetails.vue';
import UniversalPatterns from './UniversalPatterns.vue';
import TrendChart from './TrendChart.vue';

const props = defineProps({
  analysis: Object,
  history: Array,
  isPro: Boolean,
});

const allSkills = computed(() => {
  return [
    ...props.analysis.blind_spots,
    ...props.analysis.improving,
    ...props.analysis.stable,
    ...props.analysis.slipping,
  ].sort((a, b) => b.current_rate - a.current_rate);
});

const getSkillDetails = (skillName) => {
  return allSkills.value.find(s => s.skill === skillName);
};
</script>
```

---

## Component: GatedView

```vue
<template>
  <div class="gated-view">
    <div class="gated-content">
      <div class="lock-icon">🔒</div>
      <h2>{{ blindSpotCount }} Blind {{ blindSpotCount === 1 ? 'Spot' : 'Spots' }} Identified</h2>
      <p>
        You've completed {{ totalSessions }} sessions. We've found patterns in 
        your responses that could be holding you back.
      </p>
      
      <div class="redacted-box">
        <div class="redacted-line" v-for="i in Math.min(blindSpotCount, 5)" :key="i"></div>
      </div>

      <p class="unlock-text">
        Pro members see exactly what these patterns are, where they show up, and how to fix them.
      </p>

      <button @click="upgrade" class="upgrade-button">
        Unlock Blind Spots — Upgrade to Pro
      </button>
    </div>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3';

defineProps({
  blindSpotCount: Number,
  totalSessions: Number,
});

const upgrade = () => {
  router.visit('/settings/subscription');
};
</script>

<style scoped>
.gated-view {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 60vh;
}

.gated-content {
  text-align: center;
  max-width: 500px;
  padding: 48px;
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
  border: 1px solid #e94560;
  border-radius: 16px;
}

.lock-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.gated-content h2 {
  color: #e94560;
  font-size: 28px;
  margin: 0 0 16px;
}

.gated-content p {
  color: #a0a0a0;
  margin: 0 0 24px;
  line-height: 1.6;
}

.redacted-box {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  padding: 20px;
  margin: 24px 0;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.redacted-line {
  height: 14px;
  background: linear-gradient(90deg, #333 0%, #444 50%, #333 100%);
  border-radius: 4px;
}

.redacted-line:nth-child(odd) {
  width: 90%;
}

.redacted-line:nth-child(even) {
  width: 75%;
}

.unlock-text {
  font-size: 14px;
}

.upgrade-button {
  background: #e94560;
  color: white;
  border: none;
  padding: 16px 32px;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.upgrade-button:hover {
  background: #ff6b6b;
}
</style>
```

---

## Component: SkillTrajectory

```vue
<template>
  <div class="skill-trajectory">
    <h3>Skill Trajectory</h3>
    
    <div class="skill-list">
      <div 
        v-for="skill in skills" 
        :key="skill.skill"
        class="skill-row"
        :class="skill.trend"
      >
        <div class="skill-name">{{ formatSkillName(skill.skill) }}</div>
        
        <div class="skill-bar-container">
          <div 
            class="skill-bar" 
            :style="{ width: (1 - skill.current_rate) * 100 + '%' }"
            :class="getTrendClass(skill.trend)"
          ></div>
        </div>
        
        <div class="skill-trend">
          <span class="trend-label" :class="skill.trend">
            {{ formatTrend(skill.trend) }}
          </span>
        </div>
        
        <div class="skill-rate">
          {{ Math.round(skill.current_rate * 100) }}% failure
          <span v-if="skill.baseline_rate && skill.trend !== 'stable'" class="rate-change">
            (was {{ Math.round(skill.baseline_rate * 100) }}%)
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  skills: Array,
});

const formatSkillName = (skill) => {
  return skill.charAt(0).toUpperCase() + skill.slice(1);
};

const formatTrend = (trend) => {
  const labels = {
    improving: 'Improving ↑',
    stable: 'Stable',
    slipping: 'Slipping ↓',
    stuck: 'Stuck',
  };
  return labels[trend] || trend;
};

const getTrendClass = (trend) => {
  return {
    improving: 'bar-improving',
    stable: 'bar-stable',
    slipping: 'bar-slipping',
    stuck: 'bar-stuck',
  }[trend];
};
</script>

<style scoped>
.skill-trajectory {
  background: white;
  border-radius: 12px;
  padding: 24px;
  margin: 24px 0;
}

.skill-trajectory h3 {
  margin: 0 0 20px;
  font-size: 18px;
  color: #1a1a2e;
}

.skill-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.skill-row {
  display: grid;
  grid-template-columns: 120px 1fr 100px 140px;
  align-items: center;
  gap: 16px;
}

.skill-name {
  font-weight: 500;
  color: #1a1a2e;
}

.skill-bar-container {
  height: 12px;
  background: #e0e0e0;
  border-radius: 6px;
  overflow: hidden;
}

.skill-bar {
  height: 100%;
  border-radius: 6px;
  transition: width 0.3s ease;
}

.bar-improving {
  background: linear-gradient(90deg, #10b981, #34d399);
}

.bar-stable {
  background: linear-gradient(90deg, #6b7280, #9ca3af);
}

.bar-slipping {
  background: linear-gradient(90deg, #f59e0b, #fbbf24);
}

.bar-stuck {
  background: linear-gradient(90deg, #ef4444, #f87171);
}

.trend-label {
  font-size: 13px;
  font-weight: 500;
}

.trend-label.improving { color: #10b981; }
.trend-label.stable { color: #6b7280; }
.trend-label.slipping { color: #f59e0b; }
.trend-label.stuck { color: #ef4444; }

.skill-rate {
  font-size: 13px;
  color: #6b7280;
  text-align: right;
}

.rate-change {
  color: #9ca3af;
}
</style>
```

---

## Component: TrendChart

```vue
<template>
  <div class="trend-chart">
    <div class="chart-header">
      <h3>Trend Over Time</h3>
      <select v-model="timeRange" class="time-select">
        <option value="4">4 weeks</option>
        <option value="8">8 weeks</option>
      </select>
    </div>
    
    <div class="chart-container">
      <canvas ref="chartCanvas"></canvas>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
  history: Array,
  skills: Array,
});

const chartCanvas = ref(null);
const timeRange = ref(4);
let chartInstance = null;

const skillColors = {
  authority: { line: '#ef4444', bg: 'rgba(239, 68, 68, 0.1)' },
  structure: { line: '#10b981', bg: 'rgba(16, 185, 129, 0.1)' },
  brevity: { line: '#6366f1', bg: 'rgba(99, 102, 241, 0.1)' },
  composure: { line: '#f59e0b', bg: 'rgba(245, 158, 11, 0.1)' },
  directness: { line: '#8b5cf6', bg: 'rgba(139, 92, 246, 0.1)' },
};

const renderChart = () => {
  if (chartInstance) {
    chartInstance.destroy();
  }

  const data = props.history.slice(-timeRange.value);
  const labels = data.map(d => d.week);

  const datasets = props.skills.map(skill => ({
    label: skill.charAt(0).toUpperCase() + skill.slice(1),
    data: data.map(d => d.data ? Math.round(d.data[skill] * 100) : null),
    borderColor: skillColors[skill]?.line || '#6b7280',
    backgroundColor: skillColors[skill]?.bg || 'rgba(107, 114, 128, 0.1)',
    fill: true,
    tension: 0.3,
    spanGaps: true,
  }));

  chartInstance = new Chart(chartCanvas.value, {
    type: 'line',
    data: { labels, datasets },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          max: 100,
          ticks: {
            callback: (value) => value + '%',
          },
        },
      },
      plugins: {
        tooltip: {
          callbacks: {
            label: (context) => `${context.dataset.label}: ${context.raw}% failure rate`,
          },
        },
      },
    },
  });
};

onMounted(renderChart);
watch(timeRange, renderChart);
</script>

<style scoped>
.trend-chart {
  background: white;
  border-radius: 12px;
  padding: 24px;
  margin: 24px 0;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.chart-header h3 {
  margin: 0;
  font-size: 18px;
  color: #1a1a2e;
}

.time-select {
  padding: 8px 12px;
  border: 1px solid #e0e0e0;
  border-radius: 6px;
  font-size: 14px;
}

.chart-container {
  height: 300px;
}
</style>
```

---

## Component: PatternDetails

```vue
<template>
  <div class="pattern-details">
    <h3>Pattern Details</h3>
    
    <!-- Blind Spots (expanded by default) -->
    <PatternSection 
      v-for="pattern in blindSpots" 
      :key="pattern.skill"
      :pattern="pattern"
      :type="'blind-spot'"
      :default-open="true"
    />
    
    <!-- Improving -->
    <PatternSection 
      v-for="pattern in improving" 
      :key="pattern.skill"
      :pattern="pattern"
      :type="'improving'"
    />
    
    <!-- Slipping -->
    <PatternSection 
      v-for="pattern in slipping" 
      :key="pattern.skill"
      :pattern="pattern"
      :type="'slipping'"
    />
    
    <!-- Stable (collapsed) -->
    <PatternSection 
      v-for="pattern in stable" 
      :key="pattern.skill"
      :pattern="pattern"
      :type="'stable'"
    />
  </div>
</template>

<script setup>
import PatternSection from './PatternSection.vue';

defineProps({
  blindSpots: Array,
  improving: Array,
  stable: Array,
  slipping: Array,
});
</script>
```

---

## Component: PatternSection

```vue
<template>
  <div class="pattern-section" :class="type">
    <button class="section-header" @click="toggle">
      <span class="toggle-icon">{{ isOpen ? '▼' : '▶' }}</span>
      <span class="skill-name">{{ formatSkillName(pattern.skill) }}</span>
      <span class="type-badge">{{ formatType(type) }}</span>
    </button>
    
    <div v-if="isOpen" class="section-content">
      <ul class="detail-list">
        <li v-if="pattern.primary_issue">
          {{ formatCriteria(pattern.primary_issue) }} detected in 
          {{ Math.round(pattern.current_rate * 100) }}% of responses
        </li>
        <li v-if="pattern.context">
          Most common in: {{ pattern.context }}
        </li>
        <li v-if="pattern.baseline_rate && pattern.trend === 'improving'">
          Improved from {{ Math.round(pattern.baseline_rate * 100) }}% 
          to {{ Math.round(pattern.current_rate * 100) }}%
        </li>
        <li v-for="criteria in pattern.failing_criteria" :key="criteria">
          {{ formatCriteria(criteria) }} needs attention
        </li>
      </ul>
      
      <button 
        v-if="type === 'blind-spot' || type === 'slipping'"
        @click="trainSkill"
        class="train-button"
      >
        Train {{ formatSkillName(pattern.skill) }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  pattern: Object,
  type: String,
  defaultOpen: Boolean,
});

const isOpen = ref(props.defaultOpen || false);

const toggle = () => {
  isOpen.value = !isOpen.value;
};

const formatSkillName = (skill) => {
  return skill.charAt(0).toUpperCase() + skill.slice(1);
};

const formatType = (type) => {
  const labels = {
    'blind-spot': 'Blind Spot',
    'improving': 'Improving',
    'stable': 'Stable',
    'slipping': 'Slipping',
  };
  return labels[type];
};

const formatCriteria = (criteria) => {
  const labels = {
    hedging: 'Hedging language',
    ran_long: 'Running long',
    filler_phrases: 'Filler phrases',
    structure_followed: 'Missing structure',
    direct_opening: 'Buried lead',
  };
  return labels[criteria] || criteria;
};

const trainSkill = () => {
  router.visit(`/practice-modes?skill=${props.pattern.skill}`);
};
</script>

<style scoped>
.pattern-section {
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  margin-bottom: 12px;
  overflow: hidden;
}

.pattern-section.blind-spot {
  border-color: #fecaca;
  background: #fef2f2;
}

.pattern-section.improving {
  border-color: #a7f3d0;
  background: #ecfdf5;
}

.pattern-section.slipping {
  border-color: #fde68a;
  background: #fffbeb;
}

.section-header {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  background: none;
  border: none;
  cursor: pointer;
  text-align: left;
}

.toggle-icon {
  color: #6b7280;
  font-size: 12px;
}

.skill-name {
  font-weight: 600;
  color: #1a1a2e;
  flex: 1;
}

.type-badge {
  font-size: 12px;
  padding: 4px 8px;
  border-radius: 4px;
  font-weight: 500;
}

.blind-spot .type-badge {
  background: #ef4444;
  color: white;
}

.improving .type-badge {
  background: #10b981;
  color: white;
}

.slipping .type-badge {
  background: #f59e0b;
  color: white;
}

.stable .type-badge {
  background: #6b7280;
  color: white;
}

.section-content {
  padding: 0 16px 16px;
}

.detail-list {
  margin: 0 0 16px;
  padding-left: 20px;
  color: #4b5563;
}

.detail-list li {
  margin-bottom: 8px;
}

.train-button {
  background: #1a1a2e;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
}

.train-button:hover {
  background: #2d2d4a;
}
</style>
```

---

## Navigation Link

Add to your main navigation:

```vue
<nav>
  <!-- ... other links ... -->
  <NavLink :href="route('blind-spots.index')" :active="route().current('blind-spots.*')">
    Blind Spots
    <span v-if="blindSpotCount" class="badge">{{ blindSpotCount }}</span>
  </NavLink>
</nav>
```

---

## File Structure

```
app/
├── Http/
│   └── Controllers/
│       └── BlindSpotDashboardController.php
├── Services/
│   └── BlindSpotService.php (updated with getHistoricalTrends)
resources/
└── js/
    └── Pages/
        └── BlindSpots/
            └── Index.vue
    └── Components/
        └── BlindSpots/
            ├── GatedView.vue
            ├── SummaryStats.vue
            ├── BiggestGapCard.vue
            ├── BiggestWinCard.vue
            ├── SkillTrajectory.vue
            ├── PatternDetails.vue
            ├── PatternSection.vue
            ├── UniversalPatterns.vue
            └── TrendChart.vue
routes/
└── web.php (updated)
```

---

## Testing

### Test: Pro user sees full dashboard

```php
$user = User::factory()->create(['plan' => 'pro']);
createSessionsWithBlindSpots($user, 10);

$response = $this->actingAs($user)->get('/blind-spots');

$response->assertOk();
$response->assertInertia(fn ($page) => 
    $page->component('BlindSpots/Index')
        ->has('analysis.blind_spots')
        ->where('analysis.is_unlocked', true)
);
```

### Test: Free user sees gated view

```php
$user = User::factory()->create(['plan' => 'free']);
createSessionsWithBlindSpots($user, 10);

$response = $this->actingAs($user)->get('/blind-spots');

$response->assertOk();
$response->assertInertia(fn ($page) => 
    $page->component('BlindSpots/Index')
        ->where('analysis.is_unlocked', false)
        ->where('analysis.blind_spot_count', 3)
        ->missing('analysis.blind_spots.0.skill') // Details hidden
);
```

### Test: Historical trends returned for Pro

```php
$user = User::factory()->create(['plan' => 'pro']);
createSessionsAcrossWeeks($user, weeks: 6);

$response = $this->actingAs($user)->get('/blind-spots');

$response->assertOk();
$response->assertInertia(fn ($page) => 
    $page->has('history', 8) // 8 weeks of data
        ->has('history.0.week')
        ->has('history.0.data.authority')
);
```

### Test: User with no sessions

```php
$user = User::factory()->create(['plan' => 'pro']);

$response = $this->actingAs($user)->get('/blind-spots');

$response->assertOk();
$response->assertInertia(fn ($page) => 
    $page->where('analysis.has_enough_data', false)
);
```

---

## Success Criteria

- [ ] Route and controller created
- [ ] Full dashboard renders for Pro users
- [ ] Gated view renders for free users
- [ ] Skill trajectory bars display correctly
- [ ] Pattern details expandable/collapsible
- [ ] Trend chart renders with historical data
- [ ] "Train This" buttons link to relevant Practice Modes
- [ ] Upgrade CTA works for free users
- [ ] Responsive on mobile
- [ ] All tests pass

---

## Future Enhancements

After v1 launch:

1. **Drill-down views** — Click a skill to see all responses that failed that criteria
2. **Compare periods** — "This month vs. last month"
3. **Export data** — Download training history as CSV
4. **Goal setting** — "I want to reduce hedging to under 30%"
5. **Achievements** — "You went 5 sessions without hedging"
6. **Session replay** — See your actual responses that triggered patterns
