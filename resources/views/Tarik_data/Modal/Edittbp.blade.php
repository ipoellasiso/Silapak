<div class="modal fade" id="modalEditTbp" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit TBP</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="formEditTbp">
          @csrf
          @method('PUT')

          <input type="hidden" id="edit_id_tbp">

          <div class="mb-3">
            <label>Nomor SPM</label>
            <input type="text" id="edit_no_spm" class="form-control" required>
          </div>

          <div class="mb-3">
            <label>Tanggal TBP</label>
            <input type="date" id="edit_tanggal_tbp" class="form-control" required>
          </div>

        </form>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-primary" id="btnSimpanEdit">
          <i class="fas fa-save"></i> Simpan
        </button>
      </div>
    </div>
  </div>
</div>
