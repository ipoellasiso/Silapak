@extends('Template.Layout')

@section('content')
<div class="card">
  <div class="card-body">
    <h4>Audit Log Perubahan</h4>

    <table id="auditTable" class="table table-bordered table-hover">
      <thead>
        <tr>
          <th>No</th>
          <th>User</th>
          <th>Aksi</th>
          <th>Tabel</th>
          <th>ID Data</th>
          <th>Waktu</th>
          <th>Data Lama</th>
          <th>Data Baru</th>
        </tr>
      </thead>
    </table>
  </div>
</div>


<script>
$('#auditTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: "{{ url('/audit-log/data') }}",
    columns: [
        { data: 'DT_RowIndex', orderable:false, searchable:false },
        { data: 'user_name' },
        { data: 'action' },
        { data: 'table_name' },
        { data: 'record_id' },
        { data: 'created_at' },
        { data: 'old_data' },
        { data: 'new_data' },
    ]
});
</script>

@endsection


