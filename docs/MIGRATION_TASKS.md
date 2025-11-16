# Documentation Migration Task List

**Status Legend:** ⬜ Not Started | 🟦 In Progress | ✅ Complete

---

## 📋 PHASE 1: Core Structure (Day 1-2)

### ✅ Task 1.1: Create index.md
**Estimate:** 2 hours | **Priority:** HIGH

**Checklist:**
- [x] Create `/docs/guide/index.md`
- [x] Write "What is Stickle?" section (2 paragraphs)
- [x] Add key features section (6 bullet points from homepage)
- [x] Add "When to Use Stickle" section (from use-cases.md)
- [x] Add "Next Steps" with link to quick-start
- [x] Proofread for clarity

**Sources:**
- `/docs/index.md` (lines 17-29)
- `guide/use-cases.md`
- `/README.md` (lines 5-20)

---

### ✅ Task 1.2: Create quick-start.md
**Estimate:** 3 hours | **Priority:** HIGH

**Checklist:**
- [x] Create `/docs/guide/quick-start.md`
- [x] Write prerequisites section
- [x] Write Step 1: Add StickleEntity trait (complete code)
- [x] Write Step 2: Track first attribute (complete code)
- [x] Write Step 3: Create first segment (complete code)
- [x] Write Step 4: View in StickleUI (with descriptions)
- [x] Add "Next Steps" section
- [x] Test all code examples compile
- [x] Verify tutorial takes ~15 minutes

**Sources:**
- `guide/getting-started.md` (lines 9-51)

---

### ✅ Task 1.3: Update installation.md
**Estimate:** 1 hour | **Priority:** MEDIUM

**Checklist:**
- [x] Open `guide/installation.md`
- [x] Remove duplicate "Getting Started" heading (line 5)
- [x] Verify prerequisites section is clear
- [x] Verify installation commands are correct
- [x] Keep migration & scheduled tasks content
- [x] Proofread

---

### ✅ Task 1.4: Verify configuration.md
**Estimate:** 15 minutes | **Priority:** LOW

**Checklist:**
- [x] Open `guide/configuration.md`
- [x] Proofread for typos
- [x] Verify all config options are current
- [x] Verify examples are correct
- [x] No changes needed (keep as-is)

---

## 📋 PHASE 2: Core Features (Day 3-5)

### ✅ Task 2.1: Create tracking-attributes.md
**Estimate:** 4 hours | **Priority:** HIGH

**Checklist:**
- [x] Create `/docs/guide/tracking-attributes.md`
- [x] Write "What are Attributes?" section
- [x] Write "Observed vs Tracked Attributes" section
- [x] Write "Defining Trackable Attributes" section
  - [x] stickleObservedAttributes() method
  - [x] stickleTrackedAttributes() method
- [x] Write "Accessing Attributes" section
  - [x] stickleAttribute() method
  - [x] trackable_attributes accessor
- [x] Write "Attribute Types" section
  - [x] Numeric attributes
  - [x] Text attributes
  - [x] Boolean attributes
  - [x] Date attributes
- [x] Write "Parent-Child Aggregation" section (Eloquent, no SQL)
- [x] Add 3-4 complete code examples
- [x] Proofread

**Sources:**
- `tracking-model-attributes.md` (intro)
- `aggregate-attributes.md` (lines 5-28)
- `macros.md` (lines 62-111, 82-110)

**After Completion:**
- [x] Delete `tracking-model-attributes.md`
- [x] Delete `aggregate-attributes.md`

---

### ✅ Task 2.2: Create segments.md
**Estimate:** 3 hours | **Priority:** HIGH

**Checklist:**
- [x] Create `/docs/guide/segments.md`
- [x] Write "What are Segments?" section
- [x] Write "Creating Segment Classes" section
  - [x] Basic example
  - [x] StickleSegmentMetadata attribute
- [x] Write "Using Filters in Segments" section
- [x] Write "Common Segment Examples" section
  - [x] Active users example
  - [x] High-value customers example
  - [x] At-risk customers example
  - [x] Trial users example
  - [x] Power users example
- [x] Write "How Stickle Tracks Segments" section
- [x] Write "Querying by Segment" section
- [x] Fix typo: "Trickle" → "Stickle" (what-are-segments.md line 19)
- [x] Fix typo: "Stitch" → "Stickle" (creating-segments.md line 48)
- [x] Fix typo: "longr" → "longer" (creating-segments.md line 50)
- [x] Proofread

**Sources:**
- `what-are-segments.md`
- `creating-segments.md`
- `tracking-segments.md`

**After Completion:**
- [x] Delete `what-are-segments.md`
- [x] Delete `creating-segments.md`
- [x] Delete `tracking-segments.md`

---

### ✅ Task 2.3: Create filters.md
**Estimate:** 4 hours | **Priority:** HIGH

**Checklist:**
- [x] Create `/docs/guide/filters.md`
- [x] Write "Overview" section (stickleWhere/stickleOrWhere)
- [x] Write "Filter Types" section
  - [x] Boolean filters (with examples)
  - [x] Date filters (with examples)
  - [x] Datetime filters (with examples)
  - [x] EventCount filters (with examples)
  - [x] Number filters (with examples)
  - [x] RequestCount filters (with examples)
  - [x] SessionCount filters (with examples)
  - [x] Text filters (with examples)
- [x] Write "Creating Custom Scopes" section
- [x] Write "Performance Tips" section
- [x] Add complex filtering examples
- [x] Proofread

**Sources:**
- `eloquent-methods.md` (entire file)
- `scopes.md` (all content)
- `macros.md` (lines 9-57)

**After Completion:**
- [x] Delete `eloquent-methods.md`
- [x] Delete `scopes.md`
- [x] Delete `macros.md`

---

### ✅ Task 2.4: Create event-listeners.md
**Estimate:** 4 hours | **Priority:** HIGH

**Checklist:**
- [x] Create `/docs/guide/event-listeners.md`
- [x] Write "Events in Stickle" overview section
- [x] Write "User Events (Track)" section
  - [x] How to create custom events
  - [x] Listening to user events
  - [x] Complete example
- [x] Write "Page View Events (Page)" section
  - [x] How page views work
  - [x] Listening to page views
  - [x] Complete example
- [x] Write "Attribute Change Events" section
  - [x] When triggered
  - [x] Listening to attribute changes
  - [x] Complete example
- [x] Write "Segment Events" section
  - [x] ObjectEnteredSegment
  - [x] ObjectExitedSegment
  - [x] Complete examples
- [x] Write "Authentication Events" section
  - [x] Illuminate\Auth events
  - [x] Configuration
  - [x] Complete example
- [x] Write "Real-World Examples" section
  - [x] Send email on segment entry
  - [x] Slack notification on event
  - [x] Update CRM on attribute change
- [x] Remove draft notes from listeners-page-views.md (lines 28-34)
- [x] Remove draft notes from listeners-segment-events.md (lines 31-37)
- [x] Fix typo: `Illuminate\Aut\` → `Illuminate\Auth\` (listeners-illuminate-auth-events.md line 16)
- [x] Proofread

**Sources:**
- `listeners-user-events.md`
- `user-events.md`
- `listeners-page-views.md`
- `listeners-segment-events.md`
- `listeners-model-attribute-changes.md`
- `listeners-illuminate-auth-events.md`
- `illuminate-auth-events.md`

**After Completion:**
- [x] Delete `listeners-user-events.md`
- [x] Delete `user-events.md`
- [x] Delete `listeners-page-views.md`
- [x] Delete `listeners-segment-events.md`
- [x] Delete `listeners-model-attribute-changes.md`
- [x] Delete `listeners-illuminate-auth-events.md`
- [x] Delete `illuminate-auth-events.md`

---

### ✅ Task 2.5: Create javascript-tracking.md
**Estimate:** 2 hours | **Priority:** MEDIUM

**Checklist:**
- [x] Create `/docs/guide/javascript-tracking.md`
- [x] Write "Overview" section (client vs server)
- [x] Write "Client-Side Tracking" section
  - [x] How it's injected
  - [x] stickle.page()
  - [x] stickle.track()
  - [x] Configuration options
- [x] Write "Server-Side Tracking" section
  - [x] How it works
  - [x] Configuration
  - [x] When to use
- [x] Write "SPA Frameworks" section
  - [x] Livewire
  - [x] Inertia.js
  - [x] Vue.js
  - [x] React
- [x] Write "Configuration" section
- [x] Proofread

**Sources:**
- `javascript-sdk.md`
- `request-middleware.md`

**After Completion:**
- [x] Delete `request-middleware.md`

---

### ✅ Task 2.6: Create stickle-ui.md
**Estimate:** 3 hours | **Priority:** MEDIUM

**Checklist:**
- [x] Create `/docs/guide/stickle-ui.md`
- [x] Write "What is StickleUI?" section
- [x] Write "Accessing the Dashboard" section
- [x] Write "Navigation Overview" section
- [x] Write "Customization" section
  - [x] Theming
  - [x] Adding custom pages
  - [x] Overriding components
- [x] Keep it simple (not overly detailed)
- [x] Proofread

**Sources:**
- `ui-getting-started.md`
- `ui-customizing.md` (currently empty - write new content)

**After Completion:**
- [x] Delete `ui-getting-started.md`
- [x] Delete `ui-customizing.md`

---

## 📋 PHASE 3: Reference Documentation (Day 6)

### ✅ Task 3.1: Rename endpoints.md
**Estimate:** 5 minutes | **Priority:** LOW

**Checklist:**
- [x] Rename `guide/endpoints.md` to `guide/api-endpoints.md`
- [x] Search for links to `/guide/endpoints` in all docs
- [x] Update any links to `/guide/api-endpoints`
- [x] Verify no broken links

---

### ✅ Task 3.2: Create filter-reference.md
**Estimate:** 2 hours | **Priority:** MEDIUM

**Checklist:**
- [x] Create `/docs/guide/filter-reference.md`
- [x] Create quick reference table of all filter types
- [x] Document Boolean filters (methods & parameters)
- [x] Document Date filters (methods & parameters)
- [x] Document Datetime filters (methods & parameters)
- [x] Document EventCount filters (methods & parameters)
- [x] Document Number filters (methods & parameters)
- [x] Document RequestCount filters (methods & parameters)
- [x] Document SessionCount filters (methods & parameters)
- [x] Document Text filters (methods & parameters)
- [x] Add "See Also" link to filters.md
- [x] Proofread

**Sources:**
- `eloquent-methods.md` (lines 39-346)

---

### ✅ Task 3.3: Create events-reference.md
**Estimate:** 2 hours | **Priority:** MEDIUM

**Checklist:**
- [x] Create `/docs/guide/events-reference.md`
- [x] Write overview section
- [x] Create event index table
- [x] Document Page event
  - [x] Class signature
  - [x] Properties
  - [x] Example payload
- [x] Document Track event
  - [x] Class signature
  - [x] Properties
  - [x] Example payload
- [x] Document ObjectAttributeChanged event
- [x] Document ObjectEnteredSegment event
- [x] Document ObjectExitedSegment event
- [x] Add "See Also" link to event-listeners.md
- [x] Proofread

**Sources:**
- Extract from event-listeners.md

---

## 📋 PHASE 4: Advanced Documentation (Day 7)

### ✅ Task 4.1: Create recipes.md
**Estimate:** 3 hours | **Priority:** MEDIUM

**Checklist:**
- [x] Create `/docs/guide/recipes.md`
- [x] Write "Track MRR" recipe (complete code)
- [x] Write "Identify Churning Customers" recipe (complete code)
- [x] Write "Find Power Users" recipe (complete code)
- [x] Write "Send Email on Segment Entry" recipe (complete code)
- [x] Write "Track Feature Adoption" recipe (complete code)
- [x] Write "Calculate Customer Health Score" recipe (complete code)
- [x] Test all code examples
- [x] Proofread

---

### ✅ Task 4.2: Create deployment.md
**Estimate:** 2 hours | **Priority:** MEDIUM

**Checklist:**
- [x] Create `/docs/guide/deployment.md`
- [x] Write "Production Checklist" section
- [x] Write "Queue Workers" section
- [x] Write "WebSockets (Reverb/Pusher)" section
- [x] Write "Database Optimization" section
  - [x] Indexing recommendations
  - [x] Partition strategy
- [x] Write "Scheduled Tasks" section
- [x] Write "Monitoring" section
- [x] Proofread

**Sources:**
- Extract from `installation.md` (scheduled tasks, reverb)
- Extract from `configuration.md` (production settings)

---

### ✅ Task 4.3: Create troubleshooting.md
**Estimate:** 2 hours | **Priority:** MEDIUM

**Checklist:**
- [x] Create `/docs/guide/troubleshooting.md`
- [x] Write "Tracking Not Working" issue
- [x] Write "StickleUI Not Loading" issue
- [x] Write "Performance Issues" issue
- [x] Write "WebSocket Connection Failed" issue
- [x] Write "Migrations Failing" issue
- [x] Write "Debugging" section
  - [x] Enable debug mode
  - [x] Checking logs
  - [x] Database queries
- [x] Write "Getting Help" section
- [x] Proofread

---

## 📋 PHASE 5: Cleanup & Finalization (Day 8)

### ✅ Task 5.1: Delete deprecated files
**Estimate:** 30 minutes | **Priority:** HIGH

**Checklist:**
- [x] Delete `guide/__webhooks.md`
- [x] Delete `guide/3rd-party-integrations.md`
- [x] Delete `guide/aggregate-attributes.md`
- [x] Delete `guide/creating-segments.md`
- [x] Delete `guide/eloquent-methods.md`
- [x] Delete `guide/getting-started.md`
- [x] Delete `guide/illuminate-auth-events.md`
- [x] Delete `guide/listeners-illuminate-auth-events.md`
- [x] Delete `guide/listeners-model-attribute-changes.md`
- [x] Delete `guide/listeners-page-views.md`
- [x] Delete `guide/listeners-segment-events.md`
- [x] Delete `guide/listeners-user-events.md`
- [x] Delete `guide/macros.md`
- [x] Delete `guide/quickstart.md`
- [x] Delete `guide/request-middleware.md`
- [x] Delete `guide/scopes.md`
- [x] Delete `guide/tracking-model-attributes.md`
- [x] Delete `guide/tracking-segments.md`
- [x] Delete `guide/ui-customizing.md`
- [x] Delete `guide/ui-getting-started.md`
- [x] Delete `guide/use-cases.md`
- [x] Delete `guide/user-events.md`
- [x] Delete `guide/what-are-segments.md`
- [x] Verify 16 files remain
- [x] Commit with message: "Remove deprecated documentation files"

---

### ✅ Task 5.2: Update VitePress config
**Estimate:** 1 hour | **Priority:** HIGH

**Checklist:**
- [x] Open `/docs/.vitepress/config.mjs`
- [x] Update sidebar with new structure:
  - [x] Introduction section (1 item)
  - [x] Getting Started section (3 items)
  - [x] Core Features section (6 items)
  - [x] Reference section (3 items)
  - [x] Advanced section (3 items)
- [x] Remove old sidebar items
- [x] Update rewrites if needed
- [x] Test locally (npm run docs:dev)
- [x] Verify all sidebar links work
- [x] Verify no 404s
- [x] Commit changes

---

### ✅ Task 5.3: Fix internal links
**Estimate:** 1 hour | **Priority:** HIGH

**Checklist:**
- [x] Search all files for `/guide/eloquent-methods` → update to `/guide/filters`
- [x] Search all files for `/guide/creating-segments` → update to `/guide/segments`
- [x] Search all files for `/guide/what-are-segments` → update to `/guide/segments`
- [x] Search all files for `/guide/ui-getting-started` → update to `/guide/stickle-ui`
- [x] Search all files for `/guide/use-cases` → update to `/guide/index`
- [x] Search all files for `/guide/tracking-model-attributes` → update to `/guide/tracking-attributes`
- [x] Search all files for `/guide/scopes` → update to `/guide/filters`
- [x] Search all files for other old file names
- [x] Test all internal links
- [x] Verify no 404s
- [x] Commit changes

---

### ✅ Task 5.4: Final proofread
**Estimate:** 2 hours | **Priority:** MEDIUM

**Checklist:**
- [x] Proofread `index.md`
  - [x] No typos
  - [x] No grammatical errors
  - [x] Code examples correct
  - [x] Formatting consistent
  - [x] Links work
- [x] Proofread `installation.md`
- [x] Proofread `quick-start.md`
- [x] Proofread `configuration.md`
- [x] Proofread `tracking-attributes.md`
- [x] Proofread `segments.md`
- [x] Proofread `filters.md`
- [x] Proofread `event-listeners.md`
- [x] Proofread `javascript-tracking.md`
- [x] Proofread `stickle-ui.md`
- [x] Proofread `api-endpoints.md`
- [x] Proofread `filter-reference.md`
- [x] Proofread `events-reference.md`
- [x] Proofread `recipes.md`
- [x] Proofread `deployment.md`
- [x] Proofread `troubleshooting.md`
- [x] All 16 files proofread
- [x] No TODO comments remain
- [x] No draft notes remain
- [x] Commit changes

---

## ✅ COMPLETION CHECKLIST

### Documentation Quality
- [x] 16 documentation files exist
- [x] 0 empty/stub files
- [x] All code examples are complete and tested
- [x] All internal links work (no 404s)
- [x] All typos corrected
- [x] Consistent formatting throughout
- [x] No TODO or draft notes

### Navigation
- [x] VitePress sidebar updated
- [x] All sidebar links work
- [x] Clear learning progression
- [x] Easy to find information

### Content Coverage
- [x] Introduction explains what Stickle is
- [x] Quick start gets user working in 15 min
- [x] All core features documented
- [x] Reference documentation complete
- [x] Advanced topics covered
- [x] Real-world examples included

### Final Steps
- [x] Build docs locally (npm run docs:build)
- [x] No build errors
- [x] Preview looks good (npm run docs:preview)
- [ ] Create PR with all changes
- [ ] Request review
- [ ] Merge to main
- [ ] Deploy documentation

---

## 📊 PROGRESS TRACKING

**Total Tasks:** 24
**Completed:** 24
**In Progress:** 0
**Not Started:** 0

**Estimated Total Time:** ~40 hours (8 days)
**Actual Time Spent:** ~40 hours

---

## 🚀 READY TO START?

1. Review this task list
2. Set up local environment (npm install, npm run docs:dev)
3. Start with Phase 1, Task 1.1
4. Mark tasks as you complete them
5. Commit frequently
6. Test after each phase

Good luck! 🎉
