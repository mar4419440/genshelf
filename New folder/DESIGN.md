---
name: GenShelf Retail Excellence
colors:
  surface: '#fbf8ff'
  surface-dim: '#d7d8f6'
  surface-bright: '#fbf8ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f4f2ff'
  surface-container: '#edecff'
  surface-container-high: '#e6e6ff'
  surface-container-highest: '#dfe0fe'
  on-surface: '#171a30'
  on-surface-variant: '#464554'
  inverse-surface: '#2c2f46'
  inverse-on-surface: '#f1efff'
  outline: '#777586'
  outline-variant: '#c7c4d7'
  surface-tint: '#4d4ad5'
  primary: '#4441cc'
  on-primary: '#ffffff'
  primary-container: '#5e5ce6'
  on-primary-container: '#f4f1ff'
  inverse-primary: '#c2c1ff'
  secondary: '#5d5c74'
  on-secondary: '#ffffff'
  secondary-container: '#e2e0fc'
  on-secondary-container: '#63627a'
  tertiary: '#443adb'
  on-tertiary: '#ffffff'
  tertiary-container: '#5e57f4'
  on-tertiary-container: '#f4f1ff'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#e2dfff'
  primary-fixed-dim: '#c2c1ff'
  on-primary-fixed: '#0c006b'
  on-primary-fixed-variant: '#332dbc'
  secondary-fixed: '#e2e0fc'
  secondary-fixed-dim: '#c6c4df'
  on-secondary-fixed: '#1a1a2e'
  on-secondary-fixed-variant: '#45455b'
  tertiary-fixed: '#e2dfff'
  tertiary-fixed-dim: '#c3c0ff'
  on-tertiary-fixed: '#0f0069'
  on-tertiary-fixed-variant: '#3323cc'
  background: '#fbf8ff'
  on-background: '#171a30'
  surface-variant: '#dfe0fe'
typography:
  h1:
    fontFamily: Inter
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.02em
  h2:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.01em
  h3:
    fontFamily: Inter
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.05em
  data-tabular:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '500'
    lineHeight: 20px
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  base: 8px
  xs: 4px
  sm: 8px
  md: 16px
  lg: 24px
  xl: 32px
  container-margin: 24px
  grid-gutter: 16px
---

## Brand & Style

The brand personality is rooted in reliability and operational intelligence. It serves a dual audience of floor managers and corporate analysts who require high-density information without cognitive overload. The emotional response is one of "ordered control"—a calm, systematic environment that reduces the stress of inventory and financial management.

This design system employs a **Corporate / Modern** style. It prioritizes clarity through a structured hierarchy, ample white space, and a refined color palette. By utilizing subtle borders and tonal layering rather than aggressive gradients or heavy shadows, the UI remains functional and timeless, ensuring the focus stays on the data.

## Colors

The palette is anchored by a vibrant primary blue (#5E5CE6) that signals action and technical precision. It is supported by a deep navy secondary color (#1A1A2E) used for high-contrast navigation and primary headings to establish authority.

The background uses a cool light gray (#F4F5F7) to reduce eye strain during long shifts, while pure white (#FFFFFF) is reserved for interactive cards and containers. Tertiary indigo (#4F46E5) provides subtle variance for secondary actions or data categories, while the neutral slate (#555770) handles body text and UI iconography to maintain a balanced visual weight.

## Typography

This design system utilizes **Inter** across all levels to ensure maximum readability and a systematic, utilitarian aesthetic. The type scale is optimized for data-heavy retail environments, featuring "Data Tabular" settings (tabular figures) for price lists and stock counts to ensure vertical alignment of numbers.

Headlines use tighter letter spacing and heavier weights to create a strong visual anchor, while body text maintains standard tracking for flow. Labels are rendered in a slightly smaller, semi-bold uppercase style to distinguish metadata from actionable content.

## Layout & Spacing

The layout follows a **Fluid Grid** model, utilizing a 12-column system that adapts to the wide-screen monitors typically found in retail back-offices. A strict 8px spacing rhythm ensures consistency across all components.

Layouts should prioritize "Safe Zones" for high-frequency interactions. Gutters are kept at 16px to maximize information density while preventing visual clutter. Dashboards use a modular approach where cards can span 3, 4, 6, or 12 columns depending on the complexity of the data visualization (e.g., small KPIs span 3 columns, while complex inventory tables span 12).

## Elevation & Depth

To maintain a clean and professional look, this design system avoids heavy shadows. Depth is primarily communicated through **Tonal Layers** and **Low-Contrast Outlines**.

1.  **Level 0 (Background):** The base gray (#F4F5F7).
2.  **Level 1 (Surface):** White cards (#FFFFFF) with a 1px solid border (#E2E8F0).
3.  **Level 2 (Interaction):** Elements like active dropdowns or hovered buttons receive a very soft, ambient shadow (0px 4px 12px rgba(26, 26, 46, 0.05)) to suggest lift without breaking the flat aesthetic.

This "ghost border" technique ensures that the UI feels crisp and structural, reinforcing the sense of efficiency.

## Shapes

The shape language is **Soft** (Level 1). A 0.25rem (4px) corner radius is applied to standard components like input fields, buttons, and small tags to maintain a professional, slightly technical edge. 

Large containers and cards utilize the `rounded-lg` (0.5rem / 8px) setting to provide enough visual distinction from the background without appearing overly playful. This balance of sharp and slightly rounded edges conveys a precision-engineered feel suitable for retail management.

## Components

### Buttons
Primary buttons use the #5E5CE6 fill with white text. Secondary buttons use a #FFFFFF fill with a 1px border of #5E5CE6. Both feature 4px rounded corners and 16px horizontal padding for a compact, efficient footprint.

### Cards
Cards are the primary container for data. They must feature a white background, a 1px border (#E2E8F0), and an 8px corner radius. Padding inside cards should be a consistent 24px (lg spacing) for readability.

### Input Fields
Inputs use a white background with a #D1D5DB border. On focus, the border transitions to #5E5CE6 with a subtle 2px outer glow in the same color at 20% opacity. 

### Chips & Status Indicators
Status chips (e.g., "In Stock," "Low Inventory") use a desaturated background version of the status color (e.g., light green for success) with high-contrast bold text to ensure accessibility and quick scanning.

### Data Tables
Tables are the heart of the system. They use a flat style with 1px horizontal dividers. Header rows are slightly tinted (#F9FAFB) with semi-bold text. Row heights are kept tight (40px–48px) to allow for high data density.

### Additional Components
- **KPI Metrics:** Large-format numbers within cards, utilizing the primary blue for the metric value.
- **Progress Bars:** Thin, 4px height bars for stock levels or sales targets, using tonal variations of the primary blue.