<div class="dashboard-frame">
    <div class="mock-toolbar">
        <div class="dot-row"><span class="dot"></span><span class="dot"></span><span class="dot"></span></div>
        <span class="pill">Realtime</span>
    </div>
    <div class="mock-layout">
        <div class="mock-side">
            <div class="side-line" style="width: 82%"></div>
            <div class="side-line" style="width: 64%"></div>
            <div class="side-line" style="width: 72%"></div>
            <div class="side-line" style="width: 58%"></div>
        </div>
        <div class="mock-main">
            <div class="mock-grid">
                @foreach (($metrics ?? []) as $metric)
                    <div class="mini-card">
                        <div class="skeleton" style="width: 68%"></div>
                        <strong style="display:block;margin-top:18px">{{ $metric['value'] }}</strong>
                        <small class="muted">{{ $metric['label'] }}</small>
                    </div>
                @endforeach
            </div>
            <div class="chart">
                @foreach ([46, 62, 55, 82, 74, 92, 68, 88] as $height)
                    <span class="bar" style="height: {{ $height }}%"></span>
                @endforeach
            </div>
            <div class="grid grid-3" style="margin-top:14px">
                @foreach (($orders ?? []) as $order)
                    <div class="mini-card">
                        <strong>{{ $order['code'] }}</strong>
                        <div class="muted" style="margin-top:8px">{{ $order['status'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
