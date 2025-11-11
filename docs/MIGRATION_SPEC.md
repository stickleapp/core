# Documentation Migration Specification

## Project Overview
**Goal:** Migrate existing 30-file documentation structure to lean 16-file structure
**Timeline:** 8 days
**Status:** Ready to execute

## Success Criteria
- ✅ 16 well-structured documentation files
- ✅ Zero stub/empty files
- ✅ No duplicate content
- ✅ All internal links working
- ✅ All typos corrected
- ✅ Complete code examples in every file

---

## File Inventory

### Target Structure (16 files)

#### Introduction (1 file)
- `index.md` - What is Stickle? [NEW]

#### Getting Started (3 files)
- `installation.md` - Installation steps [UPDATE]
- `quick-start.md` - 15-minute tutorial [NEW]
- `configuration.md` - Config reference [KEEP]

#### Core Features (6 files)
- `tracking-attributes.md` - Model attributes [NEW - MERGE]
- `segments.md` - Customer segments [NEW - MERGE]
- `filters.md` - Eloquent query filters [NEW - MERGE]
- `event-listeners.md` - Event system [NEW - MERGE]
- `javascript-tracking.md` - Client/server tracking [UPDATE]
- `stickle-ui.md` - Dashboard UI [UPDATE]

#### Reference (3 files)
- `api-endpoints.md` - REST API reference [RENAME]
- `filter-reference.md` - Quick filter reference [NEW]
- `events-reference.md` - Events reference [NEW]

#### Advanced (3 files)
- `recipes.md` - Code recipes [NEW]
- `deployment.md` - Production guide [NEW]
- `troubleshooting.md` - Common issues [NEW]

### Files to Delete (23 files)
1. `__webhooks.md`
2. `3rd-party-integrations.md`
3. `aggregate-attributes.md`
4. `creating-segments.md`
5. `eloquent-methods.md`
6. `getting-started.md`
7. `illuminate-auth-events.md`
8. `listeners-illuminate-auth-events.md`
9. `listeners-model-attribute-changes.md`
10. `listeners-page-views.md`
11. `listeners-segment-events.md`
12. `listeners-user-events.md`
13. `macros.md`
14. `quickstart.md`
15. `request-middleware.md`
16. `scopes.md`
17. `tracking-model-attributes.md`
18. `tracking-segments.md`
19. `ui-customizing.md`
20. `ui-getting-started.md`
21. `use-cases.md`
22. `user-events.md`
23. `what-are-segments.md`

---

## Phase 1: Core Structure

### Task 1.1: Create index.md
**Type:** NEW
**Priority:** HIGH
**Estimated Time:** 2 hours

**Description:** Create introduction page explaining what Stickle is

**Content Sources:**
- `/docs/index.md` (homepage features, lines 17-29)
- `guide/use-cases.md` (all content)
- `/README.md` (lines 5-20)

**Structure:**
```markdown
# What is Stickle?

## Overview
[2 paragraphs - from README]

## Key Features
[6 bullet points - from homepage]

## When to Use Stickle
[From use-cases.md]

## Next Steps
[Link to quick-start.md]
```

**Acceptance Criteria:**
- [ ] Clear 2-paragraph explanation
- [ ] 6 key features listed
- [ ] Use cases explained
- [ ] Links to quick-start
- [ ] No marketing fluff

---

### Task 1.2: Create quick-start.md
**Type:** NEW
**Priority:** HIGH
**Estimated Time:** 3 hours

**Description:** Create 15-minute quick start tutorial

**Content Sources:**
- `guide/getting-started.md` (lines 9-51)
- Create new code examples

**Structure:**
```markdown
# Quick Start Guide

## Prerequisites
[PHP 8.2+, Laravel 12+]

## Step 1: Add StickleEntity Trait
[Complete code example]

## Step 2: Track Your First Attribute
[Complete code example]

## Step 3: Create Your First Segment
[Complete code example]

## Step 4: View in StickleUI
[Screenshots/descriptions]

## Next Steps
[Links to core features]
```

**Acceptance Criteria:**
- [ ] Complete working code examples
- [ ] Copy-paste ready
- [ ] Takes 15 minutes to follow
- [ ] Results in working Stickle setup
- [ ] No errors or typos

---

### Task 1.3: Update installation.md
**Type:** UPDATE
**Priority:** MEDIUM
**Estimated Time:** 1 hour

**Description:** Clean up installation documentation

**Changes:**
- Remove duplicate "Getting Started" heading (line 5)
- Focus only on installation mechanics
- Keep all existing content about migrations, scheduled tasks

**Acceptance Criteria:**
- [ ] No duplicate headings
- [ ] Clear installation flow
- [ ] Prerequisites listed
- [ ] All commands correct

---

### Task 1.4: Keep configuration.md
**Type:** KEEP
**Priority:** LOW
**Estimated Time:** 15 minutes

**Description:** Verify configuration.md needs no changes

**Changes:**
- Proofread only
- Fix any typos
- Otherwise keep as-is

**Acceptance Criteria:**
- [ ] No typos
- [ ] All config options documented
- [ ] Examples are correct

---

## Phase 2: Core Features

### Task 2.1: Create tracking-attributes.md
**Type:** NEW - MERGE
**Priority:** HIGH
**Estimated Time:** 4 hours

**Description:** Merge 3 files into comprehensive attribute tracking guide

**Content Sources:**
- `tracking-model-attributes.md` (intro, skip incomplete sections)
- `aggregate-attributes.md` (lines 5-28 practical example)
- `macros.md` (lines 62-111 stickleAttribute, lines 82-110 trackable_attributes)

**Structure:**
```markdown
# Tracking Attributes

## What are Attributes?

## Observed vs Tracked Attributes

## Defining Trackable Attributes
[stickleObservedAttributes() method]
[stickleTrackedAttributes() method]

## Accessing Attributes
[stickleAttribute() method]
[trackable_attributes accessor]

## Attribute Types
### Numeric Attributes
### Text Attributes
### Boolean Attributes
### Date Attributes

## Parent-Child Aggregation
[Practical example, no SQL]

## Complete Examples
[3-4 real-world examples]
```

**Acceptance Criteria:**
- [ ] All three source files merged
- [ ] No duplicate content
- [ ] Complete code examples
- [ ] No SQL queries (use Eloquent)
- [ ] Parent-child aggregation explained clearly

**Files to Delete After:**
- `tracking-model-attributes.md`
- `aggregate-attributes.md`

---

### Task 2.2: Create segments.md
**Type:** NEW - MERGE
**Priority:** HIGH
**Estimated Time:** 3 hours

**Description:** Merge segment documentation into single comprehensive guide

**Content Sources:**
- `what-are-segments.md` (all content)
- `creating-segments.md` (all content, fix typos)
- `tracking-segments.md` (all content)

**Structure:**
```markdown
# Customer Segments

## What are Segments?

## Creating Segment Classes
[Basic example]
[StickleSegmentMetadata attribute]

## Using Filters in Segments
[Examples with different filter types]

## Common Segment Examples
### Active Users
### High-Value Customers
### At-Risk Customers
### Trial Users
### Power Users

## How Stickle Tracks Segments
[Explain automatic tracking]
[History tracking]
[Aggregate statistics]

## Querying by Segment
[InSegment, HasBeenInSegment, etc.]
```

**Typos to Fix:**
- Line 19 in `what-are-segments.md`: "Trickle" → "Stickle"
- Line 48 in `creating-segments.md`: "Stitch" → "Stickle"
- Line 50 in `creating-segments.md`: "longr" → "longer"

**Acceptance Criteria:**
- [ ] All three files merged logically
- [ ] 5 complete segment examples
- [ ] All typos fixed
- [ ] Clear progression from basics to advanced

**Files to Delete After:**
- `what-are-segments.md`
- `creating-segments.md`
- `tracking-segments.md`

---

### Task 2.3: Create filters.md
**Type:** NEW - MERGE
**Priority:** HIGH
**Estimated Time:** 4 hours

**Description:** Merge filter documentation into comprehensive guide

**Content Sources:**
- `eloquent-methods.md` (entire file - already excellent)
- `scopes.md` (all content)
- `macros.md` (lines 9-57 scope documentation)

**Structure:**
```markdown
# Filters and Eloquent Methods

## Overview
[stickleWhere() / stickleOrWhere()]

## Filter Types
[All content from eloquent-methods.md]
- Boolean
- Date
- Datetime
- EventCount
- Number
- RequestCount
- SessionCount
- Text

## Creating Custom Scopes
[All content from scopes.md]

## Performance Tips
[From scopes.md]

## Complete Examples
[Complex filtering examples]
```

**Acceptance Criteria:**
- [ ] All filter types documented
- [ ] Code examples for each filter
- [ ] Custom scopes explained
- [ ] Performance tips included
- [ ] No duplicate content

**Files to Delete After:**
- `eloquent-methods.md`
- `scopes.md`
- `macros.md`

---

### Task 2.4: Create event-listeners.md
**Type:** NEW - MERGE
**Priority:** HIGH
**Estimated Time:** 4 hours

**Description:** Merge all listener documentation into single guide

**Content Sources:**
- `listeners-user-events.md` (all)
- `user-events.md` (currently empty - add intro)
- `listeners-page-views.md` (remove draft notes)
- `listeners-segment-events.md` (remove draft notes)
- `listeners-model-attribute-changes.md` (all)
- `listeners-illuminate-auth-events.md` (fix typo, all content)
- `illuminate-auth-events.md` (configuration section)

**Structure:**
```markdown
# Event Listeners

## Events in Stickle
[Overview of event system]

## User Events (Track)
[How to create custom events]
[Listening to user events]
[Complete example]

## Page View Events (Page)
[How page views work]
[Listening to page views]
[Complete example]

## Attribute Change Events (ObjectAttributeChanged)
[When triggered]
[Listening to attribute changes]
[Complete example]

## Segment Events
[ObjectEnteredSegment]
[ObjectExitedSegment]
[Complete examples]

## Authentication Events
[Illuminate\Auth events]
[Configuration]
[Complete example]

## Real-World Examples
[Send email on segment entry]
[Slack notification on event]
[Update CRM on attribute change]
```

**Typos to Fix:**
- Line 16 in `listeners-illuminate-auth-events.md`: `Illuminate\Aut\` → `Illuminate\Auth\`

**Draft Notes to Remove:**
- Lines 28-34 in `listeners-page-views.md` ("How do we constrain...")
- Lines 31-37 in `listeners-segment-events.md` ("How do we constrain...")

**Acceptance Criteria:**
- [ ] All 7 files merged cohesively
- [ ] Draft notes removed
- [ ] Typo fixed
- [ ] Complete example for each event type
- [ ] Clear when to use which event

**Files to Delete After:**
- `listeners-user-events.md`
- `user-events.md`
- `listeners-page-views.md`
- `listeners-segment-events.md`
- `listeners-model-attribute-changes.md`
- `listeners-illuminate-auth-events.md`
- `illuminate-auth-events.md`

---

### Task 2.5: Create javascript-tracking.md
**Type:** UPDATE
**Priority:** MEDIUM
**Estimated Time:** 2 hours

**Description:** Expand JavaScript SDK docs to include server-side tracking

**Content Sources:**
- `javascript-sdk.md` (all content)
- `request-middleware.md` (context about server-side)

**Structure:**
```markdown
# Tracking: Client and Server

## Overview
[When to use client vs server tracking]

## Client-Side Tracking (JavaScript SDK)
[How it's injected]
[stickle.page()]
[stickle.track()]
[Configuration options]

## Server-Side Tracking (Middleware)
[How it works]
[Configuration]
[When to use]

## SPA Frameworks
[Livewire]
[Inertia.js]
[Vue.js]
[React]

## Configuration
[All tracking config options]
```

**Acceptance Criteria:**
- [ ] Both client and server tracking covered
- [ ] Clear guidance on when to use each
- [ ] SPA framework examples
- [ ] Configuration reference

**Files to Delete After:**
- `request-middleware.md`

---

### Task 2.6: Create stickle-ui.md
**Type:** UPDATE - MERGE
**Priority:** MEDIUM
**Estimated Time:** 3 hours

**Description:** Merge UI documentation and add customization section

**Content Sources:**
- `ui-getting-started.md` (all content - already good)
- `ui-customizing.md` (currently empty - write it)
- `stickle-ui.md` (if exists)

**Structure:**
```markdown
# StickleUI Dashboard

## What is StickleUI?
[Overview]

## Accessing the Dashboard
[/stickle URL]
[Authentication]

## Navigation Overview
[Main sections]
[What each section shows]

## Customization
### Theming
[Tailwind configuration]
[Brand colors]

### Adding Custom Pages
[Routes]
[Blade templates]

### Overriding Components
[Component structure]
[How to override]

## That's It
[Keep it simple]
```

**Acceptance Criteria:**
- [ ] Getting started content preserved
- [ ] Customization section written
- [ ] Practical examples
- [ ] Not overly complex

**Files to Delete After:**
- `ui-getting-started.md`
- `ui-customizing.md`

---

## Phase 3: Reference Documentation

### Task 3.1: Rename endpoints.md
**Type:** RENAME
**Priority:** LOW
**Estimated Time:** 5 minutes

**Description:** Rename for clarity

**Changes:**
- `endpoints.md` → `api-endpoints.md`
- Update any references in other docs

**Acceptance Criteria:**
- [ ] File renamed
- [ ] Links updated in other files
- [ ] No content changes needed

---

### Task 3.2: Create filter-reference.md
**Type:** NEW
**Priority:** MEDIUM
**Estimated Time:** 2 hours

**Description:** Quick reference for all filter types

**Content Source:**
- Extract from `eloquent-methods.md` (lines 39-346)
- Create concise reference table

**Structure:**
```markdown
# Filter Reference

## Quick Reference Table
| Filter Type | Methods | Use Case |
|-------------|---------|----------|
| Boolean | isTrue(), isFalse() | ... |
| ... | ... | ... |

## Boolean Filters
[Method signatures]
[Parameters]

## Date Filters
[Method signatures]
[Parameters]

[...etc for all filter types]

## See Also
[Link to filters.md for examples and tutorials]
```

**Acceptance Criteria:**
- [ ] All filter types listed
- [ ] Method signatures accurate
- [ ] Quick reference format (not tutorial)
- [ ] Links to filters.md for examples

---

### Task 3.3: Create events-reference.md
**Type:** NEW
**Priority:** MEDIUM
**Estimated Time:** 2 hours

**Description:** Reference for all events Stickle dispatches

**Content Source:**
- Create from event-listeners.md content
- List all event classes

**Structure:**
```markdown
# Events Reference

## Overview
[Brief explanation]

## Event Index
| Event | When Dispatched | Properties |
|-------|----------------|------------|
| Page | Page view | url, user, session_uid, ... |
| Track | Custom event | name, data, user, ... |
| ... | ... | ... |

## Page Event
[Class signature]
[Properties]
[Example payload]

## Track Event
[Class signature]
[Properties]
[Example payload]

[...etc for all events]

## See Also
[Link to event-listeners.md for usage examples]
```

**Acceptance Criteria:**
- [ ] All events listed
- [ ] Event properties documented
- [ ] Example payloads shown
- [ ] Links to event-listeners.md

---

## Phase 4: Advanced Documentation

### Task 4.1: Create recipes.md
**Type:** NEW
**Priority:** MEDIUM
**Estimated Time:** 3 hours

**Description:** Code recipes for common patterns

**Content Source:**
- Create from scratch
- Pull examples from merged docs where applicable

**Structure:**
```markdown
# Recipes

## Track MRR
[Complete code example]

## Identify Churning Customers
[Complete code example]

## Find Power Users
[Complete code example]

## Send Email on Segment Entry
[Complete code example]

## Track Feature Adoption
[Complete code example]

## Calculate Customer Health Score
[Complete code example]
```

**Acceptance Criteria:**
- [ ] 5-6 complete recipes
- [ ] Each recipe is 5-20 lines
- [ ] Copy-paste ready
- [ ] Real-world scenarios
- [ ] Covers different feature areas

---

### Task 4.2: Create deployment.md
**Type:** NEW
**Priority:** MEDIUM
**Estimated Time:** 2 hours

**Description:** Production deployment guide

**Content Sources:**
- Extract from `installation.md` (scheduled tasks, reverb)
- Extract from `configuration.md` (production settings)
- Create new content

**Structure:**
```markdown
# Deployment Guide

## Production Checklist
[Step-by-step checklist]

## Queue Workers
[Setup and configuration]

## WebSockets (Reverb/Pusher)
[Production setup]

## Database Optimization
[Indexing recommendations]
[Partition strategy]

## Scheduled Tasks
[Cron configuration]

## Monitoring
[What to monitor]
[Health checks]
```

**Acceptance Criteria:**
- [ ] Complete production checklist
- [ ] Queue setup documented
- [ ] WebSocket configuration covered
- [ ] Database optimization tips

---

### Task 4.3: Create troubleshooting.md
**Type:** NEW
**Priority:** MEDIUM
**Estimated Time:** 2 hours

**Description:** Common issues and solutions

**Content Source:**
- Create from scratch
- Research common issues

**Structure:**
```markdown
# Troubleshooting

## Common Issues

### Tracking Not Working
[Symptoms]
[Solutions]

### StickleUI Not Loading
[Symptoms]
[Solutions]

### Performance Issues
[Symptoms]
[Solutions]

### WebSocket Connection Failed
[Symptoms]
[Solutions]

### Migrations Failing
[Symptoms]
[Solutions]

## Debugging

### Enable Debug Mode
[How to enable logging]

### Checking Logs
[Where logs are]
[What to look for]

### Database Queries
[How to debug queries]

## Getting Help
[GitHub issues]
[Discussions]
```

**Acceptance Criteria:**
- [ ] 5-6 common issues covered
- [ ] Clear symptoms and solutions
- [ ] Debugging instructions
- [ ] Links to support resources

---

## Phase 5: Cleanup and Finalization

### Task 5.1: Delete deprecated files
**Type:** CLEANUP
**Priority:** HIGH
**Estimated Time:** 30 minutes

**Description:** Remove all 23 deprecated files

**Files to Delete:**
(See complete list at top of spec)

**Acceptance Criteria:**
- [ ] All 23 files deleted
- [ ] Verify no broken links remain
- [ ] Git commit with clear message

---

### Task 5.2: Update VitePress config
**Type:** UPDATE
**Priority:** HIGH
**Estimated Time:** 1 hour

**Description:** Update sidebar navigation for new structure

**File:** `/docs/.vitepress/config.mjs`

**New Sidebar Structure:**
```javascript
sidebar: [
  {
    text: "Introduction",
    items: [
      { text: "What is Stickle?", link: "/guide/index" },
    ],
  },
  {
    text: "Getting Started",
    items: [
      { text: "Installation", link: "/guide/installation" },
      { text: "Quick Start", link: "/guide/quick-start" },
      { text: "Configuration", link: "/guide/configuration" },
    ],
  },
  {
    text: "Core Features",
    items: [
      { text: "Tracking Attributes", link: "/guide/tracking-attributes" },
      { text: "Customer Segments", link: "/guide/segments" },
      { text: "Filters", link: "/guide/filters" },
      { text: "Event Listeners", link: "/guide/event-listeners" },
      { text: "JavaScript Tracking", link: "/guide/javascript-tracking" },
      { text: "StickleUI", link: "/guide/stickle-ui" },
    ],
  },
  {
    text: "Reference",
    items: [
      { text: "API Endpoints", link: "/guide/api-endpoints" },
      { text: "Filter Reference", link: "/guide/filter-reference" },
      { text: "Events Reference", link: "/guide/events-reference" },
    ],
  },
  {
    text: "Advanced",
    items: [
      { text: "Recipes", link: "/guide/recipes" },
      { text: "Deployment", link: "/guide/deployment" },
      { text: "Troubleshooting", link: "/guide/troubleshooting" },
    ],
  },
]
```

**Acceptance Criteria:**
- [ ] Sidebar matches new structure
- [ ] All links work
- [ ] Sections properly collapsed/expanded
- [ ] No 404s

---

### Task 5.3: Fix internal links
**Type:** UPDATE
**Priority:** HIGH
**Estimated Time:** 1 hour

**Description:** Update all internal documentation links

**Process:**
1. Search for all markdown links in all 16 files
2. Update links to point to new file names
3. Test all links work

**Common Link Updates:**
- `/guide/eloquent-methods` → `/guide/filters`
- `/guide/creating-segments` → `/guide/segments`
- `/guide/ui-getting-started` → `/guide/stickle-ui`
- etc.

**Acceptance Criteria:**
- [ ] All internal links updated
- [ ] No 404 errors
- [ ] No dead links

---

### Task 5.4: Final proofread
**Type:** QUALITY
**Priority:** MEDIUM
**Estimated Time:** 2 hours

**Description:** Read through all 16 files for quality

**Checklist per file:**
- [ ] No typos
- [ ] No grammatical errors
- [ ] Code examples are correct
- [ ] Formatting is consistent
- [ ] Headings follow hierarchy
- [ ] Links work
- [ ] No TODO comments
- [ ] No draft notes

**Acceptance Criteria:**
- [ ] All 16 files proofread
- [ ] All issues fixed
- [ ] Ready for release

---

## Execution Timeline

### Day 1-2: Core Structure
- Task 1.1: Create index.md (2h)
- Task 1.2: Create quick-start.md (3h)
- Task 1.3: Update installation.md (1h)
- Task 1.4: Keep configuration.md (15m)

### Day 3-4: Core Features Part 1
- Task 2.1: Create tracking-attributes.md (4h)
- Task 2.2: Create segments.md (3h)
- Task 2.3: Create filters.md (4h)

### Day 4-5: Core Features Part 2
- Task 2.4: Create event-listeners.md (4h)
- Task 2.5: Create javascript-tracking.md (2h)
- Task 2.6: Create stickle-ui.md (3h)

### Day 6: Reference
- Task 3.1: Rename endpoints.md (5m)
- Task 3.2: Create filter-reference.md (2h)
- Task 3.3: Create events-reference.md (2h)

### Day 7: Advanced
- Task 4.1: Create recipes.md (3h)
- Task 4.2: Create deployment.md (2h)
- Task 4.3: Create troubleshooting.md (2h)

### Day 8: Cleanup
- Task 5.1: Delete deprecated files (30m)
- Task 5.2: Update VitePress config (1h)
- Task 5.3: Fix internal links (1h)
- Task 5.4: Final proofread (2h)

---

## Risk Assessment

### High Risk
- **Merging content**: May lose important information
  - *Mitigation*: Careful review of each source file before deletion

- **Broken links**: Internal links may break
  - *Mitigation*: Comprehensive link check in Phase 5

### Medium Risk
- **Timeline**: 8 days is tight
  - *Mitigation*: Can extend if needed, quality over speed

### Low Risk
- **Content quality**: May need iteration
  - *Mitigation*: Final proofread phase built in

---

## Success Metrics

### Quantitative
- ✅ 16 files (down from 30)
- ✅ 0 empty files
- ✅ 0 stub files
- ✅ 0 broken links
- ✅ 100% proofread

### Qualitative
- ✅ Clear learning path
- ✅ Complete code examples
- ✅ Easy to navigate
- ✅ Professional quality
- ✅ User can go from zero to working Stickle in 30 minutes

---

## Notes

### Writing Standards
- Use second person ("you")
- Active voice
- Complete code examples
- No theory without practice
- Show before tell

### Code Example Standards
- Must be complete (copy-paste ready)
- Include namespace imports
- Include type hints
- Follow PSR-12
- Add comments where helpful

### Markdown Standards
- Use `#` for title (one per page)
- Use `##` for major sections
- Use `###` for subsections
- Code blocks must specify language
- Use tables for reference data
- Use callouts (:::tip, :::warning) sparingly
