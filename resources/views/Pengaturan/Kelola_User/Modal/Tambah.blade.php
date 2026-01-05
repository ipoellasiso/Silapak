<div class="modal modal-right fade " id="tambahuser">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="side-modal-wrapper">
                <div class="modal-header">
                    <h4 class="modal-title">Tambah Data User</h4>
                </div>
                {{-- <div class="vertical-align"> --}}
                    {{-- <div class="table-cell"> --}}
                        <form id="userForm" name="userForm" method="POST" action="/user/store" enctype="multipart/form-data">
                        <div class="modal-body">
                            <input type="hidden" name="id" id="id">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group mb-4">
                                        {{-- <label>OPD</label> --}}
                                        <select class="form-select" name="id_opd" id="id_opd" style="width: 100%" required>
                                            <option></option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-4">
                                        {{-- <label>Nama</label> --}}
                                        <input type="text" class="form-control" name="fullname" id="fullname" value="" placeholder="Nama Lengkap ...." required>
                                    </div>
                                {{-- </div> --}}
                                {{-- <div class="col-12"> --}}
                                    <div class="form-group mb-4">
                                        {{-- <label>Email</label> --}}
                                        <input type="text" class="form-control" name="email" id="email" value="" placeholder="Email ...." required>
                                    </div>
                                {{-- </div> --}}
                                {{-- <div class="col-12"> --}}
                                    <div class="form-group mb-4">
                                        {{-- <label>Password</label> --}}
                                        <input type="text" class="form-control" name="password" id="password" value="" placeholder="Password ....">
                                    </div>
                                {{-- </div> --}}
                                {{-- <div class="col-12"> --}}
                                    <div class="form-group mb-4">
                                        {{-- <label>Role</label> --}}
                                        <select class="form-control" name="role" id="role" value="" required>
                                            <option value="" hidden>Pilih role</option>
                                            <option value="Admin">Admin</option>
                                            <option value="User">User</option>
                                            {{-- <option value="Verifikasi">Verifikasi</option> --}}
                                            {{-- <option value="Pegawai">Pegawai</option> --}}
                                        </select>
                                    </div>
                                {{-- </div> --}}
                                {{-- <div class="col-12"> --}}
                                    <div class="form-group mb-4">
                                        <label>Upload Foto</label>
                                        <input type="file" class="form-control" name="gambar" id="gambar" accept="image/*" onchange="readURL(this);">
                                        <input type="hidden" name="hidden_image" id="hidden_image">
                                        <small>Upload Foto Harus Format JPG,JPEG / PNS dan Max File 5MB </small>
                                    </div>
                                    <div class="form-group">
                                        <img id="modal-preview" src="https://via/placeholder.com/150" alt="Preview" class="form-group hidden" width="100" height="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" id="saveBtn" value="create" class="btn btn-secondary">
                                <i class="fa fa-save"></i>  Simpan
                            </button>

                        </div>
                    </form>
                    {{-- </div> --}}
                {{-- </div> --}}
            </div>
        </div>
    </div>
</div>