# 💻 ADMIN PANEL - JAVASCRIPT IMPLEMENTATION GUIDE

## Core Interactive Components

This guide shows how to implement the interactive elements in vanilla JavaScript.

---

## 1. DARK MODE TOGGLE

```javascript
// Dark Mode Manager
class DarkModeManager {
  constructor() {
    this.prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    this.currentTheme = localStorage.getItem('theme') || (this.prefersDark ? 'dark' : 'light');
    this.toggleBtn = document.getElementById('theme-toggle');
    
    this.init();
  }
  
  init() {
    // Set initial theme
    this.applyTheme(this.currentTheme);
    
    // Add toggle listener
    if (this.toggleBtn) {
      this.toggleBtn.addEventListener('click', () => this.toggle());
    }
    
    // Listen for system preference changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
      if (!localStorage.getItem('theme')) {
        this.applyTheme(e.matches ? 'dark' : 'light');
      }
    });
  }
  
  toggle() {
    const newTheme = this.currentTheme === 'light' ? 'dark' : 'light';
    this.applyTheme(newTheme);
    localStorage.setItem('theme', newTheme);
  }
  
  applyTheme(theme) {
    this.currentTheme = theme;
    document.documentElement.setAttribute('data-theme', theme);
    
    // Update toggle button icon
    if (this.toggleBtn) {
      this.toggleBtn.innerHTML = theme === 'dark' ? '☀️' : '🌙';
    }
  }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
  new DarkModeManager();
});
```

**HTML Usage:**
```html
<button id="theme-toggle" class="btn btn-ghost" aria-label="Toggle dark mode">
  🌙
</button>
```

---

## 2. MODAL COMPONENT

```javascript
class Modal {
  constructor(options = {}) {
    this.element = null;
    this.overlay = null;
    this.options = {
      size: 'medium', // small, medium, large
      closeOnOverlayClick: true,
      closeOnEscape: true,
      onClose: null,
      onOpen: null,
      ...options
    };
  }
  
  create(title, content, footer = '') {
    // Create overlay
    this.overlay = document.createElement('div');
    this.overlay.className = 'modal-overlay';
    
    // Create modal
    this.element = document.createElement('div');
    this.element.className = `modal ${this.options.size === 'small' ? 'small' : ''} ${this.options.size === 'large' ? 'large' : ''}`;
    
    // Modal structure
    this.element.innerHTML = `
      <div class="modal-header">
        <h2 class="modal-title">${title}</h2>
        <button class="modal-close" aria-label="Close modal">&times;</button>
      </div>
      <div class="modal-body">
        ${content}
      </div>
      ${footer ? `<div class="modal-footer">${footer}</div>` : ''}
    `;
    
    this.overlay.appendChild(this.element);
    
    // Event listeners
    this.setupEventListeners();
    
    return this;
  }
  
  setupEventListeners() {
    const closeBtn = this.element.querySelector('.modal-close');
    
    // Close button
    closeBtn.addEventListener('click', () => this.close());
    
    // Overlay click
    if (this.options.closeOnOverlayClick) {
      this.overlay.addEventListener('click', (e) => {
        if (e.target === this.overlay) this.close();
      });
    }
    
    // Escape key
    if (this.options.closeOnEscape) {
      this.escapeListener = (e) => {
        if (e.key === 'Escape') this.close();
      };
    }
  }
  
  open() {
    document.body.appendChild(this.overlay);
    
    // Trigger animation
    setTimeout(() => {
      this.overlay.classList.add('active');
    }, 10);
    
    // Disable body scroll
    document.body.style.overflow = 'hidden';
    
    if (this.escapeListener) {
      document.addEventListener('keydown', this.escapeListener);
    }
    
    if (this.options.onOpen) {
      this.options.onOpen();
    }
  }
  
  close() {
    this.overlay.classList.remove('active');
    
    setTimeout(() => {
      this.overlay.remove();
      document.body.style.overflow = '';
      
      if (this.escapeListener) {
        document.removeEventListener('keydown', this.escapeListener);
      }
      
      if (this.options.onClose) {
        this.options.onClose();
      }
    }, 300);
  }
}

// Usage Example:
const deleteModal = new Modal({ size: 'small' });
deleteModal.create(
  'Delete Product',
  `<p>Are you sure you want to delete this product?</p>
   <p class="text-sm" style="color: var(--text-secondary);">This action cannot be undone.</p>`,
  `<button class="btn btn-ghost">Cancel</button>
   <button class="btn btn-danger">Delete</button>`
);

document.getElementById('delete-btn').addEventListener('click', () => {
  deleteModal.open();
});
```

---

## 3. TOAST NOTIFICATION SYSTEM

```javascript
class Toast {
  static container = null;
  
  static init() {
    if (!this.container) {
      this.container = document.createElement('div');
      this.container.className = 'toast-container';
      document.body.appendChild(this.container);
    }
  }
  
  static show(message, type = 'info', duration = 5000) {
    this.init();
    
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    const icons = {
      success: '✓',
      error: '✕',
      info: 'ℹ',
      warning: '⚠'
    };
    
    toast.innerHTML = `
      <span class="toast-icon">${icons[type]}</span>
      <span class="toast-message">${message}</span>
      <button class="toast-close" aria-label="Close notification">×</button>
    `;
    
    this.container.appendChild(toast);
    
    // Close button
    toast.querySelector('.toast-close').addEventListener('click', () => {
      this.removeToast(toast);
    });
    
    // Auto-remove
    if (duration > 0) {
      setTimeout(() => {
        this.removeToast(toast);
      }, duration);
    }
  }
  
  static removeToast(toast) {
    toast.classList.add('removing');
    setTimeout(() => {
      toast.remove();
    }, 300);
  }
  
  // Convenience methods
  static success(message, duration = 5000) {
    this.show(message, 'success', duration);
  }
  
  static error(message, duration = 7000) {
    this.show(message, 'error', duration);
  }
  
  static info(message, duration = 5000) {
    this.show(message, 'info', duration);
  }
  
  static warning(message, duration = 6000) {
    this.show(message, 'warning', duration);
  }
}

// Usage:
Toast.success('Product added successfully!');
Toast.error('Failed to delete product.');
Toast.info('Please fill all required fields.');
Toast.warning('This action cannot be undone.');
```

---

## 4. FORM VALIDATION

```javascript
class FormValidator {
  constructor(form) {
    this.form = form;
    this.errors = {};
    this.setupValidation();
  }
  
  setupValidation() {
    const inputs = this.form.querySelectorAll('input, textarea, select');
    
    inputs.forEach(input => {
      input.addEventListener('blur', () => this.validateField(input));
      input.addEventListener('change', () => this.validateField(input));
    });
    
    this.form.addEventListener('submit', (e) => this.handleSubmit(e));
  }
  
  validateField(field) {
    const value = field.value.trim();
    const fieldName = field.name || field.id;
    let isValid = true;
    let errorMsg = '';
    
    // Required validation
    if (field.hasAttribute('required') && !value) {
      isValid = false;
      errorMsg = 'This field is required';
    }
    // Email validation
    else if (field.type === 'email' && value) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(value)) {
        isValid = false;
        errorMsg = 'Please enter a valid email';
      }
    }
    // Number validation
    else if (field.type === 'number' && value) {
      if (isNaN(value)) {
        isValid = false;
        errorMsg = 'Please enter a valid number';
      }
      // Check min/max
      if (field.hasAttribute('min') && parseFloat(value) < parseFloat(field.min)) {
        isValid = false;
        errorMsg = `Must be at least ${field.min}`;
      }
      if (field.hasAttribute('max') && parseFloat(value) > parseFloat(field.max)) {
        isValid = false;
        errorMsg = `Must not exceed ${field.max}`;
      }
    }
    // Min length validation
    else if (field.hasAttribute('minlength') && value.length < parseInt(field.minlength)) {
      isValid = false;
      errorMsg = `Must be at least ${field.minlength} characters`;
    }
    // Custom validation
    else if (field.dataset.validate) {
      const validation = this.customValidations[field.dataset.validate];
      if (validation && !validation(value)) {
        isValid = false;
        errorMsg = field.dataset.error || 'Invalid input';
      }
    }
    
    this.updateFieldState(field, isValid, errorMsg);
    return isValid;
  }
  
  updateFieldState(field, isValid, errorMsg) {
    const group = field.closest('.form-group');
    let errorElement = group?.querySelector('.error-message');
    
    if (isValid) {
      field.classList.remove('error');
      field.classList.add('valid');
      if (errorElement) errorElement.remove();
      delete this.errors[field.name];
    } else {
      field.classList.remove('valid');
      field.classList.add('error');
      
      if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.className = 'error-message';
        group?.appendChild(errorElement);
      }
      
      errorElement.textContent = errorMsg;
      this.errors[field.name] = errorMsg;
    }
  }
  
  handleSubmit(e) {
    let isFormValid = true;
    const inputs = this.form.querySelectorAll('input, textarea, select');
    
    inputs.forEach(input => {
      if (!this.validateField(input)) {
        isFormValid = false;
      }
    });
    
    if (!isFormValid) {
      e.preventDefault();
      Toast.error('Please fix the errors in the form');
    }
  }
  
  customValidations = {
    phone: (value) => /^[0-9]{10}$/.test(value),
    sku: (value) => /^[A-Z0-9-]+$/.test(value),
    slug: (value) => /^[a-z0-9-]+$/.test(value)
  };
}

// Usage:
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('product-form');
  if (form) {
    new FormValidator(form);
  }
});
```

---

## 5. TABLE FUNCTIONALITY

```javascript
class DataTable {
  constructor(tableId, options = {}) {
    this.table = document.getElementById(tableId);
    this.options = {
      sortable: true,
      searchable: true,
      paginated: true,
      perPage: 25,
      ...options
    };
    
    this.currentPage = 1;
    this.sortColumn = null;
    this.sortDirection = 'asc';
    this.searchTerm = '';
    this.data = [];
    
    this.init();
  }
  
  init() {
    if (this.options.sortable) this.makeSortable();
    if (this.options.paginated) this.setupPagination();
  }
  
  makeSortable() {
    const headers = this.table.querySelectorAll('thead th');
    headers.forEach((header, index) => {
      if (header.textContent.toLowerCase() !== 'actions') {
        header.style.cursor = 'pointer';
        header.addEventListener('click', () => this.sort(index));
      }
    });
  }
  
  sort(columnIndex) {
    if (this.sortColumn === columnIndex) {
      this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
      this.sortColumn = columnIndex;
      this.sortDirection = 'asc';
    }
    
    this.updateTable();
  }
  
  search(term) {
    this.searchTerm = term.toLowerCase();
    this.currentPage = 1;
    this.updateTable();
  }
  
  setupPagination() {
    const container = this.table.parentElement;
    const paginationDiv = document.createElement('div');
    paginationDiv.className = 'pagination';
    paginationDiv.id = `${this.table.id}-pagination`;
    container.appendChild(paginationDiv);
  }
  
  updateTable() {
    const rows = Array.from(this.table.querySelectorAll('tbody tr'));
    
    // Filter
    let filtered = rows.filter(row => {
      if (!this.searchTerm) return true;
      return row.textContent.toLowerCase().includes(this.searchTerm);
    });
    
    // Sort
    if (this.sortColumn !== null) {
      filtered.sort((a, b) => {
        const aCell = a.cells[this.sortColumn].textContent.trim();
        const bCell = b.cells[this.sortColumn].textContent.trim();
        
        let comparison = 0;
        if (!isNaN(aCell) && !isNaN(bCell)) {
          comparison = parseFloat(aCell) - parseFloat(bCell);
        } else {
          comparison = aCell.localeCompare(bCell);
        }
        
        return this.sortDirection === 'asc' ? comparison : -comparison;
      });
    }
    
    // Paginate
    if (this.options.paginated) {
      const start = (this.currentPage - 1) * this.options.perPage;
      const end = start + this.options.perPage;
      const paged = filtered.slice(start, end);
      
      this.displayRows(paged);
      this.updatePaginationControls(filtered.length);
    } else {
      this.displayRows(filtered);
    }
  }
  
  displayRows(rows) {
    const tbody = this.table.querySelector('tbody');
    tbody.querySelectorAll('tr').forEach(row => row.style.display = 'none');
    rows.forEach(row => row.style.display = '');
  }
  
  updatePaginationControls(totalItems) {
    const totalPages = Math.ceil(totalItems / this.options.perPage);
    const paginationDiv = document.getElementById(`${this.table.id}-pagination`);
    
    paginationDiv.innerHTML = '';
    
    if (totalPages <= 1) return;
    
    // Previous button
    const prevBtn = document.createElement('button');
    prevBtn.className = 'btn btn-sm btn-ghost';
    prevBtn.textContent = 'Previous';
    prevBtn.disabled = this.currentPage === 1;
    prevBtn.addEventListener('click', () => {
      this.currentPage--;
      this.updateTable();
    });
    
    paginationDiv.appendChild(prevBtn);
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
      const pageBtn = document.createElement('button');
      pageBtn.className = `btn btn-sm ${i === this.currentPage ? 'btn-primary' : 'btn-ghost'}`;
      pageBtn.textContent = i;
      pageBtn.addEventListener('click', () => {
        this.currentPage = i;
        this.updateTable();
      });
      
      paginationDiv.appendChild(pageBtn);
    }
    
    // Next button
    const nextBtn = document.createElement('button');
    nextBtn.className = 'btn btn-sm btn-ghost';
    nextBtn.textContent = 'Next';
    nextBtn.disabled = this.currentPage === totalPages;
    nextBtn.addEventListener('click', () => {
      this.currentPage++;
      this.updateTable();
    });
    
    paginationDiv.appendChild(nextBtn);
  }
}

// Usage:
document.addEventListener('DOMContentLoaded', () => {
  const productsTable = new DataTable('products-table', {
    sortable: true,
    searchable: true,
    paginated: true,
    perPage: 25
  });
  
  // Search input
  const searchInput = document.getElementById('search-input');
  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      productsTable.search(e.target.value);
    });
  }
});
```

---

## 6. SIDEBAR COLLAPSE TOGGLE

```javascript
class Sidebar {
  constructor(sidebarSelector = '.sidebar', mainSelector = '.main') {
    this.sidebar = document.querySelector(sidebarSelector);
    this.main = document.querySelector(mainSelector);
    this.toggleBtn = document.querySelector('.hamburger-menu');
    this.isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
    
    if (this.sidebar && this.main) {
      this.init();
    }
  }
  
  init() {
    if (this.isCollapsed) {
      this.collapse();
    }
    
    if (this.toggleBtn) {
      this.toggleBtn.addEventListener('click', () => this.toggle());
    }
    
    // Close sidebar on mobile when item is clicked
    if (window.innerWidth <= 768) {
      this.sidebar.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', () => {
          if (window.innerWidth <= 768) this.collapse();
        });
      });
    }
  }
  
  toggle() {
    this.isCollapsed ? this.expand() : this.collapse();
  }
  
  collapse() {
    this.isCollapsed = true;
    this.sidebar.classList.add('collapsed');
    this.main.classList.add('expanded');
    localStorage.setItem('sidebar-collapsed', 'true');
  }
  
  expand() {
    this.isCollapsed = false;
    this.sidebar.classList.remove('collapsed');
    this.main.classList.remove('expanded');
    localStorage.setItem('sidebar-collapsed', 'false');
  }
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
  new Sidebar();
});
```

---

## 7. STATUS UPDATE WITH AJAX

```javascript
class StatusUpdater {
  static async updateStatus(orderId, newStatus) {
    try {
      const response = await fetch('/admin/orders.php?action=update_status', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          order_id: orderId,
          status: newStatus
        })
      });
      
      if (!response.ok) throw new Error('Network error');
      
      const data = await response.json();
      
      if (data.success) {
        Toast.success('Order status updated successfully');
        // Update UI
        const statusBadge = document.querySelector(`[data-order-id="${orderId}"] .status-badge`);
        if (statusBadge) {
          statusBadge.textContent = newStatus;
          statusBadge.className = `badge badge-${newStatus.toLowerCase()}`;
        }
      } else {
        Toast.error(data.message || 'Failed to update status');
      }
    } catch (error) {
      Toast.error('Error updating status: ' + error.message);
    }
  }
}

// Usage in modal
const statusModal = new Modal({ size: 'small' });
const orderId = 123;

statusModal.create(
  'Update Order Status',
  `<select id="status-select" class="form-control" required>
    <option value="">Select status...</option>
    <option value="Confirmed">Confirmed</option>
    <option value="Shipped">Shipped</option>
    <option value="Delivered">Delivered</option>
    <option value="Cancelled">Cancelled</option>
  </select>`,
  `<button class="btn btn-ghost" onclick="this.closest('.modal-overlay').parentElement.removeChild(this.closest('.modal-overlay'))">
    Cancel
  </button>
  <button class="btn btn-primary" onclick="updateOrderStatus()">Update</button>`
);

function updateOrderStatus() {
  const select = statusModal.element.querySelector('#status-select');
  if (select.value) {
    StatusUpdater.updateStatus(orderId, select.value);
    statusModal.close();
  }
}
```

---

## 8. FILE UPLOAD PREVIEW

```javascript
class FileUploadPreview {
  constructor(inputSelector, previewSelector) {
    this.input = document.querySelector(inputSelector);
    this.preview = document.querySelector(previewSelector);
    
    if (this.input) {
      this.input.addEventListener('change', (e) => this.handleFileSelect(e));
    }
  }
  
  handleFileSelect(e) {
    const files = e.target.files;
    
    if (!files.length) {
      this.preview.innerHTML = '';
      return;
    }
    
    const file = files[0];
    
    // Validate file
    const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
    const maxSize = 2 * 1024 * 1024; // 2MB
    
    if (!validTypes.includes(file.type)) {
      Toast.error('Please upload a valid image (JPG, PNG, WebP)');
      this.input.value = '';
      return;
    }
    
    if (file.size > maxSize) {
      Toast.error('File size must be less than 2MB');
      this.input.value = '';
      return;
    }
    
    // Show preview
    const reader = new FileReader();
    reader.onload = (e) => {
      this.preview.innerHTML = `
        <div class="preview-container">
          <img src="${e.target.result}" alt="Preview" class="preview-image">
          <div class="preview-info">
            <p class="text-sm"><strong>File:</strong> ${file.name}</p>
            <p class="text-sm"><strong>Size:</strong> ${(file.size / 1024).toFixed(2)} KB</p>
          </div>
        </div>
      `;
    };
    
    reader.readAsDataURL(file);
  }
}

// Usage:
document.addEventListener('DOMContentLoaded', () => {
  new FileUploadPreview('#product-image', '.image-preview');
});
```

---

## 📋 QUICK IMPLEMENTATION CHECKLIST

```html
<!-- Required Scripts Order -->
<script src="/assets/js/dark-mode.js"></script>
<script src="/assets/js/modal.js"></script>
<script src="/assets/js/toast.js"></script>
<script src="/assets/js/form-validator.js"></script>
<script src="/assets/js/data-table.js"></script>
<script src="/assets/js/sidebar.js"></script>
<script src="/assets/js/status-updater.js"></script>
<script src="/assets/js/file-upload.js"></script>
```

---

**This JavaScript library covers all the interactive elements needed for your admin panel. Customize as needed!**

