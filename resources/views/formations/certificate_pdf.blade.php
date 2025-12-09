<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ __('messages.certificate') }} - {{ $driver->full_name ?? '' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; margin: 28px; color: #0f172a; }
        .header { text-align: center; margin-bottom: 20px; }
        .company { font-size: 24px; font-weight: 700; letter-spacing: 1px; color: #0f172a; }
        .title { font-size: 26px; font-weight: 700; margin: 8px 0 16px; color: #0f172a; }
        .subtitle { font-size: 14px; color: #475569; margin-bottom: 24px; }
        .card { border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; background: #f8fafc; }
        .row { display: flex; flex-wrap: wrap; margin: 0 -10px; }
        .col { flex: 1 0 50%; padding: 0 10px; margin-bottom: 12px; font-size: 14px; }
        .label { font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 4px; }
        .value { font-size: 14px; font-weight: 600; color: #0f172a; }
        .section { margin-top: 24px; }
        .signature { margin-top: 40px; display: flex; justify-content: space-between; }
        .signature-box { width: 45%; text-align: center; }
        .line { margin-top: 50px; border-top: 1px solid #cbd5e1; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">Trans Marhaba</div>
        <div class="title">{{ __('messages.certificate_of_completion') ?? 'Certificate of Completion' }}</div>
        <div class="subtitle">{{ __('messages.certifies_completion') ?? 'This certifies that the following driver has successfully completed the referenced formation.' }}</div>
    </div>

    <div class="card">
        <div class="section">
            <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse;">
                <tr>
                    <td style="width:50%; vertical-align:top; padding-right:12px;">
                        <div class="label">{{ __('messages.driver') }}</div>
                        <div class="value" style="font-size:18px;">{{ $driver->full_name ?? '-' }}</div>
                    </td>
                    <td style="width:50%; vertical-align:top; padding-left:12px;">
                        <div class="label">{{ __('messages.done_at') ?? 'Done at' }}</div>
                        <div class="value">{{ $driverFormation->done_at ? $driverFormation->done_at->format('d/m/Y') : '-' }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="label">{{ __('messages.formation_information') ?? 'Formation Information' }}</div>
            <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse; margin-top:6px;">
                <tr>
                    <td class="col">
                        <div class="label">{{ __('messages.formation_theme') }}</div>
                        <div class="value">{{ $formation->theme ?? '-' }}</div>
                    </td>
                    <td class="col">
                        <div class="label">{{ __('messages.formation_duration') }}</div>
                        <div class="value">{{ $formation->duree ? $formation->duree . ' ' . __('messages.days') : '-' }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="col">
                        <div class="label">{{ __('messages.formation_delivery_type') }}</div>
                        <div class="value">{{ $formation->delivery_type === 'externe' ? __('messages.formation_delivery_external') : __('messages.formation_delivery_internal') }}</div>
                    </td>
                    <td class="col">
                        <div class="label">{{ __('messages.flotte') }}</div>
                        <div class="value">{{ $formation->flotte->name ?? __('messages.not_assigned') }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="signature">
            <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse;">
                <tr>
                    <td style="width:50%; padding-right:12px; text-align:center; vertical-align:bottom;">
                        <div class="label">{{ __('messages.driver') }}</div>
                        <div class="value">{{ $driver->full_name ?? '-' }}</div>
                        <div class="line"></div>
                        <div class="label">{{ __('messages.signature') ?? 'Signature' }}</div>
                    </td>
                    <td style="width:50%; padding-left:12px; text-align:center; vertical-align:bottom;">
                        <div class="label">{{ __('messages.formation_organisme') ?? 'Organization' }}</div>
                        <div class="value">Trans Marhaba</div>
                        <div class="line"></div>
                        <div class="label">{{ __('messages.signature') ?? 'Signature' }}</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>

