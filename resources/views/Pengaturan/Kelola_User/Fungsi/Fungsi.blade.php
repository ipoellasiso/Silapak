<script type="text/javascript">
    $(function () {

      /*------------------------------------------
       --------------------------------------------
       Pass Header Token
       --------------------------------------------
       --------------------------------------------*/
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

      /*------------------------------------------
      --------------------------------------------
      Render DataTable
      --------------------------------------------
      --------------------------------------------*/
    var table = $('#data-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "/tampiluser",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'nama_opd', name: 'nama_opd'},
            {data: 'fullname', name: 'fullname'},
            {data: 'email', name: 'email'},
            {data: 'role', name: 'role'},
            {data: 'is_active1', name: 'is_active1'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ]
    });

    // tambah data
    $('#createUser').click(function (){
        $('#saveBtn').val("create-user");
        $('#id').val('');
        $('#userForm').trigger("reset");
        $('#tambahuser').modal('show');
        $('#modal-preview').attr('src', 'https://via/placeholder.com/150');

    });

    // edit data
    $('body').on('click', '.editUser', function()  {
        var iduser = $(this).data('id');
        $.get("/user/edit/"+iduser, function (data) {
            $('#saveBtn').val("edit-user");
            $('#tambahuser').modal('show');
            $('#id').val(data.id);
            $('#fullname').val(data.fullname);
            $('#email').val(data.email);
            $('#role').val(data.role);
            // $('#id_opd').val(data.id_opd);
            $("#id_opd").html('<option value = "'+data.id_opd+'" selected >'+data.nama_opd+'</option>');

            $('#modal-preview').attr('alt', 'No image available');
            if(data.gambar){
                $('#modal-preview').attr('src','app/assets/images/user/'+data.gambar);
                $('#hidden_image').attr('src','app/assets/images/user/'+data.gambar);
            }
        })
    });

    // simpan data
    $('body').on('submit', '#userForm', function(e){
        e.preventDefault();

        var actionType = $('#saveBtn').val();
        $('#saveBtn').html('Tunggu..');

        var formData = new FormData(this);

        $.ajax({
            type:'POST',
            url: "/user/store",
            data: formData,
            cacha: false,
            contentType: false,
            processData: false,
            success: (data) => {
                if(data.success)
                {
                    $('#userForm').trigger("reset");
                    $('#tambahuser').modal('hide');
                    $('#saveBtn').html('Simpan');

                    Swal.fire({
                        icon: "success",
                        title: "success",
                        text: "Data Berhasil Disimpan"
                    })

                    table.draw();
                }
                else
                {
                    $('#userForm').trigger("reset");
                    $('#tambahopd').modal('hide');
                    $('#saveBtn').html('Simpan');

                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Data Email Sudah Ada"
                    })

                    table.draw();
                }
            },
            error: function(data){
                console.log('Error:', data);
                $('saveBtn').html('Simpan');
            }
        });
    });

    // hapus data
    $('body').on('click', '.deleteUser', function () {

        var id = $(this).data("id");

        Swal.fire({
            title: 'Warning ?',
            text: "Hapus Data Ini ?"  +id,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Delete!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "DELETE",
                    url: "/user/destroy/"+id,
                    dataType: "JSON",
                    success: function(data)
                    {
                        Swal.fire({
                            icon: "success",
                            title: "Success",
                            text: data.success
                        })
                        table.draw();
                    },
                });
            }else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Data Gagal Dihapus"
                })
            }
        })
    });

    $('#id_opd').select2({
	    placeholder: "Pilih Opd",
    	allowClear: true,
        dropdownParent: $('#tambahuser'),
	    ajax: { 
            url: "/user/opd",
            type: "Get",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    searchOpd: params.term // search term
                };
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
                cache: true
            }
    });


    $('body').on('click', '.nonaktifUser', function () {

        var id = $(this).data("id");

        Swal.fire({
            title: 'Warning ?',
            text: "Nonaktifkan Data Ini ?"+id,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Nonaktifkan!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: "/user/nonaktif/"+id,
                    dataType: "JSON",
                    success: function(data)
                    {
                        Swal.fire({
                            icon: "success",
                            title: "Success",
                            text: data.success
                        })
                        table.draw();
                    },
                });
            }else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Data Gagal Dinonaktifkan"
                })
            }
        })
        });

        $('body').on('click', '.aktifUser', function () {

        var id = $(this).data("id");

        Swal.fire({
        title: 'Warning ?',
        text: "Aktif Data Ini ?"+id,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Aktikan!'
        }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "POST",
                url: "/user/aktif/"+id,
                dataType: "JSON",
                success: function(data)
                {
                    Swal.fire({
                        icon: "success",
                        title: "Success",
                        text: data.success
                    })
                    table.draw();
                },
            });
        }else {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Data Gagal Diaktifkan"
            })
        }
        })
        });

});

function readURL(input, id) {
    id = id || '#modal-preview';
    if (input.files && input.files[0]){
        var reader = new FileReader();

        reader.onload = function (e) {
            $(id).attr('src', e.target.result);
        };

        reader.readAsDataURL(input.files[0]);
        $('#modal-preview').removeClass('hidden');
        $('#start').hide();
    }
}

</script>

{{-- <script>
    $(document).ready(function(){

        $("#id_opd1").select2({

        });
    });
</script> --}}

<script>
    function deleteData(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel!',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/delete/' + id,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire(
                            'Deleted!',
                            'Your data has been deleted.',
                            'success'
                        );
                    },
                    error: function(response) {
                        Swal.fire(
                            'Error!',
                            'There was an error deleting your data.',
                            'error'
                        );
                    }
                });
            }
        });
    }
</script>   