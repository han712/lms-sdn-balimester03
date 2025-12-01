<div class="card border-left-{{ $color }}">
    <div class="card-body">
        <div class="row no-gutters align-items-center">
            <div class="col mr-2">
                <div class="text-xs font-weight-bold text-{{ $color }} text-uppercase mb-1">
                    {{ $title }}
                </div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">
                    {{ $value }}
                </div>
            </div>
            <div class="col-auto">
                <i class="fas {{ $icon }} fa-2x text-gray-300"></i>
            </div>
        </div>
        @if($link)
        <a href="{{ $link }}" class="card-footer text-center small text-muted">
            Lihat Detail <i class="fas fa-arrow-right"></i>
        </a>
        @endif
    </div>
</div>