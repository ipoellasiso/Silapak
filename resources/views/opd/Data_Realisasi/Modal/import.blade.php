<div class="modal fade" id="tambahimportbku">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Import BKU</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <i class="anticon anticon-close"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="userForm1" name="userForm1" enctype="multipart/form-data" action="{{ route('bku.import') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="id" id="id">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Pilih File</label>
                                    <input type="file" class="form-control" name="file">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-secondary">
                            <i class="fa fa-save"></i>  Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
