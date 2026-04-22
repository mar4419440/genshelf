---
name: Retail Management Design System
colors:
  surface: '#f7f9fb'
  surface-dim: '#d8dadc'
  surface-bright: '#f7f9fb'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f2f4f6'
  surface-container: '#eceef0'
  surface-container-high: '#e6e8ea'
  surface-container-highest: '#e0e3e5'
  on-surface: '#191c1e'
  on-surface-variant: '#45464d'
  inverse-surface: '#2d3133'
  inverse-on-surface: '#eff1f3'
  outline: '#76777d'
  outline-variant: '#c6c6cd'
  surface-tint: '#565e74'
  primary: '#000000'
  on-primary: '#ffffff'
  primary-container: '#131b2e'
  on-primary-container: '#7c839b'
  inverse-primary: '#bec6e0'
  secondary: '#515f74'
  on-secondary: '#ffffff'
  secondary-container: '#d5e3fd'
  on-secondary-container: '#57657b'
  tertiary: '#000000'
  on-tertiary: '#ffffff'
  tertiary-container: '#00201d'
  on-tertiary-container: '#0c9488'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dae2fd'
  primary-fixed-dim: '#bec6e0'
  on-primary-fixed: '#131b2e'
  on-primary-fixed-variant: '#3f465c'
  secondary-fixed: '#d5e3fd'
  secondary-fixed-dim: '#b9c7e0'
  on-secondary-fixed: '#0d1c2f'
  on-secondary-fixed-variant: '#3a485c'
  tertiary-fixed: '#89f5e7'
  tertiary-fixed-dim: '#6bd8cb'
  on-tertiary-fixed: '#00201d'
  on-tertiary-fixed-variant: '#005049'
  background: '#f7f9fb'
  on-background: '#191c1e'
  surface-variant: '#e0e3e5'
typography:
  h1:
    fontFamily: Public Sans
    fontSize: 30px
    fontWeight: '700'
    lineHeight: 38px
    letterSpacing: -0.02em
  h2:
    fontFamily: Public Sans
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.01em
  h3:
    fontFamily: Public Sans
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
    letterSpacing: '0'
  body-lg:
    fontFamily: Public Sans
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
    letterSpacing: '0'
  body-md:
    fontFamily: Public Sans
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
    letterSpacing: '0'
  body-sm:
    fontFamily: Public Sans
    fontSize: 13px
    fontWeight: '400'
    lineHeight: 18px
    letterSpacing: '0'
  label-md:
    fontFamily: Public Sans
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.04em
  data-mono:
    fontFamily: Public Sans
    fontSize: 14px
    fontWeight: '500'
    lineHeight: 20px
    letterSpacing: -0.01em
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  unit: 4px
  container-margin: 24px
  gutter: 16px
  compact-padding: 8px
  comfortable-padding: 16px
  section-gap: 32px
---

## Brand & Style

This design system is engineered for high-stakes retail environments where precision and speed are paramount. The brand personality is authoritative, systematic, and transparent. It aims to evoke a sense of "calm control" over complex financial and inventory data, positioning the software as a reliable partner rather than just a tool.

The visual style is **Corporate Modern** with a heavy emphasis on **Functional Minimalism**. By prioritizing white space for legibility and using color only to convey meaning (status, action, or hierarchy), the system reduces cognitive load during intensive data entry and analysis. Every element is stripped of decorative excess to ensure that the user's focus remains entirely on the financial health and operational flow of the business.

## Colors

The palette is anchored by a sophisticated **Deep Navy** primary color, chosen to symbolize stability and professional trust. This is contrasted against **Crisp Whites** and very light cool-greys to maintain an airy, clean workspace that prevents eye fatigue during long shifts.

Financial status is handled with a "Subtle-High-Contrast" approach. **Emerald Green** and **Rose Red** are used specifically for monetary indicators (profit/loss, stock levels, payment status). These colors are applied to text and small UI accents rather than large blocks to ensure they draw attention without overwhelming the functional density of the interface. Secondary actions and inactive states utilize **Steel Blues** to maintain a cohesive monochromatic foundation.

## Typography

This design system utilizes **Public Sans** for its institutional clarity and exceptional legibility in dense data tables. As a typeface designed for government and corporate interfaces, it excels at making numerical data easy to scan and compare.

A strict hierarchy is enforced to manage information density. Headlines use a heavier weight and tighter tracking to anchor sections, while body text remains neutral. A specific "Data-Mono" style (using Public Sans with tabular figures) is reserved for currency and inventory counts, ensuring that decimals and digits align perfectly in vertical columns for rapid financial auditing.

## Layout & Spacing

The layout operates on a **12-column fluid grid** to maximize the real estate of widescreen retail POS and back-office monitors. The system prioritizes "Vertical Density," allowing more rows of data to be visible on a single screen without scrolling.

Spacing follows a strict 4px base unit. In data-heavy views (like inventory logs), the design system utilizes "Compact" padding (8px) to increase information density. In administrative or settings views, "Comfortable" padding (16px) is preferred to improve focus. Horizontal margins are kept consistent at 24px to provide a structured frame for the content, while gutters are fixed at 16px to maintain clear separation between data widgets.

## Elevation & Depth

To maintain the "Clean and Professional" aesthetic, this design system avoids heavy shadows or distracting gradients. Instead, it uses **Tonal Layering** and **Low-Contrast Outlines** to create depth.

- **Surface Tiers:** The background is the lowest level (Neutral #F8FAFC). Cards and containers sit on top in pure White (#FFFFFF).
- **Outlines:** All containers and input fields use a subtle 1px border (#E2E8F0). This provides structure without the visual "fuzziness" of shadows.
- **Interactive Elevation:** Only primary buttons and active modals use a very subtle, highly diffused ambient shadow (0px 4px 12px rgba(15, 23, 42, 0.08)) to indicate their proximity to the user.

## Shapes

The shape language is conservative and disciplined. A **Soft (0.25rem)** border radius is applied to all standard components like buttons, input fields, and cards. This slight rounding softens the "industrial" feel of the system without losing the professional, grid-aligned structure required for retail management.

Larger containers like modals or dashboard widgets may use a `rounded-lg` (0.5rem) to subtly distinguish them from the repetitive rows of a data table. Icons must follow a similar geometric path, using 2px stroke weights and matching corner radii for visual continuity.

## Components

### Buttons
- **Primary:** Solid Deep Navy background with White text. High contrast for final actions like "Complete Sale" or "Save Changes."
- **Secondary:** Outlined Steel Blue. Used for "Cancel" or "Export" functions.
- **Ghost:** No border or background. Used for navigation items or low-priority utility actions.

### Data Tables
Tables are the heart of the design system. They feature:
- Sticky headers with a subtle bottom border.
- Alternating row stripes (Zebra striping) using the Neutral color.
- Right-aligned numerical columns to ensure decimal points line up.
- Inline status chips for immediate financial visibility.

### Status Chips
Small, rounded badges used for "Paid," "Pending," or "Overdue." These use a desaturated background of the status color with high-contrast text for maximum readability without being visually loud.

### Input Fields
Outlined style with a 1px border. On focus, the border transitions to the Primary Deep Navy with a 2px thickness. Labels are always positioned above the field in the `label-md` style for clarity during rapid data entry.

### Cards & KPIs
Dashboard cards use the White surface against the Light Grey background. Key Performance Indicators (KPIs) feature large numerical values in the Primary color, with secondary trend indicators (percentage change) using the status Green/Red.