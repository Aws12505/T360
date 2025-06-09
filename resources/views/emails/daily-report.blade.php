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
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="800" style="background-color:#ffffff;border-radius:8px;overflow:hidden;">
          
          <!-- Header -->
          <tr>
            <td align="center" bgcolor="#2c3e50" style="padding:24px;">
              <img src="{{ $logoCid }}" width="60" alt="T360 Logo" style="display:block;margin-bottom:16px;border:0;">
              <h1 style="margin:0;font-size:24px;font-weight:600;color:#ffffff;">Performance Report</h1>
              <p style="margin:8px 0 0;font-size:16px;color:#ffffff;opacity:0.9;">{{ $reportDate }}</p>
            </td>
          </tr>
          
          <!-- Greeting and Introduction -->
          <tr>
            <td style="padding:24px 24px 0;border-bottom:0;">
              <p style="margin:0 0 16px;font-size:16px;color:#1a1a1a;">Good morning {{ $userName }},</p>
              <p style="margin:0 0 24px;font-size:16px;color:#1a1a1a;">
                Here is your daily performance overview from Trucking 360. This summary reflects your most recent metrics and is designed to help you stay on track for Fantastic+ performance.
              </p>
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
              <h2 style="margin:0 0 16px;font-size:18px;font-weight:600;color:#2c3e50;">Operational Excellence Scores</h2>
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                  <td width="50%" style="padding-right:8px;">
                    <div style="margin-bottom:8px;font-weight:600;color:#4b5563;text-align:center;">Yesterday</div>
                    <div style="background:#f9fafb;padding:12px;border-radius:6px;text-align:center;border:1px solid #eaeaea;">
                      <span style="font-size:18px;font-weight:600;{{ 
                        strtolower($yesterdayOperationalExcellenceScore)=='good' ? 'color:#2563eb;' : 
                        (str_contains(strtolower($yesterdayOperationalExcellenceScore),'fantastic_plus') ? 'color:#10b981;' : 
                        (str_contains(strtolower($yesterdayOperationalExcellenceScore),'fantastic') ? 'color:#059669;' : 
                        (strtolower($yesterdayOperationalExcellenceScore)=='fair' ? 'color:#d97706;' : 
                        (strtolower($yesterdayOperationalExcellenceScore)=='poor' ? 'color:#dc2626;' : 'color:#4b5563;')))) 
                      }}">
                        {{ str_contains(strtolower($yesterdayOperationalExcellenceScore), 'fantastic_plus') ? 'Fantastic Plus' : ucfirst($yesterdayOperationalExcellenceScore) }}
                      </span>
                    </div>
                  </td>
                  <td width="50%" style="padding-left:8px;">
                    <div style="margin-bottom:8px;font-weight:600;color:#4b5563;text-align:center;">T6W</div>
                    <div style="background:#f9fafb;padding:12px;border-radius:6px;text-align:center;border:1px solid #eaeaea;">
                      <span style="font-size:18px;font-weight:600;{{ 
                        strtolower($operationalExcellenceScore)=='good' ? 'color:#2563eb;' : 
                        (str_contains(strtolower($operationalExcellenceScore),'fantastic_plus') ? 'color:#10b981;' : 
                        (str_contains(strtolower($operationalExcellenceScore),'fantastic') ? 'color:#059669;' : 
                        (strtolower($operationalExcellenceScore)=='fair' ? 'color:#d97706;' : 
                        (strtolower($operationalExcellenceScore)=='poor' ? 'color:#dc2626;' : 'color:#4b5563;')))) 
                      }}">
                        {{ str_contains(strtolower($operationalExcellenceScore), 'fantastic_plus') ? 'Fantastic Plus' : ucfirst($operationalExcellenceScore) }}
                      </span>
                    </div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Performance Summary -->
          <tr>
            <td style="padding:24px;border-bottom:1px solid #eaeaea;">
              <h2 style="margin:0 0 16px;font-size:18px;font-weight:600;color:#2c3e50;">Performance Summary</h2>
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="font-size:14px;">
                <thead>
                  <tr>
                    <th align="left" style="padding:10px;border:1px solid #eaeaea;background:#f5f7fa;font-weight:600;color:#4b5563;">Metric</th>
                    <th align="left" style="padding:10px;border:1px solid #eaeaea;background:#f5f7fa;font-weight:600;color:#4b5563;">Yesterday's Score</th>
                    <th align="left" style="padding:10px;border:1px solid #eaeaea;background:#f5f7fa;font-weight:600;color:#4b5563;">T6W Score</th>
                    <th align="left" style="padding:10px;border:1px solid #eaeaea;background:#f5f7fa;font-weight:600;color:#4b5563;">T6W Rating</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ([
                    ['Acceptance', 
                     number_format($performanceMain['average_acceptance'] ?? 0,1).'%', 
                     number_format($t6wPerformanceMain['average_acceptance'] ?? 0,1).'%', 
                     ucfirst($performanceRatings['average_acceptance'] ?? 'N/A')],
                    ['On-Time', 
                     number_format($performanceMain['average_on_time'] ?? 0,1).'%', 
                     number_format($t6wPerformanceMain['average_on_time'] ?? 0,1).'%', 
                     ucfirst($performanceRatings['average_on_time'] ?? 'N/A')],
                    ['MVtS', 
                     number_format($mvtsPercent,1).'%', 
                     number_format($mvtsPercent,1).'%', 
                     ucfirst($performanceRatings['average_maintenance_variance_to_spend'] ?? 'N/A')],
                    ['Open BOC', 
                     round($performanceRolling['sum_open_boc'] ?? 0), 
                     round($t6wPerformanceRolling['sum_open_boc'] ?? 0), 
                     ucfirst($performanceRatings['open_boc'] ?? 'N/A')],
                    ['VCR-P', 
                     round($performanceRolling['sum_vcr_preventable'] ?? 0), 
                     round($t6wPerformanceRolling['sum_vcr_preventable'] ?? 0), 
                     ucfirst($performanceRatings['vcr_preventable'] ?? 'N/A')],
                    ['VMCR-P', 
                     round($performanceRolling['sum_vmcr_p'] ?? 0), 
                     round($t6wPerformanceRolling['sum_vmcr_p'] ?? 0), 
                     ucfirst($performanceRatings['vmcr_p'] ?? 'N/A')],
                  ] as $row)
                    @php
                      $rating = strtolower($row[3]);
                      $displayRating = str_contains($rating, 'fantastic_plus') ? 'Fantastic Plus' : $row[3];
                      $color = $rating === 'good' ? '#2563eb' : 
                              ($rating === 'fantastic_plus' || str_contains($rating, 'fantastic_plus') ? '#10b981' : 
                              (str_contains($rating, 'fantastic') ? '#059669' : 
                              ($rating === 'fair' ? '#d97706' : 
                              ($rating === 'poor' ? '#dc2626' : '#4b5563'))));
                    @endphp
                    <tr style="background-color:{{ $loop->even ? '#f9fafb' : '#ffffff' }};">
                      <td style="padding:10px;border:1px solid #eaeaea;">{{ $row[0] }}</td>
                      <td style="padding:10px;border:1px solid #eaeaea;font-weight:500;">{{ $row[1] }}</td>
                      <td style="padding:10px;border:1px solid #eaeaea;font-weight:500;">{{ $row[2] }}</td>
                      <td style="padding:10px;border:1px solid #eaeaea;font-weight:500;color:{{ $color }};">{{ $displayRating }}</td>
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
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h2 style="margin:0;font-size:18px;font-weight:600;color:#2c3e50;">Safety Alerts &amp; Rates</h2>
                <div style="background:#f5f7fa;padding:8px 12px;border-radius:6px;border:1px solid #eaeaea;">
                  <span style="font-weight:600;color:#2c3e50;margin-right:6px;">Overall Safety Rating:</span>
                  <span style="font-weight:600;{{ strtolower($overallSafetyRating) === 'gold tier' ? 'color:#e17100;' : (strtolower($overallSafetyRating) === 'silver tier' ? 'color:#45556c;' : (strtolower($overallSafetyRating) === 'not eligible' ? 'color:#e7000b;' : 'color:#4b5563;')) }}">{{ $overallSafetyRating }}</span>
                </div>
              </div>
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="font-size:14px;">
                <thead>
                  <tr>
                    <th align="left" style="padding:10px;border:1px solid #eaeaea;background:#f5f7fa;font-weight:600;color:#4b5563;">Alert Type</th>
                    <th align="left" style="padding:10px;border:1px solid #eaeaea;background:#f5f7fa;font-weight:600;color:#4b5563;">Yesterday's Count</th>
                    <th align="left" style="padding:10px;border:1px solid #eaeaea;background:#f5f7fa;font-weight:600;color:#4b5563;">T6W Count</th>
                    <th align="left" style="padding:10px;border:1px solid #eaeaea;background:#f5f7fa;font-weight:600;color:#4b5563;">T6W Rating</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($safetyRates as $metric => $rate)
                    @php
                      $label = ucwords(str_replace('_',' ',$metric));
                      $yesterdayCount = round($safetyAggregate[$metric] ?? 0);
                      $t6wCount = round($t6wSafetyAggregate[$metric] ?? 0);
                      $ratingText = ucfirst($safetyRatings[$metric] ?? 'N/A');
                      $ratingLower = strtolower($ratingText);
                      $displayRating = str_contains($ratingLower, 'not_eligible') ? 'Not Eligible' : $ratingText;
                      $ratingColor = $ratingLower === 'gold' ? '#e17100' : 
                                    ($ratingLower === 'silver' || str_contains($ratingLower, 'fantastic_plus') ? '#45556c' : 
                                    (str_contains($ratingLower, 'fantastic') ? '#059669' : 
                                    ($ratingLower === 'not_eligible' ? '#e7000b' : 
                                    ($ratingLower === 'poor' ? '#dc2626' : '#4b5563'))));
                    @endphp
                    <tr style="background-color:{{ $loop->even ? '#f9fafb' : '#ffffff' }};">
                      <td style="padding:10px;border:1px solid #eaeaea;">{{ $label }}</td>
                      <td style="padding:10px;border:1px solid #eaeaea;font-weight:500;">{{ $yesterdayCount }}</td>
                      <td style="padding:10px;border:1px solid #eaeaea;font-weight:500;">{{ $t6wCount }}</td>
                      <td style="padding:10px;border:1px solid #eaeaea;font-weight:500;color:{{ $ratingColor }};">{{ $displayRating }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>

              <!-- Safety summary -->
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top:32px;">
                <tr>
                  <td width="33.33%" style="background:#f9fafb;padding:12px;border-radius:6px;text-align:center;border:1px solid #eaeaea;margin-right:8px;">
                    <strong style="display:block;margin-bottom:4px;color:#4b5563;">Average GreenZone Score</strong>
                    <span style="font-size:18px;font-weight:600;">{{ round($safetyAggregate['average_driver_score'] ?? 0) }}</span>
                  </td>
                  <td width="33.33%" style="background:#f9fafb;padding:12px;border-radius:6px;text-align:center;border:1px solid #eaeaea;margin:0 8px;">
                    <strong style="display:block;margin-bottom:4px;color:#4b5563;">Driver Star</strong>
                    <span style="font-size:18px;font-weight:600;">{{ round($safetyInfractions['driver_star'] ?? 0) }}</span>
                  </td>
                  <td width="33.33%" style="background:#f9fafb;padding:12px;border-radius:6px;text-align:center;border:1px solid #eaeaea;margin-left:8px;">
                    <strong style="display:block;margin-bottom:4px;color:#4b5563;">Total Hours Analyzed</strong>
                    <span style="font-size:18px;font-weight:600;">{{ round(($safetyAggregate['total_minutes_analyzed'] ?? 0)/60) }}</span>
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

          <!-- Dashboard Access Message -->
          <tr>
            <td style="padding:24px;border-bottom:1px solid #eaeaea;">
              <p style="margin:0 0 16px;font-size:16px;color:#1a1a1a;">
                If you'd like to review your complete performance data, including visuals and driver-level breakdowns, you can access your full report immediately through <a href="https://dashboard360.io/" style="color:#2563eb;text-decoration:underline;">Dashboard 360</a>.
              </p>
              <p style="margin:0 0 16px;font-size:16px;color:#1a1a1a;">
                Consistency is key—let's continue building toward Fantastic+ performance!
              </p>
              <p style="margin:0;font-size:16px;color:#1a1a1a;">
                Best regards,<br>
                Trucking 360 Team.
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td align="center" style="padding:20px;background-color:#f9fafb;font-size:13px;color:#6b7280;">
              © {{ date('Y') }} Trucking 360. Automated report; please do not reply.
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>
