<!-- ================= MODAL EDIT PAJAK BPKAD ================= -->
<div class="modal fade" id="modalInput" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <form id="formInput" enctype="multipart/form-data">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">
                        ✏️ Edit Pajak (BPKAD)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="id" id="id_pajak">

                    <div class="row mb-2">
                        <div class="col-md-6">
                            <label>No TBP</label>
                            <input type="text" id="nomor_tbp" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label>No SPM</label>
                            <input type="text" id="no_spm" class="form-control" readonly>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label>Jenis Pajak</label>
                        <input type="text" id="jenis_pajak" class="form-control" readonly>
                    </div>

                    <div class="mb-2">
                        <label>Nilai Pajak</label>
                        <input type="text" id="nilai_pajak" class="form-control" readonly>
                    </div>

                    <hr>

                    <div class="row mb-2">
                        <div class="col-md-6">
                            <label>Akun Pajak</label>
                            <select name="akun_pajak" id="akun_pajak" class="form-control" required>
                                <option value="">-- Pilih Akun Pajak --</option>
                                @foreach($akun_pajak ?? [] as $a)
                                    <option value="{{ $a->kode_akun }}">
                                        {{ $a->kode_akun }} - {{ $a->nama_akun }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label>Rekening Belanja</label>
                            <input type="text" name="rek_belanja" id="rek_belanja" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-6">
                            <label>Nama NPWP</label>
                            <input type="text" name="nama_npwp" id="nama_npwp" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>No NPWP</label>
                            <input type="text" name="no_npwp" id="no_npwp" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label>NTPN</label>
                        <input type="text" name="ntpn" id="ntpn" class="form-control" required>
                    </div>

                    <div class="mb-2">
                        <label>Upload Bukti Setoran (Opsional)</label>
                        <input type="file" name="bukti_setoran" class="form-control">
                    </div>

                    <div class="mt-2" id="previewBukti"></div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Tutup
                    </button>
                    <button type="submit" class="btn btn-primary">
                        💾 Simpan Perubahan
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
<!-- ========================================================== -->
