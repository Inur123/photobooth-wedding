# Guest experience verification

Verified locally on 5 November 2026. Design rationale: [guest-experience-design.md](guest-experience-design.md).

Mobile cover follow-up: PASS. Below 768px the animated envelope now precedes the event text and opening button. Browser checks at 320px and 390px confirm that order with no horizontal overflow. Desktop retains its two-column layout. Opening the cover still reaches the album; formatting, lint and production build pass.

## Automated checks

- PASS: Vite Plus formatting and lint, 71 files, no warnings/errors.
- PASS: `vue-tsc --noEmit`.
- PASS: production Vite build.
- PASS: Laravel Pint and PHPStan, zero errors.
- PASS: PHPUnit, 43 tests: 41 passed, 2 skipped, 262 assertions. The guest experience tests cover approved-only gallery data, private visibility, event state, theme/capability payloads, frame ownership/layout/package limits, captions, required wishes, media uploads, and an actual WAV fixture.
- PASS: six seeded palettes checked with WCAG luminance calculations. Minimum contrast across ink/muted/action against background/surface/soft and action labels: Titip Momen 5.00:1; Cream & Ink 4.66:1; Peach Fizz 4.64:1; Matcha 4.53:1; Lilac Hour 4.58:1; Tinta Malam 7.52:1.

PHPUnit forces SQLite `:memory:` in `tests/bootstrap.php`. Browser submissions used a separate SQLite file and media directory under `/tmp/titip-guest-qa.bMLD4m`, served on port 8001. No migration, reset, seeding, or test submission was run against the development MySQL database. The final preview on port 8000 was read-only.

## Browser click-through

Chrome, with responsive checks at 320, 390, 768, 1024 and 1440 pixels.

| Control or state      | Observed result                                                                                                  |
| --------------------- | ---------------------------------------------------------------------------------------------------------------- |
| Open cover            | Envelope opens, album appears, page starts at the top                                                            |
| Six saved themes      | Cover adopts saved palette; no horizontal page overflow at 390px                                                 |
| Album feed/grid       | Photos render in both modes; grid opens photo detail; close returns to album                                     |
| Reels                 | Uploaded/recorded QA video appears under Reels                                                                   |
| Catatan               | Wishes and voice notes appear here; Ucapan/Suara filters separate them                                           |
| Acara                 | Event name, date and venue render from the event                                                                 |
| Refresh               | Returns current contributions without leaving the album                                                          |
| Share                 | Clipboard-denied environment shows the manual-copy error message; native mobile sharing remains device-dependent |
| Center capture action | Opens photo/video/wish/voice choices; Back returns to those choices                                              |
| Photo layouts         | One, two, three and four pose choices update the expected file count                                             |
| Four-photo upload     | Produces a 2x2 preview and saves to Album with caption                                                           |
| Camera photo          | Simulated stream, countdown, preview, explicit submission; no live hardware access                               |
| Video recording       | Simulated audio/video stream, stop, preview, submission, Reels destination                                       |
| Voice recording       | Simulated audio stream automatically stops at 60 seconds, previews, submits to Catatan                           |
| WAV upload            | Two-second WAV preview and submission succeed after accounting for `audio/x-wav` MIME detection                  |
| Empty wish            | Displays an Indonesian validation message; valid wish then saves to Catatan                                      |
| Media cleanup         | Track indicator returns to zero on preview and when closing the active camera dialog                             |
| Permission denied     | Explains the failed camera/microphone access and leaves upload available                                         |
| Private event         | No contribution list; guests can still send their own moment                                                     |
| Closed event          | Capture action disabled, existing gallery remains readable                                                       |
| Keyboard              | Tab advances inside the dialog; Escape closes it; visible focus styling                                          |
| Small screen dialog   | At 320px the dialog is 296px wide, with 12px side margins; at 390px it is 366px wide                             |
| Console               | No JavaScript errors observed during the tested flows                                                            |

Camera and microphone behavior was exercised with real MediaStream/MediaRecorder objects fed by a QA-only canvas/audio generator. This validates the application flow, not physical camera/microphone hardware or Safari compatibility. No field Core Web Vitals numbers are claimed.

## Delivery gate

### Hard gate

- R-02 PASS: no em dash in the new guest UI copy.
- R-03 PASS: responsive page and dialog checks above; no horizontal page overflow.
- R-17 PASS: counts derive from approved database contributions.
- R-18 PASS: no testimonials or fabricated guest identities in production UI; QA fixtures clearly identify themselves as tests.
- R-23 PASS: owner explicitly requested the envelope, 3D motion, themes and navigation; illustration is code-native and monograms use event names.
- R-24 PASS: Album, Reels, Catatan and Acara switch real views; capture opens the real composer.
- R-25 PASS: measured theme token contrasts listed above; photo/video overlays use dark backing.
- R-26 PASS: controls have implemented handlers; central capture, preview and submission flows were exercised.
- R-27 PASS: empty/private/closed states, capture/upload loading, permission errors, validation and media errors are implemented; representative states verified above.
- R-28 PASS: no FAQ added.
- R-32 PASS: native controls, labels, keyboard focus and working Escape dismissal.
- R-33 PASS: application features live in Vue/TypeScript/CSS/PHP source, edited directly. Temporary capture simulation is isolated QA infrastructure.
- R-34 PASS: all six event themes resolve consistently; no global appearance toggle added.
- R-35 PASS: production build and recorded click-through above; automated backend checks include package frame selection.
- R-36 PASS: no security/performance/customer claims invented.
- R-37 PASS: owner-directed album concept and explicit design dials recorded before implementation.
- R-38 PASS: names, dates, palettes, frames and counts come from the database; production empty states contain no invented posts.
- R-39 PASS: native labeled controls, image descriptions, disabled/loading states and reduced-motion handling; short envelope choreography; existing document language/title/landmarks retained.
- R-40 PASS: media reserves aspect-ratio space, cover photo has high fetch priority, font assets stay local, and no new third-party render-blocking script or invented performance metric.

### Purpose gate

- R-01 PASS: paper folds and shading describe the requested physical envelope; palette derives from the selected theme.
- R-04 PASS: existing product icon family retained for camera, film, audio, notes, location and sharing, matching each control's function.
- R-06 PASS: Playfair Display provides the personal keepsake voice; Plus Jakarta Sans matches the product controls.
- R-07 PASS: no decorative background grid or dot pattern.
- R-08 PASS: arrows are limited to navigation/back and the envelope annotation.
- R-09 PASS: no promotional pills; photo layout marker identifies actual multi-pose content.
- R-10 PASS: no repeated glass treatment.
- R-12 PASS: elevation belongs to envelope layers, dialogs and fixed navigation, rather than every feed element.
- R-13 PASS: no decorative glow system.
- R-14 PASS: four equal composer choices have equal functional priority; other screen sections use distinct hierarchy.
- R-19 PASS: opening, entrance and selection motion serve transitions; reduced-motion disables choreography; no decorative endless animation.
- R-22 PASS: the envelope represents opening the owner's guest album.

### Liveliness

- Dials PASS: ENERGY 3 / RHYTHM 2 / MOTION 3 documented.
- Consistency PASS: expressive opening, calmer album, animated interaction transitions.
- Focal point PASS: couple and envelope on entry; content or composer after entry.
- Whitespace PASS: separates the invitation, event context, feed and capture controls.
- Accent PASS: selected event accent highlights actions and meaningful selection.
- Identity PASS: paper layers, monogram and personal editorial typography repeat coherently.
- Design read PASS: mobile-first keepsake direction is recorded in the companion design document.

### Craftsmanship

- C-1 PASS: palette, typography, envelope and composition have recorded purposes.
- C-2 PASS: implemented controls and tested core flows listed above; unsupported media paths offer uploads.
- C-3 PASS: sections serve invitation, album browsing or contribution capture; no filler statistics or charts.
- C-4 PASS: representative theme, width, error, privacy and keyboard checks completed; device-specific limits explicitly recorded.
- C-5 PASS: no fabricated testimonials, statistics or validation results.
- R-05 PASS: invitation, feed, Reels, notes and composer vary with their content.
- R-11 PASS: paper/card corners, circular monograms and capture button have different shapes for different purposes.
- R-15 PASS: CTAs describe actions such as opening the album, activating camera and submitting a moment.
- R-16 PASS: no generic technology marketing copy.
- R-20 PASS: event-specific envelope and paper album preserve titip.moment identity.
- R-21 PASS: light is the default; Tinta Malam follows the explicitly selected event theme.
- R-29 PASS: each theme uses saved primary, accent and neutral colors with derived accessible shades.
- R-30 PASS: requested Instagram-style content destinations are adapted to the keepsake identity, without copying its branding or inventing social counters.
- R-31 PASS: major visual decisions are explained in the design document.
