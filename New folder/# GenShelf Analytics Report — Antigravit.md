# GenShelf Analytics Report — Antigravity Prompt

## System Context

You are building a multi-dashboard analytics report for **GenShelf**, a retail point-of-sale and inventory management system. The database is MySQL. All monetary values are in **EGP** (pulled from the `settings` table where `key = 'currency'`). The tax rate is pulled from `settings` where `key = 'tax_rate'`.

The report consists of **6 dashboards**. There is a **global date range picker** at the top of the report that filters all dashboards simultaneously. Default range is the current month.

---

## Global Date Filter

Apply to all queries unless stated otherwise:
- Filter on `created_at` or `entry_date` or `expense_date` depending on the table.
- Provide quick presets: Today, This Week, This Month, Last Month, This Quarter, This Year, Custom Range.

---

## Dashboard 1: Executive Overview

### Section 1 — KPI Cards (row of 5)

| Card                | Calculation                                                | Comparison               |
| ------------------- | ---------------------------------------------------------- | ------------------------ |
| Total Revenue       | `SUM(total)` from `transactions` in range                  | vs same period last year |
| Net Profit          | Total Revenue − COGS − Total Expenses                      | vs same period last year |
| Total Invoices      | `COUNT(id)` from `transactions`                            | vs previous period       |
| Average Order Value | `AVG(total)` from `transactions`                           | vs previous period       |
| Active Customers    | `COUNT(DISTINCT customer_id)` from `transactions` in range | vs previous period       |

Each card shows the value + percentage change with a green up or red down indicator.

---

### Section 2 — Revenue Trend Line Chart

- X-axis: dates grouped by day (if range ≤ 60 days) or by week (if range > 60 days)
- Y-axis: `SUM(total)` from `transactions`
- Two lines: current period (primary color) and same period last year (muted color)
- Source: `transactions.total` grouped by `DATE(created_at)`

---

### Section 3 — 30-Day Revenue Forecast Strip

- Appears directly below the trend chart as a continuation
- Use **Linear Regression** on the last 90 days of daily revenue to project the next 30 days
- Display as a dashed line extending from the trend chart
- Show confidence band (shaded area) around the forecast
- Source: same as Section 2

---

### Section 4 — Three Mini Charts (side by side)

**Mini Chart A — Top 5 Products by Revenue**
- Horizontal bar chart
- `SUM(line_total)` from `transaction_items` grouped by `product_id`, joined with `products.name`
- Top 5 only

**Mini Chart B — Payment Method Distribution**
- Pie or donut chart
- `COUNT(id)` and `SUM(total)` from `transactions` grouped by `payment_method`
- Values: cash, credit, card, partial, debt
- Highlight `debt` and `partial` in warning color (orange/red)

**Mini Chart C — Return Rate**
- Single gauge or stat card
- Formula: `SUM(refund_amount)` from `returns` ÷ `SUM(total)` from `transactions` × 100
- Color: green if < 5%, yellow if 5–10%, red if > 10%

---

## Dashboard 2: Sales & Revenue Analytics

### Section 1 — Sales Breakdown Table

Columns: Product Name, Category, Units Sold, Total Revenue, Unit Cost (avg from `product_batches.unit_cost`), Gross Margin %, Contribution % to total revenue.

- Source: `transaction_items` joined with `products` and `product_batches`
- Sortable by any column
- Searchable by product name

---

### Section 2 — ABC Analysis

Classify all products sold in the selected period:
- **Class A**: products that cumulatively account for 0–80% of total revenue (sorted descending)
- **Class B**: 80–95%
- **Class C**: 95–100%

Display as a colored table with a Pareto combo chart: bars for individual product revenue + cumulative % line.

- Source: `transaction_items` grouped by `product_id`

---

### Section 3 — Sales Heatmap

- Rows: Days of the week (Saturday → Friday)
- Columns: Hours of the day (0–23)
- Cell value: `COUNT(id)` or `SUM(total)` from `transactions`
- Color intensity: darker = higher sales
- Source: `transactions.created_at` — extract `DAYOFWEEK()` and `HOUR()`

---

### Section 4 — Payment Method Trend

- Stacked area chart over time
- Each area = one payment method
- Source: `transactions` grouped by `DATE(created_at)` and `payment_method`

---

### Section 5 — Special Offers Impact

- Two grouped bars per time period: Revenue with active offers vs Revenue without
- Show total discount amount given (difference between full price and sold price where offer applied)
- Source: `special_offers` joined with `transactions` by date overlap (`start_date` / `end_date`)

---

## Dashboard 3: Inventory Intelligence

### Section 1 — Stock Health Overview (4 status buckets)

Calculate current stock per product as `SUM(qty)` from `product_batches` grouped by `product_id`.

| Bucket        | Condition                                                         |
| ------------- | ----------------------------------------------------------------- |
| Normal Stock  | current qty > `products.low_stock_threshold`                      |
| Low Stock     | 0 < current qty ≤ `products.low_stock_threshold`                  |
| Out of Stock  | current qty = 0                                                   |
| Expiring Soon | `product_batches.expiration_date` within next 30 days AND qty > 0 |

Display as 4 clickable cards. Clicking each filters the table below.

---

### Section 2 — Inventory Valuation Table

Columns: Product, Category, Current Stock (units), Unit Cost, Total Cost Value (`qty × unit_cost`), Sale Price (`products.default_price`), Potential Revenue, Potential Gross Profit.

- Source: `product_batches` joined with `products`
- Show totals row at bottom
- Sort by Total Cost Value descending by default

---

### Section 3 — Reorder Prediction Table

For each product:
1. Calculate **Average Daily Sales Rate** = total units sold in last 60 days ÷ 60, from `transaction_items`
2. Calculate **Days of Stock Remaining** = Current Stock ÷ Average Daily Sales Rate
3. Calculate **Suggested Reorder Date** = today + Days Remaining − 7 (buffer)

Color code:
- Red: Days Remaining < 7
- Orange: 7–14 days
- Green: > 14 days

Columns: Product, Current Stock, Avg Daily Sales, Days Remaining, Suggested Reorder Date, Last Supplier (from latest `purchase_orders.supplier_id`)

---

### Section 4 — Storage Distribution Bar Chart

- Grouped bar chart: one group per product (top 10 by total stock)
- Each bar = stock quantity in a specific storage location
- Source: `product_batches` grouped by `product_id` and `storage_id`, joined with `storages.name`

---

### Section 5 — Batch Expiry Tracker

- Table of all batches where `expiration_date IS NOT NULL`
- Columns: Product, Batch Number, Storage, Qty, Expiration Date, Days Until Expiry
- Sorted by expiration date ascending
- Highlight rows where expiry < 30 days in red, 30–60 days in orange
- Source: `product_batches` joined with `products` and `storages`

---

## Dashboard 4: Financial Control

### Section 1 — P&L Summary Table

Rows:

| Line Item         | Source                                                                      |
| ----------------- | --------------------------------------------------------------------------- |
| Gross Revenue     | `SUM(total)` from `transactions`                                            |
| Returns & Refunds | `SUM(refund_amount)` from `returns`                                         |
| Net Revenue       | Gross Revenue − Returns                                                     |
| COGS              | `SUM(transaction_items.qty × product_batches.unit_cost)` matched by product |
| Gross Profit      | Net Revenue − COGS                                                          |
| Gross Margin %    | Gross Profit ÷ Net Revenue × 100                                            |
| Total Expenses    | `SUM(amount)` from `expenses` where `status = 'approved'`                   |
| Net Profit        | Gross Profit − Total Expenses                                               |
| Net Margin %      | Net Profit ÷ Net Revenue × 100                                              |
| Tax Collected     | `SUM(tax)` from `transactions`                                              |

Show 4 columns: This Period, Previous Period, This Year, Last Year.

---

### Section 2 — Cash Flow Statement

Three panels side by side:

**Operating Activities**
- Inflows: `SUM(paid_amount)` from `transactions` + `SUM(amount)` from `customer_payments`
- Outflows: `SUM(amount)` from `expenses` + `SUM(refund_amount)` from `returns`
- Also include entries from `cash_flow_entries` where `type = 'operating'`

**Investing Activities**
- From `cash_flow_entries` where `type = 'investing'`
- Group by `direction` (inflow / outflow)

**Financing Activities**
- From `cash_flow_entries` where `type = 'financing'`
- Group by `direction`

Each panel shows: Total Inflows, Total Outflows, Net Cash.

---

### Section 3 — Expense Analysis

**Left: Treemap** — Size of each rectangle = expense amount. Hierarchy: Category → Sub-category. Source: `expenses` grouped by `category` and `sub_category`.

**Right: Budget vs Actual Table**
- Columns: Category, Sub-category, Month, Budgeted Amount, Actual Spent, Variance, Variance %
- Color: red if over budget, green if under
- Source: `expense_budgets` joined with `expenses` by `category`, `sub_category`, `year`, `month`

---

### Section 4 — Debt & Credit Tracker

Table of customers with outstanding balances:
- Columns: Customer Name, Phone, Total Purchased, Total Paid, Outstanding Balance, Due Date, Days Overdue
- Formula: Outstanding = `SUM(due_amount)` from `transactions` where `customer_id` matches, minus `SUM(amount)` from `customer_payments`
- Sort by Outstanding descending
- Highlight rows where Days Overdue > 0 in red
- Source: `transactions` and `customer_payments` joined with `customers`

---

### Section 5 — Tax Summary

- Bar chart: Tax Collected vs Tax Remitted per month
- Source: `tax_entries` grouped by month of `period_start`
- Status breakdown: collected vs remitted
- Total liability = collected − remitted

---

### Section 6 — Cash Drawer Reconciliation

- Timeline of `cash_drawer_events` per day
- Summary table: Opening Balance (last close amount), Total In, Total Out, Expected Closing Balance
- Columns: Date, Event Type (open/close/in/out), Amount, Description, User
- Source: `cash_drawer_events` joined with `users`

---

## Dashboard 5: Customer & Loyalty Intelligence

### Section 1 — RFM Segmentation

For each customer calculate:
- **Recency**: days since last transaction (`MAX(created_at)` from `transactions`)
- **Frequency**: `COUNT(DISTINCT id)` from `transactions`
- **Monetary**: `SUM(total)` from `transactions`

Score each metric 1–5 (quintiles). Assign segments:

| Segment             | RFM Pattern   |
| ------------------- | ------------- |
| Champions           | R=5, F=5, M=5 |
| Loyal               | R≥4, F≥4      |
| Potential Loyalists | R≥4, F≤3      |
| At Risk             | R≤2, F≥3      |
| Hibernating         | R≤2, F≤2      |
| Lost                | R=1, F=1      |

Display as a scatter plot (Recency vs Frequency, bubble size = Monetary) with colored segments, plus a table below.

---

### Section 2 — Customer Lifetime Value

- CLV = Average Order Value × Purchase Frequency × Average Customer Lifespan (in months)
- Average Lifespan = months between first and latest transaction per customer
- Display top 20 customers by CLV in a ranked table
- Columns: Rank, Name, Phone, First Purchase, Last Purchase, Total Orders, Total Spent, CLV

---

### Section 3 — Loyalty Points Dashboard

- Bar chart: top 10 customers by `loyalty_points`
- Stat cards: Total Points Issued (all customers), Average Points per Active Customer
- Source: `customers.loyalty_points`

---

### Section 4 — Churn Risk Indicator

- Calculate each customer's average gap between purchases (days between consecutive transactions)
- Flag as **At Risk** if: days since last purchase > 2× their average purchase gap
- Display flagged customers in a warning table with: Name, Last Purchase, Expected Next Purchase, Days Overdue
- Source: `transactions` grouped by `customer_id` ordered by `created_at`

---

## Dashboard 6: Operations & Quality Control

### Section 1 — Returns Analysis

**Row of 3 stat cards**: Total Returns Count, Total Refund Value, Return Rate %.

**Bar chart**: Returns count grouped by `type` (invoice, defective, general) per month.

**Table**: Top returned products with: Product Name, Return Count, Total Refund Value, Return Rate (returns ÷ units sold × 100).

- Source: `returns` and `return_items` joined with `products`

---

### Section 2 — Defective Products Tracker

- Kanban-style status board or filterable table
- Columns: Product, Supplier, Batch Number, Description, Status (open / claimed / resolved), Created Date, Linked Transaction
- Filter by status, supplier, product
- Source: `defective_products` joined with `products`, `suppliers`, `product_batches`, `transactions`

---

### Section 3 — Warranty Management

**Three expiry buckets**:
- Expiring within 30 days
- Expiring within 31–90 days
- Active claims

Table columns: Product, Customer, Purchase Date, Warranty End Date, Days Remaining, Active Claims Count.

**Claims table**: warranty_claims with description, resolution status, linked warranty.

- Source: `warranties` joined with `products`, `customers`, and `warranty_claims`

---

### Section 4 — Staff Performance Leaderboard

- Table ranked by Total Revenue Generated
- Columns: User, Role, Total Invoices, Total Revenue, Average Order Value, Total Returns Processed
- Bar chart: revenue per user per month (grouped)
- Source: `transactions.user_id` joined with `users` and `roles`

---

### Section 5 — Audit Log Summary

- Table: recent audit actions with User, Action, Timestamp
- Bar chart: action frequency grouped by `action` type
- Highlight any user with unusually high frequency of deletions or edits
- Source: `audit_logs` joined with `users`

---

## Technical Notes for the Builder

- Currency: always display with `EGP` suffix, pulled from `settings` where `key = 'currency'`
- Tax Rate: pulled from `settings` where `key = 'tax_rate'`
- All decimal values: round to 2 decimal places
- Stock quantity: sum of `product_batches.qty` grouped by `product_id` represents current available stock
- COGS matching: use `product_batches.unit_cost` matched to `transaction_items.product_id` via the most recent batch (or weighted average if multiple batches exist)
- Services: `transaction_items.is_service = 1` items have no COGS, exclude from inventory calculations
- Forecasting: use **Linear Regression** for 30-day revenue forecast using last 90 days of daily data
- Churn detection: calculate per-customer average inter-purchase interval from `transactions` history
- RFM scoring: use quintile distribution (split into 5 equal groups) across all customers in the database