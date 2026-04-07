    </main><!-- /admin-main -->
  </div><!-- /admin-main-wrapper -->

</div><!-- /admin-shell -->

<!-- Global Confirm Modal -->
<div id="confirm-modal" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="confirm-modal-title">
  <div class="modal-box modal-sm">
    <div class="modal-header">
      <h2 class="modal-title" id="confirm-modal-title">Confirm Action</h2>
      <button class="modal-close" data-modal-close aria-label="Close">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <p style="color:var(--text-secondary);font-size:14px;" id="confirm-message">Are you sure you want to proceed? This action cannot be undone.</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" data-modal-close>Cancel</button>
      <button class="btn btn-danger" id="confirm-ok">Confirm</button>
    </div>
  </div>
</div>

<!-- ARS Admin JS -->
<script src="../assets/js/admin.js"></script>

<!-- Reinit Lucide after all content loaded -->
<script>
  if (typeof lucide !== 'undefined') {
    document.addEventListener('DOMContentLoaded', function() { lucide.createIcons(); });
    lucide.createIcons();
  }
</script>

</body>
</html>
