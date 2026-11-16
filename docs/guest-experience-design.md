# Album tamu titip.moment

Direction from the owner: a 3D opening cover, Instagram-inspired photo posts and grid, a Reels destination for videos, notes containing written wishes and voice messages, and a central capture action. Every event uses the palette selected by the administrator.

Design read: a mobile-first keepsake album for event guests, with tactile paper and editorial typography. ENERGY 3 / RHYTHM 2 / MOTION 3.

- The envelope, layered letter, seal, perspective, and opening motion make entering the event feel like opening a personal invitation. These are CSS elements, using actual event names and dates.
- The event's saved colors are the source of truth. Accessible text and action shades are derived from them; Titip Momen keeps its coral identity. Tinta Malam follows its explicitly selected ink palette only inside the guest experience.
- Existing Playfair Display identifies personal messages and the couple; Plus Jakarta Sans keeps navigation, controls, and metadata consistent with the product.
- Desktop places event context beside a readable feed. Mobile concentrates on the album and a safe-area-aware five-position navigation with capture in the center.
- Photos have real feed and grid modes. Video entries have portrait players in Reels. Wishes and voice recordings share Catatan, with separate filters.
- Motion marks opening, selection, and capture. There are no decorative endless loops. Reduced-motion preferences disable choreography.
- Borders reuse the neutral table/card tone. Accent is reserved for primary actions and meaningful selected states.
- All current packages include photo/video/voice/wish. Available frames respect max_frames, prioritizing the event's own frames over global ones. No new package restrictions are inferred from marketing copy.
- Guest media is never requested at page load. Camera/microphone begin only on activation. Recordings have a preview and explicit submission, a 60-second cap, cleanup on close, and file-upload fallback.
- Empty albums remain empty until a guest contributes. Counts are derived from approved contributions. Private events expose no contribution payload to unauthorized guests.

Verification and the delivery gate are recorded in [guest-experience-verification.md](guest-experience-verification.md). Database checks use the guarded in-memory PHPUnit environment or a dedicated temporary SQLite database, never the development MySQL database.
