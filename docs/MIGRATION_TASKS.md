# Documentation Migration Task List

**Status Legend:** ⬜ Not Started | 🟦 In Progress | ✅ Complete

---

## 📋 PHASE 1: Core Structure (Day 1-2)

### ⬜ Task 1.1: Create index.md
**Estimate:** 2 hours | **Priority:** HIGH

**Checklist:**
- [ ] Create `/docs/guide/index.md`
- [ ] Write "What is Stickle?" section (2 paragraphs)
- [ ] Add key features section (6 bullet points from homepage)
- [ ] Add "When to Use Stickle" section (from use-cases.md)
- [ ] Add "Next Steps" with link to quick-start
- [ ] Proofread for clarity

**Sources:**
- `/docs/index.md` (lines 17-29)
- `guide/use-cases.md`
- `/README.md` (lines 5-20)

---

### ⬜ Task 1.2: Create quick-start.md
**Estimate:** 3 hours | **Priority:** HIGH

**Checklist:**
- [ ] Create `/docs/guide/quick-start.md`
- [ ] Write prerequisites section
- [ ] Write Step 1: Add StickleEntity trait (complete code)
- [ ] Write Step 2: Track first attribute (complete code)
- [ ] Write Step 3: Create first segment (complete code)
- [ ] Write Step 4: View in StickleUI (with descriptions)
- [ ] Add "Next Steps" section
- [ ] Test all code examples compile
- [ ] Verify tutorial takes ~15 minutes

**Sources:**
- `guide/getting-started.md` (lines 9-51)

---

### ⬜ Task 1.3: Update installation.md
**Estimate:** 1 hour | **Priority:** MEDIUM

**Checklist:**
- [ ] Open `guide/installation.md`
- [ ] Remove duplicate "Getting Started" heading (line 5)
- [ ] Verify prerequisites section is clear
- [ ] Verify installation commands are correct
- [ ] Keep migration & scheduled tasks content
- [ ] Proofread

---

### ⬜ Task 1.4: Verify configuration.md
**Estimate:** 15 minutes | **Priority:** LOW

**Checklist:**
- [ ] Open `guide/configuration.md`
- [ ] Proofread for typos
- [ ] Verify all config options are current
- [ ] Verify examples are correct
- [ ] No changes needed (keep as-is)

---

## 📋 PHASE 2: Core Features (Day 3-5)

### ⬜ Task 2.1: Create tracking-attributes.md
**Estimate:** 4 hours | **Priority:** HIGH

**Checklist:**
- [ ] Create `/docs/guide/tracking-attributes.md`
- [ ] Write "What are Attributes?" section
- [ ] Write "Observed vs Tracked Attributes" section
- [ ] Write "Defining Trackable Attributes" section
  - [ ] stickleObservedAttributes() method
  - [ ] stickleTrackedAttributes() method
- [ ] Write "Accessing Attributes" section
  - [ ] stickleAttribute() method
  - [ ] trackable_attributes accessor
- [ ] Write "Attribute Types" section
  - [ ] Numeric attributes
  - [ ] Text attributes
  - [ ] Boolean attributes
  - [ ] Date attributes
- [ ] Write "Parent-Child Aggregation" section (Eloquent, no SQL)
- [ ] Add 3-4 complete code examples
- [ ] Proofread

**Sources:**
- `tracking-model-attributes.md` (intro)
- `aggregate-attributes.md` (lines 5-28)
- `macros.md` (lines 62-111, 82-110)

**After Completion:**
- [ ] Delete `tracking-model-attributes.md`
- [ ] Delete `aggregate-attributes.md`

---

### ⬜ Task 2.2: Create segments.md
**Estimate:** 3 hours | **Priority:** HIGH

**Checklist:**
- [ ] Create `/docs/guide/segments.md`
- [ ] Write "What are Segments?" section
- [ ] Write "Creating Segment Classes" section
  - [ ] Basic example
  - [ ] StickleSegmentMetadata attribute
- [ ] Write "Using Filters in Segments" section
- [ ] Write "Common Segment Examples" section
  - [ ] Active users example
  - [ ] High-value customers example
  - [ ] At-risk customers example
  - [ ] Trial users example
  - [ ] Power users example
- [ ] Write "How Stickle Tracks Segments" section
- [ ] Write "Querying by Segment" section
- [ ] Fix typo: "Trickle" → "Stickle" (what-are-segments.md line 19)
- [ ] Fix typo: "Stitch" → "Stickle" (creating-segments.md line 48)
- [ ] Fix typo: "longr" → "longer" (creating-segments.md line 50)
- [ ] Proofread

**Sources:**
- `what-are-segments.md`
- `creating-segments.md`
- `tracking-segments.md`

**After Completion:**
- [ ] Delete `what-are-segments.md`
- [ ] Delete `creating-segments.md`
- [ ] Delete `tracking-segments.md`

---

### ⬜ Task 2.3: Create filters.md
**Estimate:** 4 hours | **Priority:** HIGH

**Checklist:**
- [ ] Create `/docs/guide/filters.md`
- [ ] Write "Overview" section (stickleWhere/stickleOrWhere)
- [ ] Write "Filter Types" section
  - [ ] Boolean filters (with examples)
  - [ ] Date filters (with examples)
  - [ ] Datetime filters (with examples)
  - [ ] EventCount filters (with examples)
  - [ ] Number filters (with examples)
  - [ ] RequestCount filters (with examples)
  - [ ] SessionCount filters (with examples)
  - [ ] Text filters (with examples)
- [ ] Write "Creating Custom Scopes" section
- [ ] Write "Performance Tips" section
- [ ] Add complex filtering examples
- [ ] Proofread

**Sources:**
- `eloquent-methods.md` (entire file)
- `scopes.md` (all content)
- `macros.md` (lines 9-57)

**After Completion:**
- [ ] Delete `eloquent-methods.md`
- [ ] Delete `scopes.md`
- [ ] Delete `macros.md`

---

### ⬜ Task 2.4: Create event-listeners.md
**Estimate:** 4 hours | **Priority:** HIGH

**Checklist:**
- [ ] Create `/docs/guide/event-listeners.md`
- [ ] Write "Events in Stickle" overview section
- [ ] Write "User Events (Track)" section
  - [ ] How to create custom events
  - [ ] Listening to user events
  - [ ] Complete example
- [ ] Write "Page View Events (Page)" section
  - [ ] How page views work
  - [ ] Listening to page views
  - [ ] Complete example
- [ ] Write "Attribute Change Events" section
  - [ ] When triggered
  - [ ] Listening to attribute changes
  - [ ] Complete example
- [ ] Write "Segment Events" section
  - [ ] ObjectEnteredSegment
  - [ ] ObjectExitedSegment
  - [ ] Complete examples
- [ ] Write "Authentication Events" section
  - [ ] Illuminate\Auth events
  - [ ] Configuration
  - [ ] Complete example
- [ ] Write "Real-World Examples" section
  - [ ] Send email on segment entry
  - [ ] Slack notification on event
  - [ ] Update CRM on attribute change
- [ ] Remove draft notes from listeners-page-views.md (lines 28-34)
- [ ] Remove draft notes from listeners-segment-events.md (lines 31-37)
- [ ] Fix typo: `Illuminate\Aut\` → `Illuminate\Auth\` (listeners-illuminate-auth-events.md line 16)
- [ ] Proofread

**Sources:**
- `listeners-user-events.md`
- `user-events.md`
- `listeners-page-views.md`
- `listeners-segment-events.md`
- `listeners-model-attribute-changes.md`
- `listeners-illuminate-auth-events.md`
- `illuminate-auth-events.md`

**After Completion:**
- [ ] Delete `listeners-user-events.md`
- [ ] Delete `user-events.md`
- [ ] Delete `listeners-page-views.md`
- [ ] Delete `listeners-segment-events.md`
- [ ] Delete `listeners-model-attribute-changes.md`
- [ ] Delete `listeners-illuminate-auth-events.md`
- [ ] Delete `illuminate-auth-events.md`

---

### ⬜ Task 2.5: Create javascript-tracking.md
**Estimate:** 2 hours | **Priority:** MEDIUM

**Checklist:**
- [ ] Create `/docs/guide/javascript-tracking.md`
- [ ] Write "Overview" section (client vs server)
- [ ] Write "Client-Side Tracking" section
  - [ ] How it's injected
  - [ ] stickle.page()
  - [ ] stickle.track()
  - [ ] Configuration options
- [ ] Write "Server-Side Tracking" section
  - [ ] How it works
  - [ ] Configuration
  - [ ] When to use
- [ ] Write "SPA Frameworks" section
  - [ ] Livewire
  - [ ] Inertia.js
  - [ ] Vue.js
  - [ ] React
- [ ] Write "Configuration" section
- [ ] Proofread

**Sources:**
- `javascript-sdk.md`
- `request-middleware.md`

**After Completion:**
- [ ] Delete `request-middleware.md`

---

### ⬜ Task 2.6: Create stickle-ui.md
**Estimate:** 3 hours | **Priority:** MEDIUM

**Checklist:**
- [ ] Create `/docs/guide/stickle-ui.md`
- [ ] Write "What is StickleUI?" section
- [ ] Write "Accessing the Dashboard" section
- [ ] Write "Navigation Overview" section
- [ ] Write "Customization" section
  - [ ] Theming
  - [ ] Adding custom pages
  - [ ] Overriding components
- [ ] Keep it simple (not overly detailed)
- [ ] Proofread

**Sources:**
- `ui-getting-started.md`
- `ui-customizing.md` (currently empty - write new content)

**After Completion:**
- [ ] Delete `ui-getting-started.md`
- [ ] Delete `ui-customizing.md`

---

## 📋 PHASE 3: Reference Documentation (Day 6)

### ⬜ Task 3.1: Rename endpoints.md
**Estimate:** 5 minutes | **Priority:** LOW

**Checklist:**
- [ ] Rename `guide/endpoints.md` to `guide/api-endpoints.md`
- [ ] Search for links to `/guide/endpoints` in all docs
- [ ] Update any links to `/guide/api-endpoints`
- [ ] Verify no broken links

---

### ⬜ Task 3.2: Create filter-reference.md
**Estimate:** 2 hours | **Priority:** MEDIUM

**Checklist:**
- [ ] Create `/docs/guide/filter-reference.md`
- [ ] Create quick reference table of all filter types
- [ ] Document Boolean filters (methods & parameters)
- [ ] Document Date filters (methods & parameters)
- [ ] Document Datetime filters (methods & parameters)
- [ ] Document EventCount filters (methods & parameters)
- [ ] Document Number filters (methods & parameters)
- [ ] Document RequestCount filters (methods & parameters)
- [ ] Document SessionCount filters (methods & parameters)
- [ ] Document Text filters (methods & parameters)
- [ ] Add "See Also" link to filters.md
- [ ] Proofread

**Sources:**
- `eloquent-methods.md` (lines 39-346)

---

### ⬜ Task 3.3: Create events-reference.md
**Estimate:** 2 hours | **Priority:** MEDIUM

**Checklist:**
- [ ] Create `/docs/guide/events-reference.md`
- [ ] Write overview section
- [ ] Create event index table
- [ ] Document Page event
  - [ ] Class signature
  - [ ] Properties
  - [ ] Example payload
- [ ] Document Track event
  - [ ] Class signature
  - [ ] Properties
  - [ ] Example payload
- [ ] Document ObjectAttributeChanged event
- [ ] Document ObjectEnteredSegment event
- [ ] Document ObjectExitedSegment event
- [ ] Add "See Also" link to event-listeners.md
- [ ] Proofread

**Sources:**
- Extract from event-listeners.md

---

## 📋 PHASE 4: Advanced Documentation (Day 7)

### ⬜ Task 4.1: Create recipes.md
**Estimate:** 3 hours | **Priority:** MEDIUM

**Checklist:**
- [ ] Create `/docs/guide/recipes.md`
- [ ] Write "Track MRR" recipe (complete code)
- [ ] Write "Identify Churning Customers" recipe (complete code)
- [ ] Write "Find Power Users" recipe (complete code)
- [ ] Write "Send Email on Segment Entry" recipe (complete code)
- [ ] Write "Track Feature Adoption" recipe (complete code)
- [ ] Write "Calculate Customer Health Score" recipe (complete code)
- [ ] Test all code examples
- [ ] Proofread

---

### ⬜ Task 4.2: Create deployment.md
**Estimate:** 2 hours | **Priority:** MEDIUM

**Checklist:**
- [ ] Create `/docs/guide/deployment.md`
- [ ] Write "Production Checklist" section
- [ ] Write "Queue Workers" section
- [ ] Write "WebSockets (Reverb/Pusher)" section
- [ ] Write "Database Optimization" section
  - [ ] Indexing recommendations
  - [ ] Partition strategy
- [ ] Write "Scheduled Tasks" section
- [ ] Write "Monitoring" section
- [ ] Proofread

**Sources:**
- Extract from `installation.md` (scheduled tasks, reverb)
- Extract from `configuration.md` (production settings)

---

### ⬜ Task 4.3: Create troubleshooting.md
**Estimate:** 2 hours | **Priority:** MEDIUM

**Checklist:**
- [ ] Create `/docs/guide/troubleshooting.md`
- [ ] Write "Tracking Not Working" issue
- [ ] Write "StickleUI Not Loading" issue
- [ ] Write "Performance Issues" issue
- [ ] Write "WebSocket Connection Failed" issue
- [ ] Write "Migrations Failing" issue
- [ ] Write "Debugging" section
  - [ ] Enable debug mode
  - [ ] Checking logs
  - [ ] Database queries
- [ ] Write "Getting Help" section
- [ ] Proofread

---

## 📋 PHASE 5: Cleanup & Finalization (Day 8)

### ⬜ Task 5.1: Delete deprecated files
**Estimate:** 30 minutes | **Priority:** HIGH

**Checklist:**
- [ ] Delete `guide/__webhooks.md`
- [ ] Delete `guide/3rd-party-integrations.md`
- [ ] Delete `guide/aggregate-attributes.md`
- [ ] Delete `guide/creating-segments.md`
- [ ] Delete `guide/eloquent-methods.md`
- [ ] Delete `guide/getting-started.md`
- [ ] Delete `guide/illuminate-auth-events.md`
- [ ] Delete `guide/listeners-illuminate-auth-events.md`
- [ ] Delete `guide/listeners-model-attribute-changes.md`
- [ ] Delete `guide/listeners-page-views.md`
- [ ] Delete `guide/listeners-segment-events.md`
- [ ] Delete `guide/listeners-user-events.md`
- [ ] Delete `guide/macros.md`
- [ ] Delete `guide/quickstart.md`
- [ ] Delete `guide/request-middleware.md`
- [ ] Delete `guide/scopes.md`
- [ ] Delete `guide/tracking-model-attributes.md`
- [ ] Delete `guide/tracking-segments.md`
- [ ] Delete `guide/ui-customizing.md`
- [ ] Delete `guide/ui-getting-started.md`
- [ ] Delete `guide/use-cases.md`
- [ ] Delete `guide/user-events.md`
- [ ] Delete `guide/what-are-segments.md`
- [ ] Verify 16 files remain
- [ ] Commit with message: "Remove deprecated documentation files"

---

### ⬜ Task 5.2: Update VitePress config
**Estimate:** 1 hour | **Priority:** HIGH

**Checklist:**
- [ ] Open `/docs/.vitepress/config.mjs`
- [ ] Update sidebar with new structure:
  - [ ] Introduction section (1 item)
  - [ ] Getting Started section (3 items)
  - [ ] Core Features section (6 items)
  - [ ] Reference section (3 items)
  - [ ] Advanced section (3 items)
- [ ] Remove old sidebar items
- [ ] Update rewrites if needed
- [ ] Test locally (npm run docs:dev)
- [ ] Verify all sidebar links work
- [ ] Verify no 404s
- [ ] Commit changes

---

### ⬜ Task 5.3: Fix internal links
**Estimate:** 1 hour | **Priority:** HIGH

**Checklist:**
- [ ] Search all files for `/guide/eloquent-methods` → update to `/guide/filters`
- [ ] Search all files for `/guide/creating-segments` → update to `/guide/segments`
- [ ] Search all files for `/guide/what-are-segments` → update to `/guide/segments`
- [ ] Search all files for `/guide/ui-getting-started` → update to `/guide/stickle-ui`
- [ ] Search all files for `/guide/use-cases` → update to `/guide/index`
- [ ] Search all files for `/guide/tracking-model-attributes` → update to `/guide/tracking-attributes`
- [ ] Search all files for `/guide/scopes` → update to `/guide/filters`
- [ ] Search all files for other old file names
- [ ] Test all internal links
- [ ] Verify no 404s
- [ ] Commit changes

---

### ⬜ Task 5.4: Final proofread
**Estimate:** 2 hours | **Priority:** MEDIUM

**Checklist:**
- [ ] Proofread `index.md`
  - [ ] No typos
  - [ ] No grammatical errors
  - [ ] Code examples correct
  - [ ] Formatting consistent
  - [ ] Links work
- [ ] Proofread `installation.md`
- [ ] Proofread `quick-start.md`
- [ ] Proofread `configuration.md`
- [ ] Proofread `tracking-attributes.md`
- [ ] Proofread `segments.md`
- [ ] Proofread `filters.md`
- [ ] Proofread `event-listeners.md`
- [ ] Proofread `javascript-tracking.md`
- [ ] Proofread `stickle-ui.md`
- [ ] Proofread `api-endpoints.md`
- [ ] Proofread `filter-reference.md`
- [ ] Proofread `events-reference.md`
- [ ] Proofread `recipes.md`
- [ ] Proofread `deployment.md`
- [ ] Proofread `troubleshooting.md`
- [ ] All 16 files proofread
- [ ] No TODO comments remain
- [ ] No draft notes remain
- [ ] Commit changes

---

## ✅ COMPLETION CHECKLIST

### Documentation Quality
- [ ] 16 documentation files exist
- [ ] 0 empty/stub files
- [ ] All code examples are complete and tested
- [ ] All internal links work (no 404s)
- [ ] All typos corrected
- [ ] Consistent formatting throughout
- [ ] No TODO or draft notes

### Navigation
- [ ] VitePress sidebar updated
- [ ] All sidebar links work
- [ ] Clear learning progression
- [ ] Easy to find information

### Content Coverage
- [ ] Introduction explains what Stickle is
- [ ] Quick start gets user working in 15 min
- [ ] All core features documented
- [ ] Reference documentation complete
- [ ] Advanced topics covered
- [ ] Real-world examples included

### Final Steps
- [ ] Build docs locally (npm run docs:build)
- [ ] No build errors
- [ ] Preview looks good (npm run docs:preview)
- [ ] Create PR with all changes
- [ ] Request review
- [ ] Merge to main
- [ ] Deploy documentation

---

## 📊 PROGRESS TRACKING

**Total Tasks:** 24
**Completed:** 0
**In Progress:** 0
**Not Started:** 24

**Estimated Total Time:** ~40 hours (8 days)
**Actual Time Spent:** 0 hours

---

## 🚀 READY TO START?

1. Review this task list
2. Set up local environment (npm install, npm run docs:dev)
3. Start with Phase 1, Task 1.1
4. Mark tasks as you complete them
5. Commit frequently
6. Test after each phase

Good luck! 🎉
