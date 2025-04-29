<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Report — {{ $reportDate }}</title>
    <style>
      /* Base styles */
      body { 
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        color: #1a1a1a; 
        max-width: 800px; 
        margin: 0 auto;
        line-height: 1.5;
        background-color: #f9fafb;
        padding: 0;
      }
      .container {
        background-color: #ffffff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin: 20px auto;
      }
      .header { 
        background: #2c3e50; 
        color: #fff; 
        padding: 24px; 
        text-align: center;
        border-bottom: 1px solid rgba(255,255,255,0.1);
      }
      .logo {
        margin-bottom: 16px;
        display: block;
        text-align: center;
      }
      .logo img {
        max-height: 60px;
        width: auto;
      }
      .header h1 {
        margin: 0 0 8px 0;
        font-weight: 600;
        font-size: 24px;
      }
      .header p {
        margin: 0;
        opacity: 0.9;
        font-size: 16px;
      }
      .section { 
        margin: 0; 
        padding: 24px; 
        border-bottom: 1px solid #eaeaea;
      }
      h2 { 
        margin-top: 0; 
        color: #2c3e50; 
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 16px;
      }
      h3 {
        font-size: 16px;
        font-weight: 600;
        color: #2c3e50;
        margin: 20px 0 12px 0;
      }
      table { 
        width: 100%; 
        border-collapse: collapse; 
        margin-bottom: 8px;
        font-size: 14px;
      }
      th, td { 
        padding: 10px 12px; 
        border: 1px solid #eaeaea; 
        text-align: left; 
      }
      th { 
        background: #f5f7fa; 
        font-weight: 600;
        color: #4b5563;
      }
      tr:nth-child(even) {
        background-color: #f9fafb;
      }
      .metric-value {
        font-weight: 500;
      }
      .rating {
        font-weight: 500;
      }
      .rating-good {
        color: #10b981;
      }
      .rating-average {
        color: #f59e0b;
      }
      .rating-poor {
        color: #ef4444;
      }
      .footer { 
        text-align: center; 
        font-size: 13px; 
        color: #6b7280; 
        padding: 20px;
        background-color: #f9fafb;
      }
      .footer p {
        margin: 0;
      }
      .summary-data {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        margin-top: 16px;
      }
      .summary-item {
        background: #f9fafb;
        padding: 12px;
        border-radius: 6px;
        flex: 1;
        min-width: 180px;
      }
      .summary-item strong {
        display: block;
        margin-bottom: 4px;
        color: #4b5563;
      }
      .summary-value {
        font-size: 18px;
        font-weight: 600;
        color: #2c3e50;
      }
      .summary-value.rating-good {
        color: #10b981;
      }
      .summary-value.rating-average {
        color: #f59e0b;
      }
      .summary-value.rating-poor {
        color: #ef4444;
      }
      
      /* Ensure email client compatibility */
      @media only screen and (max-width: 600px) {
        .container {
          width: 100% !important;
          margin: 0 !important;
        }
        .section {
          padding: 16px !important;
        }
        .summary-item {
          min-width: 100% !important;
        }
      }
    </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <div class="logo">
        <img src="{{ url('favicon.ico') }}" alt="T360 Logo">
      </div>
      <h1>Performance Report</h1>
      <p>{{ $reportDate }}</p>
    </div>
    <div class="section">
      <h2>Operational Excellence Score</h2>
      <div class="score-card">
        <div class="summary-data">
          <div class="summary-item">
            <strong>Overall Performance Score</strong>
            <div class="summary-value {{ strtolower($operationalExcellenceScore) == 'good' ? 'rating-good' : (strtolower($operationalExcellenceScore) == 'poor' ? 'rating-poor' : (strtolower($operationalExcellenceScore) == 'fair' ? 'rating-average' : (strtolower($operationalExcellenceScore) == 'fantastic' || strtolower($operationalExcellenceScore) == 'fantastic +' ? 'rating-good' : 'rating-average'))) }}">{{ $operationalExcellenceScore }}</div>
          </div>
        </div>
      </div>
    </div>
    <div class="section">
      <h2>Performance Summary (6 Weeks)</h2>
      <table>
        <thead>
          <tr>
            <th>Metric</th>
            <th>Value</th>
            <th>Rating</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Acceptance Rate</td>
            <td class="metric-value">{{ number_format($performanceMain['avg_acceptance'] ?? 0,1) }}%</td>
            <td class="rating {{ strtolower($performanceRatings['average_acceptance'] ?? '') == 'good' ? 'rating-good' : (strtolower($performanceRatings['average_acceptance'] ?? '') == 'poor' ? 'rating-poor' : 'rating-average') }}">
              {{ ucfirst($performanceRatings['average_acceptance'] ?? 'N/A') }}
            </td>
          </tr>
          <tr>
            <td>On-Time Rate</td>
            <td class="metric-value">{{ number_format($performanceMain['avg_on_time'] ?? 0,1) }}%</td>
            <td class="rating {{ strtolower($performanceRatings['average_on_time'] ?? '') == 'good' ? 'rating-good' : (strtolower($performanceRatings['average_on_time'] ?? '') == 'poor' ? 'rating-poor' : 'rating-average') }}">
              {{ ucfirst($performanceRatings['average_on_time'] ?? 'N/A') }}
            </td>
          </tr>
          <tr>
            <td>Safety Bonus Met</td>
            <td class="metric-value">{{ ($performanceMain['meets_safety'] ?? 0) ? 'Yes' : 'No' }}</td>
            <td class="rating {{ strtolower($performanceRatings['meets_safety_bonus_criteria'] ?? '') == 'good' ? 'rating-good' : (strtolower($performanceRatings['meets_safety_bonus_criteria'] ?? '') == 'poor' ? 'rating-poor' : 'rating-average') }}">
              {{ ucfirst($performanceRatings['meets_safety_bonus_criteria'] ?? 'N/A') }}
            </td>
          </tr>
          <tr>
            <td>Open BOC</td>
            <td class="metric-value">{{ $performanceRolling['sum_open_boc'] ?? 0 }}</td>
            <td class="rating {{ strtolower($performanceRatings['open_boc'] ?? '') == 'good' ? 'rating-good' : (strtolower($performanceRatings['open_boc'] ?? '') == 'poor' ? 'rating-poor' : 'rating-average') }}">
              {{ ucfirst($performanceRatings['open_boc'] ?? 'N/A') }}
            </td>
          </tr>
          <tr>
            <td>VCR Preventable</td>
            <td class="metric-value">{{ $performanceRolling['sum_vcr_preventable'] ?? 0 }}</td>
            <td class="rating {{ strtolower($performanceRatings['vcr_preventable'] ?? '') == 'good' ? 'rating-good' : (strtolower($performanceRatings['vcr_preventable'] ?? '') == 'poor' ? 'rating-poor' : 'rating-average') }}">
              {{ ucfirst($performanceRatings['vcr_preventable'] ?? 'N/A') }}
            </td>
          </tr>
          <tr>
            <td>VMCR P</td>
            <td class="metric-value">{{ $performanceRolling['sum_vmcr_p'] ?? 0 }}</td>
            <td class="rating {{ strtolower($performanceRatings['vmcr_p'] ?? '') == 'good' ? 'rating-good' : (strtolower($performanceRatings['vmcr_p'] ?? '') == 'poor' ? 'rating-poor' : 'rating-average') }}">
              {{ ucfirst($performanceRatings['vmcr_p'] ?? 'N/A') }}
            </td>
          </tr>
          <tr>
            <td>MVtS</td>
            <td class="metric-value">{{ number_format($mvtsPercent,1) }}%</td>
            <td class="rating {{ strtolower($performanceRatings['average_maintenance_variance_to_spend'] ?? '') == 'good' ? 'rating-good' : (strtolower($performanceRatings['average_maintenance_variance_to_spend'] ?? '') == 'poor' ? 'rating-poor' : 'rating-average') }}">
              {{ ucfirst($performanceRatings['average_maintenance_variance_to_spend'] ?? 'N/A') }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    @if(($safetyAggregate['total_minutes'] ?? 0) > 0)
      <div class="section">
        <h2>Safety Alerts & Rates</h2>
        <table>
          <thead>
            <tr>
              <th>Alert Type</th>
              <th>Count</th>
              <th>Rate (per 1 000 h)</th>
              <th>Rating</th>
            </tr>
          </thead>
          <tbody>
            @foreach($safetyRates as $metric => $rate)
              <tr>
                <td>{{ ucwords(str_replace('_',' ',$metric)) }}</td>
                <td class="metric-value">{{ $safetyAggregate[$metric] ?? 0 }}</td>
                <td class="metric-value">{{ number_format($rate,2) }}</td>
                <td class="rating {{ strtolower($safetyRatings[$metric] ?? '') == 'good' ? 'rating-good' : (strtolower($safetyRatings[$metric] ?? '') == 'poor' ? 'rating-poor' : 'rating-average') }}">
                  {{ ucfirst($safetyRatings[$metric] ?? 'N/A') }}
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>

        <h3>Infractions</h3>
        <table>
          <thead>
            <tr>
              <th>Infraction</th>
              <th>Count</th>
            </tr>
          </thead>
          <tbody>
            @foreach($safetyInfractions as $inf => $cnt)
              <tr>
                <td>{{ ucwords(str_replace('_',' ',$inf)) }}</td>
                <td class="metric-value">{{ $cnt }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>

        <div class="summary-data">
          <div class="summary-item">
            <strong>Average Driver Score</strong>
            <div class="summary-value">{{ number_format($safetyAggregate['avg_driver_score'] ?? 0,1) }}</div>
          </div>
          <div class="summary-item">
            <strong>Total Hours Analyzed</strong>
            <div class="summary-value">{{ number_format(($safetyAggregate['total_minutes'] ?? 0)/60,1) }}</div>
          </div>
        </div>
      </div>
    @endif

    <div class="footer">
      <p>© {{ date('Y') }} T360. Automated report; please do not reply.</p>
    </div>
  </div>
</body>
</html>