@extends('Template.Layout')
@section('content')

<div class="card">
 <div class="card-body">
  <h4>{{ $title }}</h4>

  <button class="btn btn-primary mb-3" id="btnTambah">
   Tambah Akun Pajak
  </button>

  <table id="tbAkunPajak" class="table table-hover"></table>
 </div>
</div>

{{-- MODAL --}}
<div class="modal fade" id="modalAkun">
 <div class="modal-dialog">
  <form id="formAkun">
   @csrf
   <div class="modal-content">
    <div class="modal-header"><h5>Form Akun Pajak</h5></div>
    <div class="modal-body">
     <input class="form-control mb-2" name="kode_akun" placeholder="Kode Akun">
     <input class="form-control mb-2" name="nama_akun" placeholder="Nama Akun">
     <select class="form-control mb-2" name="jenis_pajak">
      <option value="">-- Jenis Pajak --</option>
      <option value="PPN">PPN</option>
      <option value="PPh">PPh</option>
     </select>
     <textarea class="form-control" name="keterangan" placeholder="Keterangan"></textarea>
    </div>
    <div class="modal-footer">
     <button class="btn btn-primary">Simpan</button>
    </div>
   </div>
  </form>
 </div>
</div>

<script>
let table = $('#tbAkunPajak').DataTable({
    ajax:"{{ url('/master/akun-pajak/data') }}",
    columns:[
        {data:'DT_RowIndex',title:'No'},
        {data:'kode_akun',title:'Kode'},
        {data:'nama_akun',title:'Nama Akun'},
        {data:'jenis_pajak',title:'Jenis'},
        {data:'status',title:'Status'},
        {data:'aksi',title:'Aksi'}
    ]
});

$('#btnTambah').click(()=>$('#modalAkun').modal('show'));

$('#formAkun').submit(function(e){
 e.preventDefault();
 $.post("{{ url('/master/akun-pajak/store') }}",$(this).serialize(),()=>{
    Swal.fire('Berhasil','Data disimpan','success');
    $('#modalAkun').modal('hide');
    table.ajax.reload();
 });
});
</script>

@endsection
