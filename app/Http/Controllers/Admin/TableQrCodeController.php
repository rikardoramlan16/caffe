<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CafeTable;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TableQrCodeController extends Controller
{
    public function show(Request $request, CafeTable $table): Response
    {
        $branchId = $request->session()->get('auth_user.branch_id');
        abort_if($branchId && (int) $table->branch_id !== (int) $branchId, 403);

        $payload = route('qr.login', $table->code);

        $renderer = new ImageRenderer(
            new RendererStyle(320, 16),
            new SvgImageBackEnd()
        );

        $svg = (new Writer($renderer))->writeString($payload, 'UTF-8', ErrorCorrectionLevel::M());

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
