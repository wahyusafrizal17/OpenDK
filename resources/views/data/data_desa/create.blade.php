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
        <li><a href="{{ route('data.data-desa.index') }}">Data Desa</a></li>
        <li class="active">{{ $page_title }}</li>
    </ol>
</section>

<!-- Main content -->
<section class="content container-fluid">
    <div class="row">
        <div class="col-md-12">
            @include( 'partials.flash_message' )
            
                <!-- form start -->
                {!! Form::open( [ 'route' => 'data.data-desa.store', 'method' => 'post','id' => 'datadesa-ektp', 'class' => 'form-horizontal form-label-left'] ) !!}

                <div class="box-body">

                    @if (count($errors) > 0)
                        <div class="alert alert-danger">
                            <strong>Oops!</strong> Ada yang salah dengan inputan Anda.<br><br>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @include('data.data_desa.form_create')

                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    <div class="pull-right">
                        <div class="control-group">
                            <a href="{{ route('data.data-desa.index') }}">
                                <button type="button" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i> Batal
                                </button>
                            </a>
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-save"></i> Simpan
                            </button>
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
@include(('partials.asset_select2'))
@push('scripts')
<script>

    $(function () {

        const host = '<?= config('app.host_pantau'); ?>';
        const token = '<?= config('app.token_pantau'); ?>';
        const nama_provinsi = '<?= $profil->nama_provinsi ?>';
        const nama_kabupaten = '<?= $profil->nama_kabupaten ?>';
        const nama_kecamatan = '<?= $profil->nama_kecamatan ?>';

        $.ajax({
            type: 'GET',
            url: host + '/index.php/api/wilayah/list_wilayah?token=' + token + '&provinsi=' + nama_provinsi + '&kabupaten=' + nama_kabupaten + '&kecamatan=' + nama_kecamatan,
            dataType: 'json',
            success: function(data) {
                var html = '<option value="" selected>-- Pilih Desa --</option>';
                var i;
                for(i=0; i<data.length; i++) {
                    html += '<option value="' + data[i].nama_desa + '" data-kode="' + data[i].kode_desa + '">' + data[i].nama_desa + '</option>';
                }
                $('#list_desa').html(html);
            }
        });
        $('#list_desa').select2();

        $("#list_desa").change(function () {
            desa_id = $('#list_desa option:selected').data('kode');
            $('#desa_id').val(desa_id);
        });
    })
</script>
@endpush