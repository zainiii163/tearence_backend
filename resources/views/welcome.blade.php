<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WorldWideAdverts - Global Job Board & Marketplace</title>
    <meta name="description" content="WorldWideAdverts - Find your dream job or post opportunities. Connect employers and job seekers worldwide.">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            width: 100%;
        }
        
        .welcome-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .header {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            padding: 60px 40px;
            text-align: center;
            color: white;
        }
        
        .header h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 16px;
            letter-spacing: -0.02em;
        }
        
        .header p {
            font-size: 1.25rem;
            opacity: 0.95;
            font-weight: 400;
        }
        
        .content {
            padding: 60px 40px;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }
        
        .feature-card {
            padding: 30px;
            border-radius: 16px;
            background: #f8f9fa;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 28px;
        }
        
        .feature-card h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 12px;
            color: #1a202c;
        }
        
        .feature-card p {
            color: #718096;
            line-height: 1.6;
        }
        
        .cta-section {
            text-align: center;
            padding: 40px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            color: white;
        }
        
        .cta-section h2 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .cta-section p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 30px;
        }
        
        .btn-group {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 16px 40px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            border: 2px solid transparent;
        }
        
        .btn-primary {
            background: white;
            color: #667eea;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .btn-secondary {
            background: transparent;
            color: white;
            border-color: white;
        }
        
        .btn-secondary:hover {
            background: white;
            color: #667eea;
            transform: translateY(-2px);
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin-top: 50px;
            padding-top: 50px;
            border-top: 2px solid #e2e8f0;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
        }
        
        .stat-label {
            color: #718096;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.05em;
        }
        
        @media (max-width: 768px) {
            .header h1 {
                font-size: 2.5rem;
            }
            
            .header {
                padding: 40px 20px;
            }
            
            .content {
                padding: 40px 20px;
            }
            
            .btn {
                padding: 14px 30px;
                width: 100%;
            }
            
            .btn-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="welcome-card">
            <div class="header">
                <h1>🌍 WorldWideAdverts</h1>
                <p>Your Gateway to Global Opportunities</p>
            </div>
            
            <div class="content">
                <div class="features">
                    <div class="feature-card">
                        <div class="feature-icon">💼</div>
                        <h3>Job Board</h3>
                        <p>Find your dream job or post opportunities. Connect with employers and job seekers worldwide with our advanced job matching system.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">👥</div>
                        <h3>Candidate Profiles</h3>
                        <p>Create professional profiles, showcase your skills, and get discovered by top employers. Boost your visibility with premium features.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">🔔</div>
                        <h3>Job Alerts</h3>
                        <p>Never miss an opportunity. Set up personalized job alerts and get notified when new positions match your criteria.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">⭐</div>
                        <h3>Premium Features</h3>
                        <p>Stand out with featured listings, suggested jobs, and priority matching. Get more visibility and better results.</p>
                    </div>
                </div>
                
                <div class="cta-section">
                    <h2>Ready to Get Started?</h2>
                    <p>Access the admin panel to manage jobs, candidates, and revenue</p>
                    <div class="btn-group">
                        <a href="/admin" class="btn btn-primary">Admin Dashboard</a>
                        <a href="/admin/login" class="btn btn-secondary">Admin Login</a>
                    </div>
                </div>
                
                <div class="stats">
                    <div class="stat-item">
                        <div class="stat-number">10K+</div>
                        <div class="stat-label">Active Jobs</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">5K+</div>
                        <div class="stat-label">Candidates</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">500+</div>
                        <div class="stat-label">Companies</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">50+</div>
                        <div class="stat-label">Countries</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
