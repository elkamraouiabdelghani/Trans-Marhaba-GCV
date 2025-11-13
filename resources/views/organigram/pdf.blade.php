@php
    use Illuminate\Support\Str;
    use Carbon\Carbon;

    $members = $members ?? collect();
    $lastUpdateDate = $lastUpdateDate ?? null;

    $textFor = function (string $label, array $positions) use ($members): string {
        $names = collect($positions)
            ->flatMap(fn ($position) => ($members->get($position) ?? collect())->pluck('name'))
            ->filter()
            ->map(fn ($name) => Str::upper($name));

        $title = Str::upper($label);

        return $names->isEmpty() ? $title : $title . "\n" . $names->implode(' / ');
    };
@endphp

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Organigramme</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 24px 40px 32px;
            font-family: "Times New Roman", serif;
            background: white;
            color: #000;
        }

        .page {
            width: 100%;
            max-width: 1070px;
            margin: 0 auto;
            background: white;
            padding: 24px 28px 36px;
            border: 1.8px solid #000;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 36px;
            table-layout: fixed;
        }

        .header-table td {
            border: 1.5px solid #000;
            background: white;
            text-transform: uppercase;
            color: #000;
            letter-spacing: .08em;
            padding: 12px 16px;
            font-size: 12px;
            vertical-align: middle;
        }

        .header-table td.title-cell {
            background: linear-gradient(180deg, #3d3c07 0%, #262500 100%);
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #dcdcdc;
            letter-spacing: .12em;
        }

        .header-table td strong {
            display: block;
            font-size: 17px;
            color: #000;
        }

        .header-table td small {
            font-size: 11px;
            color: #000;
        }

        .header-table td .type-value {
            margin-top: 5px;
            font-weight: bold;
            font-size: 13px;
        }

        .header-table td.right {
            text-align: right;
        }

        .header-table td.right span {
            display: block;
            font-size: 12px;
        }

        .header-table td.right strong {
            font-size: 14px;
        }

        .org-container {
            width: 100%;
            text-align: center;
        }

        .org-row {
            width: 100%;
            margin: 0 auto 28px;
            text-align: center;
        }

        .org-row.single {
            margin-bottom: 28px;
        }

        .org-row.multi {
            position: relative;
            margin-bottom: 28px;
        }

        .org-row.multi::before {
            content: "";
            position: absolute;
            top: -18px;
            left: 5%;
            width: 90%;
            height: 3px;
            background: #000;
        }

        .node {
            display: inline-block;
            position: relative;
            background: #fdfdfd;
            border: 3px solid #000;
            padding: 12px 14px;
            min-width: 150px;
            max-width: 165px;
            text-align: center;
            text-transform: uppercase;
            font-weight: bold;
            font-size: 14px;
            line-height: 1.35;
            white-space: pre-line;
            color: #000;
            margin: 0 6px;
            vertical-align: top;
        }

        .node.has-parent::before {
            content: "";
            position: absolute;
            top: -26px;
            left: 50%;
            margin-left: -1.5px;
            width: 3px;
            height: 26px;
            background: #000;
        }

        .vertical-spacer {
            width: 3px;
            height: 32px;
            background: #000;
            margin: 0 auto 28px;
            display: block;
        }

        .org-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .org-table td {
            padding: 0;
            vertical-align: top;
            text-align: center;
        }

        .org-table td .node {
            display: block;
            margin: 0 auto;
        }

        .org-table td.branch-column {
            padding-bottom: 8px;
        }

        .branch-connector {
            width: 3px;
            height: 26px;
            background: #000;
            margin: 0 auto 18px;
            display: block;
        }

        .branch-children {
            text-align: center;
        }

        .branch-children-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto;
        }

        .branch-children-table td {
            vertical-align: top;
            text-align: center;
            padding: 0 8px;
        }

        .branch-children-table .node {
            display: block;
            margin: 0 auto 14px;
            max-width: 140px;
        }

        .branch-children-table .node:last-child {
            margin-bottom: 0;
        }

        .branch-children-table .node:first-child::before {
            content: "";
            position: absolute;
            top: -22px;
            left: 50%;
            margin-left: -1.5px;
            width: 3px;
            height: 22px;
            background: #000;
        }

        .branch-children-table .node:not(:first-child)::before {
            content: "";
            position: absolute;
            top: -18px;
            left: 50%;
            margin-left: -1.5px;
            width: 3px;
            height: 18px;
            background: white;
        }

        .branch-children-left-col {
            text-align: center;
        }

        .branch-children-right-col {
            text-align: center;
        }

        .branch-children-right-col .node {
            margin-top: 0;
        }
    </style>
</head>
<body>
    <div class="w-100">
        <table class="header-table">
            <tr>
                <td style="width: 24%;">
                    <strong>Trans Marhaba</strong>
                    <small>Type de document :</small>
                    <span class="type-value">Formulaire</span>
                </td>
                <td class="title-cell" style="width: 52%; color: #000 !important;">Organigramme Transmarhaba</td>
                <td class="right" style="width: 24%;">
                    <span>Réf :</span>
                    <strong>Revision : {{ str_pad($revision ?? 0, 2, '0', STR_PAD_LEFT) }}</strong>
                    <span style="margin-top:8px;">Date d'application :</span>
                    <strong>{{ $lastUpdateDate ? Carbon::parse($lastUpdateDate)->format('d/m/Y') : now()->format('d/m/Y') }}</strong>
                </td>
            </tr>
        </table>

        <div class="org-container">
            <div class="org-row single">
                <div class="node">
                    {{ $textFor('DG', ['DG']) }}
                </div>
            </div>

            <div class="vertical-spacer"></div>

            <div class="org-row single">
                <div class="node has-parent">
                    {{ $textFor('DGA', ['DGA']) }}
                </div>
            </div>

            <div class="vertical-spacer" style="margin-bottom: 28px;"></div>

            <div class="org-row multi">
                <table class="org-table">
                    <tr>
                        <td>
                            <div class="node has-parent">
                                {{ $textFor('COMPTABILITE', ['COMPTABILITE']) }}
                            </div>
                        </td>
                        <td>
                            <div class="node has-parent">
                                {{ $textFor('IT ET OBC', ['IT', 'OBC']) }}
                            </div>
                        </td>
                        <td>
                            <div class="node has-parent">
                                {{ $textFor('RH', ['RH']) }}
                            </div>
                        </td>
                        <td>
                            <div class="node has-parent">
                                {{ $textFor('HSSE', ['HSSE']) }}
                            </div>
                        </td>
                        <td class="branch-column">
                            <div class="node has-parent">
                                {{ $textFor('DEPOT ET EXPLOITATION', ['DEPOT_ET_EXPLOITATION']) }}
                            </div>
                            <span class="branch-connector"></span>
                            <div class="branch-children">
                                <table class="branch-children-table">
                                    <tr>
                                        <td class="branch-children-left-col">
                                            <div class="node">
                                                {{ $textFor('MAINTENANCE', ['MAINTENANCE']) }}
                                            </div>
                                            <div class="node">
                                                {{ $textFor('DEPOT', ['DEPOT']) }}
                                            </div>
                                            <div class="node">
                                                {{ $textFor('CHAUFFEURS', ['CHAUFFEURS']) }}
                                            </div>
                                        </td>
                                        <td class="branch-children-right-col">
                                            <div class="node">
                                                {{ $textFor('MONITEUR', ['MONITEUR']) }}
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

