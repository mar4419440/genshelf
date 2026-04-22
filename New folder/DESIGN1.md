---
name: Precision Retail
colors:
  surface: '#f9f9ff'
  surface-dim: '#cfdaf2'
  surface-bright: '#f9f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f0f3ff'
  surface-container: '#e7eeff'
  surface-container-high: '#dee8ff'
  surface-container-highest: '#d8e3fb'
  on-surface: '#111c2d'
  on-surface-variant: '#464556'
  inverse-surface: '#263143'
  inverse-on-surface: '#ecf1ff'
  outline: '#777587'
  outline-variant: '#c7c4d8'
  surface-tint: '#4f40eb'
  primary: '#3a24d8'
  on-primary: '#ffffff'
  primary-container: '#5446f0'
  on-primary-container: '#e1ddff'
  inverse-primary: '#c4c0ff'
  secondary: '#505f76'
  on-secondary: '#ffffff'
  secondary-container: '#d0e1fb'
  on-secondary-container: '#54647a'
  tertiary: '#494c4e'
  on-tertiary: '#ffffff'
  tertiary-container: '#616466'
  on-tertiary-container: '#dfe1e3'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#e3dfff'
  primary-fixed-dim: '#c4c0ff'
  on-primary-fixed: '#110068'
  on-primary-fixed-variant: '#351ad4'
  secondary-fixed: '#d3e4fe'
  secondary-fixed-dim: '#b7c8e1'
  on-secondary-fixed: '#0b1c30'
  on-secondary-fixed-variant: '#38485d'
  tertiary-fixed: '#e0e3e5'
  tertiary-fixed-dim: '#c4c7c9'
  on-tertiary-fixed: '#191c1e'
  on-tertiary-fixed-variant: '#444749'
  background: '#f9f9ff'
  on-background: '#111c2d'
  surface-variant: '#d8e3fb'
typography:
  headline-xl:
    fontFamily: Manrope
    fontSize: 40px
    fontWeight: '700'
    lineHeight: '1.2'
  headline-lg:
    fontFamily: Manrope
    fontSize: 32px
    fontWeight: '600'
    lineHeight: '1.2'
  headline-md:
    fontFamily: Manrope
    fontSize: 24px
    fontWeight: '600'
    lineHeight: '1.3'
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: '1.6'
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.5'
  body-sm:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: '1.5'
  label-caps:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: '1'
    letterSpacing: 0.05em
  data-mono:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '500'
    lineHeight: '1'
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  base: 4px
  xs: 4px
  sm: 8px
  md: 16px
  lg: 24px
  xl: 32px
  container-margin: 24px
  gutter: 16px
---

## Brand & Style

This design system is engineered for the high-velocity world of retail management, where data clarity is the highest priority. The aesthetic is **Corporate / Modern**, emphasizing reliability and structural integrity. By combining the systematic rigour of a fintech dashboard with the accessibility of a modern SaaS platform, it evokes a sense of "organized intelligence." 

The UI prioritizes high legibility and an uncluttered interface to reduce cognitive load for managers handling inventory, logistics, and financial reporting. It utilizes a predominantly white and soft-gray workspace to allow the vibrant blue primary accents to guide the user's attention toward critical actions and real-time alerts.

## Colors

The color strategy uses "Vibrant Blue" as the primary driver for brand identity and interactive states. This blue is high-chroma, ensuring it remains distinct against complex data tables and white backgrounds.

- **Primary**: Used for CTA buttons, active navigation states, and primary brand iconography.
- **Secondary (Soft Gray)**: Reserved for supporting text, borders, and inactive interface elements to maintain a clean, low-noise environment.
- **Surface & Backgrounds**: A mixture of pure white and extremely light grays (Tertiary) are used to create subtle tonal layering and separate distinct data modules without relying on heavy lines.
- **Functional Colors**: Use standard semantic colors for status: Green for stock "In-Limit," Red for "Out of Stock," and Amber for "Low Inventory."

## Typography

The typography system uses a dual-font approach to balance personality with utility. **Manrope** is used for headlines to provide a modern, friendly, yet professional character. For all functional data and body text, **Inter** is utilized due to its exceptional performance in dense interfaces and its "tabular num" features, which are essential for aligning financial figures in retail reports.

Labels for data inputs should always be high-contrast and slightly smaller than the input text to maintain a clear hierarchy. For dashboards, utilize the `data-mono` setting to ensure numbers and currency align perfectly in columns.

## Layout & Spacing

This design system follows a **Fluid Grid** model with a standard 12-column layout for desktop views. It uses a strict 4px baseline grid to ensure all components—from small buttons to large cards—maintain a rhythmic vertical and horizontal flow.

- **Margins**: Use 24px margins for the primary application container to provide breathing room.
- **Gutters**: A standard 16px gutter is used between layout columns.
- **Density**: Because retail data can be dense, the system supports a "Compact" mode where vertical padding is reduced by 25% (using the `sm` unit) for inventory lists and POS terminals.

## Elevation & Depth

To maintain a clean and professional look, depth is communicated through **Tonal Layers** and **Low-Contrast Outlines**. 

1. **Base Layer**: Soft gray background (#F8FAFC).
2. **Container Layer**: Pure white cards with a subtle 1px border (#E2E8F0).
3. **Elevated State**: For modals and dropdowns, use a very soft, diffused ambient shadow with a slight blue tint (e.g., `0px 10px 30px rgba(84, 70, 240, 0.08)`) to suggest they are floating above the workspace. 

Avoid heavy drop shadows on standard dashboard widgets; use borders to define boundaries instead, keeping the interface feeling lightweight and "fast."

## Shapes

The shape language is **Soft (0.25rem)**, providing a clean, geometric feel that avoids the "playfulness" of highly rounded corners while remaining more modern than sharp edges.

- **Standard Elements**: Inputs and buttons use a 4px radius.
- **Large Components**: Cards and containers use an 8px radius (`rounded-lg`).
- **Icons**: Icons should be housed in square containers or have very slight rounding to match the UI elements.

## Components

- **Buttons**: Primary buttons are solid vibrant blue with white text. Secondary buttons are outlined with soft gray. States (Hover/Active) should involve a 10% brightness shift.
- **Input Fields**: Feature a light gray border that transitions to the primary blue on focus. Label text is positioned above the input in a smaller, bold Inter font.
- **Cards**: The primary vehicle for dashboard data. Each card has a white background, 8px corner radius, and a subtle gray border. Padding inside cards is strictly 24px (`lg`).
- **Data Tables**: Use alternating row colors (White and Off-white) instead of heavy horizontal lines. Headers are capitalized, bold, and in a slightly smaller font size for clarity.
- **Chips**: Used for status (e.g., "Shipped", "Pending"). They use a low-opacity version of the semantic color as a background with high-opacity text for maximum readability without clutter.
- **Icons**: Use a 2px stroke weight with minimal details. Avoid filled icons unless indicating an active navigation state.