<div class="modal fade" id="modalEditLs" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            {{-- ================= HEADER ================= --}}
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    ✏️ Edit Pajak LS
                </h5>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
            </div>

            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tab-form">
                        Form Edit
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-log">
                        Riwayat Koreksi
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                {{-- ================= FORM ================= --}}
                <div class="tab-pane fade show active" id="tab-form">
                    <form id="formEditLs">
                        @csrf

                        <div class="modal-body">

                            <input type="hidden" name="id" id="id_ls">

                            {{-- ================= INFO PAJAK ================= --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Jenis Pajak</label>
                                    <input type="text"
                                        id="nama_pajak"
                                        class="form-control"
                                        readonly>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Nilai Pajak</label>
                                    <input type="text"
                                        id="nilai_pajak"
                                        class="form-control text-end"
                                        readonly>
                                </div>
                            </div>

                            <hr>

                            {{-- ================= AKUN PAJAK ================= --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    Akun Pajak <span class="text-danger">*</span>
                                </label>
                                <select name="akun_pajak"
                                        id="akun_pajak"
                                        class="form-control"
                                        required>
                                    <option value="">-- Pilih Akun Pajak --</option>
                                    @foreach($akun_pajak as $akun)
                                        <option value="{{ $akun->kode_akun }}">
                                            {{ $akun->kode_akun }} - {{ $akun->nama_akun }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- ================= REKENING BELANJA ================= --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    Rekening Belanja <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                    name="rek_belanja"
                                    id="rek_belanja"
                                    class="form-control"
                                    required>
                            </div>

                            <div class="row">
                                {{-- ================= NTPN ================= --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">
                                        NTPN <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                        name="ntpn"
                                        id="ntpn"
                                        class="form-control"
                                        required>
                                </div>

                                {{-- ================= ID BILLING ================= --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">
                                        ID Billing <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                        name="id_billing"
                                        id="id_billing"
                                        class="form-control"
                                        required>
                                </div>
                            </div>

                        </div>

                        {{-- ================= FOOTER ================= --}}
                        <div class="modal-footer">
                            <button type="button"
                                    class="btn btn-secondary"
                                    data-bs-dismiss="modal">
                                Batal
                            </button>

                            <button type="submit"
                                    class="btn btn-primary">
                                💾 Simpan Perubahan
                            </button>
                        </div>

                    </form>
                </div>

                {{-- ================= TAB LOG ================= --}}
                <div class="tab-pane fade" id="tab-log">
                    <div class="p-3">

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:160px">Tanggal</th>
                                        <th style="width:140px">User</th>
                                        <th style="width:320px">Sebelum</th>
                                        <th style="width:320px">Sesudah</th>
                                    </tr>
                                </thead>
                                <tbody id="log-koreksi"></tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
