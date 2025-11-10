<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport de Formation</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1F2937;
            margin: 0;
            padding: 24px;
            background: #FFFFFF;
        }
        h1 {
            font-size: 20px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 24px;
            color: #111827;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        th, td {
            padding: 10px 12px;
            border: 1px solid #E5E7EB;
            vertical-align: top;
        }
        th {
            background-color: #F3F4F6;
            width: 35%;
            text-align: left;
            font-weight: 600;
            letter-spacing: 0.3px;
        }
        td {
            background-color: #FFFFFF;
        }
        .footer {
            text-align: right;
            font-size: 10px;
            color: #6B7280;
        }
    </style>
</head>
<body>
    <h1>Rapport de Formation</h1>

    <table>
        <tr>
            <th>Site</th>
            <td>{{ $site }}</td>
        </tr>
        <tr>
            <th>Flotte</th>
            <td>{{ $flotte }}</td>
        </tr>
        <tr>
            <th>Type de formation</th>
            <td>{{ $type }}</td>
        </tr>
        <tr>
            <th>Conducteur</th>
            <td>{{ $driver }}</td>
        </tr>
        <tr>
            <th>Thème</th>
            <td>{{ $theme }}</td>
        </tr>
        <tr>
            <th>Animateur</th>
            <td>{{ $animateur }}</td>
        </tr>
        <tr>
            <th>Date de début</th>
            <td>{{ $start_date }}</td>
        </tr>
        <tr>
            <th>Statut</th>
            <td>{{ $status }}</td>
        </tr>
        <tr>
            <th>Date prévue</th>
            <td>{{ $date_prevu }}</td>
        </tr>
        <tr>
            <th>Feedback TBX</th>
            <td>{{ $feedback_tbx }}</td>
        </tr>
        <tr>
            <th>NBR</th>
            <td>{{ $nbr }}</td>
        </tr>
        <tr>
            <th>Note de formation (%)</th>
            <td>{{ $note_formation }}</td>
        </tr>
        <tr>
            <th>Feedback Formation</th>
            <td>{{ $feedback_formation }}</td>
        </tr>
    </table>

    <div class="footer">
        Rapport généré le {{ $generated_at }}
    </div>
</body>
</html>

