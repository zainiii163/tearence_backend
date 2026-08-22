<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>WWA Security Alert</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.5;">
    <h2 style="color: #b91c1c;">Website security alert</h2>
    <p>{{ $alertMessage }}</p>

    <table cellpadding="6" cellspacing="0" style="border-collapse: collapse; margin-top: 16px;">
        @foreach (($details ?? []) as $key => $value)
            @if (!is_array($value) && $value !== null && $value !== '')
                <tr>
                    <td style="font-weight: bold; vertical-align: top;">{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                    <td>{{ is_bool($value) ? ($value ? 'yes' : 'no') : $value }}</td>
                </tr>
            @endif
        @endforeach
    </table>

    <p style="margin-top: 24px; color: #475569; font-size: 13px;">
        Super Admin and IT can review full login history in the admin backend under
        <strong>Security → Login Activity</strong>.
    </p>
</body>
</html>
