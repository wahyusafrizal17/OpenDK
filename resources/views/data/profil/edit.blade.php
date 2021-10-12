@extends('layouts.dashboard_template')

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        {{ $page_title ?? "Page Title" }}
        <small>{{ $page_description ?? '' }}</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="active">Profil</li>
    </ol>
</section>

<!-- Main content -->
<section class="content container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                {{-- <div class="box-header with-border">
                    <h3 class="box-title">Aksi</h3>
                </div>--}}
                <!-- /.box-header -->

                @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <strong>Whoops!</strong> There were some problems with your input.<br><br>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>

                    </div>

                    @endif

                            <!-- form start -->
                    {!!  Form::model($profil, [ 'route' => ['data.profil.update', $profil->id], 'method' => 'put','id' => 'form-profil', 'class' => 'form-horizontal form-label-left', 'files'=>true] ) !!}

                    <div class="box-body">


                        @include( 'flash::message' )
                        @include('data.profil.form_edit')

                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer">
                        <div class="pull-right">
                            <div class="control-group">
                                <a href="{{ route('data.profil.index') }}">
                                    <button type="button" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i> Batal</button>
                                </a>
                                <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-save"></i> Simpan</button>
                            </div>
                        </div>
                    </div>
                    {!! Form::close() !!}
            </div>
        </div>
    </div>
    <!-- /.row -->

</section>
<!-- /.content -->
@endsection

@include('partials.asset_wysihtml5')
@include(('partials.asset_select2'))
@push('scripts')
<script>

    $(function () {

        const host = '<?= config('app.host_pantau'); ?>';
        const token = '<?= config('app.token_pantau'); ?>';

        $.ajax({
            type: 'GET',
            url: host + '/index.php/api/wilayah/list_wilayah?token=' + token,
            dataType: 'json',
            success: function(data) {
                var html = '<option value="" selected>-- Pilih Provinsi --</option>';
                var i;
                for(i=0; i<data.length; i++) {
                    html += '<option value="' + data[i].nama_prov + '" data-kode="' + data[i].kode_prov + '">' + data[i].nama_prov + '</option>';
                }
                $('#list_provinsi').html(html);
                $("#list_provinsi").val('{{ $profil->nama_provinsi }}').trigger("change");
            }
        });
        $('#list_provinsi').select2();

        $("#list_provinsi").change(function () {

            provinsi_id = $('#list_provinsi option:selected').data('kode');
            nama_provinsi = $('#list_provinsi option:selected').val();
            $('#provinsi_id').val(provinsi_id);

            $.ajax({
                type: 'GET',
                url: host + '/index.php/api/wilayah/list_wilayah?token=' + token + '&provinsi=' + nama_provinsi,
                dataType: 'json',
                success: function(data) {
                    var html = '<option value="" selected>-- Pilih Kabupaten --</option>';
                    var i;
                    for(i=0; i<data.length; i++) {
                        html += '<option value="' + data[i].nama_kab + '" data-kode="' + data[i].kode_kab + '">'+data[i].nama_kab + '</option>';
                    }
                    $('#list_kabupaten').html(html);
                    $('#list_kabupaten').removeAttr("disabled");
                    $('#list_kecamatan').addClass('disabled');
                    $("#list_kabupaten").val('{{ $profil->nama_kabupaten }}').trigger("change");
                }
            });
        });
        $('#list_kabupaten').select2();

        $("#list_kabupaten").change(function () {

            kabupaten_id = $('#list_kabupaten option:selected').data('kode');
            nama_kabupaten = $('#list_kabupaten option:selected').val();
            $('#kabupaten_id').val(kabupaten_id);

            $.ajax({
                type: 'GET',
                url: host + '/index.php/api/wilayah/list_wilayah?token=' + token + '&provinsi=' + nama_provinsi + '&kabupaten=' + nama_kabupaten,
                dataType: 'json',
                success: function(data) {
                    var html = '<option value="" selected>-- Pilih {{ $sebutan_wilayah }} --</option>';
                    var i;
                    for(i=0; i<data.length; i++) {
                        html += '<option value="' + data[i].nama_kec + '"data-kode="' + data[i].kode_kec + '">'+data[i].nama_kec + '</option>';
                    }
                    $('#list_kecamatan').html(html);
                    $('#list_kecamatan').removeAttr("disabled");
                    $("#list_kecamatan").val('{{ $profil->nama_kecamatan }}').trigger("change");
                }
            });
        });
        $('#list_kecamatan').select2();

        $("#list_kecamatan").change(function () {
            kecamatan_id = $('#list_kecamatan option:selected').data('kode');
            nama_kecamatan = $('#list_kecamatan option:selected').val();
            $('#kecamatan_id').val(kecamatan_id);
            $('#nama_kecamatan').val(nama_kecamatan);
        });

        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#showgambar').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        function readURL2(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#showgambar2').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        function readURL3(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#showgambar3').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#file_struktur").change(function () {
            readURL(this);
        });
        
        $("#foto_kepala_wilayah").change(function () {
            readURL2(this);
        });

        $("#file_logo").change(function () {
            readURL3(this);
        });

        $('.textarea').wysihtml5();
    })
</script>
@endpush
