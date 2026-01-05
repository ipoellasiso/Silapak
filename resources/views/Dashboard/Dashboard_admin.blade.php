@extends('Template.Layout')
@section('content')

<section class="row">
    <div class="col-12 col-lg-12">
        <div class="row">
            <div class="col-6 col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body px-3 py-4-5">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="stats-icon purple">
                                    <i class="iconly-boldDocument"></i>
                                    {{-- <i class="bi bi-coin"></i> --}}
                                </div>
                            </div>
                            <div class="col-md-8">
                                <h6 class="text-muted font-semibold">TOTAL SP2D LS</h6>
                                {{-- <h6 class="font-extrabold mb-0">{{ number_format($total_ls) }}</h6> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body px-3 py-4-5">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="stats-icon blue">
                                    <i class="iconly-boldDocument"></i>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <h6 class="text-muted font-semibold">TOTAL SP2D GU</h6>
                                {{-- <h6 class="font-extrabold mb-0">{{ number_format($total_gu) }}</h6> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body px-3 py-4-5">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="stats-icon green">
                                    <i class="iconly-boldDocument"></i>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <h6 class="text-muted font-semibold">TOTAL SP2D KESELURUHAN</h6>
                                {{-- <h6 class="font-extrabold mb-0">{{ number_format($total_all) }}</h6> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body px-3 py-4-5">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="stats-icon red">
                                    <i class="iconly-boldDiscovery"></i>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <h6 class="text-muted font-semibold">Jumlah SP2D</h6>
                                {{-- <h6 class="font-extrabold mb-0">{{$total_all1}}</h6> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
    </div>
</section>

@endsection