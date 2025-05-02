<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
  <title>Performance Report — {{ $reportDate }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f9fafb;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;color:#1a1a1a;line-height:1.5;">

  <!-- Outer wrapper -->
  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
      <td align="center" style="padding:20px;">
        
        <!-- Inner container -->
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" style="background-color:#ffffff;border-radius:8px;overflow:hidden;">
          
          <!-- Header -->
          <tr>
            <td align="center" bgcolor="#2c3e50" style="padding:24px;">
              <img src="{{ url('logo.svg') }}" width="60" alt="T360 Logo" style="display:block;margin-bottom:16px;border:0;">
              <h1 style="margin:0;font-size:24px;font-weight:600;color:#ffffff;">Performance Report</h1>
              <p style="margin:8px 0 0;font-size:16px;color:#ffffff;opacity:0.9;">{{ $reportDate }}</p>
            </td>
          </tr>

          @if(!$dataAvailability['performance'] && !$dataAvailability['safety'])
          <!-- No Data Available Message -->
          <tr>
            <td style="padding:32px 24px;text-align:center;">
              <div style="font-size:18px;font-weight:600;color:#2c3e50;margin-bottom:16px;">No Data Available</div>
              <p style="font-size:16px;color:#4b5563;margin-bottom:16px;">
                We apologize, but we don't have any performance or safety data available for {{ $reportDate }} yet.
              </p>
              <p style="font-size:16px;color:#4b5563;">
                A complete report will be sent as soon as the data becomes available.
              </p>
            </td>
          </tr>
          @else
          
          <!-- Operational Excellence Score -->
          @if($dataAvailability['performance'])
          <tr>
            <td style="padding:24px;border-bottom:1px solid #eaeaea;">
              <h2 style="margin:0 0 16px;font-size:18px;font-weight:600;color:#2c3e50;">Operational Excellence Score</h2>
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                  <td style="background:#f9fafb;padding:12px;border-radius:6px;text-align:center;">
                    <strong style="display:block;margin-bottom:4px;color:#4b5563;">Overall Performance Score</strong>
                    <span style="font-size:18px;font-weight:600;{{ strtolower($operationalExcellenceScore)=='good'||str_contains(strtolower($operationalExcellenceScore),'fantastic')?'color:#10b981;':(strtolower($operationalExcellenceScore)=='poor'?'color:#ef4444;':'color:#f59e0b;') }}">
                      {{ $operationalExcellenceScore }}
                    </span>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Performance Summary -->
          <tr>
            <td style="padding:24px;border-bottom:1px solid #eaeaea;">
              <h2 style="margin:0 0 16px;font-size:18px;font-weight:600;color:#2c3e50;">Performance Summary </h2>
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="font-size:14px;">
                <thead>
                  <tr>
                    <th align="left" style="padding:10px;border:1px solid #eaeaea;background:#f5f7fa;font-weight:600;color:#4b5563;">Metric</th>
                    <th align="left" style="padding:10px;border:1px solid #eaeaea;background:#f5f7fa;font-weight:600;color:#4b5563;">Value</th>
                    <th align="left" style="padding:10px;border:1px solid #eaeaea;background:#f5f7fa;font-weight:600;color:#4b5563;">Rating</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ([
                    ['Acceptance Rate', number_format($performanceMain['avg_acceptance'] ?? 0,1).'%', ucfirst($performanceRatings['average_acceptance'] ?? 'N/A')],
                    ['On-Time Rate', number_format($performanceMain['avg_on_time'] ?? 0,1).'%', ucfirst($performanceRatings['average_on_time'] ?? 'N/A')],
                    ['Safety Bonus Met', ($performanceMain['meets_safety'] ?? 0) ? 'Yes' : 'No', ucfirst($performanceRatings['meets_safety_bonus_criteria'] ?? 'N/A')],
                    ['Open BOC', $performanceRolling['sum_open_boc'] ?? 0, ucfirst($performanceRatings['open_boc'] ?? 'N/A')],
                    ['VCR Preventable', $performanceRolling['sum_vcr_preventable'] ?? 0, ucfirst($performanceRatings['vcr_preventable'] ?? 'N/A')],
                    ['VMCR P', $performanceRolling['sum_vmcr_p'] ?? 0, ucfirst($performanceRatings['vmcr_p'] ?? 'N/A')],
                    ['MVtS', number_format($mvtsPercent,1).'%', ucfirst($performanceRatings['average_maintenance_variance_to_spend'] ?? 'N/A')],
                  ] as $row)
                    @php
                      $rating = strtolower($row[2]);
                      $color = $rating==='good' ? '#10b981' : ($rating==='poor' ? '#ef4444' : '#f59e0b');
                    @endphp
                    <tr style="background-color:{{ $loop->even ? '#f9fafb' : '#ffffff' }};">
                      <td style="padding:10px;border:1px solid #eaeaea;">{{ $row[0] }}</td>
                      <td style="padding:10px;border:1px solid #eaeaea;font-weight:500;">{{ $row[1] }}</td>
                      <td style="padding:10px;border:1px solid #eaeaea;font-weight:500;color:{{ $color }};">{{ $row[2] }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </td>
          </tr>
          @else
          <!-- No Performance Data Message -->
          <tr>
            <td style="padding:24px;border-bottom:1px solid #eaeaea;text-align:center;">
              <h2 style="margin:0 0 16px;font-size:18px;font-weight:600;color:#2c3e50;">Performance Data</h2>
              <p style="font-size:16px;color:#4b5563;">
                We apologize, but we don't have any performance data available for {{ $reportDate }} yet.
              </p>
              <p style="font-size:14px;color:#6b7280;margin-top:8px;">
                A complete report will be sent as soon as the data becomes available.
              </p>
            </td>
          </tr>
          @endif

          <!-- Safety Alerts & Rates (if any) -->
          @if($dataAvailability['safety'])
          <tr>
            <td style="padding:24px;border-bottom:1px solid #eaeaea;">
              <h2 style="margin:0 0 16px;font-size:18px;font-weight:600;color:#2c3e50;">Safety Alerts &amp; Rates</h2>
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="font-size:14px;">
                <thead>
                  <tr>
                    <th align="left" style="padding:10px;border:1px solid #eaeaea;background:#f5f7fa;font-weight:600;color:#4b5563;">Alert Type</th>
                    <th align="left" style="padding:10px;border:1px solid #eaeaea;background:#f5f7fa;font-weight:600;color:#4b5563;">Count</th>
                    <th align="left" style="padding:10px;border:1px solid #eaeaea;background:#f5f7fa;font-weight:600;color:#4b5563;">Rate (per 1 000 h)</th>
                    <th align="left" style="padding:10px;border:1px solid #eaeaea;background:#f5f7fa;font-weight:600;color:#4b5563;">Rating</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($safetyRates as $metric => $rate)
                    @php
                      $label = ucwords(str_replace('_',' ',$metric));
                      $count = $safetyAggregate[$metric] ?? 0;
                      $ratingText = ucfirst($safetyRatings[$metric] ?? 'N/A');
                      $ratingColor = strtolower($ratingText)==='good' ? '#10b981' : (strtolower($ratingText)==='poor' ? '#ef4444' : '#f59e0b');
                    @endphp
                    <tr style="background-color:{{ $loop->even ? '#f9fafb' : '#ffffff' }};">
                      <td style="padding:10px;border:1px solid #eaeaea;">{{ $label }}</td>
                      <td style="padding:10px;border:1px solid #eaeaea;font-weight:500;">{{ $count }}</td>
                      <td style="padding:10px;border:1px solid #eaeaea;font-weight:500;">{{ number_format($rate,2) }}</td>
                      <td style="padding:10px;border:1px solid #eaeaea;font-weight:500;color:{{ $ratingColor }};">{{ $ratingText }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>

              <!-- Infractions -->
              <h3 style="margin:20px 0 12px;font-size:16px;font-weight:600;color:#2c3e50;">Infractions</h3>
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="font-size:14px;">
                <thead>
                  <tr>
                    <th align="left" style="padding:10px;border:1px solid #eaeaea;background:#f5f7fa;font-weight:600;color:#4b5563;">Infraction</th>
                    <th align="left" style="padding:10px;border:1px solid #eaeaea;background:#f5f7fa;font-weight:600;color:#4b5563;">Count</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($safetyInfractions as $inf => $cnt)
                    <tr style="background-color:{{ $loop->even ? '#f9fafb' : '#ffffff' }};">
                      <td style="padding:10px;border:1px solid #eaeaea;">{{ ucwords(str_replace('_',' ',$inf)) }}</td>
                      <td style="padding:10px;border:1px solid #eaeaea;font-weight:500;">{{ $cnt }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>

              <!-- Safety summary -->
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top:16px;">
                <tr>
                  <td style="background:#f9fafb;padding:12px;border-radius:6px;text-align:center;">
                    <strong style="display:block;margin-bottom:4px;color:#4b5563;">Average Driver Score</strong>
                    <span style="font-size:18px;font-weight:600;">{{ number_format($safetyAggregate['avg_driver_score'] ?? 0,1) }}</span>
                  </td>
                  <td style="background:#f9fafb;padding:12px;border-radius:6px;text-align:center;">
                    <strong style="display:block;margin-bottom:4px;color:#4b5563;">Total Hours Analyzed</strong>
                    <span style="font-size:18px;font-weight:600;">{{ number_format(($safetyAggregate['total_minutes'] ?? 0)/60,1) }}</span>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          @else
          <!-- No Safety Data Message -->
          <tr>
            <td style="padding:24px;border-bottom:1px solid #eaeaea;text-align:center;">
              <h2 style="margin:0 0 16px;font-size:18px;font-weight:600;color:#2c3e50;">Safety Data</h2>
              <p style="font-size:16px;color:#4b5563;">
                We apologize, but we don't have any safety data available for {{ $reportDate }} yet.
              </p>
              <p style="font-size:14px;color:#6b7280;margin-top:8px;">
                A complete report will be sent as soon as the data becomes available.
              </p>
            </td>
          </tr>
          @endif
          @endif

          <!-- Footer -->
          <tr>
            <td align="center" style="padding:20px;background-color:#f9fafb;font-size:13px;color:#6b7280;">
              © {{ date('Y') }} T360. Automated report; please do not reply.
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>
