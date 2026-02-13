<div class="modal fade" id="tambahbku" data-bs-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" role="document">"document"> --}}
        
        <div class="modal-content">
            {{-- <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel16">Tambah Data BKU</h4>
                <button type="button" class="close" data-bs-dismiss="modal"
                    aria-label="Close">
                    <i data-feather="x"></i>
                </button>
            </div> --}}
            <div class="modal-body">
                <form id="userForm" name="userForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="id_transaksi" id="id_transaksi">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Rekening</label>
                                    <select class="form-select" name="id_rekening" id="id_rekening" style="width: 100%" required>
                                        <option></option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>OPD</label>
                                    <select class="form-select" name="id_opd" id="id_opd" style="width: 100%" required>
                                        <option></option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>BANK</label>
                                    <select class="form-select" name="id_bank" id="id_bank" style="width: 100%" required>
                                        <option></option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Nomor BKU</label>
                                    <input type="text" class="form-control" name="no_buku" id="no_buku" value="" placeholder="" required>
                                </div>
                                <div class="form-group">
                                    <label>Tanggal BKU</label>
                                    <input type="date" class="form-control" name="tgl_transaksi" id="tgl_transaksi" value="" placeholder="" required>
                                </div>
                                <div class="form-group">
                                    <label>Uraian</label>
                                    <input class="form-control" id="uraian" name="uraian" required>
                                    </input>
                                </div>
                                <div class="form-group">
                                    <label>Nilai</label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text">Rp.</span>
                                        <input type="text" class="form-control amount" name="nilai_transaksi" id="nilai_transaksi"
                                            aria-label="Amount (to the nearest dollar)"
                                            placeholder="000.000.000">
                                        <span class="input-group-text">,00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">
                            <i class="anticon anticon-undo"></i> Close
                        </button>
                        <button type="submit" id="saveBtn" value="create" class="btn btn-outline-primary">
                            <i class="fa fa-save"></i>  Simpan
                        </button>
    
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

