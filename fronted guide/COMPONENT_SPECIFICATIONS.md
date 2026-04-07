# 🎨 ADMIN PANEL - COMPONENT SPECIFICATIONS & CODE REFERENCE

## Quick Component Library

This document provides ready-to-use component specifications and CSS code snippets for building the admin panel UI.

---

## 1️⃣ COLOR PALETTE (CSS Variables)

### Light Mode (Default)
```css
:root {
  /* Primary Colors */
  --primary: #2563EB;
  --primary-dark: #1D4ED8;
  --primary-darker: #1E40AF;
  --primary-light: #DBEAFE;
  
  /* Status Colors */
  --success: #10B981;
  --warning: #F59E0B;
  --danger: #EF4444;
  --info: #3B82F6;
  
  /* Neutral Colors */
  --white: #FFFFFF;
  --gray-50: #F9FAFB;
  --gray-100: #F3F4F6;
  --gray-200: #E5E7EB;
  --gray-300: #D1D5DB;
  --gray-400: #9CA3AF;
  --gray-500: #6B7280;
  --gray-600: #4B5563;
  --gray-700: #374151;
  --gray-800: #1F2937;
  --gray-900: #111827;
  
  /* Background */
  --bg-primary: #FFFFFF;
  --bg-secondary: #F9FAFB;
  --text-primary: #111827;
  --text-secondary: #6B7280;
  --border-color: #E5E7EB;
  
  /* Shadows */
  --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
  --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
}
```

### Dark Mode
```css
[data-theme="dark"] {
  --primary: #3B82F6;
  --primary-dark: #2563EB;
  --primary-darker: #1D4ED8;
  --primary-light: #1E3A8A;
  
  --success: #34D399;
  --warning: #FBBF24;
  --danger: #F87171;
  --info: #60A5FA;
  
  --white: #0F172A;
  --gray-50: #1E293B;
  --gray-100: #334155;
  --gray-200: #475569;
  --gray-300: #64748B;
  --gray-400: #94A3B8;
  --gray-500: #CBD5E1;
  --gray-600: #E2E8F0;
  --gray-700: #F1F5F9;
  --gray-800: #F8FAFC;
  --gray-900: #FFFFFF;
  
  --bg-primary: #0F172A;
  --bg-secondary: #1E293B;
  --text-primary: #F1F5F9;
  --text-secondary: #94A3B8;
  --border-color: #334155;
  
  --shadow-sm: 0 1px 2px rgba(255, 255, 255, 0.05);
  --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.3);
  --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.4);
}
```

---

## 2️⃣ TYPOGRAPHY

```css
/* Display Fonts */
body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  font-size: 14px;
  line-height: 1.5;
  color: var(--text-primary);
  background-color: var(--bg-primary);
  transition: background-color 0.3s ease, color 0.3s ease;
}

h1 {
  font-size: 32px;
  font-weight: 700;
  line-height: 1.2;
  margin-bottom: 16px;
}

h2 {
  font-size: 24px;
  font-weight: 700;
  line-height: 1.3;
  margin-bottom: 12px;
}

h3 {
  font-size: 18px;
  font-weight: 600;
  line-height: 1.4;
  margin-bottom: 8px;
}

.text-sm {
  font-size: 12px;
  line-height: 1.4;
}

.text-base {
  font-size: 14px;
  line-height: 1.5;
}

.text-lg {
  font-size: 16px;
  line-height: 1.6;
}

.font-bold {
  font-weight: 700;
}

.font-semibold {
  font-weight: 600;
}

.font-medium {
  font-weight: 500;
}
```

---

## 3️⃣ BUTTON STYLES

```css
/* Base Button */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 16px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  text-decoration: none;
  white-space: nowrap;
}

/* Primary Button */
.btn-primary {
  background-color: var(--primary);
  color: white;
  box-shadow: var(--shadow-sm);
}

.btn-primary:hover {
  background-color: var(--primary-dark);
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.btn-primary:active {
  background-color: var(--primary-darker);
  transform: scale(0.98);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

/* Secondary Button */
.btn-secondary {
  background-color: transparent;
  color: var(--primary);
  border: 1px solid var(--primary);
}

.btn-secondary:hover {
  background-color: var(--primary-light);
}

.btn-secondary:active {
  background-color: var(--primary);
  color: white;
}

/* Danger Button */
.btn-danger {
  background-color: var(--danger);
  color: white;
}

.btn-danger:hover {
  background-color: #DC2626;
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

/* Ghost Button */
.btn-ghost {
  background-color: transparent;
  color: var(--text-primary);
  border: 1px solid var(--border-color);
}

.btn-ghost:hover {
  background-color: var(--gray-100);
}

/* Size Variants */
.btn-sm {
  padding: 6px 12px;
  font-size: 12px;
}

.btn-lg {
  padding: 12px 20px;
  font-size: 16px;
}

/* Button Group */
.btn-group {
  display: flex;
  gap: 8px;
}

.btn-group.vertical {
  flex-direction: column;
}
```

---

## 4️⃣ FORM INPUTS

```css
/* Base Input */
input[type="text"],
input[type="email"],
input[type="password"],
input[type="number"],
input[type="date"],
input[type="tel"],
textarea,
select {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background-color: var(--bg-primary);
  color: var(--text-primary);
  font-size: 14px;
  font-family: inherit;
  transition: all 0.2s ease;
}

input:focus,
textarea:focus,
select:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

/* Error State */
input.error,
textarea.error,
select.error {
  border-color: var(--danger);
}

input.error:focus,
textarea.error:focus,
select.error:focus {
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

/* Valid State */
input.valid,
textarea.valid,
select.valid {
  border-color: var(--success);
}

input.valid:focus,
textarea.valid:focus,
select.valid:focus {
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

/* Disabled State */
input:disabled,
textarea:disabled,
select:disabled {
  background-color: var(--gray-100);
  color: var(--gray-400);
  cursor: not-allowed;
}

/* Label */
label {
  display: block;
  margin-bottom: 6px;
  font-weight: 500;
  font-size: 14px;
}

label .required {
  color: var(--danger);
}

/* Form Group */
.form-group {
  margin-bottom: 16px;
}

.form-group.inline {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

@media (max-width: 768px) {
  .form-group.inline {
    grid-template-columns: 1fr;
  }
}

/* Error Message */
.error-message {
  color: var(--danger);
  font-size: 12px;
  margin-top: 4px;
}

.error-message::before {
  content: "⚠ ";
}

/* Helper Text */
.helper-text {
  color: var(--text-secondary);
  font-size: 12px;
  margin-top: 4px;
}

/* Checkbox & Radio */
input[type="checkbox"],
input[type="radio"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
  margin-right: 8px;
}

.checkbox-label,
.radio-label {
  display: flex;
  align-items: center;
  margin-bottom: 8px;
  cursor: pointer;
  font-size: 14px;
}

.checkbox-label input,
.radio-label input {
  margin-right: 8px;
}
```

---

## 5️⃣ CARD COMPONENT

```css
.card {
  background-color: var(--bg-primary);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  padding: 16px;
  box-shadow: var(--shadow-sm);
  transition: all 0.2s ease;
}

.card:hover {
  box-shadow: var(--shadow-md);
}

.card-header {
  padding-bottom: 12px;
  border-bottom: 1px solid var(--border-color);
  margin-bottom: 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-title {
  font-size: 18px;
  font-weight: 600;
  margin: 0;
}

.card-content {
  padding: 0;
}

.card-footer {
  padding-top: 12px;
  border-top: 1px solid var(--border-color);
  margin-top: 12px;
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

/* KPI Card */
.kpi-card {
  padding: 20px;
  border-radius: 8px;
  background: linear-gradient(135deg, var(--bg-primary) 0%, var(--gray-50) 100%);
}

.kpi-card-value {
  font-size: 28px;
  font-weight: 700;
  margin: 8px 0;
}

.kpi-card-label {
  color: var(--text-secondary);
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.kpi-card-change {
  font-size: 12px;
  margin-top: 8px;
}

.kpi-card-change.positive {
  color: var(--success);
}

.kpi-card-change.negative {
  color: var(--danger);
}
```

---

## 6️⃣ BADGE/CHIP STYLES

```css
.badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 12px;
  border-radius: 24px;
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
  width: fit-content;
}

/* Status Badges */
.badge-success {
  background-color: rgba(16, 185, 129, 0.1);
  color: var(--success);
}

.badge-warning {
  background-color: rgba(245, 158, 11, 0.1);
  color: var(--warning);
}

.badge-danger {
  background-color: rgba(239, 68, 68, 0.1);
  color: var(--danger);
}

.badge-info {
  background-color: rgba(59, 130, 246, 0.1);
  color: var(--info);
}

.badge-primary {
  background-color: rgba(37, 99, 235, 0.1);
  color: var(--primary);
}

/* Payment Status Badges */
.badge-paid {
  background-color: rgba(16, 185, 129, 0.1);
  color: var(--success);
}

.badge-pending {
  background-color: rgba(245, 158, 11, 0.1);
  color: var(--warning);
}

.badge-failed {
  background-color: rgba(239, 68, 68, 0.1);
  color: var(--danger);
}

/* Delivery Status Badges */
.badge-confirmed {
  background-color: rgba(59, 130, 246, 0.1);
  color: var(--info);
}

.badge-shipped {
  background-color: rgba(249, 115, 22, 0.1);
  color: #F97316;
}

.badge-delivered {
  background-color: rgba(16, 185, 129, 0.1);
  color: var(--success);
}

.badge-cancelled {
  background-color: rgba(239, 68, 68, 0.1);
  color: var(--danger);
}
```

---

## 7️⃣ TABLE STYLES

```css
.table {
  width: 100%;
  border-collapse: collapse;
  background-color: var(--bg-primary);
  border-radius: 8px;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
}

.table thead {
  background-color: var(--gray-50);
  border-bottom: 1px solid var(--border-color);
}

.table thead th {
  padding: 12px 16px;
  text-align: left;
  font-weight: 600;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--text-secondary);
}

.table tbody td {
  padding: 12px 16px;
  border-bottom: 1px solid var(--border-color);
  font-size: 14px;
}

.table tbody tr {
  transition: background-color 0.2s ease;
}

.table tbody tr:hover {
  background-color: var(--gray-50);
}

.table tbody tr:last-child td {
  border-bottom: none;
}

/* Striped Table */
.table.striped tbody tr:nth-child(even) {
  background-color: var(--gray-50);
}

.table.striped tbody tr:nth-child(even):hover {
  background-color: var(--gray-100);
}

/* Checkbox Column */
.table th.checkbox-col,
.table td.checkbox-col {
  width: 40px;
  padding: 12px;
  text-align: center;
}

.table input[type="checkbox"] {
  margin: 0;
}

/* Image Column */
.table .product-image {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 4px;
}

/* Actions Column */
.table .actions {
  display: flex;
  gap: 8px;
}

.table .actions .btn {
  padding: 6px 12px;
  font-size: 12px;
}

/* Responsive Table */
@media (max-width: 768px) {
  .table {
    font-size: 12px;
  }
  
  .table th,
  .table td {
    padding: 8px 12px;
  }
  
  .table .actions {
    flex-direction: column;
    gap: 4px;
  }
}
```

---

## 8️⃣ MODAL COMPONENT

```css
/* Modal Overlay */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
}

.modal-overlay.active {
  opacity: 1;
  visibility: visible;
}

/* Modal Container */
.modal {
  background-color: var(--bg-primary);
  border-radius: 8px;
  box-shadow: var(--shadow-lg);
  max-width: 500px;
  width: 90%;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  animation: slideUp 0.3s ease;
  transform: scale(0.95);
}

.modal-overlay.active .modal {
  transform: scale(1);
}

@keyframes slideUp {
  from {
    transform: scale(0.95) translateY(20px);
    opacity: 0;
  }
  to {
    transform: scale(1) translateY(0);
    opacity: 1;
  }
}

/* Modal Header */
.modal-header {
  padding: 20px;
  border-bottom: 1px solid var(--border-color);
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: var(--gray-50);
}

.modal-title {
  font-size: 18px;
  font-weight: 600;
  margin: 0;
}

.modal-close {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: var(--text-secondary);
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s ease;
}

.modal-close:hover {
  color: var(--danger);
}

/* Modal Body */
.modal-body {
  padding: 20px;
  overflow-y: auto;
  flex: 1;
}

/* Modal Footer */
.modal-footer {
  padding: 20px;
  border-top: 1px solid var(--border-color);
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

/* Sizes */
.modal.small {
  max-width: 400px;
}

.modal.large {
  max-width: 800px;
}

/* Responsive */
@media (max-width: 768px) {
  .modal {
    max-width: 95%;
    max-height: 95vh;
  }
}
```

---

## 9️⃣ TOAST NOTIFICATIONS

```css
/* Toast Container */
.toast-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 2000;
  display: flex;
  flex-direction: column;
  gap: 12px;
  pointer-events: none;
}

/* Toast */
.toast {
  background-color: var(--gray-50);
  border: 1px solid var(--border-color);
  border-radius: 6px;
  padding: 12px 16px;
  box-shadow: var(--shadow-lg);
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 300px;
  animation: slideInRight 0.3s ease;
  pointer-events: all;
}

@keyframes slideInRight {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@keyframes slideOutRight {
  from {
    transform: translateX(0);
    opacity: 1;
  }
  to {
    transform: translateX(100%);
    opacity: 0;
  }
}

.toast.removing {
  animation: slideOutRight 0.3s ease;
}

/* Toast Variants */
.toast.success {
  background-color: rgba(16, 185, 129, 0.1);
  border-color: var(--success);
}

.toast.success .toast-icon {
  color: var(--success);
}

.toast.error {
  background-color: rgba(239, 68, 68, 0.1);
  border-color: var(--danger);
}

.toast.error .toast-icon {
  color: var(--danger);
}

.toast.info {
  background-color: rgba(59, 130, 246, 0.1);
  border-color: var(--info);
}

.toast.info .toast-icon {
  color: var(--info);
}

.toast.warning {
  background-color: rgba(245, 158, 11, 0.1);
  border-color: var(--warning);
}

.toast.warning .toast-icon {
  color: var(--warning);
}

/* Toast Icon */
.toast-icon {
  font-size: 18px;
  flex-shrink: 0;
}

/* Toast Message */
.toast-message {
  flex: 1;
  font-size: 14px;
}

/* Toast Close Button */
.toast-close {
  background: none;
  border: none;
  font-size: 18px;
  cursor: pointer;
  color: var(--text-secondary);
  padding: 0;
  margin-left: 8px;
  flex-shrink: 0;
}

/* Responsive */
@media (max-width: 640px) {
  .toast-container {
    top: auto;
    bottom: 20px;
    left: 20px;
    right: 20px;
  }
  
  .toast {
    min-width: auto;
    width: 100%;
  }
}
```

---

## 🔟 HEADER & SIDEBAR

```css
/* Header */
.header {
  height: 64px;
  background-color: var(--bg-primary);
  border-bottom: 1px solid var(--border-color);
  padding: 0 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: sticky;
  top: 0;
  z-index: 100;
  box-shadow: var(--shadow-sm);
}

.header-left {
  display: flex;
  align-items: center;
  gap: 20px;
}

.logo {
  font-size: 20px;
  font-weight: 700;
  color: var(--primary);
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 8px;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 16px;
}

/* Sidebar */
.sidebar {
  width: 260px;
  background-color: var(--bg-primary);
  border-right: 1px solid var(--border-color);
  padding: 20px 0;
  position: fixed;
  left: 0;
  top: 64px;
  bottom: 0;
  overflow-y: auto;
  transition: width 0.3s ease;
  z-index: 50;
}

.sidebar.collapsed {
  width: 80px;
}

/* Navigation */
.nav {
  display: flex;
  flex-direction: column;
  gap: 0;
}

.nav-item {
  padding: 12px 16px;
  color: var(--text-secondary);
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 12px;
  transition: all 0.2s ease;
  position: relative;
}

.nav-item:hover {
  background-color: var(--gray-50);
  color: var(--text-primary);
}

.nav-item.active {
  color: var(--primary);
  background-color: rgba(37, 99, 235, 0.1);
  border-left: 4px solid var(--primary);
  padding-left: 12px;
  font-weight: 600;
}

.nav-icon {
  font-size: 18px;
  flex-shrink: 0;
}

.nav-label {
  flex: 1;
  font-size: 14px;
}

.sidebar.collapsed .nav-label {
  display: none;
}

/* Main Content */
.main {
  margin-left: 260px;
  margin-top: 64px;
  padding: 24px;
  min-height: calc(100vh - 64px);
  transition: margin-left 0.3s ease;
}

.main.expanded {
  margin-left: 80px;
}

@media (max-width: 768px) {
  .sidebar {
    width: 80px;
  }
  
  .main {
    margin-left: 80px;
    padding: 16px;
  }
  
  .nav-label {
    display: none;
  }
}

@media (max-width: 640px) {
  .header {
    padding: 0 16px;
  }
  
  .sidebar {
    width: 60px;
    top: 56px;
  }
  
  .main {
    margin-left: 60px;
    margin-top: 56px;
    padding: 12px;
  }
}
```

---

## 1️⃣1️⃣ LAYOUT GRID

```css
/* Container */
.container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 24px;
}

/* Grid */
.grid {
  display: grid;
  gap: 24px;
}

.grid-2 {
  grid-template-columns: repeat(2, 1fr);
}

.grid-3 {
  grid-template-columns: repeat(3, 1fr);
}

.grid-4 {
  grid-template-columns: repeat(4, 1fr);
}

/* Responsive Grid */
@media (max-width: 1024px) {
  .grid-4 {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .grid-3 {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .grid-2,
  .grid-3,
  .grid-4 {
    grid-template-columns: 1fr;
  }
}

/* Flex Utils */
.flex {
  display: flex;
}

.flex-between {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.flex-center {
  display: flex;
  justify-content: center;
  align-items: center;
}

.flex-col {
  display: flex;
  flex-direction: column;
}

.gap-2 { gap: 8px; }
.gap-3 { gap: 12px; }
.gap-4 { gap: 16px; }
.gap-5 { gap: 20px; }
.gap-6 { gap: 24px; }
```

---

## 🎯 QUICK IMPLEMENTATION CHECKLIST

- [ ] Copy CSS variables to your stylesheet
- [ ] Implement button styles (primary, secondary, danger)
- [ ] Create form input styles with focus/error states
- [ ] Build card component styles
- [ ] Add badge/chip styles for status indicators
- [ ] Style tables with hover states
- [ ] Create modal component with overlay
- [ ] Build toast notification system
- [ ] Style header and sidebar
- [ ] Test dark mode toggle
- [ ] Verify responsive breakpoints
- [ ] Test accessibility (keyboard navigation, color contrast)

---

This component library provides a solid foundation for building the complete admin panel UI. Use these as building blocks for your design!

