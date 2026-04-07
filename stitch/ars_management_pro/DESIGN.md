# Design System Document: The Executive Curator

## 1. Overview & Creative North Star: "The Digital Curator"
This design system moves away from the cluttered, utility-first aesthetics of traditional admin panels and embraces the role of **The Digital Curator**. The North Star for this system is high-end editorial clarity. We treat data not as a spreadsheet, but as a gallery of insights. 

By leveraging intentional asymmetry, high-contrast typography scales, and a "tonal layering" approach, we break the "template" look. The interface should feel like a premium, bespoke workspace where white space is a functional tool and every element feels intentionally placed rather than forced into a rigid grid.

---

## 2. Colors: Tonal Depth & The "No-Line" Rule
We utilize a sophisticated palette derived from the core brand colors, expanded into a Material-style logic to support complex layering.

### The "No-Line" Rule
**Explicit Instruction:** Prohibit the use of 1px solid borders for sectioning or containment. Traditional borders create visual noise. Instead, boundaries must be defined through:
*   **Background Shifts:** Placing a `surface-container-lowest` card on a `surface-container-low` background.
*   **Tonal Transitions:** Using subtle shifts in saturation to indicate where one functional area ends and another begins.

### Surface Hierarchy
Treat the UI as a series of physical layers—stacked sheets of frosted glass or fine paper.
*   **Base Layer:** `surface` (#faf8ff) for the main application background.
*   **Secondary Content:** `surface-container-low` (#f2f3ff) for sidebar or navigation backgrounds.
*   **Primary Focus:** `surface-container-lowest` (#ffffff) for high-priority cards and interactive modules.
*   **Active Overlays:** `surface-container-high` (#e2e7ff) for hover states or active selection indicators.

### The "Glass & Gradient" Rule
To inject "soul" into the admin experience, main CTAs and hero analytics should utilize subtle gradients (e.g., `primary` #004ac6 transitioning to `primary-container` #2563eb). For floating navigation or modals, employ **Glassmorphism**: semi-transparent `surface` colors with a `20px` backdrop-blur to allow the background logic to bleed through softly.

---

## 3. Typography: Editorial Authority
We utilize **Inter** for its mathematical precision and neutral character, allowing the content to lead.

*   **Display (lg/md/sm):** Reserved for high-level "At a Glance" metrics. Use `display-md` (2.75rem) for total revenue or critical KPIs to give them an authoritative, editorial weight.
*   **Headlines & Titles:** Use `headline-sm` (1.5rem) for page headers. The contrast between a large headline and `body-md` (0.875rem) creates a clear hierarchy that guides the eye.
*   **Labels (md/sm):** Use `label-md` (0.75rem) with a `0.05em` letter-spacing for metadata and table headers. This ensures that even small text feels "designed" and premium.
*   **The Weight Logic:** Pair `title-lg` (Medium 500) for card titles with `body-md` (Regular 400) for descriptions to ensure a professional, balanced rhythm.

---

## 4. Elevation & Depth: Tonal Layering
Traditional drop shadows are a last resort. We achieve depth through the **Layering Principle**.

*   **Tonal Stacking:** Place a `surface-container-lowest` card (Pure White) on a `surface-container-low` section (Soft Blue-Grey). The 2% difference in luminosity creates a "soft lift" that feels more natural than a shadow.
*   **Ambient Shadows:** For "floating" elements like Modals or Dropdowns, use extra-diffused shadows.
    *   *Shadow Specification:* `0px 12px 32px rgba(19, 27, 46, 0.06)`. The shadow color is a tint of `on-surface` (#131b2e), mimicking natural light.
*   **The "Ghost Border" Fallback:** If accessibility requires a container edge, use a "Ghost Border": `outline-variant` (#c3c6d7) at **15% opacity**. Never use 100% opaque borders.

---

## 5. Components: Refined Interaction

### Buttons (6px Radius)
*   **Primary:** A gradient-filled container (`primary` to `primary-container`) with `on-primary` text. No border.
*   **Secondary:** `surface-container-high` background with `primary` text. 
*   **Tertiary:** Ghost style. No background, `primary` text, shifts to `surface-container-low` on hover.

### Cards (8px Radius)
*   **Standard:** Use `surface-container-lowest` background. 
*   **The No-Divider Rule:** Forbid 1px dividers inside cards. Use **Vertical White Space** (24px to 32px) to separate the header from the content. If a separation is mandatory, use a subtle background shift for the header area using `surface-container-low`.

### Input Fields
*   **Style:** Minimalist. Use `surface-container-low` as the background with a 6px radius. 
*   **Focus State:** Instead of a heavy border, use a 2px `surface-tint` glow and transition the background to `surface-container-lowest`.

### Chips & Badges
*   **Status Badges:** Use "Soft Fills." For `success`, use a background of `#10B981` at 10% opacity with `#006242` (`tertiary`) text. This ensures readability while feeling high-end.

---

## 6. Do's and Don'ts

### Do:
*   **Embrace Negative Space:** Allow for generous margins (32px+) between major sections to let the "Editorial" feel breathe.
*   **Use Intentional Asymmetry:** If a dashboard has four widgets, make one span 66% width and the others 33% to create a dynamic visual path.
*   **Prioritize Type Scale:** Rely on font size and weight to differentiate data before resorting to color or icons.

### Don't:
*   **Don't use "pure" black or grey:** Always use the `on-surface` (#131b2e) or `outline` (#737686) tones to maintain the sophisticated blue-tinted depth.
*   **Don't use lines to separate list items:** Use a `12px` vertical gap. Let the alignment of the text define the rows.
*   **Don't overload with icons:** Icons should be used sparingly as "anchors." Too many icons turn a professional admin panel into a toy-like interface.

---

## 7. Light & Dark Mode Transition
In Dark Mode, the hierarchy remains the same but the "Luminosity Logic" inverts. 
*   **Surface:** Becomes `Neutral Dark` (#0F172A).
*   **Surface-Container-Lowest:** Becomes #1E293B (The "lifted" layer is lighter than the background).
*   **Glassmorphism:** Increase backdrop-blur to `40px` to maintain legibility against dark backgrounds.