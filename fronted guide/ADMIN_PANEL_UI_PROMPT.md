# 🎯 COMPLETE ADMIN PANEL UI PROMPT FOR GOOGLE STITCH
## Easy Shopping A.R.S eCommerce Platform

---

## PROJECT CONTEXT

**Project Name:** Easy Shopping A.R.S  
**Type:** Single-vendor eCommerce platform for Nepal market  
**Tech Stack:** Vanilla PHP (no framework), MySQL/PDO, HTML/CSS/JS  
**Current Status:** ~65% complete, needs production-grade UI overhaul  
**Database:** `ars_ecommerce` (MySQL)  
**Default Admin Login:** Mobile `9800000000` / Password `admin123`

---

## CORE REQUIREMENTS

You are building a **complete admin panel UI system** for a single-vendor eCommerce platform. This is NOT a template — this is a **fully functional, production-grade interface** with:

✅ Responsive design (mobile 320px+, tablet, desktop)  
✅ Consistent branding across all pages  
✅ Interactive elements (modals, dropdowns, notifications)  
✅ Dark/Light theme support (toggle-able)  
✅ Accessibility (WCAG 2.1 AA standard)  
✅ Professional, scalable component system  

---

## 📋 ADMIN PANEL STRUCTURE

### A) MAIN DASHBOARD (`admin/dashboard.php`)

**Purpose:** Overview of business metrics and quick actions.

**Key Metrics Cards:**
- Total Revenue (Current month)
- Total Orders (Pending, Confirmed, Shipped, Delivered)
- Total Products
- Total Customers
- Total Website Visitors (optional analytics)

**Visual Components:**
1. **KPI Cards** (4-6 cards)
   - Each card shows: metric value, % change from last month, icon
   - Color-coded by metric type (revenue=green, orders=blue, products=purple, customers=orange)
   
2. **Revenue Chart** (Line or Bar chart)
   - Last 30 days revenue trend
   - Interactive hover tooltips
   - Mobile responsive (stacks on smaller screens)

3. **Recent Orders Table**
   - Latest 5 orders: Order ID, Customer, Amount, Status, Date
   - Status badges (color-coded: Pending=yellow, Confirmed=blue, Shipped=orange, Delivered=green, Cancelled=red)
   - Quick action buttons: View Details, Update Status

4. **Top Products** (Quick stats)
   - Best selling products (by quantity sold)
   - Shows: Product image (thumbnail), Name, Sales count, Revenue

5. **Quick Action Buttons**
   - Add New Product (primary CTA)
   - View All Orders
   - Add New Category
   - View All Customers

**Layout:** 3-column grid (KPI cards) → Full-width charts → 2-column tables

---

### B) PRODUCTS MANAGEMENT (`admin/products.php`)

**Purpose:** List, search, filter, edit, delete products.

**Table Structure:**
| Column | Content |
|--------|---------|
| Image | Product thumbnail (50×50px) |
| Product Name | Clickable link to edit |
| Category | Category badge |
| Price | Original price / Discount price (if applicable) |
| Stock | Stock count with color indicator (Green: >50, Yellow: 10-50, Red: <10) |
| Status | Featured / Active badges |
| Actions | Edit, Delete, Duplicate buttons |

**Features:**
1. **Search & Filter Bar**
   - Search by product name / SKU
   - Filter by: Category, Price range, Stock status (In stock / Low stock / Out of stock)
   - Sort by: Name, Price, Stock, Date added

2. **Pagination**
   - Show 25 products per page (configurable)
   - Next/Previous + Page number buttons

3. **Bulk Actions**
   - Select multiple products via checkboxes
   - Bulk delete, bulk featured toggle, bulk category change

4. **Add New Product Button**
   - Primary button at top of page
   - Opens modal or redirects to product-add.php

5. **Delete Confirmation Modal**
   - Confirm before deletion
   - Show product name being deleted

---

### C) ADD/EDIT PRODUCT (`admin/product-add.php` / `admin/product-edit.php`)

**Purpose:** Form to create or update product details.

**Form Layout (2-column on desktop, 1-column on mobile):**

**Left Column:**
1. Product Name (text input) - Required
2. SKU / Product Code (text input) - Required
3. Category (dropdown) - Required
4. Description (rich text editor or textarea) - Optional

**Right Column:**
1. Price (number input) - Required
2. Discount Price (number input) - Optional
3. Stock Quantity (number input) - Required
4. Is Featured? (checkbox toggle) - Optional

**Image Upload Section:**
- Drag-and-drop area or file input
- Preview thumbnail before upload
- Allowed formats: JPG, PNG, WebP
- Max file size: 2MB
- Recommended size: 1000×1000px (square, white background)
- Image optimization tooltip

**Form Actions:**
- Save Product (primary button)
- Save & Add Another (secondary button)
- Cancel (tertiary button)
- Delete Product (danger button, only on edit page)

**Validation:**
- Real-time feedback (green checkmark for valid fields, red error for invalid)
- Required field indicators (red asterisk)
- Success notification after save
- Error toast if save fails

---

### D) ORDERS MANAGEMENT (`admin/orders.php`)

**Purpose:** View, filter, update order status.

**Table Structure:**
| Column | Content |
|--------|---------|
| Order ID | Unique order number (clickable to details) |
| Customer | Customer name / phone |
| Items | Count of items in order |
| Total | Order amount (formatted with currency symbol) |
| Payment Method | COD / eSewa / Bank QR (badge) |
| Payment Status | Pending / Paid / Failed (color badge) |
| Delivery Status | Pending / Confirmed / Shipped / Delivered / Cancelled (color badge) |
| Date | Order creation date |
| Actions | View, Update Status |

**Features:**
1. **Filter Panel**
   - Filter by: Order Status, Payment Status, Payment Method, Date range
   - Presets: Today's Orders, This Week, This Month

2. **Search**
   - Search by Order ID, Customer phone, Customer name

3. **Status Update Modal**
   - Quick dropdown to change delivery status
   - Confirmation before update
   - Optional notes field
   - Save without page reload

4. **Order Details View**
   - Show order details page with full breakdown
   - Customer info, shipping address, items, payment proof (if applicable)
   - Status timeline (when order was placed, confirmed, shipped, etc.)

---

### E) CATEGORIES MANAGEMENT (`admin/categories.php`)

**Purpose:** Manage product categories.

**Interface:**
1. **Category List Table**
   - Category name
   - Number of products in category
   - Actions: Edit, Delete

2. **Add New Category Form** (Modal or separate section)
   - Category name (text input)
   - Category slug (auto-generated or manual)
   - Save button

3. **Edit Category Modal**
   - Prefilled form
   - Save / Cancel buttons

---

### F) CUSTOMERS MANAGEMENT (`admin/customers.php`)

**Purpose:** View customer details, orders, and manage customer data.

**Table Structure:**
| Column | Content |
|--------|---------|
| Name | Customer full name |
| Mobile | Phone number (login key) |
| Email | Email address |
| Address | Delivery address |
| Total Orders | Count of orders placed |
| Total Spent | Total purchase amount |
| Joined Date | Registration date |
| Actions | View, Delete, Edit |

**Features:**
1. **Search & Filter**
   - Search by name, mobile, email
   - Filter by: Registration date range, Total spent range

2. **Customer Details Modal**
   - Show customer profile
   - Order history (last 10 orders)
   - Total spending stats

---

### G) SETTINGS PAGE (`admin/settings.php`)

**Purpose:** Configure website-wide settings.

**Sections:**
1. **General Settings**
   - Site name, Site description, Currency symbol
   - Contact phone, Contact email

2. **Email Configuration**
   - SMTP server, Port, Username, Password
   - Email sender address, Email sender name
   - Test email button

3. **Payment Settings**
   - eSewa QR code (text or image)
   - Bank QR code (text or image)
   - Bank details for Bank QR
   - COD enabled/disabled toggle

4. **Shipping Settings**
   - Standard shipping fee
   - Free shipping threshold
   - Delivery time (days)

5. **Tax Settings** (optional)
   - Tax percentage
   - Tax-enabled countries/regions

**Form Actions:**
- Save Settings (with success notification)
- Reset to Defaults

---

## 🎨 UI/UX DESIGN REQUIREMENTS

### COLOR PALETTE (Modern, Professional)
- **Primary Color:** `#2563EB` (Professional Blue)
- **Secondary Color:** `#7C3AED` (Vibrant Purple)
- **Success:** `#10B981` (Fresh Green)
- **Warning:** `#F59E0B` (Warm Amber)
- **Danger:** `#EF4444` (Alert Red)
- **Neutral (Light):** `#F9FAFB`, `#F3F4F6`, `#E5E7EB`, `#D1D5DB`, `#9CA3AF`, `#6B7280`, `#4B5563`, `#1F2937`
- **Neutral (Dark):** `#0F172A`, `#1E293B`, `#334155`, `#475569`

### TYPOGRAPHY
- **Display Font:** `Inter` or `Poppins` (modern, clean, professional)
- **Body Font:** `Inter` (readable, consistent)
- **Font Sizes:**
  - H1: 32px (bold)
  - H2: 24px (bold)
  - H3: 18px (semi-bold)
  - Body: 14px (regular)
  - Small: 12px (regular)

### SPACING & LAYOUT
- **Base unit:** 8px grid
- **Container max-width:** 1400px
- **Sidebar width:** 260px (collapsible to 80px on mobile)
- **Page padding:** 24px (desktop), 16px (mobile)
- **Card padding:** 16px (desktop), 12px (mobile)
- **Gap between elements:** 8px, 16px, 24px (multiples of 8px)

### RESPONSIVE BREAKPOINTS
- **Mobile:** 320px - 640px (sidebar collapses, single column layout)
- **Tablet:** 641px - 1024px (sidebar collapsible, 2-column grid)
- **Desktop:** 1025px+ (full sidebar, multi-column layout)

### INTERACTIVE ELEMENTS

**Buttons:**
- **Primary:** Solid background (primary color), white text, rounded corners (6px)
- **Secondary:** Outlined style (primary color border, transparent bg), primary text
- **Danger:** Solid red background, white text
- **Disabled:** Greyed out (60% opacity)
- **Hover state:** Slightly darker shade, subtle shadow
- **Active state:** Darker shade, inset shadow
- **Size variants:** Small (32px), Medium (40px), Large (48px)

**Form Inputs:**
- **Border:** 1px solid `#D1D5DB`
- **Focus:** Blue outline (primary color), border color changes to blue
- **Error:** Red border, red error text below input
- **Valid:** Green checkmark icon, green border
- **Placeholder:** Lighter gray text
- **Padding:** 8px 12px (vertical: 8px, horizontal: 12px)

**Badges/Chips:**
- **Style:** Rounded pill (24px height)
- **Background:** Light version of status color (e.g., light green for success)
- **Text:** Dark version of status color
- **Padding:** 4px 12px

**Tables:**
- **Header row:** Light gray background (#F3F4F6), bold text
- **Row striping:** Alternate white and off-white background
- **Hover state:** Light blue background on row hover
- **Border:** 1px solid #E5E7EB between rows

**Modals:**
- **Backdrop:** Semi-transparent black (60% opacity)
- **Modal bg:** White (#FFFFFF)
- **Header:** Light gray background (#F9FAFB)
- **Border radius:** 8px
- **Shadow:** Subtle drop shadow (0 10px 15px -3px rgba(0,0,0,0.1))
- **Padding:** 24px
- **Close button:** X icon, top-right corner, hover changes to red

**Notifications/Toasts:**
- **Success:** Green background, white text, icon (checkmark), auto-dismiss (5 seconds)
- **Error:** Red background, white text, icon (alert), auto-dismiss (7 seconds)
- **Info:** Blue background, white text, icon (info), auto-dismiss (5 seconds)
- **Position:** Top-right corner, 16px from edges
- **Animation:** Slide in from right, slide out to right

---

## 🔧 HEADER STRUCTURE

**Header Components:**
1. **Left side:**
   - App logo + site name "Easy Shopping A.R.S"
   - Hamburger menu button (visible on mobile only)

2. **Right side:**
   - Search bar (desktop only) - "Quick search products, orders..."
   - Notifications icon (with unread badge count if any)
   - User profile dropdown
     - Profile picture / Avatar (initials)
     - Display name, role (Admin)
     - Divider
     - View Profile link
     - Settings link
     - Logout link

3. **Dark mode toggle** (near user dropdown)
   - Sun/Moon icon
   - Changes entire theme

**Header styling:**
- Height: 64px
- Background: White (light mode) / Dark navy (dark mode)
- Border-bottom: 1px solid #E5E7EB
- Sticky positioning (stays at top when scrolling)
- Shadow: Subtle drop shadow

---

## 📁 SIDEBAR STRUCTURE

**Sidebar Sections:**

1. **Main Navigation**
   - Dashboard (icon: chart-bar)
   - Products (icon: shopping-bag)
   - Orders (icon: truck)
   - Customers (icon: users)
   - Categories (icon: folder)
   - Coupons (icon: ticket)

2. **Additional (Lower section)**
   - Analytics (icon: trending-up) [Future feature]
   - Reports (icon: document-text)
   - Settings (icon: cog)
   - Email Logs (icon: mail)

3. **Active state:**
   - Active page has: Bold text, left border highlight (4px primary color), background tint

4. **Hover state:**
   - Background tint (light blue), smooth transition

**Sidebar styling:**
- Width: 260px (desktop), collapses to 80px on tablet/mobile
- Background: White (light mode) / Dark gray (dark mode)
- Border-right: 1px solid #E5E7EB
- Position: Fixed, full viewport height
- Overflow: Auto scroll if items exceed height
- Smooth collapse animation (0.3s)

**Collapsed sidebar (mobile):**
- Show only icons
- Tooltips on hover (small popup with label)
- Hamburger menu to toggle open/close

---

## 🔐 AUTHENTICATION & PERMISSIONS

**Admin authentication checks:**
- All admin pages must verify `is_admin()` before rendering
- Redirect unauthenticated users to login page
- Session timeout protection (recommend 30 mins)
- CSRF token validation on all forms

**UI indicators:**
- Show current admin name in header
- Show role badge (optional, since single admin)
- Logout button in profile dropdown

---

## 📊 DATA & FUNCTIONALITY

### Database Integration Points

**Products Table:**
- `SELECT * FROM products` - Product list
- `SELECT * FROM products WHERE id = ? ` - Single product
- `INSERT/UPDATE/DELETE operations` - CRUD

**Orders Table:**
- `SELECT * FROM orders WITH order_items` - Order with items
- `UPDATE orders SET delivery_status = ?` - Update status

**Users Table:**
- `SELECT * FROM users WHERE role = 'customer'` - Customer list

**Categories Table:**
- `SELECT * FROM categories` - Category list
- `CRUD operations` - Add/edit/delete

### API Endpoints / Form Handlers

**Product operations:**
- `POST /admin/product-add.php` - Create product
- `POST /admin/product-edit.php` - Update product
- `POST /admin/products.php?action=delete&id=X` - Delete product
- `POST /admin/products.php?action=feature&id=X` - Toggle featured

**Order operations:**
- `POST /admin/order-details.php?id=X` - Get order details
- `POST /admin/orders.php?action=update_status` - Update order status

**Category operations:**
- `POST /admin/categories.php?action=add` - Create category
- `POST /admin/categories.php?action=delete&id=X` - Delete category

---

## ✨ INTERACTIVE ELEMENTS & MICRO-INTERACTIONS

### Micro-interactions:

1. **Page transitions:** Subtle fade-in (200ms) when switching pages
2. **Button hover:** Color shift + shadow elevation (8px)
3. **Form validation:** Field borders animate to green/red, error shake animation (200ms)
4. **Modals:** Fade in backdrop + scale up content (300ms cubic-bezier)
5. **Tooltips:** Fade in on hover (100ms delay)
6. **Loading states:** Spinner icon with rotation animation
7. **Dropdown menus:** Slide down + fade in (150ms)
8. **Notification toasts:** Slide in from right (300ms), auto-dismiss with fade out (300ms)
9. **Checkbox toggle:** Smooth color transition (200ms)
10. **Table row selection:** Background color transition (150ms)

### Loading states:
- Show skeleton loaders for tables while data loads
- Spinner for form submissions
- Progress indicator for file uploads

---

## 🌙 DARK MODE

**Requirements:**
- All colors must have dark mode equivalents
- Toggle button in header
- Preference saved to localStorage
- Smooth transition between themes (0.3s)
- All text must have sufficient contrast in both modes (WCAG AA)

**Dark mode adjustments:**
- Background: #0F172A (very dark blue)
- Cards: #1E293B (dark blue)
- Text: #F1F5F9 (light gray)
- Borders: #334155 (dark gray)
- Hover states: Lighter shades

---

## 🎯 DELIVERABLES

Build the complete admin panel UI with:

1. ✅ **Header component** - Sticky, responsive, with theme toggle, user dropdown, notifications
2. ✅ **Sidebar component** - Collapsible navigation, active states, smooth animations
3. ✅ **Dashboard page** - KPI cards, charts, recent orders, quick actions
4. ✅ **Products page** - Table, search, filters, bulk actions, pagination
5. ✅ **Add/Edit Product form** - 2-column layout, image upload, validations
6. ✅ **Orders page** - Table, filters, status updates, order details modal
7. ✅ **Customers page** - Table, search, view customer details modal
8. ✅ **Categories page** - CRUD with modal
9. ✅ **Settings page** - Organized sections for email, payments, shipping
10. ✅ **Modals** - Reusable modal component for confirmations, status updates, details
11. ✅ **Responsive design** - Mobile (320px), Tablet (640px), Desktop (1024px+)
12. ✅ **Dark mode support** - Full theme toggle with localStorage persistence
13. ✅ **Animations** - Micro-interactions, page transitions, loading states
14. ✅ **Accessibility** - WCAG 2.1 AA compliant (alt text, semantic HTML, keyboard navigation)

---

## 🔑 KEY TECHNICAL CONSTRAINTS

- **No frameworks required** - Pure HTML/CSS/JS (can use Tailwind for utilities)
- **No build step** - Direct PHP file serving on Apache
- **Session-based auth** - Check `$_SESSION['user']['role'] == 'admin'`
- **PDO for DB** - Use prepared statements, connection from `config/db.php`
- **File uploads** - Store in `uploads/` directory, validate on server-side
- **Image optimization** - Compress images before upload, support WebP
- **Mobile-first approach** - Design for mobile first, scale up to desktop
- **Accessibility first** - All interactive elements must be keyboard accessible, proper ARIA labels

---

## 🚀 IMPLEMENTATION APPROACH

### Phase 1: Base Layout
- Header + Sidebar structure
- Basic routing / page navigation
- Responsive grid system

### Phase 2: Components
- Reusable button styles
- Form input components
- Badge/chip styles
- Modal component
- Toast notification system

### Phase 3: Dashboard Page
- KPI cards
- Revenue chart (use Chart.js or similar)
- Recent orders table
- Top products widget

### Phase 4: Data Pages
- Products page (table, search, filters)
- Orders page (table, filters, status updates)
- Customers page (table, search)
- Categories page (CRUD)

### Phase 5: Forms
- Product add/edit form
- Settings form
- Validation and error handling

### Phase 6: Polish
- Dark mode toggle
- Animations & transitions
- Accessibility audit
- Mobile responsiveness testing

---

## 📝 EXAMPLE DESIGN SPECIFICATIONS

### Card Component
```
Background: White (#FFFFFF) / Dark (#1E293B)
Padding: 16px
Border: 1px solid #E5E7EB / #334155 (dark)
Border-radius: 8px
Shadow: 0 1px 3px rgba(0,0,0,0.1)
Hover: Shadow increased to 0 4px 6px rgba(0,0,0,0.1)
```

### Button (Primary)
```
Background: #2563EB
Text: White (#FFFFFF)
Padding: 10px 16px
Border-radius: 6px
Font-weight: 600
Cursor: pointer
Transition: 200ms ease
Hover: Background #1D4ED8, Shadow 0 4px 12px rgba(37,99,235,0.3)
Active: Background #1E40AF, Transform scale(0.98)
Disabled: Opacity 0.5, Cursor not-allowed
```

### Form Input
```
Background: White (#FFFFFF) / #1F2937 (dark)
Border: 1px solid #D1D5DB / #475569 (dark)
Padding: 8px 12px
Border-radius: 6px
Font-size: 14px
Transition: 200ms ease
Focus: Border #2563EB, Outline none, Box-shadow: 0 0 0 3px rgba(37,99,235,0.1)
Error: Border #EF4444, Error text #EF4444
Valid: Border #10B981, Icon ✓ #10B981
```

---

## 🎓 DESIGN PHILOSOPHY

This admin panel should feel:
- **Professional** - Clean, organized, trustworthy
- **Efficient** - Quick to navigate, minimal clicks for common tasks
- **Responsive** - Works seamlessly on all devices
- **Accessible** - Usable by everyone, including people with disabilities
- **Modern** - Contemporary design, smooth animations, professional color palette
- **Scalable** - Easy to add new pages/features without redesigning

---

## ✅ SUCCESS CRITERIA

The admin panel UI is production-ready when:
1. ✅ All pages match the design specifications
2. ✅ Fully responsive on mobile, tablet, desktop
3. ✅ Dark mode toggle works smoothly
4. ✅ All interactive elements have proper hover/focus states
5. ✅ Forms validate and show error messages
6. ✅ Loading states show during data fetch
7. ✅ Notifications appear for user actions (success/error)
8. ✅ WCAG 2.1 AA accessibility standards met
9. ✅ Performance: Page load < 3 seconds
10. ✅ No console errors, clean code structure

---

## 📞 NOTES FOR IMPLEMENTATION

- Use **CSS Custom Properties (variables)** for colors - makes dark mode switching easy
- Implement **CSS Grid + Flexbox** for layouts (no floats)
- Use **CSS transitions** for smooth micro-interactions
- Keep JavaScript minimal - focus on progressive enhancement
- Optimize images: Use WebP with PNG fallbacks
- Test with screen readers (NVDA, JAWS, VoiceOver)
- Test on real devices (iPhone, Android, tablets) - not just browser DevTools

---

**Ready to build? This prompt should provide everything Google Stitch needs to create your admin panel.** 🚀
