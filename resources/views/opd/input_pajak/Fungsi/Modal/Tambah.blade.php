<div class="modal fade" id="modalInput" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="formInput" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" id="id_pajak">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Input Data Pajak</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    {{-- DATA TBP --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nomor TBP</label>
                            <input type="text" class="form-control" id="nomor_tbp" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nomor SPM</label>
                            <input type="text" class="form-control" id="no_spm" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Jenis Pajak</label>
                            <input type="text" class="form-control" id="jenis_pajak" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nilai Pajak</label>
                            <input type="text" class="form-control" id="nilai_pajak" readonly>
                        </div>
                    </div>

                    <hr>

                    {{-- INPUT OPD --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Akun PAjak</label>
                            <select name="akun_pajak" id="akun_pajak" class="form-control" required>
                                <option value="">-- Pilih Akun Pajak --</option>
                                    @foreach($akun_pajak as $a)
                                        <option value="{{ $a->kode_akun }}">
                                        {{ $a->kode_akun }} - {{ $a->nama_akun }}
                                        </option>
                                    @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Rekening Belanja</label>
                            <input type="text" name="rek_belanja" id="rek_belanja" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama NPWP</label>
                            <input type="text" name="nama_npwp" id="nama_npwp" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No NPWP</label>
                            <input type="text" name="no_npwp" id="no_npwp" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">NTPN</label>
                            <input type="text" name="ntpn" id="ntpn" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">E-Billing</label>
                            <input type="text" name="id_billing" id="id_billing" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Bukti Setoran</label>
                            <input type="file" name="bukti_setoran" class="form-control"
                                   accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <div class="col-md-6">
                            <label>Bukti Setoran (Lama)</label>
                            <div id="previewBukti" class="mt-1"></div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" id="btnSimpan" type="submit">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
