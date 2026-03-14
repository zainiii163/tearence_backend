<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Alert Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #fff;
            padding: 30px;
            border: 1px solid #e0e0e0;
            border-top: none;
        }
        .job-card {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            transition: box-shadow 0.3s ease;
        }
        .job-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .job-title {
            color: #2c3e50;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .company-name {
            color: #7f8c8d;
            font-size: 16px;
            margin-bottom: 8px;
        }
        .job-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        .badge-blue {
            background: #e3f2fd;
            color: #1976d2;
        }
        .badge-green {
            background: #e8f5e8;
            color: #4caf50;
        }
        .badge-red {
            background: #ffebee;
            color: #f44336;
        }
        .job-description {
            color: #555;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 15px;
        }
        .view-job-btn {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
            transition: background 0.3s ease;
        }
        .view-job-btn:hover {
            background: #0056b3;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            border: 1px solid #e0e0e0;
            border-top: none;
            border-radius: 0 0 10px 10px;
            color: #6c757d;
        }
        .stats {
            background: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔔 New Jobs Match Your Alert</h1>
        <p>{{ $alert->title }}</p>
    </div>

    <div class="content">
        <div class="stats">
            <strong>{{ $totalJobs }} new jobs</strong> match your criteria since your last alert.
        </div>

        @if($jobs->count() > 0)
            @foreach($jobs->take(10) as $job)
                <div class="job-card">
                    <div class="job-title">{{ $job->title }}</div>
                    <div class="company-name">{{ $job->company_name }} • {{ $job->country }}{{ $job->city ? ', ' . $job->city : '' }}</div>
                    
                    <div class="job-meta">
                        <span class="badge badge-blue">{{ ucfirst(str_replace('_', ' ', $job->work_type)) }}</span>
                        @if($job->is_urgent)
                            <span class="badge badge-red">Urgent</span>
                        @endif
                        @if($job->is_featured)
                            <span class="badge badge-green">Featured</span>
                        @endif
                        @if($job->salary_range)
                            <span class="badge badge-blue">{{ $job->salary_range }} {{ $job->currency }}</span>
                        @endif
                    </div>
                    
                    <div class="job-description">
                        {!! Str::limit(strip_tags($job->description), 150) !!}
                    </div>
                    
                    <a href="{{ url('/jobs#' . $job->id) }}" class="view-job-btn">View Job Details</a>
                </div>
            @endforeach

            @if($jobs->count() > 10)
                <p style="text-align: center; color: #6c757d;">
                    And {{ $jobs->count() - 10 }} more jobs... 
                    <a href="{{ url('/jobs') }}" style="color: #007bff;">View all jobs</a>
                </p>
            @endif
        @else
            <p>No new jobs match your criteria at this time. We'll keep searching and notify you when new opportunities arise!</p>
        @endif

        <div style="margin-top: 30px; padding: 20px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px;">
            <h4 style="color: #856404; margin-bottom: 10px;">🔍 Manage Your Alerts</h4>
            <p style="color: #856404; margin-bottom: 15px;">
                You can update your alert preferences or create new alerts to find more opportunities.
            </p>
            <a href="{{ url('/dashboard/job-alerts') }}" style="background: #ffc107; color: #212529; padding: 8px 16px; text-decoration: none; border-radius: 4px; font-weight: 500;">
                Manage Alerts
            </a>
        </div>
    </div>

    <div class="footer">
        <p>This alert was sent because you subscribed to job notifications on WorldwideAdverts.</p>
        <p>
            <a href="{{ url('/jobs') }}" style="color: #007bff;">Browse All Jobs</a> | 
            <a href="{{ url('/unsubscribe/alerts/' . $alert->id) }}" style="color: #6c757d;">Unsubscribe</a>
        </p>
        <p style="font-size: 12px; margin-top: 15px;">
            © {{ date('Y') }} WorldwideAdverts. All rights reserved.
        </p>
    </div>
</body>
</html>
